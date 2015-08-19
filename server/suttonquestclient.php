<?php

class SuttonQuestClient {
    private $_connection;
    private $_address;
    private $_port;
    private $_dbcon;

    public function __construct($connection) {
        $address = '';
        $port = '';
        socket_getsockname($connection, $address, $port);
        $this->_address = $address;
        $this->_connection = $connection;
        $this->_port = $port;

        $this->_dbcon = mysqli_connect("localhost","suttonquest","Xzrr71^1","suttonquest");

        if (mysqli_connect_errno()) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
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

    public function query($query) {
        // Check if there are results
		if ($result = mysqli_query($this->_dbcon, $query))
		{
			$resultArray = array();
			$tempArray = array();
			// Loop through each row in the result set
			while($row = $result->fetch_object())
			{
				// Add each row into our results array
				$tempArray = $row;
				array_push($resultArray, $tempArray);
			}
			return $resultArray;
		}
    }
    //for insert queries
    public function insert($query) {
        if (mysqli_query($this->_dbcon, $query)) {
            return true;
        } else {
            return false;
        }
    }
}
