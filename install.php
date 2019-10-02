<?php

/**
 * Open a connection via PDO to create a
 * new database and table with structure.
 *
 */

require "config.php";

try {
    $connection = new PDO("mysql:host=$host", $username, $password, $options);
   
    $sql = file_get_contents("delete.sql"); //Deletes old DB
    $connection->exec($sql);
	
	$sql = file_get_contents("init.sql"); //Creates DB
    $connection->exec($sql);
	
	//^^^Deletes old DB and re-inits
	
    
    echo "Database and table users reinitialized successfully.";
} catch(PDOException $error) {
    echo $sql . "<br>" . $error->getMessage();
}

?>
