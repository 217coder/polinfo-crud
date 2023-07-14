<?php
//########################################################################################
//########################################################################################
//## Author: James Manrique                           ####################################
//## File: basefunctions.php                          ####################################
//## Project: POLINFO                                 ####################################
//## License: AGPL3.0                                 ####################################
//## GitHub: https://github.com/217coder/polinfo-crud ####################################
//## Description: some general functions that help    ####################################
//## with accessing info from mysqli database.        ####################################
//## Sometimes these functions are known as CRUD,     ####################################
//## Create, Read, Update, and Delete.                ####################################
//########################################################################################
//########################################################################################

include("dbpassword.php");
$dbpass = getDBPass();
$dbhost = 'localhost';
$dbuser = 'root';
$polinfo_db = 'polinfo_coredata_2023';
$users = 'users';//name of table for users

$mysqli = new mysqli($dbhost, $dbuser, $dbpass); //create sql connection
if ($mysqli->connect_error) {
        die("ERRORROROR: Connect Failed ".$mysqli->connect_error);
}
$polinfo_conn = new mysqli($dbhost, $dbuser, $dbpass, $polinfo);
if ($polinfo_conn->connect_error){
	echo("error connecting to polinfo_db: ".$polinfo_conn->connect_error."<br>");
}

function checkForDB($dbName){
	global $mysqli;
	$dbName = mysqli_real_escape_string($mysqli, $dbName); //no inject plz
	$query = "SHOW DATABASES LIKE '".$dbName."';"; //query to check if db exists
	$result = $mysqli->query($query);
	if($result->num_rows){ //DB DOES exist
		return 1;}
	else{ //DB does NOT exist
		return 0;}
}

function checkForTable($dbName, $dbTable){
	global $mysqli;
	$dbName = mysqli_real_escape_string($mysqli, $dbName);
	$dbTable = mysqli_real_escape_string($mysqli, $dbTable);

	$retv = mysqli_select_db($mysqli, $dbName);
	if(!$retv){
		die('could not connect: '.mysqli_error($mysqli)); }
	if($mysqli->query("DESCRIBE ".$dbTable)){ //table DOES exist
		return 1;}
	else{ //table does NOT exist
		return 0;}
}

function registerUser($username, $hash, $salt){
	$polinfo_conn;
	$username = mysqli_real_escape_string($mysqli, $username);
	$date = date( 'Y-m-d H:i:s');
	$query = "INSERT INTO users ( username, password, salt, create_date )
		VALUES ( '$username','$hash','$salt','$date' );";
	if(!$mysqli->query($query)){
                die('OH NOES:'.$mysqli->error);}
}

function isValueInTable($value, $column, $table, $db){
	global $mysqli;
	$db = mysqli_real_escape_string($mysqli, $db);
	$value = mysqli_real_escape_string($mysqli, $value);
	$column = mysqli_real_escape_string($mysqli, $column);
	$table = mysqli_real_escape_string($mysqli, $table);

	if(!mysqli_select_db($mysqli, $db)){
		echo "error connecting to db: ".$db." because: ".$mysqli->error."<br>";
		return false;
	}
	else{
		$query = "SELECT ".$column.
			" FROM ".$table.
			" WHERE ".$column."='".$value."';";
		$result = $mysqli->query($query);
		$result = mysqli_fetch_array($result);
		if($result==NULL){
			return false;
		}
		return true;
	}
}

function fetchRow($value, $column, $table, $db){
	global $mysqli;
	$db = mysqli_real_escape_string($mysqli, $db);
	$value = mysqli_real_escape_string($mysqli, $value);
	$column = mysqli_real_escape_string($mysqli, $column);
	$table = mysqli_real_escape_string($mysqli, $table);

	if(!mysqli_select_db($mysqli, $db)){
		echo "error connecting to db: ".$db." because: ".$mysqli->error."<br>";
		return false;
	}
	else{
		$query = "SELECT *
			FROM ".$table."
			WHERE ".$column."='".$value."';";
		$result = $mysqli->query($query);
		$result = mysqli_fetch_array($result);
		return $result;
	}
}

function updateField($table, $field, $data, $column, $id){
	global $mysqli;
	$field = mysqli_real_escape_string($mysqli, $field);
	$column = mysqli_real_escape_string($mysqli, $column);
	$table = mysqli_real_escape_string($mysqli, $table);
	$id = mysqli_real_escape_string($mysqli, $id);
	$data = mysqli_real_escape_string($mysqli, $data);

	$query = "UPDATE ".$table.
		" SET ".$field."='".$data."'
		WHERE ".$column."='".$id."';";
	if(!$mysqli->query($query)){
                die('OH NOES:'.$mysqli->error);}
}

function deleteRow($id, $table){
	global $mysqli;
	$table = mysqli_real_escape_string($mysqli, $table);
	$id = mysqli_real_escape_string($mysqli, $id);

	$query = "DELETE FROM ".$table.
		" WHERE id='".$id."';";
	if(!$mysqli->query($query)){
                die('OH NOES:'.$mysqli->error);}
}

function isCodeValid($code){
	$code = mysqli_real_escape_string($mysqli, $code);
	$data = fetchRow($code, "code", "codes"); //value, column, table
	if($data['valid']){ //magic number/field that is set in the sql database to say if this invite code has been used in the past.
		mysql_query("UPDATE codes SET valid='0'
			WHERE code='$code';");
		return true;
	}
	return false;
}

function isUsernameTake($name){
	return isValueInTable($name, "username", "users"); //value, column, table
}


function generateCode(){
	$code = rand(10000000,99999999);
	$mysql->query("INSERT INTO codes (code) VALUES ('".$code."');");
	//does 'valid' need to be set to 1?
}

function printTable($query, $header){
	//does this need no inject???
	$result=$mysql->query($query);
	//seems unnecesary? v
	//$total_rows = $result->num_rows;
	echo "<table>";
	//deprecated? v
	//printTableHeader($header);
	echo '<tr class="left">';
	for($i=0;$i<count($header);$i++){
		echo '<th>'.$header[$i].'</th>';
	}
	echo "</tr>";
	while($row = mysqli_fetch_array($result)){
		echo '<tr>';
		for($i=0;$i<count($row);$i++){
			echo '<td>'.$row[$i].'</td>';
		}
		echo '</tr>';
	}
	echo "</table>";
}

function printTableHeader($names){
	echo '<tr class="left">';
	for($i=0;$i<count($names);$i++){
		echo '<th>'.$names[$i].'</th>';
	}
	echo '</tr>';
}


function fetchRandomRow($query, $table){
	//no inject?
	$result = $mysqli->query($query);
	$num = $result->num_rows;
	if($num > 0){
		$id_array = array();
		for ($i=0;$i<$num;$i++){
			$row = mysqli_fetch_array($result);
			$id_array[$i] = $row['id'];
		}
		$query = "SELECT * FROM ".$table." WHERE id =".$id_array[rand(0,(count($id_array)-1))].";";
		$result = $mysqli->query($query);
		$row =  mysqli_fetch_array($result);
	}
	return $row;
}


function validateUser($data){
	session_regenerate_id(); //why do I call this?
	if($data['admin'])
		$_SESSION['admin']=1;
	$_SESSION["valid"]=1;
	$_SESSION["userid"]=$data['id'];
	$_SESSION["username"]=$data['username'];

	$date = date( 'Y-m-d H:i:s');
	$ip = $_SERVER[HTTP_CLIENT_IP];
//	updateField("users", "last_login", $date, "id", $data[id]);
//	updateField("users", "last_ip_used", $ip, "id", $data[id]);
}


function isLoggedIn(){
	if($_SESSION["valid"])
		return true;
	return false;
}

function bounce(){
	if(!isLoggedIn()){
		header('Location: login.php');
		die();
	}
}

function bounceAdmin(){
	if(!$_SESSION["admin"]){
		header('Location: main.php');
		die();
	}
}

function logout(){
	$_SESSION = array();
	session_destroy();
}

function loginUser($username, $password){
	global $mysqli;
	global $polinfo_db;
	global $users;
	if(isValueInTable($username, "username", $users, $polinfo_db)){//username matches
		$data = fetchRow($username, "username", $users, $polinfo_db); //value, column, table
		if(password_verify($password, $data['password'])){//password matches
			validateUser($data);
			header('Location: dashboard.php');
		}
	}
	echo "Username or password did not match<br>";

}

/*function printNavBar(){
	echo '<table>
		<th><a href="myaccount.php">'.$_SESSION["username"].'</a></th>
		<tr class="cent">
		<th><a href="userlist.php">Users</a></th>
		<th><a href="skrbs.php">skrbs</a></th>
		<th><a href="mainmenu.php">Game</a></th>
		<th><a href="logout.php">Logout</a></th>
		</tr></table>';
}*/


?>

