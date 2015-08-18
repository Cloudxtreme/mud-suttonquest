<?php

require_once "suttonquestclient.php";

class SuttonQuestServer {

    const PIPENAME = '/tmp/suttonquestserver.pid';

    private $_address = '178.63.103.197';
    private $_port = '5000';
    private $_max_clients;
    private $_listen;
    private $socket_server;
    private $conn_handler;
    private $pid;
    private $connections = array();

    public $pipe;

    public function __construct($max_clients = 10) {
        $this->pid = posix_getpid();
        $this->_listen = false;
        $this->_max_clients = $max_clients;

        //create pipe
        if(!file_exists(self::PIPENAME)) {
            umask(0);
            if(!posix_mkfifo(self::PIPENAME, 0666)) {
                die("Could not create pipe");
            }
        }

        $this->pipe = fopen(self::PIPENAME, 'r+');
    }

    public function init() {
        $this->create_socket();
        $this->bind_Socket();
    }

    public function set_conn_handler($handler) {
        $this->conn_handler = $handler;
    }

    //main listening loop
    public function listen() {
        //add some error checking
        socket_listen($this->socket_server, $this->_max_clients);
        $this->_listen = true;
        socket_set_nonblock($this->socket_server);
        pcntl_signal(SIGUSR1, array($this, 'handle_process'), true);

        printf("Server started, listening on port %d", $this->_port);

        while($this->_listen) {
            if(($client = @socket_accept($this->socket_server)) === false) {
                $info = array();
                //waits for a signal, writes sig info to the $info array, timeout 1 second.
                if(pcntl_sigtimedwait(array(SIGUSR1), $info, 1) > 0) {
                    if($info['signo'] == SIGUSR1) {
                        $this->handle_process();
                    }
                }
                continue;
            }

            $socket_client = new SuttonQuestClient($client, $this);

            if (is_array($this->conn_handler)) {
                $obj = $this->conn_handler[0];
                $method = $this->conn_handler[1];
                $child_pid = $obj->$method($socket_client);
            } else {
                $function = $this->conn_handler;
                $child_pid = $function($socket_client);
            }

            if(!$child_pid) {
                return;
            }

            $this->connections[$child_pid] = $socket_client;
        }

        //close the socket
        socket_close($this->socket_server);
    }

    public function broadcast(Array $msg) {
        $msg['pid'] = posix_getpid();
        $message = serialize($msg);
        $f = fopen(self::PIPENAME, 'w+');
        if(!$f) {
            return;
        }
        fwrite($f, $this->strlenInBytes($message) . $message);
        fclose($f);
        posix_kill($this->pid, SIGUSR1);
    }

    private function bind_socket() {
        //add some error checking
        socket_bind($this->socket_server, $this->_address, $this->_port);
    }

    private function create_socket() {
        //tcp stream socket, add some error checking
        $this->socket_server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->socket_server, SOL_SOCKET, SO_REUSEADDR, 1);
    }

    public function handle_process() {
        $header = fread($this->pipe, 4);
		$len = $this->bytesToInt($header);

        //add some error checking here
        $message = unserialize(fread( $this->pipe, $len));

        if($message['type'] == 'msg') {
            $client = $this->connections[$message['pid']];
            $msg = sprintf('%d:%s', $message['pid'], $message['data']);
            printf("broadcast:%s", $msg);
            foreach ($this->connections as $pid => $conn) {
                //don't rebroadcast to the sending client
                if($pid == $message['pid']) {
                    continue;
                }
                $conn->send($msg);
            }
        } else if($message['type'] == 'disc') {
            unset($this->connections[$message['pid']]);
        }
    }

    public function bytesToInt($char) {
		$num = ord($char[0]);
		$num += ord($char[1]) << 8;
		$num += ord($char[2]) << 16;
		$num += ord($char[3]) << 24;
		return $num;
	}

    protected function strlenInBytes($str) {
		$len = strlen($str);
		$chars = chr( $len & 0xFF );
		$chars .= chr( ($len >> 8 ) & 0xFF );
		$chars .= chr( ($len >> 16 ) & 0xFF );
		$chars .= chr( ($len >> 24 ) & 0xFF );
		return $chars;
	}
}
