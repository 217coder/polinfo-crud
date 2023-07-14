<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<?php
//########################################################################################
//########################################################################################
//## Author: James Manrique                           ####################################
//## File: install.php                                ####################################
//## Project: POLINFO                                 ####################################
//## License: AGPL3.0                                 ####################################
//## GitHub: https://github.com/217coder/polinfo-crud ####################################
//## Description: this file helps setup the core      ####################################
//## database & basic tables for POLINFO.             ####################################
//########################################################################################
//########################################################################################

include("basefunctions.php");
session_start();?>
<link rel="stylesheet" type="text/css" href="game.css"/>
<title>The GAME</title>
</head>

<body>
<div class="center">
<?php

//list of configurable variables
//$polinfo_db = "polinfo_coredata_2023";
$users = "users";
$elections = "elections";
$codes = "codes";
$tableList = array($users, $elections, $codes);
$createdb = mysqli_real_escape_string($mysqli, $_GET["createdb"]);


$v = $polinfo_db;
echo "checking for <b>".$v."</b>, and various other <i>things</i>...<br>";
if(!checkForDB($v)){
	if($createdb == $polinfo_db){
		echo "db doesn't exist, and I've been told to create new db....<b>".$createdb."</b><br>";
		$q = "CREATE DATABASE ".$polinfo_db.";";
		echo "q =".$q."... <br>";
		if($mysqli->query($q)===TRUE){
			echo "<b>database created successfully!!!<b><br>";
			echo '<a href="?">[RELOAD to Check for Tables]</a><br>';
		} else{
			echo "errrrrror creating database: ".$mysli->error;
		}
	}
	else{
		echo '<b>'.$v.'</b> is missing would you like to create it? <a href="?createdb='.$v.'">[yes]</a><br>';}
}
else{
	echo "<b>".$v."</b> db is present...<br>";
	//check for tables
	echo "now to check for tables...<br>";
	$missing = 0;
	foreach($tableList as $t){
		if(!checkForTable($polinfo_db, $t)){
			if(!$missing){
				echo "one or more tables are missing...";
				$missing = 1;
			}
			echo "<b>".$t."</b> ";
		}
	}
	echo "<br>";
	if(!$missing){
		echo "all tables look to be here... <br>";

		if($_GET["uninstall"]==1){
			//delete everything
			if($_POST["secret"]){
				//confirm secret
				if($_POST["secret"]==getSecret()){
					//delete the whole thing
					echo "deleting everything!!!<br>";
					$q = "DROP DATABASE ".$polinfo_db.";";
					echo "q: ".$q."<br>";
					if($mysqli->query($q)===TRUE){
						echo "<b>".$polinfo_db."</b> database deleted successfully!!!<br>";
						echo '<a href="?">[RELOAD the page???]</a><br>';
					} else{
						echo "errrrrror deleting database: ".$mysli->error;}
				}
				else{
					echo "the secret you typed is wrong, please try again... !!!THIS CAN NOT BE UNDONE!!!<br>";
					echo "to confirm deleting EVERYTHING PERMANENTLY please type secret password<br>";
					echo '<form action="install.php?unsintall=1" method="post">';
					echo 'secret: <input type="text" name="secret"><br>';
					echo '<input type="submit"></form>';
				}
			}
			else{
				echo "you said you want to delete everything... !!!THIS CAN NOT BE UNDONE!!!<br>";
				echo "to confirm deleting EVERYTHING PERMANENTLY please type secret password<br>";
				echo '<form action="install.php?uninstall=1" method="post">';
				echo 'secret: <input type="text" name="secret"><br>';
				echo '<input type="submit"></form>';
			}
		}
		else{
			echo 'would you like to delete the whole database??? <a href="?uninstall=1">[YES]</a><br>';
		}
	}
	else{
		if($_GET["createtables"]==1){
			echo "creating tables...<br>";
			$retv = mysqli_select_db($mysqli, $polinfo_db);
			if(!mysqli_select_db($mysqli, $polinfo_db)){
				die('could not connect: '.mysqli_error($mysqli));}
			if(!checkForTable($polinfo_db, $users)){
				echo "creating users table...<br>";
				$q = "CREATE TABLE ".$users."  (id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, username varchar(50) NOT NULL UNIQUE,
 password varchar(255) NOT NULL, access_level int(11) DEFAULT 1)";
				echo "q =".$q."... <br>";
				if($mysqli->query($q)===TRUE){
					echo "<b>table created successfully!!!</b><br>";
				} else{
					echo "errrrrror creating table: ".mysqli_error($mysqli);}
				echo "setting up default admin user for users table, default password is PLEASECHANGEME.<br>";
				$q = "INSERT INTO ".$users." (username, password, access_level) VALUES ('admin', '".password_hash("PLEASECHANGEME",PASSWORD_DEFAULT)."', 999);";
				echo "q =".$q."... <br>";
				if($mysqli->query($q)===TRUE){
					echo "<b>admin user created successfully!!!</b><br>";
				} else{
					echo "errrrrror creating admin user: ".mysqli_error($mysqli);}
			}
			if(!checkForTable($polinfo_db, $codes)){
				echo "creating codes table...<br>";
				$q = "CREATE TABLE ".$codes."  (id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, code varchar(255) NOT NULL UNIQUE,
 valid int(11) DEFAULT 1)";
				echo "q =".$q."... <br>";
				if($mysqli->query($q)===TRUE){
					echo "<b>table created successfully!!!</b><br>";
				} else{
					echo "errrrrror creating table: ".mysqli_error($mysqli);}
			}
			if(!checkForTable($polinfo_db, $elections)){
				echo "creating elections table...<br>";
				$q = "CREATE TABLE ".$elections."  (id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, db_name varchar(50) NOT NULL UNIQUE,
 title varchar(100), nickname varchar(50), election_date DATE, election_style varchar(50), moreinfo text)";
				echo "q =".$q."... <br>";
				if($mysqli->query($q)===TRUE){
					echo "<b>table created successfully!!!</b><br>";
				} else{
					echo "errrrrror creating table: ".mysqli_error($mysqli);}
			}
			echo '<a href="?">[RELOAD Page???]</a><br>';
		}
		else{
			echo 'would you like to create missing tables? <a href="?createtables='.$missing.'">[yes]</a><br>';
		}
	}
}

?>
</div>
</body>
</html>
