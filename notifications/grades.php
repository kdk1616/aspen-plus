<?php

require "../config.php";
require "../common.php";
require "notification.php";
require "onesig.php";





$connection = new PDO($dsn, $username, $password, $options);

//need a loop here to go through DB...

//$sess = "104C08D375A79BD14BCA04EE79D240F2";

$sql = "SELECT * 
	FROM notifications";

    $statement = $connection->prepare($sql);
    $statement->execute();

    $result = $statement->fetchAll();
	
foreach($result as $row) {
	
	
	$sess = $row[1];
	$token = $row[2];
	$last = $row[3];
	$att = $row[4];



//$tok = "ea2923d10482a0b8ee6987862db338ad";
//$sess = "A06CE08CCC59C534F03BAD140D3D2AB2";
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://aspen.cpsd.us/aspen/home.do');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

$headers = array();
$headers[] = 'Connection: keep-alive';
$headers[] = 'Pragma: no-cache';
$headers[] = 'Cache-Control: no-cache';
$headers[] = 'Upgrade-Insecure-Requests: 1';
$headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.109 Safari/537.36';
$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8';
$headers[] = 'Referer: https://aspen.cpsd.us/aspen/portalClassList.do?navkey=academics.classes.list';
$headers[] = 'Accept-Encoding: gzip, deflate, br';
$headers[] = 'Accept-Language: en-US,en;q=0.9';
$headers[] = 'Cookie: deploymentId=x2sis; JSESSIONID='.$sess.'; _ga=GA1.3.144468742.1550005470; _gid=GA1.3.624495139.1550005470';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
//echo $result;
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}
curl_close ($ch);


$ch = curl_init();






//asdsadsadsad


$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://aspen.cpsd.us/aspen/studentRecentActivityWidget.do?preferences=%3C%3Fxml+version%3D%221.0%22+encoding%3D%22UTF-8%22%3F%3E%3Cpreference-set%3E%0A++%3Cpref+id%3D%22dateRange%22+type%3D%22int%22%3E4%3C%2Fpref%3E%0A%3C%2Fpreference-set%3E&rand=1550678163946');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

$headers = array();
$headers[] = 'Pragma: no-cache';
$headers[] = 'Accept-Encoding: gzip, deflate, br';
$headers[] = 'Accept-Language: en-US,en;q=0.9';
$headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.109 Safari/537.36';
$headers[] = 'Accept: application/xml, text/xml, */*; q=0.01';
$headers[] = 'Referer: https://aspen.cpsd.us/aspen/home.do';
$headers[] = 'X-Requested-With: XMLHttpRequest';
$headers[] = 'Cookie: deploymentId=x2sis; JSESSIONID='.$sess.'; _ga=GA1.3.1403816719.1550021107; _ga=GA1.2.1303193171.1550031132; _gid=GA1.3.1635508528.1550677506';
$headers[] = 'Connection: keep-alive';
$headers[] = 'Cache-Control: no-cache';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
//echo $result;
$xml = simplexml_load_string($result);
//print_r($xml->{'recent-activity'}->gradebookScore[2]);
if ($xml === false) {
    echo "Failed loading XML: ";
    foreach(libxml_get_errors() as $error) {
        echo "<br>", $error->message;
    }
} else {
	
	$arrayOfGrades = array();
	$codes = array();

	foreach($xml->{'recent-activity'}->gradebookScore as $grades) {
		//print_r($grades);
		$grade = $grades['grade']; //<-- THIS DOESNT WORK!!!!!!!!!
		$class = $grades['classname'];
		$name = $grades['assignmentname'];
	 	$date = $grades['date'];
		$classID = $grades['sscoid'];
		$string = $date . ": You received a " . $grade . " on " . $name . " in " . $class;
		//echo $string . "<br></br>";
		array_push($arrayOfGrades,$string);
		array_push($codes,$classID);
		
	}
	//echo $last;
	$index = array_search($last,$arrayOfGrades);
	//echo $index;
/*	if($index !== false) {
	echo "shit";
}*/
if(strcmp($last,"") !== 0 && $index > 0) {//if the last assignment is not blank and its not at the top of the list, 
	
	for($i = $index + 1; $i >= 0; $i --) {
		
		
		notify($token,$arrayOfGrades[$i],"1",$codes[$i]);//try Apple
		sendMessage($token,$arrayOfGrades[$i]);//try onesig!
		echo $arrayOfGrades[$i];
	}
	$newLast = $arrayOfGrades[0];//moves last recored to top of new list, since the new assignmetns were sent as notifications to user!
	$sql = "UPDATE notifications set lastAssignment = '$newLast'
		 WHERE sess='$sess'";

	    $statement = $connection->prepare($sql);
	    $statement->execute();
	
	
	
}
//DITTO FOR ATTTENDANCE --------------------------
	$arrayOfAtt = array();
//	$table = "<table id='stream'><tr><th>Attendance:</th></tr>";
	foreach($xml->{'recent-activity'}->periodAttendance as $grades) {
		//print_r($grades);
		$class = $grades['classname'];
		$period =  $grades['period'];
		$name = $grades['assignmentname'];
	 	$date = $grades['date'];
		$absent = $grades['absent'];
		$excused = $grades['excused'];
		$tardy = $grades['tardy'];
		$dismissed = $grades['dismissed'];
		
		if(strcmp($absent,"true") == 0) {
			$type = 'absent';
		}
		if(strcmp($excused,"true") == 0) {
			$type = 'excused absent';
			
		}
		if(strcmp($tardy,"true") == 0) {
			$type = 'tardy';
			
		}
		if(strcmp($dismissed,"true") == 0) {
			$type = 'dismissed';
			
		}
		
		$string = $date . ": You were marked ".$type." for " . $class . " during period " . $period."";//<-- issue where bold tags screwed it up because they aren't in the response from Aspen!!!! 
		//$table .= "<tr><td>" . $string . "</tr></td>";
		//echo $string . "<br></br>";
		array_push($arrayOfAtt,$string);
	}
	echo $att;
	print_r($arrayOfAtt);
	$index = array_search($att,$arrayOfAtt);
	echo $index;
//	$table .= "</table>";
//	echo $table;
if(strcmp($att,"") !== 0 && $index > 0) {//if the last assignment is not blank and its not at the top of the list, 
echo "asasdasd";	
	for($i = $index + 1; $i >= 0; $i --) {
		
		
		notify($token,$arrayOfAtt[$i],"1","att");//So app opens to attendance page
		sendMessage($token,$arrayOfAtt[$i]);//try onesig!
		
		echo $arrayOfAtt[$i];
	}
	$newLast = $arrayOfAtt[0];//moves last recored to top of new list, since the new assignmetns were sent as notifications to user!
	$sql = "UPDATE notifications set attendance = '$newLast'
		 WHERE sess='$sess'";

	    $statement = $connection->prepare($sql);
	    $statement->execute();
	
	
	
}




	
	
	
//print_r($xml);

	
}


if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}
curl_close ($ch);




}




// Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/





?>