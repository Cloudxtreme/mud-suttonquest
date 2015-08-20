<?php

require_once "suttonquestclient.php";
require_once "model.php";

class SuttonQuestServer {
    private $address = '178.63.103.197';
    private $port = '5000';
    private $socket_server;
    private $max_clients;
    private $_listen;
    private $conn_handler;
    private $world;

    public function __construct($max_clients = 10) {
        $this->_listen = false;
        $this->max_clients = $max_clients;
        $this->world = new World('world.txt');
    }

    public function init() {
        //generate world here
        $this->create_socket();
        $this->bind_Socket();
    }

    public function set_conn_handler($handler) {
        $this->conn_handler = $handler;
    }

    public function listen() {
        //add some error checking
        socket_listen($this->socket_server, $this->max_clients);
        $this->_listen = true;

        printf("Server started, listening on port %d", $this->port);
        printf("testing: %s", $this->world->get_node(0, 9)->get_type());

        //listening loop
        while($this->_listen) {
            //add some error checking
            //send world state here in connection handler.
            //perform updates here too?

            $client = socket_accept($this->socket_server);
            $socket_client = new SuttonQuestClient($client);

            if (is_array ($this->conn_handler)) {
                $obj = $this->conn_handler[0];
                $method = $this->conn_handler[1];
                $obj->$method($socket_client);
            } else {
                $function = $this->conn_handler;
                $function($socket_client);
            }
        }

        //close the socket
        socket_close($this->socket_server);
    }

    private function bind_socket() {
        //add some error checking
        socket_bind($this->socket_server, $this->address, $this->port);
    }

    private function create_socket() {
        //tcp stream socket, add some error checking
        $this->socket_server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->socket_server, SOL_SOCKET, SO_REUSEADDR, 1);
    }
}
