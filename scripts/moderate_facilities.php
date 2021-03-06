<?php

function sanitize($in) {
	return htmlentities(trim($in), ENT_QUOTES);
}

function connectToDatabase($envFile) {
	require_once "$envFile";

	try {
		return new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	} catch (PDOException $e) {
		die("Connection failed: " . $e->getMessage() . "\n");
	}
}


function servicesList($row) {
	$services = [];
	if ($row["diesel"] == 1) $services[] = "Diesel";
	if ($row["washroom"] == 1) $services[] = "Washroom";
	if ($row["shower"] == 1) $services[] = "Shower";
	if ($row["reststop"] == 1) $services[] = "Parking";
	if ($row["coffee"] == 1) $services[] = "Coffee";
	if ($row["snacks"] == 1) $services[] = "Snacks";
	if ($row["meal"] == 1) $services[] = "Meals";
	if ($row["drivethrough"] == 1) $services[] = "Drive-through";
	if ($row["walkthrough"] == 1) $services[] = "Walk-through";
	if (!empty($row["otherservices"])) $services[] = $row["otherservices"];

	return $services;
}


function getPendingFacilities($dbHandler) {
	$query = "SELECT * FROM `facilities` WHERE active AND approval_status='pending';";

	$statement = $dbHandler->prepare($query);
	$result = array();
	if ($statement->execute() === TRUE) {
		while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
			array_push($result, [
				'id' => sanitize($row['id']),
				'name' => sanitize($row['name']),
				'address' => sanitize($row['address']),
				'city' => sanitize($row['city']),
				'province_state' => sanitize($row['province_state']),
				'country' => sanitize($row['country']),
				'postal' => sanitize($row['postal']),
				'phone' => sanitize($row['phone']),
				'email' => sanitize($row['email']),
				'website' => sanitize($row['website']),
				'lat' => sanitize($row['lat']),
				'lng' => sanitize($row['lng']),
				'services_list' => implode(', ', servicesList($row))
			]);
		}
	}

	return $result;
}


function promptUserForValidAnswer($facility) {
	while (true) {
		echo "\n_________________________\n";
		echo "Facility details:\n";
		print_r($facility);

		echo "Choose any of the options:\n";
		echo "    'y' - to approve\n";
		echo "    'n' - to reject\n";
		echo "    's' - to stop and exit the process\n";
		echo "input is: ";
		$handle = fopen ("php://stdin","r");
		$line = fgets($handle);

		$answer = trim($line);
		if($answer === "y"){
			return "approve";
		} elseif ($answer === "n") {
			return "reject";
		} elseif ($answer === "s") {
			echo "Exiting the program as requested...\n";
			exit(0);
		} else {
			echo "\nInvalid answer. Please choose one of the following options on prompt ['y', 'n', 's']...\n\n";
		}
	}
}

function approveFacility($dbHandler, $facility) {
	echo "Approving...\n\n";
	$sql = "UPDATE `facilities` SET approval_status='approved' WHERE id=?;";
	$parameters = [$facility["id"]];
	$statement = $dbHandler->prepare($sql);
	if ($statement->execute($parameters) !== TRUE) {
		echo "[ERROR] approving facility " . $facility['id'] . ": " . $facility['name'] . "...";
		exit(1);
	}
}

function autoApproveAll($dbHandler) {
	echo "Approving...\n\n";
	$sql = "UPDATE `facilities` SET approval_status='approved' WHERE active=1 AND approval_status=?;";
	$parameters = ["pending"];
	$statement = $dbHandler->prepare($sql);
	if ($statement->execute($parameters) !== TRUE) {
		echo "[ERROR] approving facility " . $facility['id'] . ": " . $facility['name'] . "...";
		exit(1);
	}
}


function rejectFacility($dbHandler, $facility) {
	echo "Rejecting...\n\n";
	$sql = "UPDATE `facilities` SET approval_status='rejected' WHERE id=?;";

	$parameters = [$facility["id"]];
	$statement = $dbHandler->prepare($sql);
	if ($statement->execute($parameters) !== TRUE) {
		echo "[ERROR] rejecting facility " . $facility['id'] . ": " . $facility['name'] . "...";
		exit(1);
	}
}



function main() {
	echo "Starting Facilities Moderation...\n";
	$envFile = getenv('ENV_FILE');
	$dbHandler = connectToDatabase($envFile);

	$pendingFacilities = getPendingFacilities($dbHandler);
	foreach ($pendingFacilities as $facility) {
		$answer = promptUserForValidAnswer($facility);

		if ($answer === "approve") {
			approveFacility($dbHandler, $facility);
		} else {
			rejectFacility($dbHandler, $facility);
		}
	}

	echo "Done moderating...See you next time!\n";
}

if ($argc == 1){
	main();
}else if ($argc >= 3 && $argv[1] == "modcron"){
	echo "Checking for entries needing moderation\n";
	$envFile = $argv[2];
	$dbHandler = connectToDatabase($envFile);
	$pendingFacilities = getPendingFacilities($dbHandler);
	$numEntries = sizeof($pendingFacilities);
	if ($numEntries){
		require "$envFile";
		$message = "There are $numEntries entries in the facilities database in need of moderator approval.";
		
		if ($argc == 4 && $argv[3] == "autoapprove"){
			autoApproveAll($dbHandler);
			$message = $message . " Entries auto-approved by cron.\n";
		}      
		$hmessage = array('payload' => json_encode(array('text' => $message)));
		// Use curl to send your message
		$c = curl_init($slackhook);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_POSTFIELDS, $hmessage);
		curl_exec($c);
		curl_close($c);      
	}

}
