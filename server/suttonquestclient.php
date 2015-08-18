<?php

class SuttonQuestClient {
    private $_connection;
    private $_address;
    private $_port;
    private $_server;

    public function __construct($connection, SuttonQuestServer $server) {
        $address = '';
        $port = '';
        socket_getsockname($connection, $address, $port);
        $this->_address = $address;
        $this->_connection = $connection;
        $this->_port = $port;
        $this->_server = $server;
    }

    public function close() {
        socket_shutdown($this->_connection);
        socket_close($this->_connection);
    }

    public function read() {
        if(($buffer = socket_read($this->_connection, 1024, PHP_BINARY_READ)) === false) {
            return null;
        }

        return $buffer;
    }

    public function send($msg) {
        socket_write($this->_connection, $msg, strlen($msg));
    }

    public function send_broadcast($msg) {
        $this->_server->broadcast(array('data' => $msg, 'type' => 'msg'));
    }

    public function disconnected() {
        $this->_server->broadcast(array('type' => 'disc'));
        $this->close();
    }

    public function connected() {
        unset($this->_server->pipe); //close file
        $this->_server->broadcast(array('data' => "connected\n", 'type' => 'msg'));
    }
}
