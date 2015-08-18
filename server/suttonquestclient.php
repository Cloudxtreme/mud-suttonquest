<?php

class SuttonQuestClient {
    private $_connection;
    private $_address;
    private $_port;

    public function __construct($connection) {
        $address = '';
        $port = '';
        socket_getsockname($connection, $address, $port);
        $this->_address = $address;
        $this->_connection = $connection;
        $this->_port = $port;
    }

    public function close() {
        socket_shutdown($this->_connection);
        socket_close($this->_conection);
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
}

?>
