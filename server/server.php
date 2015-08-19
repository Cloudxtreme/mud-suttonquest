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

    printf("[+] Client Connected");
    //printf("[+] Client %s connected at port %d", $client->get_address(), $client->get_port());

    while (true) {
        $read = $client->read();
        if($read != '') {
            //write to client
            $client->send('[' . date('g:i a') . ']: ' . $read);
        } else {
            break;
        }

        if(preg_replace('/[^a-z]/', '', $read) == 'exit') {
            break;
        }
        if ($read === null) {
            printf("[-] Client Disconnected");
            return false;
        } else {
            //received from client
            printf("[+] Received: %s", $read);
        }
    }

    $client->close();
    printf("[-] Client Disconnected");
}

require "suttonquestserver.php";

$server = new SuttonQuestServer();
$server->init();
$server->set_conn_handler('connection_handler');
$server->listen();
