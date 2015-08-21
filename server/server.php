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
                        //adds the message to the update queue
                        $cmd = $json['cmd'];
                        $body = $json['body'];
                        $playerID = $json['playerID'];
                        $query = "INSERT INTO update_queue (updateID, playerID, time_queued, update_type, update_body) VALUES (NULL, '$playerID', NOW(), '$cmd', '$body');";
                        $send = $client->insert($query);
                        if($send) {
                            //$client->send('[' . date('Y-m-d H:i:s') . '][' . $player_name . ']: ' . $body);
                        } else {
                            $client->send('[' . date('Y-m-d H:i:s') . ']' . 'message send failure');
                        }
                        break;
                    case 'move':
                        //adds the message to the update queue
                        $cmd = $json['cmd'];
                        $body = $json['body'];
                        $playerID = $json['playerID'];
                        $locationX = $json['locationX'];
                        $locationY = $json['locationY'];

                        switch($body) {
                            case 'north': $locationX--; break;
                            case 'south': $locationX++; break;
                            case 'west': $locationY--; break;
                            case 'east': $locationX--; break;
                        }

                        $query = "UPDATE players SET locationX='$locationX', locationY='$locationY' WHERE playerID='$playerID';";
                        $send = $client->insert($query);
                        if($send) {
                            //add to the update_queue
                            $locationstr = $locationX . ',' . $locationY;
                            $move_query = "INSERT INTO update_queue (updateID, playerID, time_queued, update_type, update_body) VALUES (NULL, '$playerID', NOW(), '$cmd', '$locationstr');";
                            $client->insert($move_query);
                        } else {
                            $client->send('[' . date('Y-m-d H:i:s') . '][' . $player_name . ']: ' . 'failed to move, check if valid');
                        }
                        break;
                    case 'update':
                        $playerID = $json['body']; //the playerID
                        printf("[+] Updating Client with playerID %d\n", $playerID);

                        //gets a list of updates for a player, works by comparing the requesting players last update time, and returns all new updates since that time
                        $query = "SELECT a.last_update, time_queued, update_type, update_body, players.playerID, name FROM update_queue INNER JOIN players ON players.playerID=update_queue.playerID INNER JOIN (SELECT last_update, playerID FROM players WHERE playerID='$playerID') AS a ON a.playerID WHERE a.last_update < update_queue.time_queued;";

                        //send updates to the client
                        $client->send(json_encode($client->query($query)));

                        //update last_update time
                        $update_query = "UPDATE players SET last_update=NOW() WHERE playerID='$playerID'";
                        $client->insert($update_query);
                        break;
                    case 'init':
                        //pick random player from pool, set player to active.
                        //if player inactive for more than 10 seconds, set to inactive again, and respawn their location to base.
                        //5 players on monsters, 5 on humans

                        //find an unactive player
                        $query = "SELECT * FROM players WHERE active='N'";
                        $player_list = $client->query($query);
                        $playerID = rand(0, count($player_list) - 1);
                        $selected = $player_list[$playerID];

                        //update player to active, and set last_update
                        $update_query = "UPDATE players SET last_update=NOW() WHERE playerID='$playerID'";
                        $client->insert($update_query);

                        //send to client
                        $welcome_message = 'Welcome to Sutton Quest [' . $selected->name . ']. You awaken to the noise of a man rushing past, brandishing a large fly swatter, yelling "We can\'t stop here, this is bat country!".';
                        $reply = array('worldstr' => $world->get_worldstr(), 'playerID' => $selected->playerID, 'player_name' => $selected->name, 'locationX' => $selected->locationX, 'locationY' => $selected->locationY, 'welcome_message' => $welcome_message);
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
