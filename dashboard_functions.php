<?php
include("basefunctions.php");
$actionList = array("Logout", "ChangePassword", "CreateElection", "ManageElections", "GenerateRegistrationCode", "UserList", "CodeList");

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

function handleAction($currentAction){
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
			break;
		case "CodeList":
			echo "put code here to print out a code list<br>";
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
?>
