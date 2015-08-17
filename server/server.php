<?php
set_time_limit(0);

//setup address, port, and clients
$address = '178.63.103.197';
$port = '5000';
$max_clients = 1000;
$clients = Array(); //holds our clients

//create the socket, bind it and listen
$sock = socket_create(AF_INET, SOCK_STREAM, 0);
socket_bind($sock, $address, $port);
socket_listen($sock);

while (true) {
    $read[0] = $sock;

    //setup sockets for reading
    for ($i = 0;$i < $max_clients; $i++) {
        if ($client[$i]['sock'] != null) {
            $read[$i + 1] = $client[$i]['sock'];
        }
    }

    //blocking call to socket_select()
    //$ready = $socket_select($read, null, null, null);

    if(in_array($sock, $read)) {
        for($i = 0; $i < $max_clients; $i++) {
            if($client[$i]['sock'] == null) {
                $client[$i]['sock'] = socket_accept($sock);
                break;
            } elseif ($i == $max_clients - 1) {
                //reached client capacity; abort
                print ("error: max clients");
            }
        }

        if (--$ready <= 0) {
            continue;
        }
    }

    for ($i = 0; $i < $max_clients; $i++) {
        if(in_array($client[$i]['sock'], $read)) {
            $input = socket_read($client[$i]['sock'], 1024);
            if ($input == null) {
                unset($client[$i]); //delete disconected client
            }

            $n = trim($input);

            if ($input == 'logout') {
                socket_close($client[$i]['sock']);
            } elseif ($input) {
                //strip output of whitespace
                $output = ereg_replace("[ \t\n\r]","",$input).chr(0);
                $socket_write($client[$i]['sock'], $output);

                //code to output to multiple clients
                $output = 'hello world';

                for ($j = 0; $j < max_clients; $j++) {
                    if ($client[$j]['sock'], $output);
                }
            }
        } else {
            socket_close($client[i]['sock']);
            unset($client[$i]);
        }
    }
}

socket_close($sock);
?>
