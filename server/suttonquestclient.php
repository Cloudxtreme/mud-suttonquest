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
        socket_shutdown($this->connection);
        socket_close($this->conection);
    }
}

?>
