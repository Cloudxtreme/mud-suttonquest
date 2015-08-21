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
                        //add to queue
                        $cmd = $json['cmd'];
                        $body = $json['body'];
                        $query = "INSERT INTO update_queue (updateID, playerID, time_queued, update_type, update_body) VALUES (NULL, 1, NOW(), '$cmd', '$body');";
                        $send = $client->insert($query);
                        if($send) {
                            $client->send('[' . date('g:i a') . ']: ' . 'message success');
                        } else {
                            $client->send('[' . date('g:i a') . ']: ' . 'message failure');
                        }
                        break;
                    case 'move':
                        $client->send('[' . date('g:i a') . ']: ' . $json['body']);
                        break;
                    case 'update':
                        $playerID = $json['body']; //the playerID
                        printf("[+] Updating Client with playerID %d", $playerID);
                        //$query = "SELECT update_type, update_body FROM update_queue INNER JOIN players ON update_queue.playerID = players.playerID WHERE update_queue.playerID='$playerID' AND players.last_update < update_queue.time_queued;";

                        //if successful, update player last update time
                        //$client->send(json_encode($client->query($query)));

                        //works
                        //$client->send($world->get_node(0, 0)->get_desc());
                        //works
                        //$client->send($world->players[0]->location['x'] . ',' . $world->players[0]->location['y'] );
                        break;
                    case 'init':
                        //pick random player from pool, set player to active.
                        //if player inactive for more than 10 seconds, set to inactive again, and respawn their location to base.
                        //5 players on monsters, 5 on humans.

                        $query = "SELECT * FROM players WHERE active='N'"; //send this to client, set to active
                        $reply = array('worldstr' => $world->get_worldstr(), 'playerID' => 1);
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
