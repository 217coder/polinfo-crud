<?php

//===============================================================
//Written By: James Manrique (james@votechampaign.org)
//Example PolInfo Website: votechampaign.org/guide
//GitHub Address:
//Filename: cfunctions2020.php
//Last Update: Aug 2nd 2020
//Info:
//These are CRUD functions for our polinfo website (CRUD: Create, Read, Update, Delete)
//As with most of the initial polinfo codebase, this was written by hand. Please excuse any errors or ineffecient functions.
//These functions help create a backend to make it easier to update the information on our polinfo website.
//A current flaw with this approach, is that the security may be weak in preventing people from accessing this page & changing the live database.
//It's a good idea to not have this page "live" at the same time as the live website. Or, making a unique password to validate the changes.
//===============================================================
//Future Wants:
//--Better user login, validation, & security features
//--Better layout & UI
//--Color Picker/Changer
//===============================================================



//===============================================================
//=======================And now... THE CODE=====================
//===============================================================
//==================DATABASE NAME STUFF - Hand Typed=============
$dbName = "general2022";
$db_table = "externallinks";
$db_password = "";//entered manually by hand
$key = "id"; //global variable for name of the 'key' variable in the database table
//===============================================================
//==================DATABASE PASSWORD STUFF======================
$election_db = "municipal2023"; //default for if everything fails
$election_get = $_GET['election'];
$election_sess = $_SESSION['election'];
if($election_sess){
	$election_switch = $election_sess;
}
if($election_get){ //get overides a session variable
	$election_switch = $election_get;
}
switch ($election_switch){ //this should sanitize user input variables/injections... I think? - tho this is all MAGIC NUMBERS
        case "general2022":
                $election_db = "general2022";
                break;
        case "primary":
                $election_db = "primary2022";
                break;
        case "municipal":
                $election_db = "municipal2023";
                break;
        case "primary2022":
                $election_db = "primary2022";
                break;
        case "municipal2021":
                $election_db = "municipal2021";
                break;
	case "general2020":
		$election_db = "general2020";
		break;
}
$dbName = $election_db;
$_SESSION['election']=$dbName;

$mysqli = new mysqli("localhost", "root", $db_password, $dbName); //create sql connection
if ($mysqli->connect_errno) {
        echo "ERRORROROR: Connect Failed ".$mysqli->connect_error;
        exit();
}  //if error, print error & exit

//the $fields that we want to print for the header of the table - THESE ARE ENTERED BY HAND
//future version idea - check boxes/preferences
$fields = array("racekey", "link_type", "link_source", "link_icon", "link_address");

$test_fields = array();

$candidateDefaultFields =  array("name","party","phone","email","website","twitter","facebook","instagram","race_key","photo");


//the $tables we want to be able to switch between - THESE ARE ENTERED BY HAND
//"basicinfo" is obsolete at the moment
//$table_set = array("basicinfo","candidates","contests","externallinks");
$table_set = array("candidates","contests","externallinks");

$defaultLevel = "county";
$defaultCountywide = "0";
$defaultSeats = "1";




function buildSuperFields($table){
//=============================================================
//This function helps build an array of (data type, field name)
//Makes it easier for the new/update entry form for datatype validation to avoid sql errors
//this returns the results it found from the $table you give it.
//=============================================================

	global $dbName;
	global $mysqli;
	global $test_fields;
	$newFieldList = array();


	$q = "SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$dbName."' AND TABLE_NAME = '".$table."'";
//	echo "q:".$q."-<br>";
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


function buildSuperFieldsFromList($table, $list){
//----------------------------------------------
//give it a $list of table fields, see if they
//are in our $table, if so, put them into a new
//$superList with data types and names, this
//way our add new entry screen will have the
//names and data types it needs to know about.
//This returns the $fields that it found
//----------------------------------------------

	global $dbName;
	global $mysqli;
	global $test_fields;
	$newFieldList = array();


	$q = "SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$dbName."' AND TABLE_NAME = '".$table."'";
//	echo "q:".$q."-<br>";
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

function buildFields($table){
//============================================================
//Build a list of fields for our various functions.
//Give it a $table name, and it will pull the field titles for that table
//it returns the $newFields that it found
//============================================================

	global $dbName;
	global $mysqli;
	global $test_fields;
	$newFieldList = array();


	$q = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$dbName."' AND TABLE_NAME = '".$table."'";
//	echo "q:".$q."-<br>";
	$query = $mysqli->query($q);


	while($row = $query->fetch_assoc()){
		if($row['COLUMN_NAME']!="id"){ //we don't need the ID for a table field. We'll get that in the other functions
			 $result[]=$row;
		}
		else {
			//nothing
		}
	}

	$newFieldList = array_column($result, 'COLUMN_NAME');


	return $newFieldList;
}



//============================================================
//Print Data Base
//============================================================
function printDB($table,$tableFields){
	global $mysqli;
	global $key; //call in global


        $query = $mysqli->query("SELECT * FROM ".$table.";"); //build an sql $query
        $totalRows = $query->num_rows; //get a row count



        printTableHead($tableFields);

	for($i=0; $i<$totalRows; $i++){ //print each row
                $row = mysqli_fetch_array($query);
		printRow($row, $i, $tableFields);
	}

        echo "</table>"; //close out table
}

//print table header bar
function printTableHead($tableFields){
	echo "<table><tr>"; //begin printing html

	echo "<th>Edit</th>
		<th>Delete</th>
		  <th>ID</th>";


	$c = count($tableFields); //count headers/fields
	echo $c;
	for($i=0;$i<$c;$i++){ //print each header
		$header=$tableFields[$i]; //set temp $header

		echo '<th>'.$header.'</th>';
	}
	echo "</tr>"; //and the rest
}


//print single rows
function printRow($row, $i, $tableFields){
	global $key;

	if($i%2){ //shade every other line darker or lighter
//		echo '<tr class="altline">AA';
		echo '<tr style="color:#550055">';
	}
	else{
		echo "<tr>";	}

	//print edit & delete buttons
	echo "<td><a href=?edit=".$row[$key].">Edit</a></td>
               <td><a href=".$_SERVER['PHP_SELF']."?a=rusure&item=".$row[$key].">Delete</a></td>";
	echo '<td>'.$row['id'].'</td>';

	$c = count($tableFields); //temp $c(ount) variable
	for($j=0;$j<$c;$j++){ //print each $field
		$v = strtolower($tableFields[$j]);
		echo "<td>".$row[$v]."</td>";
	}


	echo "</tr>"; //and the rest

}


function printNewEditForm($id,$table,$tableFields){
	global $mysqli, $db_table;
	global $fields; //call global
	$cleanID = mysqli_real_escape_string($mysqli, $id);

	$q="SELECT * FROM ".$table." WHERE id=".$cleanID.";";
	echo 'q: '.$q.'<br><br>';

	$politician = $mysqli->query($q);
	$pTotal = $politician->num_rows;

	$superFields = buildSuperFields($table);
	$qnaFields = array("q1","q2","q3","q4","a1","a2","a3","a4");

        if($pTotal){
                $pRow = mysqli_fetch_array($politician);

		echo '<table>';
		echo "<form action=\"?update=".$cleanID."\" method=\"post\">";

		$c = count($superFields);
		for($j=0;$j<$c;$j++){ //print each field as an editable form option
			$v = strtolower($superFields[$j]['COLUMN_NAME']);
			$datatype = strtolower($superFields[$j]['DATA_TYPE']);
			if(in_array($v, $qnaFields)){
				echo '<tr><td>'.$v.'</td><td>'.$datatype.'</td><td><textarea name="'.$v.'" cols="80" rows="8">'.$pRow[$v].'</textarea></td></tr>';
			}
			else{
				echo '<tr><td>'.$v.'</td><td>'.$datatype.'</td><td><textarea name="'.$v.'" cols="80" rows="1">'.$pRow[$v].'</textarea></td></tr>';
			}
		}

		echo "<input type=\"submit\" value=\"update\"></form>
			<br></table>"; //and the rest of the form
        }
}



//form for adding a new entry
function printEntryForm($superFields){
	global $fields; //call in global
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
	global $fields, $db_table, $mysqli; //pull in globals

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
function updateEntry($id, $table, $tablefields){
	global $fields, $db_table, $key, $mysqli; //pull in globals

	$query = "UPDATE ".$table." SET ";//start query

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

	echo "q-".$query."-q";//print for fun

	if(!$mysqli->query($query)){//insert and test for error
		echo "there was a VERY critical error..".mysqli_error($mysqli).".";
	}
}

//delete entry
function deleteEntry($id, $table){
	global $mysqli, $db_table, $key; //call globals
	$v = mysqli_real_escape_string($mysqli, $id);//no injects plz
	$t = mysqli_real_escape_string($mysqli, $table);//no injects plz
	$query ="DELETE FROM ".$t." WHERE id=".$v.";";//build $query

	echo "executing ".$query."...<br><br>";

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
	<a href=\"".$_SERVER[PHP_SELF]."?a=delete&item=".$v."\">Yes</a>";
}


/////////ROUGH & UNFINISHED//////////
/////////Needs Cleaner Rewrite//////////
function candidatePhotoQuickUpdate($id,$photoName){
	global $mysqli; //call global

//	$id = mysqli_real_escape_string($mysqli, $id);//no injects plz
//	$photoName = mysqli_real_escape_string($mysqli, $photoName);


	echo '<form action="" method="post">';
	echo '<table>'; //start building entry form

////////CONFIRMATION CODE STUFF, for "SECURITY"... haha////////
// this needs to be updated... never got it implemented
//	echo '<tr><td>Confirmation Code</td><td>varchar</td><td><textarea name="confirm_add" cols="80" rows="1"></textarea></td</tr>';
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-///////

	echo '<tr><td>ID</td><td><input type="text" name="id" value="'.$id.'"></input></td></tr>';
	echo '<tr><td>Photo:</td><td><input type="text" name="photo" value="'.$photoName.'"></input></td></tr>';

	//finish form
	echo '<tr><td></td><td><input type="submit" value="Do it!"></td></tr>';
	echo '</table>';
	echo '</form>';

	if ($id==NULL){
	}
	else {
		$q = 'UPDATE candidates SET photo="'.$photoName.'" WHERE id="'.$id.'";';
		echo "Running this query: :".$q."<br><br>";
		if(!$mysqli->query($q)){//test query for error
			echo "OH no... there was an error-".mysqli_error($mysqli).".";
		}
		else{
			echo "Success?!<br><br>";
		}
		//process
	}

	printCandidateIDList();
}

function candidatePhotoQuickUpdateV2($id,$photoName){
	global $mysqli; //call global

//	$id = mysqli_real_escape_string($mysqli, $id);//no injects plz
//	$photoName = mysqli_real_escape_string($mysqli, $photoName);


	echo '<form action="" method="post">';
	echo '<table>'; //start building entry form

////////CONFIRMATION CODE STUFF, for "SECURITY"... haha////////
// this needs to be updated... never got it implemented
//	echo '<tr><td>Confirmation Code</td><td>varchar</td><td><textarea name="confirm_add" cols="80" rows="1"></textarea></td</tr>';
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-///////

	echo '<tr><td>ID</td><td><input type="text" name="id" value="'.$id.'"></input></td></tr>';
	echo '<tr><td>Photo:</td><td><input type="text" name="photo" value="'.$photoName.'"></input></td></tr>';

	//finish form
	echo '<tr><td></td><td><input type="submit" value="Do it!"></td></tr>';
	echo '</table>';
	echo '</form>';

	if ($id==NULL){
	}
	else {
		$q = 'UPDATE candidates SET photo="'.$photoName.'" WHERE id="'.$id.'";';
		echo "Running this query: :".$q."<br><br>";
		if(!$mysqli->query($q)){//test query for error
			echo "OH no... there was an error-".mysqli_error($mysqli).".";
		}
		else{
			echo "Success?!<br><br>";
		}
		//process
	}

	printCandidateIDListV2();
}
function printCandidateIDListV2(){
	global $mysqli;

	$q = "SELECT ID, name, photo FROM candidates";
	$query = $mysqli->query($q);

//		$row = mysqli_fetch_array($queryRaw);

	echo "modern vampires of the city<br>";
        $rowCount = $query->num_rows; //get a row count
	for($i=0; $i<$rowCount; $i++){
		$row = mysqli_fetch_array($query);
		if(!$row['photo']){
			echo "ID: ".$row['ID']." Name: ".$row['name']."- Photo: ".$row['photo']." | ";
		}
	}



}

/////////QUICK AND DRITY -- could be cleaned up//////////
//-----------------------------------------------------//
function printCandidateIDList(){
	global $mysqli;

	$q = "SELECT ID, name, photo FROM candidates";
	$query = $mysqli->query($q);

//		$row = mysqli_fetch_array($queryRaw);

	echo "modern vampires of the city<br>";
        $rowCount = $query->num_rows; //get a row count
	for($i=0; $i<$rowCount; $i++){
		$row = mysqli_fetch_array($query);
		echo "ID: ".$row['ID']." Name: ".$row['name']."- Photo: ".$row['photo']."<br>";
	}



}
//-----------------------------------------------------//

/////////QUICK AND DRITY -- could be cleaned up//////////
//-----------------------------------------------------//
function printPhotoCopyCommand(){
	global $mysqli;

	$sql="SELECT photo FROM candidates";

	$result = $mysqli->query($sql);

	$copy_statement = "cp ./poliphotos/{";
	$resultcount = mysqli_num_rows($result);
	echo "result count:".$resultcount."-<br>";
	for($i=0;$i<$resultcount;$i++){
	        if($i!=0){
	                $copy_statement = $copy_statement.",";
        	}
        	$row = mysqli_fetch_array($result);
        	$copy_statement = $copy_statement.$row['photo'];
	}
	$copy_statement = $copy_statement."}";

	echo "<br>";
	echo "copy statement: ".$copy_statement;
	echo "<br>";
}
//-----------------------------------------------------//

/////////ROUGH & UNFINISHED//////////
/////////Needs Cleaner Rewrite--currently based on photo updater//////////
function candidateQuestionBatchUpdate($race_id,$questions){
	global $mysqli; //call global

//	$id = mysqli_real_escape_string($mysqli, $id);//no injects plz
//	$photoName = mysqli_real_escape_string($mysqli, $photoName);


	echo '<form action="" method="post">';
	echo '<table>'; //start building entry form

////////CONFIRMATION CODE STUFF, for "SECURITY"... haha////////
// this needs to be updated... never got it implemented
//	echo '<tr><td>Confirmation Code</td><td>varchar</td><td><textarea name="confirm_add" cols="80" rows="1"></textarea></td</tr>';
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-///////

	echo '<tr><td>Race ID</td><td><input type="text" name="race_id" value="'.$race_id.'"></input></td></tr>';
	echo '<tr><td>Question 1</td><td><textarea name="q1" cols="80" rows="2">'.$questions[0].'</textarea></td></tr>';
	echo '<tr><td>Question 2</td><td><textarea name="q2" cols="80" rows="2">'.$questions[1].'</textarea></td></tr>';
	echo '<tr><td>Question 3</td><td><textarea name="q3" cols="80" rows="2">'.$questions[2].'</textarea></td></tr>';
	echo '<tr><td>Question 4</td><td><textarea name="q4" cols="80" rows="2">'.$questions[3].'</textarea></td></tr>';


	//finish form
	echo '<tr><td></td><td><input type="submit" value="Do it!"></td></tr>';
	echo '</table>';
	echo '</form>';


	if ($race_id==NULL){
	}
	else {
		$q = 'UPDATE candidates SET q1="'.$questions[0].'", q2="'.$questions[1].'", q3="'.$questions[2].'", q4="'.$questions[3].'" WHERE race_key="'.$race_id.'";';
		echo "Running this query: :".$q."<br><br>";
		if(!$mysqli->query($q)){//test query for error
			echo "OH no... there was an error-".mysqli_error($mysqli).".";
		}
		else{
			echo "Success?!<br><br>";
		}
		//process
	}

}

/////////ROUGH & UNFINISHED//////////
/////////Needs Cleaner Rewrite--currently based on photo updater//////////
function candidateQuestionBatchTweaker($race_id,$questions, $tweakmode){
	global $mysqli; //call global

//	$id = mysqli_real_escape_string($mysqli, $id);//no injects plz
//	$photoName = mysqli_real_escape_string($mysqli, $photoName);

	if($race_id){
		if($tweakmode!="yes"){//pull data
			$q = 'SELECT q1, q2, q3, q4 FROM candidates WHERE race_key="'.$race_id.'";';
			echo "Running this query: :".$q."<br><br>";
			$query = $mysqli->query($q);

			$row = mysqli_fetch_array($query);
			$questions[0]=$row['q1'];
			$questions[1]=$row['q2'];
			$questions[2]=$row['q3'];
			$questions[3]=$row['q4'];

		}

		echo "MONEY...<br>";
	}

	echo '<form action="" method="post">';
	echo '<table>'; //start building entry form

////////CONFIRMATION CODE STUFF, for "SECURITY"... haha////////
// this needs to be updated... never got it implemented
//	echo '<tr><td>Confirmation Code</td><td>varchar</td><td><textarea name="confirm_add" cols="80" rows="1"></textarea></td</tr>';
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-///////

	echo '<tr><td>Race ID</td><td><input type="text" name="race_id" value="'.$race_id.'"></input></td></tr>';
	echo '<tr><td>Question 1</td><td><textarea name="q1" cols="80" rows="2">'.$questions[0].'</textarea></td></tr>';
	echo '<tr><td>Question 2</td><td><textarea name="q2" cols="80" rows="2">'.$questions[1].'</textarea></td></tr>';
	echo '<tr><td>Question 3</td><td><textarea name="q3" cols="80" rows="2">'.$questions[2].'</textarea></td></tr>';
	echo '<tr><td>Question 4</td><td><textarea name="q4" cols="80" rows="2">'.$questions[3].'</textarea></td></tr>';
	echo '<tr><td><select name="tweakmode" id="tweakmode"><option selected="firstItem">Pull</option><option value="yes">YES</option></select></td>';


	//finish form
	echo '<tr><td></td><td><input type="submit" value="Do it!"></td></tr>';
	echo '</table>';
	echo '</form>';


	if ($race_id==NULL){
	}
	else {
		if($tweakmode=="yes"){
			$q = 'UPDATE candidates SET q1="'.$questions[0].'", q2="'.$questions[1].'", q3="'.$questions[2].'", q4="'.$questions[3].'" WHERE race_key="'.$race_id.'";';
			echo "Running this query: :".$q."<br><br>";
			if(!$mysqli->query($q)){//test query for error
				echo "OH no... there was an error-".mysqli_error($mysqli).".";
			}
			else{
				echo "Success?!<br><br>";
			}
		}
		//process
	}

}
/////////ROUGH & UNFINISHED//////////
/////////Needs Cleaner Rewrite--currently based on photo updater//////////
function candidateQuestionMagicUpdate2022($candidate_name,$answers){
	global $mysqli; //call global

//	$id = mysqli_real_escape_string($mysqli, $id);//no injects plz
//	$photoName = mysqli_real_escape_string($mysqli, $photoName);


////////CONFIRMATION CODE STUFF, for "SECURITY"... haha////////
// this needs to be updated... never got it implemented
//	echo '<tr><td>Confirmation Code</td><td>varchar</td><td><textarea name="confirm_add" cols="80" rows="1"></textarea></td</tr>';
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-///////

	echo '<form action="" method="post">';
	echo '<table>'; //start building entry form
	echo '<tr><td>Name</td><td><select name="candidate_name" id="candidate_name">';
	echo '<option selected="firstItem">Choose a Candidate</option>';

	$q = "SELECT id, name, contested, a1 FROM candidates;";
	$query = $mysqli->query($q);
	$names = array();
        $rowCount = $query->num_rows; //get a row count
	for($i=0; $i<$rowCount; $i++){
		$row = mysqli_fetch_array($query);
		if(!$row['a1']){
	//		if($row['contested']){
				echo '<option value="'.$row['name'].'">'.$row['name'].'</option>';
				array_push($nameList, $row['name']);
	//		}
		}
	}
	echo '</select></td>';

//	echo '<tr><td>Candidate ID</td><td><input type="text" name="race_id" value="'.$candidate_id.'"></input></td></tr>';
	echo '<tr><td>Answer 1</td><td><textarea name="a1" cols="120" rows="12">'.$answers[0].'</textarea></td></tr>';
	echo '<tr><td>Answer 2</td><td><textarea name="a2" cols="120" rows="12">'.$answers[1].'</textarea></td></tr>';
	echo '<tr><td>Answer 3</td><td><textarea name="a3" cols="120" rows="12">'.$answers[2].'</textarea></td></tr>';
	echo '<tr><td>Answer 4</td><td><textarea name="a4" cols="120" rows="12">'.$answers[3].'</textarea></td></tr>';
	echo '<tr><td><select name="rusure" id="rusure"><option selected="firstItem">No</option><option value="YES">YES</option></select></td>';

	//finish form
	echo '<td><input type="submit" value="Do it!"></td></tr>';
	echo '</table>';
	echo '</form>';



	if ($candidate_name==NULL){
	}
	else {
//		$q = 'UPDATE candidates SET q1="'.$questions[0].'", q2="'.$questions[1].'", q3="'.$questions[2].'", q4="'.$questions[3].'" WHERE race_key="'.$race_id.'";';
		$q = 'UPDATE candidates SET a1="'.$answers[0].'", a2="'.$answers[1].'", a3="'.$answers[2].'", a4="'.$answers[3].'" WHERE name="'.$candidate_name.'";';
		echo "Running this query: :".$q."<br><br>";

		$rusure = $_POST["rusure"];
		if($rusure=="YES"){
			if(!$mysqli->query($q)){//test query for error
				echo "OH no... there was an error-".mysqli_error($mysqli).".";
			}
			else{
				echo "Success?!<br><br>";
			}
		}
		else{
//			echo '<form action="" method="post">';
//			echo '<option selected="no">no</option>';
//			echo '<option value="YES">YES</option>';
//			echo '<input type="submit" value="Confirm">';
		}
		//process
	}

}

function printRaceKeyList(){
	global $mysqli;

	$q = "SELECT ID, title FROM contests";
//	echo "q:".$q."-<br>";
	$query = $mysqli->query($q);

//		$row = mysqli_fetch_array($queryRaw);

	echo "<br>";
        $rowCount = $query->num_rows; //get a row count
	for($i=0; $i<$rowCount; $i++){
		$row = mysqli_fetch_array($query);
		echo "ID: ".$row['ID']." Title: ".$row['title']."-<br>";
	}



}

function processCSVupload(){
	global $mysqli;

	if (isset($_POST["submit"]))
	{
                $filename = $_FILES["fileToUpload"]["tmp_name"];

                //if the file is not empty
                if($_FILES["fileToUpload"]["size"] > 0)
                {
                        //open the file
                        $file = fopen($filename, "r");

			//while loop to fetch rows of data & process it
			while (($getData = fgetcsv($file, 10000, ",")) !== FALSE)
			{

//		                $name = mysqli_real_escape_string($mysqli, $getData[0]); //no inject pls

				//breaking this up for readability
//				$sql = "INSERT into candidates (name,race_key,party,phone,email,website,twitter,facebook,instagram)";
//				$sql = $sql." values ('".$getData[0]."','".$getData[1]."','".$getData[2]."','".$getData[3]."','".$getData[4]."','".$getData[5]."','".$getData[6]."','".$getData[7]."','".$getData[8]."')";
				$sql = "INSERT into candidates (name,race_key,party)";
				$sql = $sql." values ('".$getData[0]."','".$getData[1]."','".$getData[2]."')";
                                //inset this
                                //
				echo "does it look good: ".$sql."-<br>";

				if(!$mysqli->query($sql)){//insert and test for error
					echo "<br><br>there was a VERY critical error...".mysqli_error();
				}
				else{
					echo "<br><br>my goodness... I think it worked...";
				}
//				$result = mysqli_query($con, $sql);
//				if(!isset($result)){
//					echo "errrrrorrrrrr with file or something ".$result." beeeeep<br>";
//				}
//				else {
//					echo "I think we got it!<br>";
//				}
			}//end of While loop

                        fclose($file);
                }//end of FILE size if
	}//end of POST if

	echo "ballllltimore! power play. by the shadows by your side MORE LOVE!!!!<br>";
}





function processCSVuploadV2(){
	global $mysqli;

	if (isset($_POST["submit"]))
	{
                $filename = $_FILES["fileToUpload"]["tmp_name"];

                //if the file is not empty
                if($_FILES["fileToUpload"]["size"] > 0)
                {
                        //open the file
                        $file = fopen($filename, "r");

			//while loop to fetch rows of data & process it
			while (($getData = fgetcsv($file, 10000, ",")) !== FALSE)
			{

//		                $name = mysqli_real_escape_string($mysqli, $getData[0]); //no inject pls

				//breaking this up for readability
//				$sql = "INSERT into candidates (name,race_key,party,phone,email,website,twitter,facebook,instagram)";
//				$sql = $sql." values ('".$getData[0]."','".$getData[1]."','".$getData[2]."','".$getData[3]."','".$getData[4]."','".$getData[5]."','".$getData[6]."','".$getData[7]."','".$getData[8]."')";
//				$sql = "INSERT into candidates (name,race_key,party)";
//				$sql = $sql." values ('".$getData[0]."','".$getData[1]."','".$getData[2]."')";

				$candidateID = $getData[0];
				$sql = "UPDATE candidates ";
				$sql = $sql."SET race_key='".$getData[1]."',name='".$getData[2]."',party='".$getData[3];
				$sql = $sql."',phone='".$getData[4]."',email='".$getData[5]."',website='".$getData[6];
				$sql = $sql."',facebook='".$getData[7]."',twitter='".$getData[8]."',instagram='".$getData[9];
				$sql = $sql."' WHERE ID='".$candidateID."';";






                              //inset this
                                //
				echo "does it look good: ".$sql."-<br>";

				if(!$mysqli->query($sql)){//insert and test for error
					echo "<br><br>there was a VERY critical error...".mysqli_error();
				}
				else{
					echo "<br><br>my goodness... I think it worked...";
				}
//				$result = mysqli_query($con, $sql);
//				if(!isset($result)){
//					echo "errrrrorrrrrr with file or something ".$result." beeeeep<br>";
//				}
//				else {
//					echo "I think we got it!<br>";
//				}
			}//end of While loop

                        fclose($file);
                }//end of FILE size if
	}//end of POST if

	echo "watch dem watch dem watch dem watch dem ballllltimore! power play. by the shadows by your side MORE LOVE!!!!<br>";
}
function processCSVuploadV3(){
	global $mysqli;

	if (isset($_POST["submit"]))
	{
                $filename = $_FILES["fileToUpload"]["tmp_name"];

                //if the file is not empty
                if($_FILES["fileToUpload"]["size"] > 0)
                {
                        //open the file
                        $file = fopen($filename, "r");

			//while loop to fetch rows of data & process it
			while (($getData = fgetcsv($file, 10000, ",")) !== FALSE)
			{

		                $name = mysqli_real_escape_string($mysqli, $getData[0]); //no inject pls
//
//				//breaking this up for readability

//				$candidateID = $getData[0];
				$sql = "UPDATE candidates ";
				$sql = $sql."SET contested='1";
//				$sql = $sql."SET phone='".$getData[1]."',email='".$getData[2]."',website='".$getData[3];
//				$sql = $sql."',twitter='".$getData[4]."',facebook='".$getData[5]."',instagram='".$getData[6];
				$sql = $sql."' WHERE ID='".$getData[0]."';";

//				$sql = $sql."SET race_key='".$getData[1]."',name='".$getData[2]."',party='".$getData[3];
//				$sql = $sql."' WHERE ID='".$candidateID."';";
//

//				$sql = "INSERT into candidates (party,name,seat)";
//				$sql = $sql." values ('".$getData[0]."','".$getData[1]."','".$getData[2]."')";





                              //inset this
                                //
				echo "does it look good: ".$sql."-<br>";

				if(!$mysqli->query($sql)){//insert and test for error
					echo "<br><br>there was a VERY critical error...".mysqli_error();
				}
				else{
					echo "<br><br>my goodness... I think it worked...";
				}

//				$result = mysqli_query($con, $sql);
//				if(!isset($result)){
//					echo "errrrrorrrrrr with file or something ".$result." beeeeep<br>";
//				}
//				else {
//					echo "I think we got it!<br>";
//				}
			}//end of While loop

                        fclose($file);
                }//end of FILE size if
	}//end of POST if

	echo "How was I to know... come back darling... give me another try...<br>";
}
function processCSVuploadV4(){
	global $mysqli;

	if (isset($_POST["submit"]))
	{
                $filename = $_FILES["fileToUpload"]["tmp_name"];

                //if the file is not empty
                if($_FILES["fileToUpload"]["size"] > 0)
                {
                        //open the file
                        $file = fopen($filename, "r");

			//while loop to fetch rows of data & process it
			while (($getData = fgetcsv($file, 10000, ",")) !== FALSE)
			{

		                $name = mysqli_real_escape_string($mysqli, $getData[0]); //no inject pls
//
//				//breaking this up for readability

//				$candidateID = $getData[0];
				$sql = "UPDATE candidates ";
				$sql = $sql."SET race_key='".$getData[1];
//				$sql = $sql."SET phone='".$getData[1]."',email='".$getData[2]."',website='".$getData[3];
//				$sql = $sql."',twitter='".$getData[4]."',facebook='".$getData[5]."',instagram='".$getData[6];
				$sql = $sql."' WHERE ID='".$getData[0]."';";

//				$sql = $sql."SET race_key='".$getData[1]."',name='".$getData[2]."',party='".$getData[3];
//				$sql = $sql."' WHERE ID='".$candidateID."';";
//

//				$sql = "INSERT into candidates (party,name,seat)";
//				$sql = $sql." values ('".$getData[0]."','".$getData[1]."','".$getData[2]."')";





                              //inset this
                                //
				echo "does it look good: ".$sql."-<br>";

				if(!$mysqli->query($sql)){//insert and test for error
					echo "<br><br>there was a VERY critical error...".mysqli_error();
				}
				else{
					echo "<br><br>my goodness... I think it worked...";
				}

//				$result = mysqli_query($con, $sql);
//				if(!isset($result)){
//					echo "errrrrorrrrrr with file or something ".$result." beeeeep<br>";
//				}
//				else {
//					echo "I think we got it!<br>";
//				}
			}//end of While loop

                        fclose($file);
                }//end of FILE size if
	}//end of POST if

	echo "How was I to know... come back darling... give me another try...<br>";
}


function findContestedRaces(){

	echo "haunting memories...<br>";

	global $mysqli;

	//pull all contests
	$q = "SELECT name, seat, party FROM candidates";
        $candidates = $mysqli->query($q); //build an sql $query
        $cTotal = $candidates->num_rows; //get a row count

	$contestList = array();

	echo "I could never love another<br>";

	//for each contest, pull each candidate
	for($i=0; $i<$cTotal; $i++){
		$row = mysqli_fetch_array($candidates);
	//	$contestList =
	//	array_push($contestList['
	}

	for($i=0; $i<$cTotal; $i++){
		$row = mysqli_fetch_array($contests);

		$q2 = "SELECT * FROM candidates WHERE race_key='".$row['id']."'";
		$candidates = $mysqli->query($q2);

	//if 0 candidates returned, highlight the Empty Contest
		$candidateCount = $candidates->num_rows;
		if(!$candidateCount){
			echo "OH NO - We found an empty one... ".$row['title']."-".$row['contested']."-<br>";
		}
	}

}

function fancyPhotoRenamerV2022($candidate, $photoName){

	global $mysqli;

////////quick and dirty - could be better
	$q = "SELECT ID, name, photo, contested FROM candidates";
	$query = $mysqli->query($q);

//		$row = mysqli_fetch_array($queryRaw);
	echo "modern vampires of the city<br>";


	//read all the file names in photo directory.
	$path = "./poliphotos/";
	$files = scandir($path);
	$nameList = array();
	//remove . and ..
//	$files = array_diff(scandir($path), array('.', '..'));

	if(is_dir($path)){
		if($dh = opendir($path)){
			while(($file = readdir($dh)) !== false){
//				echo "filename:".$file."<br>";
				echo "itworked";
			}
		}
		else{
			echo "Path Good - But Failed to Open DIR - who knows...<br>";
		}
	}
	else{
		echo "Path Error - not real maybe<br>";
	}

	echo "did it work?<br>";


	echo '<form action="" method="post">';
	echo '<table>'; //start building entry form
	echo '<tr><td><select name="candidate" id="candidate">';
	echo '<option selected="firstItem">Choose a Candidate</option>';
        $rowCount = $query->num_rows; //get a row count
	for($i=0; $i<$rowCount; $i++){
		$row = mysqli_fetch_array($query);
		if(!$row['photo']){
	//		if($row['contested']){
				echo '<option value="'.$row['name'].'">'.$row['name'].'</option>';
				array_push($nameList, $row['name']);
	//		}
		}
	}
	echo '</select></td>';
	echo '<td><select name="photo" id="photo">';
	echo '<option selected="secondItem">Choose a Photo</option>';
	foreach ($files as $value){
		echo '<option value="'.$value.'">'.$value.'</option>';
	}
	echo '</select></td>';
	echo '<td><input type="submit" value="DOIT"></td><tr>';
	echo '</table>';
	echo '</form>';

	echo "<br>";
	foreach($nameList as $value){
		echo " - ".$value;
	}
	echo "<br><br>";
	foreach($files as $value){
		echo " - ".$value;
	}
	echo "<br>end of lists<br>";

/*	echo "He had the NERVE file list:<br>";
	foreach ($files as $value){
		echo "$value <br>";
	}
*/
	//select candidate

	if ($candidate==NULL){
	}
	else {
		$q = 'UPDATE candidates SET photo="'.$photoName.'" WHERE name="'.$candidate.'";';
		echo "Running this query: :".$q."<br><br>";
		if(!$mysqli->query($q)){//test query for error
			echo "OH no... there was an error-".mysqli_error($mysqli).".";
		}
		else{
			echo "Success?!<br><br>";
		}
		//process
	}
	//select photo

	//update
}

//=============================================================
//=============================================================
//=============================================================
//=============================================================
//===============LEGACY CODE - Not Used Anymore================
//=============================================================
//=============================================================
//=============================================================
//=============================================================
//=============================================================
//=============================================================


//print editable row
//in a previous version I had the option to edit the row within the table. This has been dropped.
function printEditRow($row, $i, $tableFields){
	global $fields, $key; //call global

	if($i%2){ //shade alternate lines different
		echo "<tr class=\"altline\">";	}
	else{
		echo "<tr>";	}

	//start printing html form info
//	This was breaking things.......
//	echo "<td><form action=\"?".hrefBuilder("update",$row[$key])."\" method=\"post\">";
	echo "<td><form action=\"?update=".$row[$key]."\" method=\"post\">";

	$c = count($tableFields);
	for($j=0;$j<$c;$j++){ //print each field as an editable form option
		$v = strtolower($tableFields[$j]);
		echo "<input type=\"text\" value=\"".$row[$v]."\" name=\"".$v."\"></td><td>";
	}

	echo "<td><input type=\"submit\" value=\"+\"></form></td>
		<td></td>"; //and the rest of the form
	echo "</tr>";
}



//hrefBuilder
//obsolete at the moment
//this adds stuff onto the end of a url, so it can stack items
//add or update an $item with the $value
function hrefBuilder($item, $value){
        $query = $_SERVER['QUERY_STRING']; //this reads the query string in url after '='
        $qArray = explode('&',$query); //put it into an array, seperated by '&'
        $inQuery=0; //using this variable to see if we can find the $item in the $query
        if(!$qArray[0]==NULL){ //basically, if the $query is not empty, do this
                for($i=0;$i<count($qArray);$i++){
                        $x = explode('=',$qArray[$i]);
                        if($x[0]==$item){ //search for our $item, if we find it...
                                $x[1]=$value; //update the $item with the new $value
                                $inQuery=1;//we found it, so we set this flag to 'yes'
                        }
                        if($i==0){ //building out the $final new $query
                                $final=$x[0]."=".$x[1];}
                        else{
                                $final=$final."&".$x[0]."=".$x[1];}
                }
        }
        if(!$inQuery&&!$item==NULL&&!$value==NULL){ //if we didn't find our $value in the $query
                if($qArray[0]==NULL){ //build $final new $query
                        $final=$item."=".$value;}
                else{
                        $final=$final."&".$item."=".$value;}
        }
        return $final;
}

//print Rearange Row
function printRearangeRow($row, $i, $tableFields){
	global $fields; //call in globals
	global $key;

	if($i%2){ //shade every other line darker or lighter
		echo "<tr class=\"altline\">";	}
	else{
		echo "<tr>";	}

	//print edit & delete buttons
	echo "<td><a href=?edit=".$row[$key].">Edit</a></td>
               <td><a href=".$_SERVER['PHP_SELF']."?a=rusure&item=".$row[$key].">Delete</a></td>";
	echo '<td>'.$row['id'].'</td>';


	$c = count($fields); //temp $c(ount) variable
	for($j=0;$j<$c;$j++){ //print each $field
		$v = strtolower($fields[$j]);
		if($fields[$j]=="rank"){
			echo '<td><input type="text" value="'.$row['rank'].'" name="rowid'.$row['id'].'"></td>';
		}
		else{
			echo "<td>".$row[$v]."</td>";
		}
	}


	echo "</tr>"; //and the rest

}



//============================================================
//Print Fancy List
//needs re-coding
//============================================================
function printFancy(){
	global $mysqli, $key; //call in global

	$groupList = array();
	$queryData = array();
	$sortedQueryData = array();

        $queryRaw = $mysqli->query(buildQuery()); //build an sql $query
        $totalRows = $queryRaw->num_rows; //get a row count

	echo "FANCY4<br>";

	for($i=0; $i<$totalRows; $i++){
		$row = mysqli_fetch_array($queryRaw);
		$queryData[$row['rank']]=$row;
//		echo "title:".$row['title']." query:".$queryData[$row['rank']]['title']." end<br>";
	}
	ksort($queryData);

	echo "hmmm<br><br>";

        printTableHead();

	$qCount = count($queryData);
	for($i=0; $i<$qCount;$i++){
		$row = current($queryData);
//		echo "name:".$row['title']." <br>";
		printRow($row, $i);
		next($queryData);
	}
//	for($i=0; $i<$totalRows; $i++){ //print each row
//		$row = $queryData[$i];
//                $row = mysqli_fetch_array($queryData);
//		printRearangeRow($row, $i);

//	}

        echo "</table>"; //close out table
}


function fancyRankedCandidateList($table, $tableFields){

	global $mysqli, $key; //call in global
	$groupList = array(); //we'll need these later
	$queryData = array();
	$sortedQueryData = array();


        $queryRaw = $mysqli->query(buildQuery($table)); //build an sql $query
        $totalRows = $queryRaw->num_rows; //get a row count

	echo "Time after Time after Time I made it past the table, yay!<br><br>";

	for($i=0; $i<$totalRows; $i++){
		$row = mysqli_fetch_array($queryRaw);
		$queryData[$row['rank_value']]=$row;
//		echo "title:".$row['title']." query:".$queryData[$row['rank_value']]['title']." end<br>";
//		echo "title:".$row['title']." query:".$queryData[$row['rank_value']]['title']." end<br>";
	}
	ksort($queryData);


//	race_key, name, phone, email, website, facebook, twitter, instagram
	echo "<table><tr>"; //begin printing html
	echo "<th>ID</th><th>race_key</th><th>race_title</th><th>Name</th><th>Party</th>";
	echo "<th>Phone</th><th>Email</th><th>Website</th>";
	echo "<th>Facebook</th><th>Twitter</th><th>Instagram</th>";
	echo "</tr>";

	$totalRows = count($queryData);
	for($i=0; $i<$totalRows; $i++){ //print each row
//		$row = $sortedQueryData[$i];

		$row = current($queryData);
//                $row = mysqli_fetch_array($queryData);

		$qqq = 'SELECT id, race_key, name, party, phone, email, website, facebook, twitter, instagram FROM candidates WHERE race_key="'.$row['id'].'";';

		echo "<br>sql-qqq:".$qqq."-";
	        $qqqRaw = $mysqli->query($qqq); //build an sql $query
	        $totalQQQRows = $qqqRaw->num_rows; //get a row count

		echo "peace and unity";
		for($j=0; $j<$totalQQQRows; $j++){
			$candidateRow = mysqli_fetch_array($qqqRaw);
			$cr= $candidateRow;
			echo "<tr><td>".$cr['id']."</td><td>".$cr['race_key']."</td><td>".$row['title']."</td><td>".$cr['name']."</td><td>".$cr['party']."</td>";
			echo "<td>".$cr['phone']."</td><td>".$cr['email']."</td><td>".$cr['website']."</td>";
			echo "<td>".$cr['facebook']."</td><td>".$cr['twitter']."</td><td>".$cr['instagram']."</td>";
//			$queryData[$row['rank_value']]=$row;
		}

		next($queryData);
	}

}
function fancyRankedCandidateListV2($table, $tableFields){

	global $mysqli, $key; //call in global
	$groupList = array(); //we'll need these later
	$queryData = array();
	$sortedQueryData = array();


        $queryRaw = $mysqli->query(buildQuery($table)); //build an sql $query
        $totalRows = $queryRaw->num_rows; //get a row count

	echo "Knocking down stop signs Time after Time after Time I made it past the table, yay!<br><br>";

//	foreach ($files as $value){
//		echo '<option value="'.$value.'">'.$value.'</option>';
//	}
	for($i=0; $i<$totalRows; $i++){
		$row = mysqli_fetch_array($queryRaw);
		if($row['contested']){
			echo "Party:".$row['party']." Seat:".$row['title']." - ";
			if($row['party']=="Republican"){
				$queryData[$row['rank_value']+1]=$row;
			}
			else{
				$queryData[$row['rank_value']]=$row;
			}
		}
//		echo "title:".$row['title']." query:".$queryData[$row['rank_value']]['title']." end<br>";
//		echo "title:".$row['title']." query:".$queryData[$row['rank_value']]['title']." end<br>";
	}
	ksort($queryData);


//	race_key, name, phone, email, website, facebook, twitter, instagram
	echo "<table><tr>"; //begin printing html
	echo "<th>ID</th><th>race_key</th><th>race_title</th><th>Name</th><th>Party</th>";
	echo "<th>Phone</th><th>Email</th><th>Website</th>";
	echo "<th>Facebook</th><th>Twitter</th><th>Instagram</th>";
	echo "<th>q1</th><th>a1</th><th>q2</th><th>a2</th><th>q3</th><th>a3</th><th>q4</th><th>a4</th>";
	echo "</tr>";

	$totalRows = count($queryData);
	for($i=0; $i<$totalRows; $i++){ //print each row
//		$row = $sortedQueryData[$i];

		$row = current($queryData);
//                $row = mysqli_fetch_array($queryData);

//		$qqq = 'SELECT id, race_key, name, party, phone, email, website, facebook, twitter, instagram FROM candidates WHERE race_key="'.$row['id'].'";';
		$qqq = 'SELECT * FROM candidates WHERE race_key="'.$row['id'].'";';

		echo "<br>sql-qqq:".$qqq."-";
	        $qqqRaw = $mysqli->query($qqq); //build an sql $query
	        $totalQQQRows = $qqqRaw->num_rows; //get a row count

		echo "peace and unity";
		for($j=0; $j<$totalQQQRows; $j++){
			$candidateRow = mysqli_fetch_array($qqqRaw);
			$cr= $candidateRow;
			echo "<tr><td>".$cr['id']."</td><td>".$cr['race_key']."</td><td>".$row['title']."</td><td>".$cr['name']."</td><td>".$cr['party']."</td>";
			echo "<td>".$cr['phone']."</td><td>".$cr['email']."</td><td>".$cr['website']."</td>";
			echo "<td>".$cr['facebook']."</td><td>".$cr['twitter']."</td><td>".$cr['instagram']."</td>";
			echo "<td>".$cr['q1']."</td><td>".$cr['a1']."</td><td>".$cr['q2']."</td><td>".$cr['a2']."</td>";
			echo "<td>".$cr['q3']."</td><td>".$cr['a3']."</td><td>".$cr['q4']."</td><td>".$cr['a4']."</td>";

//			$queryData[$row['rank_value']]=$row;
		}

		next($queryData);
	}

}

//print ReArange Table
//needs to be re-coded
///////USES MAGIC VARIABLE for FIELD to SORT by///////////
function printRearangeForm($table, $tableFields){

	global $mysqli, $key; //call in global
	$groupList = array(); //we'll need these later
	$queryData = array();
	$sortedQueryData = array();


        $queryRaw = $mysqli->query(buildQuery($table)); //build an sql $query
        $totalRows = $queryRaw->num_rows; //get a row count

	echo "Time after Time after Time I made it past the table, yay!<br><br>";

	for($i=0; $i<$totalRows; $i++){
		$row = mysqli_fetch_array($queryRaw);
		$queryData[$row['rank_value']]=$row;
//		echo "title:".$row['title']." query:".$queryData[$row['rank_value']]['title']." end<br>";
//		echo "title:".$row['title']." query:".$queryData[$row['rank_value']]['title']." end<br>";
	}
	ksort($queryData);
//	$sortedQueryData = $queryData;

//	for($i=0; $i<$totalRows; $i++){ //now to start sorting stuff.
//		$queryData[$i] = mysqli_fetch_array($queryRaw);
//--------------MAGICVARIABLE.............vvvvvvvvvv...../
//		$rGroup = $queryData[$i]['race_group'];
//--------------MAGICVARIABLE.............^^^^^^^^^^.../
//		if(!array_search($rGroup,$groupList)){ //if we find the $magic in our list, then add it to our new group.
//			array_push($groupList,$rGroup);
//		}
//	}
//	sort($groupList);
//	$gCount = count($groupList);
//
//	for($i=0;$i<$gCount;$i++){
//		for($j=0;$j<$totalRows;$j++){
//--------------MAGICVARIABLE.............vvvvvvvvvv...../
//			if($queryData[$j]['race_group']==$groupList[$i]){
//--------------MAGICVARIABLE.............^^^^^^^^^^.../
//				array_push($sortedQueryData,$queryData[$j]);
//			}
//		}
//	}


	echo "We made it past the sort phase!!<br><br>";

	echo '<form action="?a=commitrearange" method="post">';

//        printTableHead($tableFields);
	echo "<table><tr>"; //begin printing html

	echo "<th>ID</th>";



//	echo "<th>Edit</th>
//		<th>Delete</th>

	echo "<th>Title</th><th>Rank_value</th><th>New Rank</th></tr>";
//	$totalRows = count($sortedQueryData);
	$totalRows = count($queryData);
	for($i=0; $i<$totalRows; $i++){ //print each row
//		$row = $sortedQueryData[$i];

		$row = current($queryData);
//                $row = mysqli_fetch_array($queryData);
		echo "<tr>";
//		echo "<td><a href=?edit=".$row[$key].">Edit</a></td>
  //      	       <td><a href=".$_SERVER['PHP_SELF']."?a=rusure&item=".$row[$key].">Delete</a></td>";
		echo '<td>'.$row['id'].'</td>';
		echo '<td>'.$row['title'].'</td>';
		echo '<td>'.$row['rank_value'].'</td>';
		echo '<td><input type="text" value="'.$row['rank_value'].'" name="rowid'.$row['id'].'"></td>';
		echo '</tr>';
		next($queryData);
	}

/*	$c = count($fields); //temp $c(ount) variable
	for($j=0;$j<$c;$j++){ //print each $field
		$v = strtolower($fields[$j]);
		if($fields[$j]=="rank"){
			echo '<td><input type="text" value="'.$row['rank'].'" name="rowid'.$row['id'].'"></td>';
		}
		else{
			echo "<td>".$row[$v]."</td>";
		}
	}

		printRearangeRow($row, $i, $tableFields);

	}
*/
        echo "</table>"; //close out table
	echo '<input type="submit" value="update"></form></td>';
}


function emptyContestFinder(){
	global $mysqli;

	//pull all contests
	$q = "SELECT * FROM contests";
        $contests = $mysqli->query($q); //build an sql $query
        $cTotal = $contests->num_rows; //get a row count

	echo "I could never love another<br>";

	//for each contest, pull each candidate
	for($i=0; $i<$cTotal; $i++){
		$row = mysqli_fetch_array($contests);

		$q2 = "SELECT * FROM candidates WHERE race_key='".$row['id']."'";
		$candidates = $mysqli->query($q2);

	//if 0 candidates returned, highlight the Empty Contest
		$candidateCount = $candidates->num_rows;
		if(!$candidateCount){
			echo "OH NO - We found an empty one... ".$row['title']."-".$row['contested']."-<br>";
		}
	}
}


//--------------OBSOLETE I THINK................/
function commitRearange($table){
	global $fields, $db_table, $key, $mysqli; //pull in globals

	$q1 = "SELECT id FROM ".$table.";";
        $idset = $mysqli->query($q1); //build an sql $query
        $totalRows = $idset->num_rows; //get a row count

	echo "jimbro".$totalRows."nski<br><br>";

	for($i=0;$i<$totalRows;$i++){
		$tmpRow = mysqli_fetch_array($idset);
		$postTmp = "rowid".$tmpRow['id'];
		$q2 = 'UPDATE '.$table.' SET rank_value='.$_POST[$postTmp].' WHERE id='.$tmpRow['id'].';';
		echo 'q2:'.$q2.' end<br>';
		if(!$mysqli->query($q2)){//insert and test for error
			echo "there was a VERY critical error...".mysqli_error();
			$i=$totalRows+1;
		}
		else{
			echo "this one was a success<br>";
		}

//		echo 'UPDATE contests SET rank='.$_POST[$postTmp].' WHERE id='.$tmpRow['id'].' end<br>';
	}

	echo "done.....<br><br>";
}

//used in the printFancy function
//build sql SELECT query
function buildQuery($table){
//	global $db_table; //call in global

        $query = "SELECT * FROM ".$table; //start building the $query string

        $query = $query.";";//cap off query with ';'

        return $query;
}
//--------------OBSOLETE I THINK................/


?>

