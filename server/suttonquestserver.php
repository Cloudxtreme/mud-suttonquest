<?php

class SuttonQuestServer {
    protected $address = '178.63.103.197';
    protected $port = '5000';
    protected $socket_server;
    protected $max_clients;
    protected $_listen;
    protected $_conn_handler


    public function __construct($max_clients = 10) {
        $this->_listen = false;
        $this->max_clients = $max_clients;
    }

    public function init() {
        $this->create_socket();
        $this->bind_Socket();
    }

    public function set_connection_handler($handler) {
        $this->_conn_handler = $handler;
    }

    public function listen() {
        //add some error checking
        socket_listen($this->socket_server, $this->max_clients);
        $this->_listen = true;

        while($this->_listen) {
            //add some error checking
            $client = socket_accept($this->socket_server);
            $socket_client = new SocketClient($client);

            if (is_array ($this->_conn_handler)) {
                $obj = $this->_conn_handler[0];
                $method = $this->_conn_handler[1];
                $obj->$method($socket_client);
            } else {
                $function = $this->_conn_handler;
                $function($socket_client);
            }
        }
    }

    private function bind_socket() {
        //add some error checking
        socket_bind($this->socket_server, $this->address, $this->port)
    }

    private function create_socket() {
        //tcp stream socket, add some error checking
        $this->socket_server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    }
}

?>
