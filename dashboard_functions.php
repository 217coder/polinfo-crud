<?php
include("basefunctions.php");
$actionList = array("Logout", "ChangePassword", "ElectionList", "CreateElection", "ManageElections", "GenerateRegistrationCode", "UserList", "CodeList");

function setDashboardSessionVariables(){
	//helps with managing the db & table we are on and looking at.
	global $polinfo_db;
	$defaultDB = $polinfo_db;
	$currentDB = $_SESSION["currentdb"];
	if(!$currentDB){
		$_SESSION["currentdb"] = $defaultDB;
	}
}
function printAdditionalDebugInfo(){
	$db = $_SESSION["currentdb"];
	$table = $_SESSION["currentdbtable"];
	$action = $_GET["action"];
	$item = $_GET["item"];
	$election = $_GET["currentlyelection"];
	echo "<br><U>Additional Debug Info:</u><br>";
	echo "db: <b>".$db."</b> dbTable: <b>".$table."</b><br>";
	echo "action: <b>".$action."</b> item: <b>".$item."</b><br>";
	echo "current_election: <b>".$election."</b></br>";
}

function printDashboardOptions($currentAction){
	global $actionList;
	echo "<center>";
	foreach($actionList as $action){
		if($currentAction==$action){
			echo '<b><a href="?action='.$action.'">['.$action.']</a></b><br>';}
		else{
			echo '<a href="?action='.$action.'">['.$action.']</a><br>';}
	}
	echo "</center>";

}

function handleAction($currentAction, $item){
	$currentAction = strtolower($currentAction);

	switch($currentAction){
		case "logout":
			logout();
			break;
		case "changepassword":
			updatePasswordForm();
			break;
		case "createelection":
			echo "Create Election code";
			prepCreateElectionForm(); //prep the variables for the new election db
			break;
		case "changeelection":
			echo "Changing to new election db...";
			changeElection($item);
			break;
		case "manageelections":
			echo "Manage Elections code<br>";
			printListOfElections($item);
//			printManageElectionMenu(); //print info for managing the different elections in the db
			break;
		case "generateregistrationcode":
			$c = generateCode();
			echo "Generated code: ".$c."<br>";
			echo "use it wisely<br>";
			break;
		case "electionlist":
			printElectionList();
			break;
		case "userlist":
			printUserList();
			break;
		case "codelist":
			printCodeList();
			break;
		case "edit":
			echo "print an edit form for the item selected...<br>";
			prepEditForm($item); //prep the variables for the edit form, and then call it.
			break;
		case "addnew":
			echo "adding new entry...<br>";
			prepAddNew($item);
			break;
		case "update":
			echo "update a variable...<br>";
			prepUpdateEntry($item); //prep the variable for the updateEntry() function
			break;
		case "delete":
			echo "Print a delete form for the item selected...<br>";
			printDeleteConfirmation($item);
			break;
		case "confirmdelete":
			echo "Print txt to confirm delete...<br>";
			deleteForSure($item);
			break;
		default:
			echo "Please make a selection...<br>";
	}

}

function updatePasswordForm(){
	$currentPW = $_POST["currentpw"];
	$newPW = $_POST["newpw"];
	$confirmPW = $_POST["confirmnewpw"];

	echo "New password needs to be more than 6 characters and less than 50 characters.<br>";
	if(!updatePassword($currentPW, $newPW, $confirmPW)){
		echo "<br>";
		echo '<form action="?action=ChangePassword" method="post">';
		echo 'Current Password<input type="text" name="currentpw">';
		echo 'New Password<input type="text" name="newpw">';
		echo 'Confirm New Password<input type="text" name="confirmnewpw">';
		echo '<input type="submit"></form>';
	}
	else{
		echo "everything worked?...<br>";
	}
}

function printCodeList(){
	global $codes;
	global $polinfo_db;
	setSessionDBandTable($polinfo_db, $codes);
	$fields = buildFields($polinfo_db, $codes);
	echo "<center>";
	printDBTable($polinfo_db, $codes, $fields);
	echo "</center>";
}

function printUserList(){
	global $users;
	global $polinfo_db;
	setSessionDBandTable($polinfo_db, $users);
	$fields = buildFields($polinfo_db, $users);
	echo "<center>";
	printDBTable($polinfo_db, $users, $fields);
	echo "</center>";

}

function printElectionList(){
	global $elections;
	global $polinfo_db;
	setSessionDBandTable($polinfo_db, $elections);
	$fields = buildFields($polinfo_db, $elections);
	echo "<center>";
	printDBTable($polinfo_db, $elections, $fields);
	echo "</center>";
}
function setSessionDBandTable($db, $table){
//magic variable? put into a function to replicate/change easily throughout...?
	$_SESSION["currentdb"]=$db;
	$_SESSION["currentdbtable"]=$table;
}
function setCurrentElection($input){
	$_SESSION["currentelection"]=$input;
}
function prepEditForm($item){
	$db=$_SESSION["currentdb"];
	$table=$_SESSION["currentdbtable"];
	if($db==NULL || $table==NULL){
		echo "not all variables are preseant to edit...<br>"; }
	else{
		echo "edit form...<br>";
		printEditForm($db, $table, $item);
		echo "end of edit form<br>";
	}
}
function prepUpdateEntry($item){
	$db=$_SESSION["currentdb"];
	$table=$_SESSION["currentdbtable"];
	if($db==NULL || $table==NULL){
		echo "not all variables are preseant to update...<br>"; }
	else{
		echo "updating...<br>";
		updateEntry($db, $table, $item);
		echo "<br>update complete??? yes...<br>";
		echo "<center>";
		printDBTable($db, $table, buildFields($db, $table));
		echo "</center>";
	}
}
function prepAddNew($item){
	global $elections;
	$db=$_SESSION["currentdb"];
	$table=$_SESSION["currentdbtable"];
	if($db==NULL || $table==NULL){
		echo "Missing db or table, can not add new entry....<br>"; }
	else if($item==$table){
		if($table==$elections){ //code is more complex for adding elections, need to also build new db and confirm it's good.
			echo "Need to build a new db for the new election...<br>";
			$newDBName = $_POST["db_name"];
			if(!$newDBName){
				echo "not all variables are here to create new db... dbname is missing?<br>";
				return false;
			}
			else{
				if(!buildNewElectionDB($newDBName)){
					echo "something went wrong creating the new db... exiting...<br>";
					return false;
				}
				else{
					echo "Now to add new db entry into <b>".$election."</b> table...<br>";
				}
			}
		}
//		else{ //we're adding another type of value, and don't need to build a new db...
		echo "adding... <br>";
		$fields = buildFields($db, $table);
		addEntry($db, $table, $fields);
		echo "<br><b>Done! Looks like it was a SUCCESS.</b><br>";
		echo "<center>";
		printDBTable($db, $table, $fields);
		echo "</center>";
	}
	else{
		echo "something went wrong with item equals table...<br>";
	}
}
function printDeleteConfirmation($item){
	global $elections;
	$db=$_SESSION["currentdb"];
	$table=$_SESSION["currentdbtable"];
	if($db==NULL || $table==NULL){
		echo "not all variables are preseant to delete...<br>"; }
	else{
		if(!bounceAdmin()){
			echo "You do not have a high enough user_access level to do deletion. Please check with admin.<br>";
		}
		else if($table==$elections){ //handle different for deleting a full election db
			//$data = fetchRow($db, $table, $item, $db);
			$data = fetchRow($item, "id", $table, $db);
			echo "<b><u>!!!WARNING!!!</b></u><br>";
			echo "Are you sure you would like to delete? DB: <b>".$data['db_name']."</b><br>";
			echo "<b><u>This can not be undone</b></u>. Please type '<b>I am SURE that I want to delete the entire database</b>' in the box bellow (without the quotation marks).<br>";
			echo "<center><form action='?action=confirmdelete&item=".$item."' method='post'>";
			echo "<textarea name='confirmation' cols=80 rows=1></textarea><br>";
			echo "<input type='submit' value='DELETE'></form></center>";
		}
		else{
			//$data = fetchRow($db, $table, $item, $db);
			echo "Are you sure you would like to delete? Item: <b>".$item."</b> From DB: <b>".$db."</b> and Table: <b>".$table."</b><br>";
			echo "This can not be undone. Please type '<b>I am sure</b>' in the box bellow (without the quotation marks).<br>";
			echo "<center><form action='?action=confirmdelete&item=".$item."' method='post'>";
			echo "<textarea name='confirmation' cols=80 rows=1></textarea><br>";
			echo "<input type='submit' value='DELETE'></form></center>";
		}

		echo "<br><br><center>";
		printDBTable($db, $table, buildFields($db, $table));
		echo "</center>";
	}

}
function deleteForSure($item){
	global $elections;
	global $mysqli;
	$db=$_SESSION["currentdb"];
	$table=$_SESSION["currentdbtable"];
	if($db==NULL || $table==NULL){
		echo "not all variables are preseant to delete...<br>"; }
	else{
		if(!bounceAdmin()){
			echo "You do not have a high enough user_access level to do deletion. Please check with admin.<br>";
		}
		else if($table==$elections){//handle differently when deleting a full election DB, these are special
			$confirmation = $_POST["confirmation"];
			if(!($confirmation=="I am SURE that I want to delete the entire database")){
				echo "Your confirmation (".$confirmation.") did not match what was required. Please try again.<br>";
			}
			else{
				echo "Eeverything looks to be in order... deleting your database...<br>";
				$data = fetchRow($item, "id", $table, $db);
				$dbname = $data["db_name"];
				if(!$dbname){
					echo "somehow missing db_name?? Unable to delete without that, please try again...<br>";}
				else{
					echo "deleting the db <b>".$dbname."</b>...<br>";
					$q = "DROP DATABASE ".$dbname.";";
					echo "q: ".$q." -<br>";
					$result = $mysqli->query($q);
					if(!$result){
						echo "There was a problem deleting the election table ".$dbname." because: ".mysqli_error($mysqli)."<br>";
						//return false;
					}
					else{
						echo "Successfully deleted the db <b>".$dbname."</b>, now removing entry from list...<br>";
						deleteEntry($db, $table, $item);
						echo "<b>Looks like everything should be good to go!</b>...<br>";
					}
				}
				//deleteDB();
				echo "End of block...<br>";
			}
		}
		else{ //it's a normal row in a table to delte
			$confirmation = $_POST["confirmation"];
			if(!($confirmation=="I am sure")){
				echo "Your confirmation (".$confirmation.") did not match what was required. Please try again.<br>";
			}
			else{
				echo "Eeverything looks to be in order... deleting your item...<br>";
				deleteEntry($db, $table, $item);
				echo "Did the delete work??? Yes...<br>";
			}
		}
	}
}
function deleteElectionDatabase($dbname){

}
function prepCreateElectionForm(){
	global $elections;
	global $mysqli;
	global $polinfo_db;

	if(!bounceAdmin()){
		echo "You do not have high enough permission to create new election db. Please check with administrator.<br>";
		return false;
	}
	setSessionDBandTable($polinfo_db, $elections);
	//get variables ready for new election create form
//	echo "prep form for election creation<br>;"
	//print election table
	$fields = buildSuperFields($polinfo_db, $elections);
	printEntryForm($polinfo_db, $elections, $fields, $fields);
}

function printManageElectionMenu(){
	echo "this is the temporary election menu<br>.";
	//printListOfElections();
	//other stuff
}

function printListOfElections($item){
	global $elections;
	global $mysqli;
	global $polinfo_db;

	$q = "SELECT * FROM ".$elections.";";

	if(!mysqli_select_db($mysqli, $polinfo_db)){
		die("PrintListofElections: error switching to db ".$polinfo_db." because: ".mysqli_error($mysqli));
	}
	$result = $mysqli->query($q);
	if(!$result){
		die("PrintListofElections: error running query ".$q." because: ".mysqli_error($mysqli));
	}
	else if($result->num_rows<1){
		echo "It looks like there are no elections in the <b>".$elections."</b> table, would you like to <a href='?action=createelection'>Create new Election?</a><br>";
	}
	while($row = mysqli_fetch_array($result)){
		$d = $row['db_name'];
		if(!$d){
			echo "oddly there is no db_name....<br>";}
		else if($d == $item){
			echo "<b>[".$d."]</b><br>"; }
		else{
			echo "<a href='?action=changeelection&item=".$d."'>[".$d."]</a>";
		}
	}
}
function changeElection($item){
	global $mysqli;
	global $polinfo_db;
	global $elections;
	$new_election = mysqli_real_escape_string($item);

	if(!isValueInTable($new_election, "db_name", $elections, $polinfo_db)){
		echo "I'm not sure that <b>".$new_election."</b> has been created yet... <br>";
	}
	else{
		setCurrentElection($new_election);

		if(!mysqli_select_db($mysqli, $election)){
			die("ChangeElection: error switching to db ".$polinfo_db." because: ".mysqli_error($mysqli));
		}

		echo "looks like everything worked...!<br>";
	}

}
function buildNewElectionDB($newdbname){
	global $mysqli;
	global $polinfo_db;
	$contests = "contests";
	$candidates = "candidates";

	if(checkForDB($newdbname)){
		echo "DB <b>".$newdbname."</b> already exists... unable to create new db... So instead, we will update existing one...<br>";
	}
	else{
		//it doesn't exist, we can go ahead and create it
		$q = "CREATE DATABASE ".$newdbname.";";
		echo "q: ".$q." -<br>";
		$result = $mysqli->query($q);
		if(!$result){
			echo "failed to create DB ".$newdbname." because of ".mysqli_error($mysqli)."<br>";
			return false;
		}
		else{
			echo "successfully created <b>".$newdbname."</b>!!<br>";
		}
	}
	echo "now to create the tables...<br>";
	if(!mysqli_select_db($mysqli, $newdbname)){
		echo "failed to connect to new DB ".$dbname." because of ".mysqli_error($mysqli)."<br>";
	}

	if(checkForTable($newdbname, $contests)){
		echo "The <b>".$contests."</b> table already exists, no need to recreate it...<br>";}
	else{
		echo "creating new <b>".$contests."</b> table for <b>".$newdbname."</b>...<br>";
		//##################################################################################################
		//$q = "big long string";
		$q = "CREATE TABLE ".$contests." (
			id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			title varchar(255) DEFAULT NULL,
			level varchar(100) DEFAULT NULL,
			contest_group varchar(100) DEFAULT NULL,
			contest_nickname varchar(100) DEFAULT NULL,
			countywide int(11) DEFAULT 1,
			seats_available int(11) DEFAULT NULL,
			term_length varchar(255) DEFAULT NULL,
			contested int(11) DEFAULT 1,
			moreinfo text,
			party varchar(100) DEFAULT NULL,
			rank_value int(11) DEFAULT NULL);";
		//##################################################################################################
		echo "q: ".$q." -<br>";
		$result = $mysqli->query($q);
		if(!$result){
			echo "failed to create table ".$contests." because of ".mysqli_error($mysqli)."<br>";
			//return false;
		}
		else{
			echo "successfully created <b>".$contests."</b> table!!<br>";
		}
	}

	echo "creating new <b>".$candidates."</b> table for <b>".$newdbname."</b>...<br>";
	if(checkForTable($newdbname, $candidates)){
		echo "The <b>".$candidates."</b> table already exists, no need to recreate it...<br>";}
	else{
		//##################################################################################################
		//$q = "big long string";
		$q = "CREATE TABLE ".$candidates." (
			id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			name varchar(128) DEFAULT NULL,
			contest_key int(11) DEFAULT NULL,
			party varchar(128) DEFAULT NULL,
			phone varchar(100) DEFAULT NULL,
			email varchar(128) DEFAULT NULL,
			website varchar(255) DEFAULT NULL,
			twitter varchar(255) DEFAULT NULL,
			facebook varchar(255) DEFAULT NULL,
			instagram varchar(255) DEFAULT NULL,
			youtube varchar(255) DEFAULT NULL,
			photo varchar(255) DEFAULT NULL,
			q1 text, a1 text,
			q2 text, a2 text,
			q3 text, a3 text,
			q4 text, a4 text,
			q5 text, a5 text,
			q6 text, a6 text,
			q7 text, a7 text,
			q8 text, a8 text,
			incumbent int(11) DEFAULT NULL,
			contested int(11) DEFAULT NULL,
			seat varchar(100) DEFAULT NULL,
			zone_id int(11) DEFAULT NULL,
			biography text);";
		//##################################################################################################
		echo "q: ".$q." -<br>";
		$result = $mysqli->query($q);
		if(!$result){
			echo "failed to create table ".$candidates." because of ".mysqli_error($mysqli)."<br>";
			//return false;
		}
		else{
			echo "successfully created <b>".$candidates."</b> table!!<br>";
		}
	}

	echo "Made it to the end of the createNewElection function...<br>";
	return true;

	//createDB
	//createCandidatesTable
	//createContestsTable
	//createExternalLinksTable

}
		/*$q = "CREATE TABLE ".$contests." (
			'id' int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			'title' varchar(255) DEFAULT NULL,
			'level' varchar(100) DEFAULT NULL,
		'contest_group' varchar(100) DEFAULT NULL,
		'contest_nickname' varchar(100) DEFAULT NULL,
		'countywide' int(11) DEFAULT '1',
		'seats_available' int(11) DEFAULT NULL,
		'term_length' varchar(255) DEFAULT NULL,
		'contested' int(11) DEFAULT '1',
		'moreinfo' text,
		'party' varchar(100) DEFAULT NULL,
		'rank_value' int(11) DEFAULT NULL);";*/
	/*$q = "CREATE TABLE ".$candidates." (
		'id' int(11) NOT NULL AUTO INCREMENT PRIMARY KEY,
		'name' varchar(128) DEFAULT NULL,
		'contest_key' int(11) DEFAULT NULL,
		'party' varchar(128) DEFAULT NULL,
		'phone' varchar(100) DEFAULT NULL,
		'email' varchar(128) DEFAULT NULL,
		'website' varchar(255) DEFAULT NULL,
		'twitter' varchar(255) DEFAULT NULL,
		'facebook' varchar(255) DEFAULT NULL,
		'instagram' varchar(255) DEFAULT NULL,
		'youtube' varchar(255) DEFAULT NULL,
		'photo' varchar(255) DEFAULT NULL,
		'q1' text, 'a1' text,
		'q2' text, 'a2' text,
		'q3' text, 'a3' text,
		'q4' text, 'a4' text,
		'q5' text, 'a5' text,
		'q6' text, 'a6' text,
		'q7' text, 'a7' text,
		'q8' text, 'a8' text,
		'incumbent' int(11) DEFAULT NULL,
		'contested' int(11) DEFAULT NULL,
		'seat' varchar(100) DEFAULT NULL,
		'zone_id' int(11) DEFAULT NULL,
		'biography' text);";*/

?>
