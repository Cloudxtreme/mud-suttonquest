<?php

require_once "suttonquestclient.php";

class SuttonQuestServer {
    protected $address = '178.63.103.197';
    protected $port = '5000';
    protected $socket_server;
    protected $max_clients;
    protected $_listen;
    protected $conn_handler;

    public function __construct($max_clients = 10) {
        $this->_listen = false;
        $this->max_clients = $max_clients;
    }

    public function init() {
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

        //listening loop
        while($this->_listen) {
            //add some error checking
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
    }
}

?>
