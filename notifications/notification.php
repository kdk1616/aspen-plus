<?php


/**
 * @file
 * Push Notification Admin Send - sends push notification(s) to user(s)
 *
 */


function notify($apnsId,$message,$count,$class) {
	
	

$prod = 0;
	


$default = '{
	"aps": {
		"badge" : '.$count.',
	      "mutable-content": 1,


"alert" : {
       		  "title" : "New Grade Poseted On Aspen",
         	"body": "'.$message.$class.'"
     },
	"class" : "'.$class.'"
	 
 		
	
	}

	
}';

$error = array();
$log = array();
/*
	$certificate = "Production/apple_push_notification_production.pem";//when this file is included in another, it is run from that files directory (therefore is is necessary to indicate that it is in the notifications folder)

			$server = 'ssl://gateway.push.apple.com:2195';

*/	

//DEVELOPMENT CERT:
  $certificate = "Sandbox/apple_push_notification_sandbox.pem";//when this file is included, it is run from the other files location, so it is not run from inside the notifications

$server = 'ssl://gateway.sandbox.push.apple.com:2195';



	
		$cert = $certificate;


 	$body = $default;

	
	if (isset($cert) && isset($apnsId) && isset($body)) {

		set_time_limit(0); //Keep process running forever...
		
		date_default_timezone_set('America/New_York');
		
		$ctx = stream_context_create();
		
		stream_context_set_option($ctx, 'ssl', 'local_cert', $cert);
		//stream_context_set_option($ctx, 'ssl', 'passphrase', '4rtsQu3st2o12'); //2013 has no password...
		
		$apnsConnection = stream_socket_client($server, $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
		
		if( $apnsConnection ) {
		
			if( $errstr ) {
		
				$log[] = date('Y-m-d H:i')." - Error connecting to APNS: ".$errstr; //log, that we are successfully connected - used for debugging
		
			} else {
		
				$log[] = date('Y-m-d H:i')." - Successfully connected to APNS: ".$server; //log, that we are successfully connected - used for debugging
	
				// get the push tokens - either all or only for those subscribed ppl who are members
				$recipients = array($apnsId);
	
				// for each push token from the db
				foreach ($recipients as $deviceToken) {
					
					$bodyJson = json_decode(stripslashes($body));
					$payload = json_encode($bodyJson);
	
					$log[] = date('Y-m-d H:i')." - Payload: ".$payload;
	
					$msg = chr(0) . pack("n",32) . pack('H*', str_replace(' ', '', $deviceToken)) . pack("n",strlen($payload)) . $payload;
					$log[] = date('Y-m-d H:i')." - Pushing message to APNS for token: ".$deviceToken; //another log entry
	
					$result = fwrite($apnsConnection, $msg); //this pushes the message to APNS
	
					if( !$result ) {
	
						$log[] = date('Y-m-d H:i')." - Failure writing to APNS, pipe broken!";
	
						fclose($apnsConnection);
						$log[] = date('Y-m-d H:i')." - Closing existing connection to APNS";
	
						//re-open APNS connection
						$ctx = stream_context_create();
						stream_context_set_option($ctx, 'ssl', 'local_cert', $cert);
						if(isset($passphrase)) {
							stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
						}
	
						$log[] = date('Y-m-d H:i')." - Reconnecting to APNS";
						$apnsConnection = stream_socket_client($server, $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
	
						$log[] = date('Y-m-d H:i')." - Bad token: ".$deviceToken;
	
					}
	
				}
		
				$return = "Push was Successful!";
		
				//close APNS connection
				fclose($apnsConnection);
				$log[] = date('Y-m-d H:i')." - Closing connection to APNS";
		
			}
			
		} else {
			$log[] = date('Y-m-d H:i')." - Error connecting to APNS: ".$errstr;
		}
		
		$return = join("<br/>",$log);
		
	}
	
}


//notify("7c0c4db7 12c9e1d1 37ba3f33 ade0ebdc fba17241 3f136539 9c77f8b2 ae09fb39","You FAILED your French Paper! Sucks to be you! You're really dumb!","1");
 
	
?>








