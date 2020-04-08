<?php
require_once("nogit/creds.php");

$msg = "Add a site";

if (array_key_exists('bizname', $_POST)){
   $conn = new mysqli($servername, $username, $password, $dbname);
   // Check connection
   if ($conn->connect_error) {
       die("Connection failed: " . $conn->connect_error);
   }
   
   // Initiatlize the parameters that will be set based on the form input
   $lat=0;
   $long=0;
   $washroom = 0;
   $shower = 0;
   $parking = 0;
   $coffee = 0;
   $snacks = 0;
   $meal = 0;
   $drivethrough = 0;
   $walkup = 0;
   $approved=0;
   $form_errors = array();
   
   // Extract form data safely
   $submitter_name = trim(stripslashes(mysqli_real_escape_string($conn,htmlspecialchars(stripslashes($_POST['uname'])))));
   $submitter_type = mysqli_real_escape_string($conn,htmlspecialchars(stripslashes($_POST['whoareyou'])));
   
   $bizname    = mysqli_real_escape_string($conn,htmlspecialchars(stripslashes($_POST['bizname'])));
   $street     = mysqli_real_escape_string($conn,htmlspecialchars(stripslashes($_POST['street'])));
   $city       = mysqli_real_escape_string($conn,htmlspecialchars(stripslashes($_POST['city'])));
   $province   = mysqli_real_escape_string($conn,htmlspecialchars(stripslashes($_POST['province'])));
   $country    = mysqli_real_escape_string($conn,htmlspecialchars(stripslashes($_POST['country'])));
   $postal     = mysqli_real_escape_string($conn,htmlspecialchars(stripslashes($_POST['postal'])));
   $bemail     = mysqli_real_escape_string($conn,htmlspecialchars(stripslashes($_POST['bemail'])));
   $phone      = mysqli_real_escape_string($conn,htmlspecialchars(stripslashes($_POST['phone'])));
   $website    = mysqli_real_escape_string($conn,htmlspecialchars(stripslashes($_POST['website'])));
   $modpin    = mysqli_real_escape_string($conn,htmlspecialchars(stripslashes($_POST['modpin'])));

   // Extract what facilities have been selected by expecting a specific checkbox value to be "on". Ignore other values
   if (array_key_exists('washroom', $_POST)){
      if ("on" == mysqli_real_escape_string($conn,htmlspecialchars(stripslashes($_POST['washroom'])))){
         $washroom = true;
         $cwashroom = "checked";
      }
   }
   if (array_key_exists('shower', $_POST)){
      if ("on" == mysqli_real_escape_string($conn,htmlspecialchars(stripslashes($_POST['shower'])))){
         $shower = true;
         $cshower = "checked";
      }
   }
   if (array_key_exists('parking', $_POST)){
      if ("on" == mysqli_real_escape_string($conn,htmlspecialchars(stripslashes($_POST['parking'])))){
         $parking = true;
         $cparking = "checked";
      }
   }
   if (array_key_exists('coffee', $_POST)){
      if ("on" == mysqli_real_escape_string($conn,htmlspecialchars(stripslashes($_POST['coffee'])))){
         $coffee = true;
         $ccoffee = "checked";
      }
   }
   if (array_key_exists('snacks', $_POST)){
      if ("on" == mysqli_real_escape_string($conn,htmlspecialchars(stripslashes($_POST['snacks'])))){
         $snacks = true;
         $csnacks = "checked";
      }
   }
   if (array_key_exists('meal', $_POST)){
      if ("on" == mysqli_real_escape_string($conn,htmlspecialchars(stripslashes($_POST['meal'])))){
         $meal = true;
         $cmeal = "checked";
      }
   }
   if (array_key_exists('drivethrough', $_POST)){
      if ("on" == mysqli_real_escape_string($conn,htmlspecialchars(stripslashes($_POST['drivethrough'])))){
         $drivethrough = true;
         $cdrivethrough = "checked";
      }
   }
   if (array_key_exists('walkup', $_POST)){
      if ("on" == mysqli_real_escape_string($conn,htmlspecialchars(stripslashes($_POST['walkup'])))){
         $walkup = true;
         $cwalkup = "checked";
      }
   }
   
   if (!$washroom && !$shower && !$parking && !$coffee && !$snacks && !$meal && !$drivethrough && !$walkup){
      $form_errors['Services'] = 'You must offer at least one of the services';
   }


   // Basic sanity checking to make sure data submitted is not too large for the database fields
   if (strlen($submitter_name) > 80) $form_errors['Your Name'] = 'Invalid';
   if (strlen($submitter_type) > 20) $form_errors['Who are you'] = 'Invalid';
   if (strlen($bizname) > 160) $form_errors['Business Name'] = 'Invalid';
   if (strlen($street) > 180) $form_errors['Street Address'] = 'Invalid';
   if (strlen($city) > 20) $form_errors['City'] = 'Invalid';
   if (strlen($province) > 20) $form_errors['Province'] = 'Invalid';
   if (strlen($country) > 20) $form_errors['Country'] = 'Invalid';
   if (strlen($postal) > 7) $form_errors['Postal Code'] = 'Invalid';
   if (strlen($bemail) > 160) $form_errors['Email'] = 'Invalid';
   if (strlen($phone) > 16) $form_errors['Phone'] = 'Invalid';
   if (strlen($website) > 512) $form_errors['Website'] = 'Invalid';
   if (strlen($modpin) > 5) $form_errors['Moderator PIN'] = 'Invalid';

   // If the user submitting the data is a moderator, we need to know so we can mark the data as valid immediately
   // So if this is a moderator, expect a PIN (temporary solution until we have users/roles)
   if (0 == strcmp($submitter_type, "Moderator") && empty($modpin)) 
      $form_errors['Moderator PIN'] = 'Invalid';
   else if (0 == strcmp($submitter_type, "Moderator")){
      if (false === array_search($modpin, $modpins)){
         $form_errors['Moderator PIN'] = 'Invalid';
      }else{
         $approved = 1;
      }
   }

   // Additional data format validation as appropriate
   $postal = str_replace(" ","",$postal);
   $postal = str_replace("-","",$postal);

   if (0 == sizeof($form_errors)){
      $valid_number = filter_var($phone,FILTER_SANITIZE_NUMBER_INT);
      if (empty($valid_number)) $form_errors['Phone'] = 'Invalid';
      if (!empty($bemail))
         if (!filter_var($bemail, FILTER_VALIDATE_EMAIL)) $form_errors['Email'] = 'Invalid';
      if (0 == strcasecmp($country, "canada"))
         $country = "Canada";
      else if (0 == strcasecmp($country, "usa"))
         $country = "USA";
      else
         $form_errors['Country'] = 'Must be Canada or USA';
      if (!ctype_alnum($postal)) $form_errors['Postal Code'] = 'Invalid';
      
      if (0 == strcmp($submitter_type, "Who are you?")){
         $form_errors['Who are you'] = 'Please select an option';
      }
   }
   
   // Convert the address into GPS coordinates so we can save those in the database and use them for location-based searches
   if (0 == sizeof($form_errors)){
      $addrstr = "$street,$city,$province,$country,$postal";
      $addrstr = str_replace(" ","+",$addrstr);
      
      $gpsurl = "https://maps.googleapis.com/maps/api/geocode/json?address=$addrstr&key=$geokey";
      $gpsres = file_get_contents($gpsurl);
      if (!empty($gpsres)){
         $gpsvar = json_decode($gpsres);
         if($gpsvar->status=='OK'){
            $lat = isset($gpsvar->results[0]->geometry->location->lat) ? $gpsvar->results[0]->geometry->location->lat : 0;
            $long = isset($gpsvar->results[0]->geometry->location->lng) ? $gpsvar->results[0]->geometry->location->lng : 0;
         }
      }
   }
   
   // If Google could not convert the address to GPS coordinates, return an error
   if ($lat == 0 && $long == 0 && 0 == sizeof($form_errors)){
      $form_errors['Street Address'] = "Failed to get GPS location";
   }
   
   if (0 != sizeof($form_errors)){
      // If we're here, then there was at least one error in processing the submitted form
      echo "I'm sorry, there were some errors, please correct these and try again.<BR>";
      foreach ($form_errors as $key => $val){
         echo "<FONT COLOR=RED><B>$key:</B></FONT> $val<BR>";
      }
   }else{
      // If we are here there was no error with the form data, so add it to the DB and move on
      
      $sql = "INSERT INTO facilities (submitted_by,submitter_type,name,address,city,province_state,country,postal,email,phone,website,approved,washroom,shower,reststop,coffee,snacks,meal,drivethrough,walkthrough,lat,lng) VALUES ('$submitter_name', '$submitter_type', '$bizname', '$street', '$city', '$province', '$country', '$postal', '$bemail', '$phone', '$website', $approved, $washroom, $shower, $parking, $coffee, $snacks, $meal, $drivethrough, $walkup,$lat,$long)";
      
      if ($conn->query($sql) === TRUE) {
         $msg = "Added $bizname - Add another location";
         $msg2 = "";
         
         // Clear the variables used to persist form field values in the event of an error
         $submitter_name = "";
         $submitter_type = "";
         $bizname    = "";
         $street     = "";
         $city       = "";
         $province   = "";
         $country    = "";
         $postal     = "";
         $bemail     = "";
         $phone      = "";
         $website    = "";

         $washroom = false;
         $shower = false;
         $parking = false;
         $coffee = false;
         $snacks = false;
         $meal = false;
         $drivethrough = false;
         $walkup = false;
         
         $cwashroom = "";
         $cshower = "";
         $cparking = "";
         $ccoffee = "";
         $csnacks = "";
         $cmeal = "";
         $cdrivethrough = "";
         $cwalkup = "";
      } else {
         $msg = "Error adding location<BR>";
         $msg2 = "$conn->error<BR>$sql";
      }
   }

   $conn->close();
}

?>
<html>
<head>
   <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
   <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
   <link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css">
   <script src="/jquery/jquery-3.4.1.min.js"></script>
   <script src="/bootstrap/js/bootstrap.min.js"></script>
   <title>Truckers Welcome - Add a site</title>
</head>
<body style="margin:10px; padding:10px;">
<A HREF="/"><IMG src="/img/truck.png" width="80px"></A>
<H3><?php echo "$msg" ?></H3>
<?php 
   if (!empty($msg2)) 
      echo "Please report this error: $msg2";
?>
<FORM method=POST action=addsite.php>
<div class="form-group">
   <input type="text" class="form-control" id="uname" name="uname" placeholder="Your Name, (e.g. John Doe)" <?php echo "value=\"$submitter_name\""?>>
   <select class="form-control" id="whoareyou" name="whoareyou" onchange="checkWho()">
   <option>Who are you?</option>
   <option>Driver</option>
   <option>Facility Owner</option>
   <option>Moderator</option>
   <option>Other</option>
   </select>   

   <input type="text" class="form-control" id="bizname" name="bizname" placeholder="Business Name, (e.g. Tim Hortons)" <?php echo "value=\"$bizname\""?>>
   <input type="text" class="form-control" id="street" name="street" placeholder="Street Address (e.g. 11 Monogram Pl)" <?php echo "value=\"$street\""?>>
   <input type="text" class="form-control" id="city" name="city" placeholder="City / Town (e.g. Trenton)" <?php echo "value=\"$city\""?>>
   <input type="text" class="form-control" id="province" name="province" placeholder="Province/State (e.g. Ontario)" <?php echo "value=\"$province\""?>>
   <input type="text" class="form-control" id="country" name="country" placeholder="Country (e.g. Canada)" <?php echo "value=\"$country\""?>>
   <input type="text" class="form-control" id="postal" name="postal" placeholder="Postal Code (e.g. K8V 5P8)" <?php echo "value=\"$postal\""?>>
   <input type="text" class="form-control" id="bemail" name="bemail" placeholder="Business email address (blank if unknown)" <?php echo "value=\"$bemail\""?>>
   <input type="text" class="form-control" id="phone" name="phone" placeholder="Phone (e.g. 613-394-2000)" <?php echo "value=\"$phone\""?>>
   <input type="text" class="form-control" id="website" name="website" placeholder="Website (e.g. www.timhortons.ca)" <?php echo "value=\"$website\""?>>
   <div id="modpindiv" style="display: none;"><input type="text" class="form-control" id="modpin" name="modpin" placeholder="Moderator PIN" <?php echo "value=\"$modpin\""?>></div>
</div>
<B>Services available at this location</B>
<div class="checkbox">
 <label><input type="checkbox" id="washroom" name="washroom" <?php echo $cwashroom;?>> Washroom</label>
</div>
<div class="checkbox">
 <label><input type="checkbox" id="shower" name="shower" <?php echo $cshower;?>> Shower</label>
</div>
<div class="checkbox">
 <label><input type="checkbox" id="parking" name="parking" <?php echo $cparking;?>> Parking</label>
</div>
<div class="checkbox">
 <label><input type="checkbox" id="coffee" name="coffee" <?php echo $ccoffee;?>> Coffee</label>
</div>
<div class="checkbox">
 <label><input type="checkbox" id="snacks" name="snacks" <?php echo $csnacks;?>> Snacks</label>
</div>
<div class="checkbox">
 <label><input type="checkbox" id="meal" name="meal" <?php echo $cmeal;?>> Meal</label>
</div>
<div class="checkbox">
 <label><input type="checkbox" id="drivethrough" name="drivethrough" <?php echo $cdrivethrough;?>> Drive Through (trucks)</label>
</div>
<div class="checkbox">
 <label><input type="checkbox" id="walkup" name="walkup" <?php echo $cwalkup;?>> Walk Through (walk up to drivethrough window)</label>
</div>

<button type="submit" class="btn btn-primary">Submit</button>

<script>
function checkWho() {
   var e = document.getElementById("whoareyou");
   var strUser = e.options[e.selectedIndex].text;
   if (strUser == "Moderator"){
      document.getElementById("modpindiv").style.display = "block";
   }else{
      document.getElementById("modpindiv").style.display = "none";
   }
}
</script>

</form>
</body>
</html>
