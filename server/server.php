<?php
function connection_handler($client) {
    $pid = pcntl_fork();

    if ($pid == -1) {
        die('could not fork');
    } else if ($pid) {
        //we are parent process
        return;
    }

    $read = '';

    printf("[+] Client Connected\n");
    //printf("[+] Client %s connected at port %d", $client->get_address(), $client->get_port());

    while (true) {
        $read = $client->read();
        if($read != '') {
            //parse the json here, and do something with it.
            $json = json_decode($read, true);
            if(!is_array($json)) {
                //bad message
                break;
            } else {
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
                        $query = "SELECT update_type, update_body FROM update_queue INNER JOIN players ON update_queue.playerID = players.playerID WHERE update_queue.playerID='$playerID' AND players.last_update < update_queue.time_queued;";
                        //if successful, update player last update time
                        $client->send(json_encode($client->query($query)));
                        break;
                    case 'initial_load':
                        //send world state, and set requesting playerID to last_update now()
                        break;
                    default:
                        $client->send(json_encode(array('error' => 'badly formatted request')));
                        break;
                }
            }

        } else {
            break;
        }
        /*
        if(preg_replace('/[^a-z]/', '', $read) == 'exit') {
            break;
        }*/
        if ($read === null) {
            printf("[-] Client Disconnected\n");
            return false;
        } else {
            //received from client
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
