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
$polinfo_db = "polinfo_coredata_2023";
$users = "users";
$elections = "elections";
$codes = "codes";
$tableList = array($users, $elections, $codes);
$createdb = mysqli_real_escape_string($mysqli, $_GET["createdb"]);


$v = $game_db;
echo "cchecking for <b>".$v."</b>...<br>";
if(!checkForDB($v)){
	if($createdb == $game_db){
		echo "db doesn't exist, and I've been told to create new db....<b>".$createdb."</b><br>";
		$q = "CREATE DATABASE ".$game_db.";";
		echo "q =".$q."... <br>";
		if($mysqli->query($q)===TRUE){
			echo "database created successfully!!!<br>";
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
		if(!checkForTable($game_db, $t)){
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

		if($_POST["uninstall"]==1){
			//delete everything
			if($_GET["secret"]){
				//confirm secret
				if($_GET["secret"]==getSecret()){
					//delete the whole thing
					echo "deleting everything!!!<br>";
					$q = "DROP DATABASE ".$polinfo_db.";";
					echo "q: ".$q."<br>";
/*					if($mysqli->query($q)===TRUE){
						echo "<b>".$polinfo_db."</b> database deleted successfully!!!<br>";
					} else{
						echo "errrrrror deleting database: ".$mysli->error;}
*/
				}
				else{
					echo "the secret you typed is wrong, please try again... !!!THIS CAN NOT BE UNDONE!!!<br>";
					echo "to confirm deleting EVERYTHING PERMANENTLY please type secret password<br>";
					echo '<form action="install.php" method="get">';
					echo 'secret: <input type="text" name="secret"><br>';
					echo '<input type="submit"></form>';
				}
			}
			else{
				echo "you said you want to delete everything... !!!THIS CAN NOT BE UNDONE!!!<br>";
				echo "to confirm deleting EVERYTHING PERMANENTLY please type secret password<br>";
				echo '<form action="install.php" method="get">';
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
			if(!checkForTable($polinfo_db, $users)){
				echo "creating users table...<br>";
				$q = "CREATE TABLE ".$users."  ('id' int(11) NOT NULL AUTO INCREMENT, 'username' varchar(50) NOT NULL UNIQUE,
 'password' varchar(255) NO NULL, 'access-level' int(11) DEFAULT 1, PRIMARY KEY ('id'))";
				echo "q =".$q."... <br>";
/*				if($mysqli->query($q)===TRUE){
					echo "table created successfully!!!<br>";
				} else{
					echo "errrrrror creating table: ".$mysli->error;}
*/
			}
			if(!checkForTable($polinfo_db, $codes)){
				echo "creating codes table...<br>";
				$q = "CREATE TABLE ".$codes."  ('id' int(11) NOT NULL AUTO INCREMENT, 'code' varchar(255) NOT NULL UNIQUE,
 'valid' int(11) DEFAULT 1, PRIMARY KEY ('id'))";
				echo "q =".$q."... <br>";
/*				if($mysqli->query($q)===TRUE){
					echo "table created successfully!!!<br>";
				} else{
					echo "errrrrror creating table: ".$mysli->error;}
*/
			}
			if(!checkForTable($polinfo_db, $elections)){
				echo "creating items table...<br>";
				$q = "CREATE TABLE ".$elections."  ('id' int(11) NOT NULL AUTO INCREMENT, 'db_name' varchar(50) NOT NULL UNIQUE,
 'title' varchar(100), 'nickname' varchar(50), 'election_date' DATE, 'election_style' varchar(50), 'moreinfo' text)";
				echo "q =".$q."... <br>";
/*				if($mysqli->query($q)===TRUE){
					echo "table created successfully!!!<br>";
				} else{
					echo "errrrrror creating tabe: ".$mysli->error;}
*/
			}
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
