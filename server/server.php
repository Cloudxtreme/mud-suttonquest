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
            $client->send('[' . date() . ']: ' . $read);
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
            printf("[+] Sent Client %s", $read);
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
?>
