<?php

class SuttonQuestRequest {
    private $method = '';
    private $file = NULL; //Input of put request
    private $address = '178.63.103.197';
    private $port = '5000';

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
            return $this->response($this->file);
        }
        if ($this->method == 'GET') {
            $send = array('cmd' => 'say', 'body' => 'hello world');
            return $this->response(json_encode($send));
        }
    }

    private function response($message, $status = 200) {
        //server
        $socket = socket_create(AF_INET, SOCK_STREAM, 0);
        $result = socket_connect($socket, $this->address, $this->port);
        socket_write($socket, $message, strlen($message));
        $data = socket_read($socket, 1024);
        socket_close($socket);
        //response to client
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
	$response = new SuttonQuestRequest($_REQUEST['request']);
	echo $response->process();
} catch (Exception $e) {
	echo json_encode(Array('error' => $e->getMessage()));
}
