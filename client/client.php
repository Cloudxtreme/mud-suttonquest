<?php

//setup address and port
$address = '178.63.103.197';
$port = '5000';

$message = 'Hello World';

echo '<p>message to server:' . $message . '</p>';

$socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("could not create socket\n");
$result = socket_connect($socket, $address, $port) or die("could not connect\n");

socket_write($socket, $message, strlen($message)) or die("could not write to socket\n");

$result = socket_read($socket, 1024);
echo '<p>reply from server:' . $result . '</p>';

socket_close($socket);
