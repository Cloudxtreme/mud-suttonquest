<?php

class SuttonQuestRequest {
    private $method = '';
    private $file = NULL; //Input of put request
    private $address = '178.63.103.197';
    private $port = '5000';
    private $get_query = '';

    public function __construct($request) {
        //setup headers
		header("Access-Control-Allow-Orgin: *");
		header("Access-Control-Allow-Methods: *");
		header("Content-Type: application/json");

        $this->method = $_SERVER['REQUEST_METHOD'];

        switch($this->method) {
            //getting updates, called initially to get world state, then every second in client
            case 'GET':
                $this->request = $this->_clean($_GET);
                $URI = $_SERVER['REQUEST_URI'];
                $this->get_query = parse_url($URI, PHP_URL_QUERY);
                break;
            //posting changes, called whenever a client does an action, i.e. say hello
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
            //parse the get request
            $parts = explode('&', $this->get_query);
            $cmd = explode('=', $parts[0]);
            $body = explode('=', $parts[1]);

            if($cmd[1]) {
                $send = array('cmd' => $cmd[1], 'body' => $body[1]);
                return $this->response(json_encode($send));
            } else {
                // need to fix this so it doesnt go through server
                return $this->response(json_encode(array('error' => 'badly formatted request')));
            }
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
