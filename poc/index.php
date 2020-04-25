<!DOCTYPE html >
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css">
    <script src="/jquery/jquery-3.4.1.min.js"></script>
    <script src="/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js?render=6LeujegUAAAAAImDheP5SG6ph54m55PIU1gLfkKT"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-social/5.1.1/bootstrap-social.min.css" integrity="undefined" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />

    <title>Truckers Welcome</title>
    <link rel="stylesheet" type="text/css" href="css/tw.css?2">
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
         <li><a href="#" onclick="return openBusinessForm();">Add Business</a></li>
         <li><a href="#" onclick="return openAboutModal();">About Truckers Welcome</a></li>
         <li><a href="mailto:help@truckerswelcome.ca?subject=Mail from TruckersWelcome">Email Us</a></li>
         <li><a href="#" onclick="return openTermsOfServiceModal();">Terms of Service</a></li>
         <li>
              <a class="btn btn-social-icon btn-facebook" href="https://www.facebook.com/sharer/sharer.php?u=https://truckerswelcome.ca/"><span class="fa fa-facebook"></span></a>
              <a class="btn btn-social-icon btn-twitter" href="https://twitter.com/intent/tweet?text=Help%20our%20truck,%20delivery%20and%20transit%20drivers%20access%20services%20like%20washrooms%20and%20hot%20meals%20while%20on%20the%20road%20during%20the%20pandemic.%20Add%20places%20where%20they%20can%20get%20access%20to%20services%20safely%20while%20traveling.%20Also,%20please%20help%20us%20share%20the%20word%20around!'"><span class="fa fa-twitter"></span></a>
              <a class="btn btn-social-icon btn-linkedin" href="https://www.linkedin.com/shareArticle?mini=true&url=https://truckerswelcome.ca/&summary=Help%20front%20line%20workers%20who%20are%20keeping%20goods%20and%20supplies%20flowing%20to%20our%20grocery%20stores,%20hospitals%20and%20community%20members%20stay%20safe!%20%0A%0ASuggest%20places%20where%20they%20can%20get%20access%20to%20services%20safely%20while%20traveling.%20Also,%20please%20help%20us%20share%20the%20word%20around!"><span class="fa fa-linkedin"></span></a>
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
            <input class="form-control" type="text" name="location" placeholder="Search TruckersWelcome" autocomplete="off"/>
            <input type="hidden" name="lat"/>
            <input type="hidden" name="lng"/>
            <input type="hidden" name="options"/>
         </form>
      </div>
      <div id="search-this-area">
         <button type="button" class="btn btn-light">Search this area</button>
      </div>
      <div id="map"></div>
      <div id="add-business">
         <button type="button" class="btn btn-success">Add</button>
      </div>
   </main>
<?php
   require_once 'html/start.html';
   require_once 'html/siteInfo.html';
   require_once 'html/about.html';
   require_once 'html/tos.html';
   ?>
   <script src="js/tw.js?2"></script>
   <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDpsl9O2zNsly1ygES951ava6Ckdtk5Wos&callback=initMap" async defer></script>
</body>
</html>

