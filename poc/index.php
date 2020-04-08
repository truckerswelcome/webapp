<?php
require_once("nogit/creds.php");

function show_recent_locations($num){
   global $servername;
   global $username;
   global $password;
   global $dbname;
   
   $conn = new mysqli($servername, $username, $password, $dbname);
   if ($conn->connect_error) {
       return;
   }

   $sql = "SELECT * from facilities where approved=1 ORDER BY id desc LIMIT $num";
   $result = $conn->query($sql);
   $rowid = 0;

   if ( $result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
         $name       = $row["name"];
         $address    = $row["address"];
         $city       = $row["city"];
         $province   = $row["province_state"];
         $postal     = $row["postal"];
         $phone      = $row["phone"];
         $website    = $row["website"];
         $lat        = $row["lat"];
         $long       = $row["lng"];
         
         $diesel        = $row["diesel"];
         $washroom      = $row["washroom"];
         $shower        = $row["shower"];
         $parking       = $row["reststop"];
         $coffee        = $row["coffee"];
         $snacks        = $row["snacks"];
         $meal          = $row["meal"];
         $drivethrough  = $row["drivethrough"];
         $walkup        = $row["walkthrough"];
         $other         = $row["otherservices"];
         
         $services = array();
         if ($diesel) array_push($services, "Diesel");
         if ($washroom) array_push($services, "Washroom");
         if ($shower) array_push($services, "Shower");
         if ($parking) array_push($services, "Parking");
         if ($coffee) array_push($services, "Coffee");
         if ($snacks) array_push($services, "Snacks");
         if ($meal) array_push($services, "Meals");
         if ($drivethrough) array_push($services, "Drive-through");
         if ($walkup) array_push($services, "Walk-through");
         if ($other) array_push($services, "$other");
         $service_list = implode(', ', $services);
         
         $phone = phone_number_format($phone);
         $mapsurl = "https://maps.google.com/maps?q=$address,$city,$province,$postal";
         $mapsurl = str_replace(" ", "+", $mapsurl);
         
         $content = "'<div id=\"content\"><B>$name</B><BR>$address,$city,$province,$postal<BR>Phone: <A HREF=tel:$phone>$phone</A><BR><B>Services:</B>$service_list<BR><A HREF=\"$mapsurl\">Open In Google Maps</A></div>'";
         
         $iw = "infowindow = new google.maps.InfoWindow({content: $content});";
         $ll = "latLng = {lat: $lat, lng: $long};";
         $m  = "marker$rowid = new google.maps.Marker({position: latLng,map: map,title: '$name'});";
         $al = "marker$rowid.addListener('click', function() {infowindow.open(map, marker$rowid);});";
         
         echo "$iw\n\n$ll\n\n$m\n\n$al\n";
         
         $rowid++;
      }
   }
}
function phone_number_format($number) {
   // Allow only Digits, remove all other characters.
   $number = preg_replace("/[^\d]/","",$number);
   // get number length.
   $length = strlen($number);
   // if number = 10
   if($length == 10) {
      $number = preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $number);
   }
   return $number;
}
?>
<!DOCTYPE html >
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css">
    <script src="/jquery/jquery-3.4.1.min.js"></script>
    <script src="/bootstrap/js/bootstrap.min.js"></script>
    <title>Truckers Welcome</title>
  <style>
    #map {
      height: 100%;
    }
    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
    }
 </style>
  </head>
  <body style="margin:0px; padding:0px;" onload="initMap()">

  <div>
   <CENTER>
   <BR>
   <IMG src="/img/truck.png" width="80px"> The tech community stands with our frontline workers. Help our truck drivers and transit workers find services available to them during COVID-19 physical distancing measures.<BR><BR>
   <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#locateModal">Find a service</button>
   &nbsp;&nbsp;<button type="button" class="btn btn-success" onclick="window.location.href = '/addsite.php';">Add Business</button>
   &nbsp;&nbsp;<button type="button" class="btn btn-danger" onclick="window.location.href = '/help.html';">Help</button>
   &nbsp;&nbsp;<button type="button" class="btn btn-info" onclick="window.location.href = '/about.html';">About</button>
   </CENTER>

  <!-- Locate Modal -->
  <div class="modal fade" id="locateModal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Locate a business</h4>
        </div>
        <div class="modal-body">
		<p>Choose the service(s) you need (Mockup only - not functional yet):</P>
	  <FORM>
		  <input type="checkbox" id="wr" name="rw" value="Washroom">
                  <label for="takeout"> Washroom</label><br>

		  <input type="checkbox" id="br" name="br" value="Shower">
                  <label for="takeout"> Shower</label><br>

		  <input type="checkbox" id="pr" name="rr" value="Rest stop">
                  <label for="takeout"> Rest stop</label><br>

		  <input type="checkbox" id="cs" name="cs" value="Coffee / Snacks">
                  <label for="takeout"> Coffee / Snacks</label><br>

		  <input type="checkbox" id="dt" name="dt" value="Drive through">
                  <label for="takeout"> Drive Through (meal)</label><br>

		  <input type="checkbox" id="wt" name="wt" value="Walk through">
                  <label for="takeout"> Walk Through (meal)</label><br>
	  
	  </FORM>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Find</button>
        </div>
      </div>
      
    </div>
  </div>
</div>

  <!-- Locate Modal -->
  <div class="modal fade" id="addModal" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Add business</h4>
        </div>
        <div class="modal-body">
          <p>Add your business and the services you are offering (Mockup only - not functional yet):</p>
          <FORM>
		  <input type="text" placeholder="Business Name"><BR>
		  <input type="text" placeholder="Address"><BR>
		  <input type="text" placeholder="Contact info"><BR>
                  <input type="checkbox" id="wr" name="rw" value="Washroom">
                  <label for="takeout"> Washroom</label><br>
                  <input type="checkbox" id="br" name="br" value="Shower">
                  <label for="takeout"> Shower</label><br>
                  <input type="checkbox" id="pr" name="rr" value="Rest stop">
                  <label for="takeout"> Rest stop</label><br>
                  <input type="checkbox" id="cs" name="cs" value="Coffee / Snacks">
                  <label for="takeout"> Coffee / Snacks</label><br>
                  <input type="checkbox" id="dt" name="dt" value="Drive through">
                  <label for="takeout"> Drive Through (meal)</label><br>
                  <input type="checkbox" id="wt" name="wt" value="Walk through">
                  <label for="takeout"> Walk Through (meal)</label><br>
          
          </FORM>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Add</button>
        </div>
      </div>
    </div>
  </div>
</div>



    </div>

    <div><select id="locationSelect" style="width: 10%; visibility: hidden"></select></div>
    <div id="map" style="width: 100%; height: 90%"></div>
    <script>
      var map;
      var markers = [];
      var infoWindow;
      var locationSelect;
      var latLng;

      function initMap() {
       var toronto = {lat: 43.651070, lng: -79.347015};
       map = new google.maps.Map(document.getElementById('map'), {
         center: toronto,
         zoom: 9,
         mapTypeId: 'roadmap',
         mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU}
       });
       infoWindow = new google.maps.InfoWindow();

       <?php show_recent_locations(20); ?>

      }
  </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDpsl9O2zNsly1ygES951ava6Ckdtk5Wos&callback=initMap">
    </script>
  </body>
</html>

