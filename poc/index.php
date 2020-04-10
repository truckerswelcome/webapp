<!DOCTYPE html >
<head>
   <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
   <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
   <link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css">
   <script src="/jquery/jquery-3.4.1.min.js"></script>
   <script src="/bootstrap/js/bootstrap.min.js"></script>
   <title>Truckers Welcome</title>
   <link rel="stylesheet" type="text/css" href="css/tw.css">
</head>
<body> 
   <aside class="sidenav">
      <div>
         <a href="index.php"><img src="img/truck.png" width="80px"></a>
         <div class="nav-close-icon">
            <div></div>
         </div>
      </div>
      <ul>
         <li><a href="addsite.php">Add Business</a></li>
         <li><a href="help.html">Help</a></li>
         <li><a href="about.html">About</a></li>
         <li><a href="mailto:help@truckerswelcome.ca?subject=Mail from TruckersWelcome">Email Us</a></li>
         <li><a href="tos.html">Terms of Service</a></li>
      </ul>
   </aside>
   <div class="overlay"></div>
   <main>
      <div id="search">
         <div class="nav-icon">
            <div></div>
         </div>
         <form>
            <input class="form-control" type="text" name="location" placeholder="Search TruckersWelcome" autocomplete="off"/>
            <input type="hidden" name="lat"/>
            <input type="hidden" name="lng"/>
            <input type="hidden" name="options"/>
         </form>
      </div>
      <div id="search-this-area">
         <button type="button" class="btn btn-light btn-sm">Search this area</button>
      </div>
      <div id="map"></div>
   </main>
   <?php require_once 'html/start.html';?>
   <script src="js/tw.js"></script>
   <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDpsl9O2zNsly1ygES951ava6Ckdtk5Wos&callback=initMap" async defer></script>
</body>
</html>

