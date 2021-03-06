<?php 
	require("../../config.php");
	
	// see fail peab olema siis seotud kõigiga kus
	// tahame sessiooni kasutada
	// saab kasutada nüüd $_SESSION muutujat
	session_start();
	
	$database = "if16_kirikotk_4";
	// functions.php
	
	function signup($email, $password, $name, $nimi) {
		
		$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $GLOBALS["database"]);
		
		$stmt = $mysqli->prepare("INSERT INTO er_users (email, password) VALUE (?, ?)");
		echo $mysqli->error;
		
		$stmt->bind_param("ss", $email, $password);
		
		if ( $stmt->execute() ) {
			echo "";
		} else {
			echo "ERROR ".$stmt->error;
		}
		
	}
	
	
	function login($email, $password) {
		
		$notice = "";
		
		$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $GLOBALS["database"]);
		
		$stmt = $mysqli->prepare("
			SELECT id, email, password, created
			FROM er_users
			WHERE email = ?
		");
		
		echo $mysqli->error;
		
		//asendan küsimärgi
		$stmt->bind_param("s", $email);
		
		//rea kohta tulba väärtus
		$stmt->bind_result($id, $emailFromDb, $passwordFromDb, $created);
		
		$stmt->execute();
		
		//ainult SELECT'i puhul
		if($stmt->fetch()) {
			// oli olemas, rida käes
			//kasutaja sisestas sisselogimiseks
			$hash = hash("sha512", $password);
			
			if ($hash == $passwordFromDb) {
				
				$_SESSION["userId"] = $id;
				$_SESSION["userEmail"] = $emailFromDb;
				//echo "ERROR";
				
				header("Location: gallerry.php");
				
			} else {
				$notice = "wrong password";
			}
			
			
		} else {
			
			//ei olnud ühtegi rida
			$notice = "Can't found this ".$email." email ";
		}
		
		
		$stmt->close();
		$mysqli->close();
		
		return $notice;
		
	
	}

	function saveEvent($picturl, $pictname) {
		
		$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $GLOBALS["database"]);
		
		$stmt = $mysqli->prepare("INSERT INTO er_pict (picturl, pictname, email) VALUE (?, ?, ?)");
		echo $mysqli->error;
		
		$stmt->bind_param("sss", $picturl, $pictname, $_SESSION["userEmail"]);
		
		if ( $stmt->execute() ) {
		} else {
			echo "ERROR ".$stmt->error;
		}
		
	}
	
	
	function getAllPeople() {
		
		$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $GLOBALS["database"]);
		
		$stmt = $mysqli->prepare("
		SELECT id, picturl, pictname, email
		FROM er_pict
		");
		$stmt->bind_result($id, $picturl, $pictname, $email);
		$stmt->execute();
		
		$results = array();
		
		while($stmt->fetch()) {
			
			$human = new StdClass();
			$human->id = $id;
			$human->picturl = $picturl;
			$human->pictname = $pictname;
			$human->email = $email;
			
			array_push($results, $human);
			
		}
		return $results;
		
	}
	
	function getUserInfo() {
		
		$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $GLOBALS["database"]);
		
		$stmt = $mysqli->prepare("
		SELECT id, picturl, pictname, email
		FROM er_pict
		WHERE email = ?
		");
		
		$stmt->bind_param("s", $_SESSION["userEmail"]);
		$stmt->bind_result($id, $picturl, $pictname, $email);
		$stmt->execute();
		$results = array();
		
		while($stmt->fetch()) {	
			$user = new StdClass();
			$user->id = $id;
			$user->picturl = $picturl;
			$user->pictname = $pictname;
			$user->email = $email;
			
			array_push($results, $user);	
		}
		return $results;	
	}
	
	
	function deleteart($id){
		
		$mysqli = new mysqli($GLOBALS["serverHost"], 
		$GLOBALS["serverUsername"], 
		$GLOBALS["serverPassword"], 
		$GLOBALS["database"]);		
		
		$stmt = $mysqli->prepare("
		DELETE from er_pict WHERE id=?");
		$stmt->bind_param("i", $id);
		if($stmt->execute()){
		}
		$stmt->close();	
	}
	
	function getsingleId($show_id){
			
			$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $GLOBALS["database"]);
			
			$stmt = $mysqli->prepare("
			SELECT picturl
			FROM er_pict 
			WHERE id = ?");
			
			$stmt->bind_param("i", $show_id);
			$stmt->bind_result($picturl);
			$stmt->execute();
			$singleId = new Stdclass();
			
			if($stmt->fetch()){
				$singleId->picturl = $picturl;
			}else{
				header("Location: delete.php");
				exit();
			}
			$stmt->close();
			return $singleId;
		}
	
	function cleanInput($input) {
		
		$input = trim($input);

		$input = stripslashes($input);

		$input = htmlspecialchars($input);
		
		return $input;
		
	}
	
?>