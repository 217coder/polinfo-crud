<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<?php
//########################################################################################
//########################################################################################
//## Author: James Manrique                           ####################################
//## File: dashboard.php                              ####################################
//## Project: POLINFO                                 ####################################
//## License: AGPL3.0                                 ####################################
//## GitHub: https://github.com/217coder/polinfo-crud ####################################
//## Description: This is the admin/staff dashbaord   ####################################
//## for the POLINFO backend. Sorta falls under CRUD, ####################################
//## Create, Read, Update, & Delete - but this also   ####################################
//## does functions specific to the POLINFO project.  ####################################
//########################################################################################
//########################################################################################
//include("basefunctions.php");
include("dashboard_functions.php");
session_start();?>
<link rel="stylesheet" type="text/css" href="game.css"/>
<title>The GAME</title>
</head>
<body>
<div class="center">
<?php
bounce(); //confirm if logged in
echo "Welcome to the DashBoard<br>";
$a = $_GET["action"];
$item = $_GET["item"];
setDashboardSessionVariables();
printDashboardOptions($a);
echo "<u>output & debug info:</U><br>";
handleAction($a, $item);
printAdditionalDebugInfo();
echo "<br>test footer";
?>
</div>
</body>
</html>
