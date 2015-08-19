<?php

class SuttonQuestRequest {
    private $method = '';
    private $file = NULL; //Input of put request

    public function __construct($request) {
        //setup headers
		header("Access-Control-Allow-Orgin: *");
		header("Access-Control-Allow-Methods: *");
		header("Content-Type: application/json");

        $this->method = $_SERVER['REQUEST_METHOD'];

        switch($this->method) {
            case 'GET':
                $this->request = $this->_clean($_GET);
                break;
            case 'POST':
                $this->request = $this->_clean($_POST);
                $this->file = file_get_contents("php://input");
                break;
            default:
                //invalid
                break;
        }
    }

    private function _clean($data) {
        $clean_input = array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->_clean($v);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }
        return $clean_input;
    }

    public function process() {
        if ($this->method == 'POST') {
            //json from file
            $json = json_decode($this->file, true);
			$message = $json['msg'];

            //server
            $address = '178.63.103.197';
            $port = '5000';

            $socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("could not create socket\n");
            $result = socket_connect($socket, $address, $port) or die("could not connect\n");

            socket_write($socket, $message, strlen($message)) or die("could not write to socket\n");

            $result = socket_read($socket, 1024);

            socket_close($socket);

            return $this->response($result);
        }
        if ($this->method == 'GET') {
            $address = '178.63.103.197';
            $port = '5000';

            $message = 'Hello World';

            $socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("could not create socket\n");
            $result = socket_connect($socket, $address, $port) or die("could not connect\n");

            socket_write($socket, $message, strlen($message)) or die("could not write to socket\n");

            $result = socket_read($socket, 1024);

            socket_close($socket);

            return $this->response($result);
        }
    }

    private function response($data, $status = 200) {
        header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
		return json_encode($data);
    }

    private function _requestStatus($code) {
		$status = array(
			200 => 'OK',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			500 => 'Internal Server Error',
		);
		return ($status[$code])?$status[$code]:$status[500];
	}
}

try {
	$response = new SuttonQuestRequest($_REQUEST['request']); //, $_SERVER['HTTP_ORIGIN']);
	echo $response->process();
} catch (Exception $e) {
	echo json_encode(Array('error' => $e->getMessage()));
}
