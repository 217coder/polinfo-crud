<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en">
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
<link rel="stylesheet" type="text/css" href="polinfo.css"/>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<title>Dashboard - POLINFO</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
</style>
<script src=""></script>
</head>
<body>
<div class="center">
<?php
bounce(); //confirm if logged in
echo "<div class='w3-countainer w3-amber'>";
echo "<h1>Welcome to the Dashboard</h1>";
echo "</div>";
$a = $_GET["action"];
$item = $_GET["item"];
setDashboardSessionVariables();
printDashboardOptions($a);
printElectionsBar($item);
//echo "<u>Output & Debug info:</U><br>";
handleAction($a, $item);
printAdditionalDebugInfo();
echo "<br>test footer";
?>
</div>
</body>
</html>
