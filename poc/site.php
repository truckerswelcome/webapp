<?php
require_once("/var/opt/webapp/nogit/creds.php");

try {
   $dbh = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
} catch (PDOException $e) {
   die("Connection failed: " . $e->getMessage() . "\n");
}

function sanitize($in) {
   return htmlentities(trim($in), ENT_QUOTES);
}

function processGet() {
   global $dbh;

   // fetch an entry from the database, given the id
   $entryid = filter_input(INPUT_GET, 'entryid', FILTER_SANITIZE_NUMBER_INT);

   $response = [];
   $sth = $dbh->prepare('SELECT * from facilities where id = ?');
   if ($sth->execute([$entryid]) === TRUE && $sth->rowCount()) {
      $row = $sth->fetch(PDO::FETCH_ASSOC);
      $response = [
         'name'            => sanitize($row['name']),
         'address'         => sanitize($row['address']),
         'city'            => sanitize($row['city']),
         'province_state'  => sanitize($row['province_state']),
         'country'         => sanitize($row['country']),
         'postal'          => sanitize($row['postal']),
         'phone'           => sanitize($row['phone']),
         'email'           => sanitize($row['email']),
         'website'         => sanitize($row['website']),
         'diesel'          => sanitize($row['diesel']),
         'washroom'        => sanitize($row['washroom']),
         'shower'          => sanitize($row['shower']),
         'parking'         => sanitize($row['reststop']),
         'coffee'          => sanitize($row['coffee']),
         'snacks'          => sanitize($row['snacks']),
         'meal'            => sanitize($row['meal']),
         'drivethrough'    => sanitize($row['drivethrough']),
         'walkthrough'     => sanitize($row['walkthrough']),
         'otherservices'   => sanitize($row['otherservices'])
      ];
   }
   return $response;
}

function getSiteObjectInput($inputType) {
   $obj = [];

   $filters = [
      'uname' => FILTER_DEFAULT,
      'whoareyou' => FILTER_DEFAULT,
      'bizname' => FILTER_DEFAULT,
      'street' => FILTER_DEFAULT,
      'city' => FILTER_DEFAULT,
      'province' => FILTER_DEFAULT,
      'country' => FILTER_DEFAULT,
      'postal' => FILTER_DEFAULT,
      'bemail' => FILTER_DEFAULT,
      'phone' => FILTER_DEFAULT,
      'website' => FILTER_DEFAULT,
      'entryid' => FILTER_SANITIZE_NUMBER_INT,
      'modpin' => FILTER_DEFAULT,
      'other' => FILTER_DEFAULT,
      'diesel' => FILTER_DEFAULT,
      'washroom' => FILTER_DEFAULT,
      'shower' => FILTER_DEFAULT,
      'parking' => FILTER_DEFAULT,
      'coffee' => FILTER_DEFAULT,
      'snacks' => FILTER_DEFAULT,
      'meal' => FILTER_DEFAULT,
      'drivethrough' => FILTER_DEFAULT,
      'walkup' => FILTER_DEFAULT,
      'delete' => FILTER_SANITIZE_NUMBER_INT
   ];
   $obj = filter_input_array($inputType, $filters);

   $obj['diesel']       = $obj['diesel'] == 'on' ? 1 : 0;
   $obj['washroom']     = $obj['washroom'] == 'on' ? 1 : 0;
   $obj['shower']       = $obj['shower'] == 'on' ? 1 : 0;
   $obj['parking']      = $obj['parking'] == 'on' ? 1 : 0;
   $obj['coffee']       = $obj['coffee'] == 'on' ? 1 : 0;
   $obj['snacks']       = $obj['snacks'] == 'on' ? 1 : 0;
   $obj['meal']         = $obj['meal'] == 'on' ? 1 : 0;
   $obj['drivethrough'] = $obj['drivethrough'] == 'on' ? 1 : 0;
   $obj['walkup']       = $obj['walkup'] == 'on' ? 1 : 0;

   return $obj;
}

function validateInput($obj, &$approval_status) {
   global $modpins;

   $form_errors = [];

   if (  !$obj['diesel'] &&
         !$obj['washroom'] && 
         !$obj['shower'] && 
         !$obj['parking'] && 
         !$obj['coffee'] && 
         !$obj['snacks'] && 
         !$obj['meal'] && 
         !$obj['drivethrough'] && 
         !$obj['walkup'] )
   {
      $form_errors[] = 'services';
   }

   // Basic sanity checking to make sure data submitted is not too large for the database fields
   if (strlen($obj['uname']) > 80 || empty($obj['uname']))
      $form_errors[] = 'uname';

   if (strlen($obj['whoareyou']) > 20 || empty($obj['whoareyou']))
      $form_errors[] = 'whoareyou';

   if (strlen($obj['bizname']) > 160 || empty($obj['bizname']))
      $form_errors[] = 'bizname';
      
   if (strlen($obj['street']) > 180 || empty($obj['street']))
      $form_errors[] = 'street';

   if (strlen($obj['city']) > 50 || empty($obj['city']))
      $form_errors[] = 'city';

   if (strlen($obj['province']) != 2)
      $form_errors[] = 'province';

   if (strlen($obj['country']) > 20 || empty($obj['country']))
      $form_errors[] = 'country';

   if (strlen($obj['postal']) > 7 || empty($obj['postal']))
      $form_errors[] = 'postal';

   $emailLength = strlen($obj['bemail']);
   if ($emailLength > 160 || ($emailLength > 0 && filter_var($obj['bemail'], FILTER_VALIDATE_EMAIL) === false))
      $form_errors[] = 'bemail';

   if (strlen($obj['phone']) > 16)
      $form_errors[] = 'phone';

   if (strlen($obj['website']) > 512)
      $form_errors[] = 'website';

   if (strlen($obj['modpin']) > 5)
      $form_errors[] = 'modpin';

   if (strlen($obj['other']) > 255)
      $form_errors[] = 'other';

   // If the user submitting the data is a moderator, we need to know so we can mark the data as valid immediately
   // So if this is a moderator, expect a PIN (temporary solution until we have users/roles)
   if (0 == strcmp($obj['whoareyou'], "Moderator") && empty($obj['modpin'])) {
      $form_errors[] = 'modpin';
   } else if (0 == strcmp($obj['whoareyou'], "Moderator")) {
      if (false === array_search($obj['modpin'], $modpins)) {
         $form_errors[] = 'modpin';
      } else {
         $approval_status = "approved";
      }
   }

   // Additional data format validation as appropriate
   $obj['postal'] = str_replace(" ","", $obj['postal']);
   $obj['postal'] = str_replace("-","", $obj['postal']);

   if (count($form_errors) > 0) {
      $valid_number = filter_var($obj['phone'], FILTER_SANITIZE_NUMBER_INT);
      if (empty($valid_number))
         $form_errors[] = 'phone';

      if (!empty($bemail) && !filter_var($bemail, FILTER_VALIDATE_EMAIL))
         $form_errors[] = 'bemail';

      $countries = ['CA', 'US'];
      if (!in_array($obj['country'], $countries))
         $form_errors[] = 'country';

      if (!ctype_alnum($obj['postal']))
         $form_errors[] = 'postal';
      
      if (0 == strcmp($obj['whoareyou'], "Who are you?")) {
         $form_errors[] = 'whoareyou';
      }
   }
   
   return $form_errors;
}

function geocode($obj) {
   global $geokey;
   $addrstr = $obj['street'].','.$obj['city'].','.$obj['province'].','.$obj['country'].','.$obj['postal'];
   $addrstr = str_replace(" ","+",$addrstr);
   
   $lat = 0;
   $long = 0;
   
   $gpsurl = "https://maps.googleapis.com/maps/api/geocode/json?address=$addrstr&key=$geokey";
   $gpsres = file_get_contents($gpsurl);
   if (!empty($gpsres)) {
      $gpsvar = json_decode($gpsres);
      if ($gpsvar->status == 'OK') {
         $lat = isset($gpsvar->results[0]->geometry->location->lat) ? $gpsvar->results[0]->geometry->location->lat : 0;
         $long = isset($gpsvar->results[0]->geometry->location->lng) ? $gpsvar->results[0]->geometry->location->lng : 0;
      }
   }
   return [$lat, $long];
}

function doUpdate($obj, $lat, $long, $approval_status) {
   global $dbh;

   $result = ['success' => false];

   // for now, deleting a record means to set the active flag to 0
   $active = 1;
   if ($obj['delete'] == 1) {
      $active = 0;
   }

   $stmt = <<<EOD
UPDATE facilities 
SET 
   submitted_by=?, submitter_type=?,
   name=?, address=?, city=?, province_state=?, country=?, postal=?, 
   email=?, phone=?, website=?, 
   approval_status=?, active=?, 
   diesel=?, washroom=?, shower=?, reststop=?, coffee=?, snacks=?, meal=?, drivethrough=?, walkthrough=?,
   otherservices=?,
   lat=?, lng=? 
WHERE id=?
EOD;
   $parameters = [
      $obj['uname'], $obj['whoareyou'],
      $obj['bizname'], $obj['street'], $obj['city'], $obj['province'], $obj['country'], $obj['postal'],
      $obj['bemail'], $obj['phone'], $obj['website'],
      $approval_status, $active,
      $obj['diesel'], $obj['washroom'], $obj['shower'], $obj['parking'], $obj['coffee'], $obj['snacks'], $obj['meal'], $obj['drivethrough'], $obj['walkup'],
      $obj['other'],
      $lat, $long, 
      $obj['entryid']
   ];

   $sth = $dbh->prepare($stmt);
   if ($sth->execute($parameters) === TRUE) {
      $result['success'] = true;
   } else {
      $result['sqlerror'] = [
         'errorInfo' => print_r($sth->errorInfo(), true),
         'statement' => $sql,
         'parameters' => print_r($parameters, true)
      ];
   }
   return $result;
}

function hasDuplicateCoordinates($lat, $long) {
   global $dbh;
   $sth = $dbh->prepare('SELECT id from facilities where lat=? AND lng=?');
   return ($sth->execute([$lat, $long]) === TRUE && $sth->rowCount());
}

function doInsert($obj, $lat, $long, $approval_status) {
   global $dbh;

   $result = ['success' => false];

   // prevent inserting a record that has duplicate coordinates
   if (hasDuplicateCoordinates($lat, $long)) {
      $result['errors'] = ['duplicateCoordinates'];
      return $result;
   }

   $stmt = <<<EOD
INSERT INTO facilities 
   (submitted_by, submitter_type, name, address, city, province_state, country, postal, email, phone, website, approval_status, diesel, washroom, shower, reststop, coffee, snacks, meal, drivethrough, walkthrough, otherservices, lat, lng) 
VALUES
   (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
EOD;

   $parameters = [
      $obj['uname'], $obj['whoareyou'],
      $obj['bizname'], $obj['street'], $obj['city'], $obj['province'], $obj['country'], $obj['postal'],
      $obj['bemail'], $obj['phone'], $obj['website'],
      $approval_status,
      $obj['diesel'], $obj['washroom'], $obj['shower'], $obj['parking'], $obj['coffee'], $obj['snacks'], $obj['meal'], $obj['drivethrough'], $obj['walkup'],
      $obj['other'],
      $lat, $long
   ];

   $sth = $dbh->prepare($stmt);
   if ($sth->execute($parameters) === TRUE) {
      $result['success'] = true;
   } else {
      $result['sqlerror'] = [
         'errorInfo' => print_r($sth->errorInfo(), true),
         'statement' => $sql,
         'parameters' => print_r($parameters, true)
      ];
   }
   return $result;
}

function isRecaptchaValid() {
   global $recaptchakey;

   $rc = false;

   if (isset($_POST['recaptcha_response'])) {
      $recaptcha_response = $_POST['recaptcha_response'];
   } else {
      return $rc;
   }

   // Build POST request:
   $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
   $recaptcha_secret = $recaptchakey;

   // Make and decode POST request:
   $url = $recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response;

   $recaptcha = file_get_contents($url);
   $recaptcha = json_decode($recaptcha);

   // Take action based on the score returned:
   if ($recaptcha->score >= 0.5) {
      $rc = true;
   }
   return $rc;
}

function processPost() {
   $obj = getSiteObjectInput(INPUT_POST);
   
   if (!isRecaptchaValid()) {
      return ['success' => false];
   }

   $approval_status = 'pending';
   $form_errors = validateInput($obj, $approval_status);

   // Convert the address into GPS coordinates so we can save those in the database and use them for location-based searches
   [$lat, $long] = geocode($obj);
   if ($lat == 0 && $long == 0 && count($form_errors) == 0){
      // If Google could not convert the address to GPS coordinates, return an error
      $form_errors[] = "geocode";
   }

   if (count($form_errors) > 0) {
      $result = ['success' => false, 'formErrors' => $form_errors];
   } else { 
      if ($obj['entryid'] > 0) {
         $result = doUpdate($obj, $lat, $long, $approval_status);
      } else {
         $result = doInsert($obj, $lat, $long, $approval_status);
      }
   }
   return $result;
}

$response = false;
switch ($_SERVER['REQUEST_METHOD']) {
   case 'GET': {
      $response = processGet();
      break;
   }
   case 'POST': {
      $response = processPost();
      break;
   }
}

echo json_encode($response);
?>