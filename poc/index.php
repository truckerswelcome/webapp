<!DOCTYPE html >
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css">
    <script src="/jquery/jquery-3.4.1.min.js"></script>
    <script src="/bootstrap/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-social/5.1.1/bootstrap-social.min.css" integrity="undefined" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />

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
          <li>
              <a class="btn btn-social-icon btn-facebook" href="https://www.facebook.com/sharer/sharer.php?u=https%3A//www.truckerswelcome.ca/"><span class="fa fa-facebook"></span></a>
              <a class="btn btn-social-icon btn-twitter" href="https://twitter.com/intent/tweet?text=Help%20us%20spread%20the%20word%20and%20help%20others%20keep%20safe!%20https%3A//www.truckerswelcome.ca/"><span class="fa fa-twitter"></span></a>
              <a class="btn btn-social-icon btn-linkedin" href="https://www.linkedin.com/shareArticle?mini=true&url=https%3A//www.truckerswelcome.ca/&title=Help%20us%20spread%20the%20word%20and%20help%20others%20keep%20safe!&summary=&source="><span class="fa fa-linkedin"></span></a>
          </li>
      </ul>
   </aside>
   <div class="overlay"></div>
   <main>
      <div id="search">
         <div class="nav-icon">
            <div></div>
         </div>
         <form>
            <input class="form-control" type="text" name="location" placeholder="Search TruckersWelcome"/>
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

