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
	$db=$_SESSION["currentdb"];
	$table=$_SESSION["currentdbtable"];
	if($db==NULL || $table==NULL){
		echo "Missing db or table, can not add new entry....<br>"; }
	else if($item==$table){
		echo "adding... <br>";
		$fields = buildFields($db, $table);
		addEntry($db, $table, $fields);
		echo "<br>done!<br>";
		echo "<center>";
		printDBTable($db, $table, $fields);
		echo "</center>";
	}
	else{
		echo "something went wrong...<br>";
	}
}
function printDeleteConfirmation($item){
	$db=$_SESSION["currentdb"];
	$table=$_SESSION["currentdbtable"];
	if($db==NULL || $table==NULL){
		echo "not all variables are preseant to delete...<br>"; }
	else{
		if(!bounceAdmin()){
			echo "You do not have a high enough user_access level to do deletion. Please check with admin.<br>";
		}
		else{
			$data = fetchRow($db, $table, $item, $db);
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
	$db=$_SESSION["currentdb"];
	$table=$_SESSION["currentdbtable"];
	if($db==NULL || $table==NULL){
		echo "not all variables are preseant to delete...<br>"; }
	else{
		if(!bounceAdmin()){
			echo "You do not have a high enough user_access level to do deletion. Please check with admin.<br>";
		}
		else{
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

function prepCreateElectionForm(){
	global $elections;
	global $mysqli;
	global $polinfo_db;

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
?>
