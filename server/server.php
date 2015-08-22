<?php
function connection_handler($client, $world) {
    $pid = pcntl_fork();

    if ($pid == -1) {
        die('could not fork');
    } else if ($pid) {
        //we are parent process
        return;
    }

    $read = '';
    printf("[+] Client Connected\n");

    while (true) {
        $read = $client->read();
        if($read != '') {
            //parse the json here, and do something with it.
            $json = json_decode($read, true);
            if(is_array($json)) {
                switch($json['cmd']) {
                    case 'say':
                        //adds the message to the update queue, and returns success message to the client
                        $cmd = $json['cmd'];
                        $body = $json['body'];
                        $playerID = $json['playerID'];
                        $player_name = $json['player_name'];
                        $query = "INSERT INTO update_queue (updateID, playerID, update_type, update_body) VALUES (NULL, '$playerID', '$cmd', '$body');";
                        $send = $client->insert($query);
                        if($send) {
                            $msg = '[<b>' . $player_name . '</b>]:' . $body;
                            $client->send(json_encode(array('msg' => 'say-success', 'desc' => $msg)));
                        } else {
                            $errormsg = 'failed to send message';
                            $client->send(json_encode(array('msg' => 'error', 'desc' => $errormsg)));
                        }
                        break;
                    case 'move':
                        $body = $json['body'];
                        $playerID = $json['playerID'];
                        $locationX = $json['locationX'];
                        $locationY = $json['locationY'];

                        switch($body) {
                            case 'north': $locationX--; break;
                            case 'south': $locationX++; break;
                            case 'west': $locationY--; break;
                            case 'east': $locationY++; break;
                        }

                        $node = $world->get_node($locationX, $locationY);

                        //check if we can move
                        if($node->get_type() != 'Opaque Room') {
                            //move is valid, continue
                            $query = "UPDATE players SET locationX='$locationX', locationY='$locationY' WHERE playerID='$playerID';";
                            $send = $client->insert($query);
                            if($send) {
                                //return to client success, with description of room
                                $client->send(json_encode(array('msg' => 'move-success', 'desc' => $node->get_desc(), 'locationX' => $locationX, 'locationY' => $locationY)));
                            }
                        } else {
                            $errormsg = '[' . date('Y-m-d H:i:s') . ']: failed to move, check if valid';
                            $client->send(json_encode(array('msg' => 'error', 'desc' => $errormsg)));
                        }
                        break;
                    case 'update':
                        $playerID = $json['body']; //the playerID
                        printf("[+] Updating Client with playerID %d\n", $playerID);

                        //gets a list of updates for a player, works by comparing the requesting players last updateID, and returns all new updates since that time (excluding updates from the requesting player)
                        $query = "SELECT players.playerID, update_type, update_body, name, updateID FROM update_queue INNER JOIN players ON players.playerID=update_queue.playerID INNER JOIN (SELECT last_update, playerID FROM players WHERE playerID='$playerID') as a on a.playerID WHERE a.last_update < update_queue.updateID AND update_queue.playerID != '$playerID'";

                        $updates = $client->query($query);
                        $num_updates = count($updates);

                        //update our latest update ID
                        if ($num_updates > 0) {
                            $latest_update = $updates[$num_updates - 1]->updateID;
                            //update players last_update
                            $update_query = "UPDATE players SET last_update='$latest_update', last_update_time=NOW() WHERE playerID='$playerID'";
                            $client->insert($update_query);
                        } else {
                            $update_query = "UPDATE players SET last_update_time=NOW() WHERE playerID='$playerID'";
                            $client->insert($update_query);
                        }

                        //check other room occupants
                        $others = array();
                        $others_in_room = $world->other_occupants($playerID);
                        if(!empty($others_in_room)) {
                            foreach($others_in_room as $other) {
                                array_push($others, $other->get_name());
                            }
                        }

                        //send updates to the client
                        $client->send(json_encode(array('updates' => $updates, 'others' => $others)));
                        break;
                    case 'init':
                        //find an unactive player
                        $query = "SELECT * FROM players WHERE active='N'";
                        $player_list = $client->query($query);
                        mt_srand();
                        $chosen = mt_rand(0, count($player_list) - 1);
                        $selected = $player_list[$chosen];
                        $playerID = $selected->playerID;

                        //get highest update ID
                        $current_update_id = $client->query('SELECT updateID FROM update_queue ORDER BY updateID DESC LIMIT 1;');
                        if($current_update_id) {
                            $curr_id = $current_update_id[0]->updateID;
                            //update player to active, and set last_update
                            $update_player = "UPDATE players SET last_update='$curr_id', active='Y' WHERE playerID='$playerID'";
                            $client->insert($update_player);
                        } else {
                            $update_player = "UPDATE players SET active='Y' WHERE playerID='$playerID'";
                            $client->insert($update_player);
                        }

                        //get room desc
                        $node = $world->get_node($selected->locationX, $selected->locationY);

                        //send to client
                        $welcome_message = 'Welcome to Sutton Quest, <b>' . $selected->name . '</b>. Available commands are, move <direction>, i.e. "move north", say <message>, i.e. "say hello". You awaken to the noise of a man rushing past, brandishing a large fly swatter, yelling <i>"We can\'t stop here, this is bat country!"</i>.' . $node->get_desc();

                        $reply = array('worldstr' => $world->get_worldstr(), 'playerID' => $playerID, 'player_name' => $selected->name, 'locationX' => $selected->locationX, 'locationY' => $selected->locationY, 'welcome_message' => $welcome_message);
                        $client->send(json_encode($reply));
                        break;
                    default:
                        $client->send(json_encode(array('error' => 'badly formatted request')));
                        break;
                }
            } else {
                $client->send(json_encode(array('error' => 'badly formatted request')));
                break;
            }
        } else {
            break;
        }
        if ($read === null) {
            printf("[-] Client Disconnected\n");
            return false;
        } else {
            printf("[+] Received: %s\n", $read);
        }
    }

    $client->close();
    printf("[-] Client Disconnected\n");
}

require "suttonquestserver.php";

$server = new SuttonQuestServer();
$server->init();
$server->set_conn_handler('connection_handler');
$server->listen();
