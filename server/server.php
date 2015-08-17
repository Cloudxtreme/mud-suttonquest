<?php
//prevent timeout
set_time_limit(0);

//setup address, port, and clients
$address = '178.63.103.197';
$port = '5000';
$max_clients = 10;
$clients = array(); //holds our clients

//create the socket, bind it and listen
$socket = socket_create(AF_INET, SOCK_STREAM, 0);
socket_bind($socket, $address, $port);
socket_listen($socket);

/* simple version
$client = socket_accept($socket);
$input = socket_read($client, 1024);
$output = ereg_replace("[ \t\n\r]","",$input).chr(0);
socket_write($client, $output);
socket_close($client);
*/

while (true) {
    $read[0] = $socket;

    //setup sockets for reading
    for ($i = 0;$i < $max_clients; $i++) {
        if ($clients[$i]['socket'] != null) {
            $read[$i + 1] = $clients[$i]['socket'];
        }
    }

    //blocking call to socket_select()
    $ready = $socket_select($read, null, null, null);

    if(in_array($socket, $read)) {
        for($i = 0; $i < $max_clients; $i++) {
            if($clients[$i]['socket'] == null) {
                $clients[$i]['socket'] = socket_accept($socket);
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
        if(in_array($clients[$i]['socket'], $read)) {
            $input = socket_read($clients[$i]['socket'], 1024);
            if ($input == null) {
                unset($clients[$i]); //delete disconected clients
            }

            $n = trim($input);

            if ($input == 'logout') {
                socket_close($client[$i]['socket']);
            } elseif ($input) {
                //strip output of whitespace
                $output = ereg_replace("[ \t\n\r]","",$input).chr(0);
                $socket_write($clients[$i]['socket'], $output);
                /*
                //code to output to multiple clientss
                $output = 'hello world';

                for ($j = 0; $j < max_clientss; $j++) {
                    if ($client[$j]['socket'], $output);
                }*/
            }
        } else {
            socket_close($clients[i]['socket']);
            unset($clients[$i]);
        }
    }
}

socket_close($socket);
?>
