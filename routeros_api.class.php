<?php
class RouterosAPI {
    var $debug     = false;
    var $connected = false;
    var $port      = 8728;
    var $timeout   = 3;
    var $attempts  = 3;
    var $delay     = 2;
    var $socket;
    var $error_str;
    var $error_num;

    public function connect($ip, $login, $password) {
        for ($attempt = 1; $attempt <= $this->attempts; $attempt++) {
            $this->socket = @fsockopen($ip, $this->port, $this->error_num, $this->error_str, $this->timeout);
            if ($this->socket) {
                socket_set_timeout($this->socket, $this->timeout);
                
                // Coba Login Cara Baru (RouterOS v6.43+ / v7)
                $this->write('/login', false);
                $this->write('=name=' . $login, false);
                $this->write('=password=' . $password);
                $response = $this->read(false);
                
                if (isset($response[0]) && $response[0] == '!done') {
                    if (!isset($response[1])) {
                        $this->connected = true;
                        break;
                    } else {
                        // Coba Login Cara Lama (Pre v6.43)
                        $match = array();
                        if (preg_match('/^=ret=(.*)$/', $response[1], $match)) {
                            $hash = hex2bin($match[1]);
                            $this->write('/login', false);
                            $this->write('=name=' . $login, false);
                            $this->write('=response=00' . bin2hex(hash('md5', "\x00" . $password . $hash, true)));
                            $response = $this->read(false);
                            if (isset($response[0]) && $response[0] == '!done') {
                                $this->connected = true;
                                break;
                            }
                        }
                    }
                }
                fclose($this->socket);
            }
            sleep($this->delay);
        }
        return $this->connected;
    }

    public function disconnect() {
        @fclose($this->socket);
        $this->connected = false;
    }

    public function write($command, $param2 = true) {
        if ($command) {
            $data = explode("\n", $command);
            foreach ($data as $com) {
                $com = trim($com);
                fwrite($this->socket, $this->encodeLength(strlen($com)) . $com);
            }
            if (gettype($param2) == 'boolean') {
                if ($param2) fwrite($this->socket, chr(0));
            } else {
                $data = explode("\n", $param2);
                foreach ($data as $com) {
                    $com = trim($com);
                    fwrite($this->socket, $this->encodeLength(strlen($com)) . $com);
                }
                fwrite($this->socket, chr(0));
            }
            return true;
        }
        return false;
    }

    public function read($parse = true) {
        $res = array();
        while (true) {
            $byte = ord(fread($this->socket, 1));
            if ($byte == 0) break;
            if ($byte < 128) $len = $byte;
            elseif ($byte < 192) $len = (($byte & 63) << 8) + ord(fread($this->socket, 1));
            elseif ($byte < 224) $len = (($byte & 31) << 16) + (ord(fread($this->socket, 1)) << 8) + ord(fread($this->socket, 1));
            elseif ($byte < 240) $len = (($byte & 15) << 24) + (ord(fread($this->socket, 1)) << 16) + (ord(fread($this->socket, 1)) << 8) + ord(fread($this->socket, 1));
            else $len = (ord(fread($this->socket, 1)) << 24) + (ord(fread($this->socket, 1)) << 16) + (ord(fread($this->socket, 1)) << 8) + ord(fread($this->socket, 1));
            $res[] = fread($this->socket, $len);
        }
        if ($this->debug) {
            echo "<pre>Response: "; print_r($res); echo "</pre>";
        }
        return $res;
    }

    private function encodeLength($len) {
        if ($len < 128) return chr($len);
        elseif ($len < 16384) return chr(($len >> 8) | 128) . chr($len & 255);
        elseif ($len < 2097152) return chr(($len >> 16) | 192) . chr(($len >> 8) & 255) . chr($len & 255);
        elseif ($len < 268435456) return chr(($len >> 24) | 224) . chr(($len >> 16) & 255) . chr(($len >> 8) & 255) . chr($len & 255);
        return chr(240) . chr(($len >> 24) & 255) . chr(($len >> 16) & 255) . chr(($len >> 8) & 255) . chr($len & 255);
    }

    public function comm($com, $arr = array()) {
        if (is_array($com)) {
            foreach ($com as $item) $this->write($item, false);
            $this->write('', true);
        } else {
            $this->write($com, false);
            foreach ($arr as $k => $v) $this->write($k . "=" . $v, false);
            $this->write('', true);
        }
        return $this->read();
    }
}
?>