<?php
//include("basefunctions.php");
$actionList = array("Logout", "ChangePassword", "CreateElection", "ManageElections", "GenerateRegistrationCode");

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
			echo "Logout code";
			break;
		case "ChangePassword":
			echo "Change Password code";
			break;
		case "CreateElection":
			echo "Create Election code";
			break;
		case "ManageElections":
			echo "Manage Elections code";
			break;
		case "GenerateRegistrationCode":
			echo "Generate Registration CODE code";
			break;
		default:
			echo "Please make a selection...";
	}

}
?>
