<?php declare(strict_types=1);


namespace Ktpl\Pushnotification\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const API_ACCESS_KEY = "";//Api Access key for android.
    const PASSPHASE = ""; // passphase for IOS

    const FIREBASE_FCM_API_URL = 'https://fcm.googleapis.com/fcm/send';
    const FIREBASE_FCM_API_KEY = 'pushnotification/firebase/server_key';

    protected $dir;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     */
    public function __construct(
      \Magento\Framework\App\Helper\Context $context,
      \Magento\Framework\Filesystem\DirectoryList $dir,
      \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->dir = $dir;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * send push notification for ios
     */
    public function sendIosPushNotification($message,$devicetoken){
        $mediaPath = $this->dir->getPath('media').'/';
        $tCert = $mediaPath.'/iospemfile/pushcert.pem';// your certificates file location
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $tCert);
        stream_context_set_option($ctx, 'ssl', 'passphrase', self::PASSPHASE);
        $applegateway='ssl://gateway.sandbox.push.apple.com:2195';//for sanbox mode
        //$applegateway='ssl://gateway.push.apple.com:2195';//for production mode
        $fp = stream_socket_client($applegateway, $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
        if (!$fp)exit("Failed to connect: $err $errstr" . PHP_EOL);
          $body['aps'] = array(
          'alert' => $message,
          'sound' => 'default',
          'badge' => 1,
          );
        // Encode the payload as JSON
        $payload = json_encode($body);
        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $devicetoken) . pack('n', strlen($payload)) . $payload;
        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));
        //set blocking
        stream_set_blocking($fp,0);
        //usleep(500000);
        //Check response
        $apple_error_response = fread($fp, 6);
        $error_response = unpack('Ccommand/Cstatus_code/Nidentifier', $apple_error_response); //unpack the error response (first byte 'command" should always be 8)
        // Close the connection to the server
        fclose($fp);
        if (!$result)
        return 'Message not delivered' . PHP_EOL;
        else
        return 'Message successfully delivered' . PHP_EOL;
    }

    /**
     * send push notification for android
     */
    public function sendAndroidPushNotification($message,$fcmtoken){

         $firebaseFcmApiKey = $this->_scopeConfig->getValue(self::FIREBASE_FCM_API_KEY);

         // $url = 'https://fcm.googleapis.com/fcm/send';
         $url = $this->_scopeConfig->getValue(self::FIREBASE_FCM_API_URL);
         //echo $url;
         $ch = curl_init();
         $deviceToken= $fcmtoken; // get for device
         //$message = array('title'=>'sai', 'body'=>'Hai');

         $fields = array(
             'registration_ids' => array($deviceToken),
             'data' => $message,
         );
         // print_r(json_encode($fields));
         $headers = array(
             'Authorization: key=' .$firebaseFcmApiKey,
             'Content-Type: application/json'
         );
         $ch = curl_init();
         curl_setopt( $ch, CURLOPT_URL, $url);
         curl_setopt( $ch, CURLOPT_POST, true );
         curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
         curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
         curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
         curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
         curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($fields));
         $response=curl_exec($ch);
         $response = json_decode($response,true);
         print_r($response);
         exit;
     }
}

