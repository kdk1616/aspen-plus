CREATE DATABASE aspen;

use aspen;


CREATE TABLE notifications (
	id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
	sess VARCHAR(70) NULL,
	token VARCHAR(10000) NULL,
	lastAssignment VARCHAR(1000) NULL,
	attendance VARCHAR(1000) NULL

	
	
	
);