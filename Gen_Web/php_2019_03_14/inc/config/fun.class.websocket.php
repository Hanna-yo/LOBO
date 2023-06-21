<?php
define('WebSocketKey','mitown2018websocket');
#對應 cmd
$aWebSocket = array(
      'AgentEnable'     => 101,
      'Kick'            => 102,
      'KickGame'        => 103,
      'chgSerStatus'    => 104,
      'chgGameStatus'   => 105,
      'authToken'       => 106,
);
function set_websocket($iCode,$aSocket = null)
{
      header('Content-type: text/html; charset=utf-8');
      $ch = curl_init();#啟用curl
      	
      $sSign = md5(md5($iCode.WebSocketKey).time());
      
      $curl_post_data = array(
            "sign"    => $sSign,
            "time"    => time(),
            "code"    => $iCode,
      );

      if(!empty($aSocket))
      {
      	$curl_post_data = array_merge($curl_post_data,$aSocket);
      }
      #print_r($curl_post_data);
      
      $curl_post_data = json_encode($curl_post_data);
      $curl_post_data = base64_encode($curl_post_data);
      #echo $curl_post_data.'<br />';
      
      $ch = curl_init('http://103.35.207.162:8080/XG/websocket/websocket.php');
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array("data" => $curl_post_data)));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $result = curl_exec($ch);
      curl_close($ch);
      #echo $result;
}
class WebsocketClient 
{
      private $_Socket = null;

      public function __construct($host, $port) 
      {
            $this->_connect($host, $port);
      }

      public function __destruct() 
      {
            $this->_disconnect();
      }

      public function sendData($data)
      {
            // send actual data:
            fwrite($this->_Socket, "\x00" . $data . "\xff") or die('Error:' . $errno . ':' . $errstr);
            $wsData = fread($this->_Socket, 2000);
            $retData = trim($wsData, "\x00\xff");
            return $retData;
      }

      private function _connect($host, $port)
      {
            $key1 = $this->_generateRandomString(32);
            $key2 = $this->_generateRandomString(32);
            $key3 = $this->_generateRandomString(8, false, true);

            $header = "GET / HTTP/1.1\r\n";
            $header.= "Upgrade: WebSocket\r\n";
            $header.= "Connection: Upgrade\r\n";
            $header.= "Host: " . $host . ":" . $port . "\r\n";
            $header.= "Origin: http://127.0.0.1\r\n";
            $header.= "Sec-WebSocket-Key1: " . $key1 . "\r\n";
            $header.= "Sec-WebSocket-Key2: " . $key2 . "\r\n";
            $header.= "\r\n";
            $header.= $key3;


            $this->_Socket = fsockopen($host, $port, $errno, $errstr, 2);
            fwrite($this->_Socket, $header) or die('Error: ' . $errno . ':' . $errstr);
            $response = fread($this->_Socket, 2000);

            /**
             * @todo: check response here. Currently not implemented cause "2 key handshake" is already deprecated.
             * See: http://en.wikipedia.org/wiki/WebSocket#WebSocket_Protocol_Handshake
             */
            return true;
      }

      private function _disconnect()
      {
            fclose($this->_Socket);
      }

      private function _generateRandomString($length = 10, $addSpaces = true, $addNumbers = true)
      {
            $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"ยง$%&/()=[]{}';
            $useChars = array();
            // select some random chars:    
            for ($i = 0; $i < $length; $i++)
            {
                  $useChars[] = $characters[mt_rand(0, strlen($characters) - 1)];
            }
            // add spaces and numbers:
            if ($addSpaces === true)
            {
                  array_push($useChars, ' ', ' ', ' ', ' ', ' ', ' ');
            }
            if ($addNumbers === true)
            {
                  array_push($useChars, rand(0, 9), rand(0, 9), rand(0, 9));
            }
            shuffle($useChars);
            $randomString = trim(implode('', $useChars));
            $randomString = substr($randomString, 0, $length);
            return $randomString;
      }
}
?>