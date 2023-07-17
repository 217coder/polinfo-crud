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
			prepEditForm($item);
			break;
		case "delete":
			echo "print a delete form for the item selected...<br>";
			//printAreYouSureYouWannaDelete($item);
			break;
		case "deleteforreal":
			echo "print txt to confirm delete...<br>";
			//securedelete($item);
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
	$fields = buildFields($codes, $polinfo_db);
	echo "<center>";
	printDBTable($polinfo_db, $codes, $fields);
	echo "</center>";
}

function printUserList(){
	global $users;
	global $polinfo_db;
	setSessionDBandTable($polinfo_db, $users);
	$fields = buildFields($users, $polinfo_db);
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


?>
