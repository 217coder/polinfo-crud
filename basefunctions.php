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
$codes = 'codes';//name of table for codes
$key = "id";//used for the key values of our tables, perhaps unnecessary?

$mysqli = new mysqli($dbhost, $dbuser, $dbpass); //create sql connection
if ($mysqli->connect_error) {
        die("ERRORROROR: Connect Failed ".$mysqli->connect_error);
}
$polinfo_conn = new mysqli($dbhost, $dbuser, $dbpass, $polinfo);
if ($polinfo_conn->connect_error){
	echo("error connecting to polinfo_db: ".$polinfo_conn->connect_error."<br>");
}

//$fields = array("racekey", "link_type", "link_source", "link_icon", "link_address"); //old variable? not used?

$candidateDefaultFields =  array("name","party","phone","email","website","twitter","facebook","instagram","race_key","photo");

//the $tables we want to be able to switch between - THESE ARE ENTERED BY HAND
//"basicinfo" is obsolete at the moment
//$table_set = array("basicinfo","candidates","contests","externallinks");
$table_set = array("candidates","contests","externallinks");

$defaultLevel = "county";
$defaultCountywide = "0";
$defaultSeats = "1";


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

function printDBTable($db, $table, $tableFields){
        global $mysqli;
	$db = mysqli_escape_string($mysqli, $db);
	$table = mysqli_real_escape_string($mysqli, $table);
	$admin = bounceAdmin(); //true if admin, false if not

	if(!mysqli_select_db($mysqli, $db)){
		die("error selecting db...".$mysqli->error);}
	else{
	        if($query = $mysqli->query("SELECT * FROM ".$table.";")){ //build an sql $query
		        $totalRows = $query->num_rows; //get a row count

		        printTableHead($tableFields, $admin);

		        for($i=0; $i<$totalRows; $i++){ //print each row
		                $row = mysqli_fetch_array($query);
				printRow($row, $i, $tableFields, $admin);
	       		}
		        echo "</table>"; //close out table
		}
		else{
			die('there was an error with printing table...:'.$mysqli->error);
		}
	}
}

function buildSuperFields($dbname, $table){
//=============================================================
//This function helps build an array of (data type, field name)
//Makes it easier for the new/update entry form for datatype validation to avoid sql errors
//this returns the results it found from the $table you give it.
//=============================================================
        global $mysqli;
        $newFieldList = array();

	$table = mysqli_real_escape_string($mysqli, $table);
	$dbname = mysqli_real_escape_string($mysqli, $dbname);


        $q = "SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$dbname."' AND TABLE_NAME = '".$table."'";
//	echo "q:".$q."-<br>";

	if(!mysqli_select_db($mysqli, $dbname)){
		die("BuilSuperFields: There was an error switching the db to ".$dbname." because of: ".mysqli_error($mysqli));
	}

        $query = $mysqli->query($q);


        while($row = $query->fetch_assoc()){
                if($row['COLUMN_NAME']!="id"){ //we don't need the ID for a table field. We'll get that in the other functions
                        $result[]=$row;
                }
                else {
                        //nothing
                }
        }

        return $result;
}
function buildSuperFieldsFromList($table, $list, $dbname){
//----------------------------------------------
//give it a $list of table fields, see if they
//are in our $table, if so, put them into a new
//$superList with data types and names, this
//way our add new entry screen will have the
//names and data types it needs to know about.
//This returns the $fields that it found
//----------------------------------------------

        global $mysqli;
	$table = mysqli_real_escape_string($mysqli, $table);
	$dbname = mysqli_escape_string($mysqli, $dbname);

        $newFieldList = array();

        $q = "SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$dbName."' AND TABLE_NAME = '".$table."'";
//      echo "q:".$q."-<br>";
        $query = $mysqli->query($q);


        while($row = $query->fetch_assoc()){
                if($row['COLUMN_NAME']!="id"){ //we don't need the ID for a table field. We'll get that in the other functions
                        if(in_array($row['COLUMN_NAME'],$list)){ //and we want to make sure it's in our $list
                                $result[]=$row; }
                }
                else {
                        //nothing
                }
        }


        return $result;
}

function buildFields($db, $table){
//============================================================
//Build a list of fields for our various functions.
//Give it a $table name, and it will pull the field titles for that table
//it returns the $newFields that it found
//============================================================
        global $mysqli;

	$table = mysqli_real_escape_string($mysqli, $table);
	$db = mysqli_escape_string($mysqli, $db);

        $newFieldList = array();

        $q = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$db."' AND TABLE_NAME = '".$table."'";
        if($query = $mysqli->query($q)){
	        while($row = $query->fetch_assoc()){
        	        if($row['COLUMN_NAME']!="id"){ //we don't need the ID for a table field. We'll get that in the other functions
        	                 $result[]=$row;
        	        }
        	        else {
        	                //nothing
        	        }
		}
	}
	else{
		die('there was an error with building fields...:'.$mysqli->error);
	}
        $newFieldList = array_column($result, 'COLUMN_NAME');

        return $newFieldList;
}
function printTableHead($tableFields, $admin){
        echo "<table><tr>"; //begin printing html

        echo "<th>Edit</th>";
	if($admin){
		echo "<th>Delete</th>";}
	echo "<th>ID</th>";


        $c = count($tableFields); //count headers/fields
        for($i=0;$i<$c;$i++){ //print each header
                $header=$tableFields[$i]; //set temp $header

                echo '<th>'.$header.'</th>';
        }
        echo "</tr>"; //and the rest
}
function printRow($row, $i, $tableFields, $admin){
        global $key;

        if($i%2){ //shade every other line darker or lighter
//              echo '<tr class="altline">AA';
                echo '<tr style="color:#550055">';
        }
        else{
                echo "<tr>";    }

        //print edit & delete buttons
        echo "<td><a href=?action=edit&item=".$row[$key].">Edit</a></td>";
	if($admin){
		echo "<td><a href=".$_SERVER['PHP_SELF']."?action=delete&item=".$row[$key].">Delete</a></td>";}
        echo '<td>'.$row['id'].'</td>';

        $c = count($tableFields); //temp $c(ount) variable
        for($j=0;$j<$c;$j++){ //print each $field
                $v = strtolower($tableFields[$j]);
                echo "<td>".$row[$v]."</td>";
        }


        echo "</tr>"; //and the rest
}
function printEditForm($dbname, $table, $id){
        global $mysqli;
        $cleanID = mysqli_real_escape_string($mysqli, $id);
	$table = mysqli_real_escape_string($mysqli, $table);
	$dbname = mysqli_real_escape_string($mysqli, $dbname);


	echo "Welcome to NewEdit home of the NewEdit may I take your order!!<br>";

        $q="SELECT * FROM ".$table." WHERE id=".$cleanID.";";
        echo 'q: '.$q.'<br><br>';


	if(!mysqli_select_db($mysqli, $dbname)){
		die("Edit Form: There was an error switching the db to ".$dbname." because of: ".mysqli_error($mysqli));
	}

	$result = $mysqli->query($q);

	$superFields = buildSuperFields($dbname, $table);

	if(!$result){
		echo "Something went wrong with the query for the EditForm...<br>";}
	else{
		echo "<center><table>";
		echo "<form action='?action=update&item=".$cleanID."' method='post'>";

		$data = mysqli_fetch_array($result);
		foreach($superFields as $value){
			$v = strtolower($value['COLUMN_NAME']);
			$datatype = strtolower($value['DATA_TYPE']);
			echo "<tr><td>".$v."</td><td>".$datatype."</td><td><textarea name='".$v."' cols=80 rows=1>".$data[$v]."</textarea></td></tr>";
		}
		echo "<input type='submit' value='update'></form><br></table></center>";
	}
}
//form for adding a new entry
function printEntryForm($superFields){
        global $defaultLevel, $defaultCountywide, $defaultSeats;
        global $candidateDefaultFields;

        echo "Start of New Entry Form<br>";

        echo "<div class=\"search\">
        <form action=\"\" method=\"post\">
        <table>"; //start building entry form
        echo '<tr><td>Confirmation Code</td><td>varchar</td><td><textarea name="confirm_add" cols="80" rows="1"></textarea></td</tr>';

        $c = count($superFields); //print all the $fields
        for($i=0;$i<$c;$i++){
                $v = strtolower($superFields[$i]['COLUMN_NAME']);
                $datatype = strtolower($superFields[$i]['DATA_TYPE']);
                /////////////////////////////////////////////////
                ///////----really ugly manual defaults-----//////
                /////////////////////////////////////////////////
                if($v!="id"){
                        echo '<tr><td>'.$v.'</td><td>'.$datatype.'</td><td><textarea name="'.$v.'" cols="80" rows="1">';
                        if($v=="level"){
                                echo $defaultLevel;
                        }
                        else if($v=="countywide"){
                                echo $defaultCountywide;
                        }
                        else if($v=="seats_available"){
                                echo $defaultSeats;
                        }
                        echo '</textarea></td></tr>';
                }
        }
        //finish form
        echo "<tr><td></td><td></td><td><input type=\"submit\" value=\"Add!\"></td></tr>
        </table>
        </form>
        </div>";
}
//add entry (from form) into database
function addEntry($table, $tableFields){
        global $mysqli; //pull in globals

        $query = "INSERT INTO ".$table." (";//start query

        $c = count($tableFields);
        for($i=0;$i<$c;$i++){ //read in fields to update
                $f = $tableFields[$i];
                $query = $query.$f;
                if($i!=$c-1) //dont add a comma on the last one
                        $query = $query.", ";
        }
        $query = $query.") VALUES ("; //half-way done
        for($i=0;$i<$c;$i++){ //read in fields & values to update
                $f = $tableFields[$i];
                $v = mysqli_real_escape_string($mysqli, $_POST[$f]); //no inject pls

                if($v==NULL){ //NULL values create problems if they aren't handled right.
                        echo "f-".$f."-v is BNULnul.";
                        $query = $query."NULL";}
                else{
                        $query = $query."'".$v."'"; }

                if($i!=$c-1) //dont add a comma on the last one
                        $query = $query.", ";
        }
        $query = $query.");";//cap it off
        echo "q-".$query."-q";//print for fun

        if(!$mysqli->query($query)){//insert and test for error
                echo "there was a VERY critical error...".mysqli_error();
        }

}
//UpdateEntry, almost a copy of addEntry
//this function gets all of it's data from $_POST
function updateEntry($dbname, $table, $id){
        global $key, $mysqli; //pull in globals
	$dbname = mysqli_real_escape_string($mysqli, $dbname);

	if(!mysqli_select_db($mysqli, $dbname)){
		echo "UpdateEntry: error connecting to db: ".$dbname." because: ".mysqli_error($mysqli)."<br>";
		return false;
	}


        $query = "UPDATE ".$table." SET ";//start query

	//bobbob
	$tablefields = buildFields($dbname, $table);

        $c = count($tablefields);
        for($i=0;$i<$c;$i++){ //read in fields & values to update
                $f = strtolower($tablefields[$i]);
                $v = mysqli_real_escape_string($mysqli, $_POST[$f]); //no inject pls

                if($v==NULL){ //NULL values create problems if they aren't handled right.
                        echo "v is nul.";
                        $query = $query.$f."=NULL";}
                else{
                        $query = $query.$f."='".$v."'"; }

                if($i!=$c-1) //dont add a comma on the last one
                        $query = $query.", ";
        }
        $query = $query." WHERE id=".$id.";";//cap off the $query

        echo "q-".$query."<br>";//print for fun

        if(!$mysqli->query($query)){//insert and test for error
                echo "there was a VERY critical error..".mysqli_error($mysqli).".";
        }
}
//delete entry
function deleteEntry($dbname, $table, $id){
        global $mysqli, $key; //call globals
	$db = mysqli_real_escape_string($mysqli, $dbname);
        $t = mysqli_real_escape_string($mysqli, $table);//no injects plz
        $v = mysqli_real_escape_string($mysqli, $id);//no injects plz


        $query ="DELETE FROM ".$t." WHERE id=".$v.";";//build $query

        echo "executing ".$query."...<br><br>";

	if(!mysqli_select_db($mysqli, $db)){
		die("DeleteEntry: There was an error switching the db to ".$db." because of: ".mysqli_error($mysqli));
	}

        if(!$mysqli->query($query)){//test query for error
                echo "OH no... there was an error-".mysqli_error($mysqli).".";
        }
        echo "Success?!<br><br>";
}

//first, double check that they want to delete the item
function areYouSure($id, $table){
        global $mysqli; //call globals
        $v = mysqli_real_escape_string($mysqli, $id); //no injects plz
        $t = mysqli_real_escape_string($mysqli, $table);//no injects plz

        $query ="DELETE FROM ".$t." WHERE id=".$v.";";//build $query
        echo $query."<br>";//echo it, with a line break

        echo "<br><br>Are you sure you want to delete with this command??";

        echo "<a href=\"".$_SERVER[PHP_SELF]."?a=list\">No</a>
        <a href=\"".$_SERVER[PHP_SELF]."?action=deleteforreal&item=".$v."\">Yes</a>";
}


function registerUser($username, $password, $code){
	global $mysqli;
	global $polinfo_db;
	global $users;
	global $codes;

	$username = mysqli_real_escape_string($mysqli, $username);
	$password = mysqli_real_escape_string($mysqli, $password);
	$code = mysqli_real_escape_string($mysqli, $code);

	$date = date( 'Y-m-d H:i:s');

	$hashed = password_hash($password, PASSWORD_DEFAULT);
	$query = "INSERT INTO ".$users." ( username, password, access_level )
		VALUES ( '".$username."','".$hashed."', 1 );";
	mysqli_select_db($mysqli, $polinfo_db);
	if(!$mysqli->query($query)){
		die("There was an error running the query to create the new user:".$mysqli->error);
	}
	else{
		echo "<b>User Registration was a success!!</b><br>";
	}
	//invalidate code
	$q = "UPDATE ".$codes." SET valid='0' WHERE code='".$code."';";
	if(isCodeValid($code)){
		if(!$mysqli->query($q)){
			die("There was an error running the query to invalidate the code:".$mysqli->error);
		}
	}
	else{
		echo "the code has successfully been used & invalidated.<br>";
	}

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
		echo "FetchRow: error connecting to db: ".$db." because: ".$mysqli->error."<br>";
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

function isCodeValid($mycode){
	global $mysqli;
	global $polinfo_db;
	global $codes;
	$code = mysqli_real_escape_string($mysqli, $mycode);
	$data = fetchRow($mycode, "code", $codes, $polinfo_db); //value, column, table
	if($data['valid']){ //number/field that is set in the sql database to say if this invite code has been used in the past.
		return true;
	}
	return false;
}

function isUsernameTaken($name){
	global $mysqli;
	global $polinfo_db;
	global $users;
	return isValueInTable($name, "username", $users, $polinfo_db); //value, column, table
}


function generateCode(){
	global $mysqli;
	global $polinfo_db;
	global $codes;
	$code = rand(10000000,99999999);

	mysqli_select_db($mysqli, $polinfo_db);

	if(isValueInTable($code, "code", $codes, $polinfo_db)){
		echo "code has already been generated... please try again...<br>";
		return "try again";
	}
	else{
		$q = "INSERT INTO codes (code) VALUES ('".$code."');";
		echo "q: ".$q." -<br>";
		if(!$mysqli->query($q)){
			die("there was an error inserting the code into the db:".$mysqli->error);
		}
		//does 'valid' need to be set to 1? (currently defaults to 1, 7/14/23)
		return $code;
	}
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
	global $mysqli;
	global $polinfo_db;
	global $users;

	$userid = $_SESSION["userid"];

	$data = fetchRow($userid, "id", $users, $polinfo_db);
	if($data["access_level"]>100){
		//user is admin level
		//echo "Access granted for ".$data["username"].".<br>";
		return true;
	}
	else{
		//echo "User ".$data["username"]." only has an access_level of ".$data["access_level"].", needs level 101 or greater.<br>";
		return false;
	}

	echo "this should not print....<br><br><br>";
}

function logout(){
	$_SESSION = array();
	session_destroy();
	header('Location: login.php');
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

function updatePassword($currentpw, $newpw, $confirmpw){
	global $mysqli;
	global $polinfo_db;
	global $users;
	$userid = $_SESSION["userid"];

	if(!$currentpw){
		return false;
	}
	else if(!checkPasswordStrength($newpw)){
		return false;
	}
	else{
		echo "password looks strong engough...<br>";
	}

	$data = fetchRow($userid, "id", $users, $polinfo_db);

	if(!password_verify($currentpw, $data['password'])){
		echo "password entered doesn't match current password.<br>";
		return false;
	}
	else{
		if(! ($newpw === $confirmpw)){
			echo "new password doesn't match confirm password<br>";
			return false;
		}
		else{
			echo "everything else looks to be in order, updating password...<br>";
			$newpw = mysqli_real_escape_string($mysqli, $newpw);//no inject plz
			mysqli_select_db($mysqli, $polinfo_db);
			$hashedpw = password_hash($newpw, PASSWORD_DEFAULT);
			$q = "UPDATE ".$users." SET password='".$hashedpw."' WHERE id='".$userid."';";
			if(!$mysqli->query($q)){
        		        die('OH NOES:'.$mysqli->error);}
			echo "<b>I think it worked...</b><br>";
			return true;
		}
	}

}

function checkPasswordStrength($password){
	if(strlen($password)<6){
		echo "password is too short, please use at least 6 characters<br>";
		return 0;
	} else if(strlen($password)>50){
		echo "password is too long, please use less than 50 characters<br>";
		return 0;
	} else {
		return 1;
	}
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

