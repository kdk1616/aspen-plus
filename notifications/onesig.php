<?PHP
        function sendMessage($playerID,$message){
        //      $message = "hi";
        //      $playerID = "c9df358f-42af-45a1-b69b-9240dd0a8b0a";
                $content = array(
                        "en" => "$message"
                        );
                        
                        
                        $title = array(
                                "en" => "New Grade Poseted On Aspen"
                                );
                
                $fields = array(
                        'app_id' => "67256b55-23b1-4706-8f70-7383fc160be0",
                        'safari_web_id' => "web.onesignal.auto.4a2f472e-2de1-469e-8f55-0b3384f6ae6c",
                        'include_player_ids' => array("$playerID"),
                        'headings' => $title,
                        //'template_id' => "notification",
                        'url' => "https://capacitiveideas.com/aspen/aspen.php",
                        //'data' => array("foo" => "bar"),
                        'contents' => $content
                );
                
                $fields = json_encode($fields);
        //print("\nJSON sent:\n");
        //print($fields);
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HEADER, FALSE);
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

                $response = curl_exec($ch);
                curl_close($ch);
                
                return $response;
        }
        
        /*$response = sendMessage();
        $return["allresponses"] = $response;
        $return = json_encode( $return);
        
        print("\n\nJSON received:\n");
        print($return);
        print("\n");*/
?>

