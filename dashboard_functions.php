<?php
include("basefunctions.php");
$actionList = array("Logout", "ChangePassword", "CreateElection", "ManageElections", "GenerateRegistrationCode", "UserList", "CodeList");

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
	echo "db: <b>".$db."</b> dbTable: <b>".$table."</b><br>";
	echo "action: <b>".$action."</b> item: <b>".$item."</b><br>";
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
	switch($currentAction){
		case "Logout":
			logout();
			break;
		case "ChangePassword":
			updatePasswordForm();
			break;
		case "CreateElection":
			echo "Create Election code";
			break;
		case "ManageElections":
			echo "Manage Elections code";
			break;
		case "GenerateRegistrationCode":
			$c = generateCode();
			echo "Generated code: ".$c."<br>";
			echo "use it wisely<br>";
			break;
		case "UserList":
			echo "put code here to print out a users list<br>";
			printUserList();
			break;
		case "CodeList":
			printCodeList();
			break;
		case "edit":
			echo "print an edit form for the item selected...<br>";
			prepEditForm($item); //prep the variables for the edit form, and then call it.
			break;
		case "update":
			echo "update a variable...<br>";
			prepUpdateEntry($item); //pre the variable for the updateEntry() function
			//updateItem($item)
			break;
		case "delete":
			echo "Print a delete form for the item selected...<br>";
			printDeleteConfirmation($item);
			//adminBounce
			//printAreYouSureYouWannaDelete($item);
			break;
		case "confirmdelete":
			echo "Print txt to confirm delete...<br>";
			deleteForSure($item);
			break;
		default:
			echo "Please make a selection...";
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

function setSessionDBandTable($db, $table){
//magic variable? put into a function to replicate/change easily throughout...?
	$_SESSION["currentdb"]=$db;
	$_SESSION["currentdbtable"]=$table;
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
?>
