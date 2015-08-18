<?php
function connection_handler($client) {
    $pid = pcntl_fork();

    if ($pid == -1) {
        die('could not fork');
    } else if ($pid) {
        //we are parent process
        return $pid;
    }

    printf("[+] Client Connected");
    //printf("[+] Client %s connected at port %d", $client->get_address(), $client->get_port());

    $client->connected();

    $read = '';

    while (true) {
        $read = $client->read();
        if($read == '') {
            break;
        }
        $client->send_broadcast($read);
    }

    $client->disconnected();
    printf("[-] Client Disconnected");
}

require "suttonquestserver.php";

$server = new SuttonQuestServer();
$server->init();
$server->set_conn_handler('connection_handler');
$server->listen();
