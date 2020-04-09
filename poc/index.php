<!DOCTYPE html >
<head>
   <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
   <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
   <link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css">
   <script src="/jquery/jquery-3.4.1.min.js"></script>
   <script src="/bootstrap/js/bootstrap.min.js"></script>
   <title>Truckers Welcome</title>
   
   <link rel="stylesheet" type="text/css" href="tw.css">
</head>
<body>

   <div>
      <div style="text-align: center;">
         <button type="button" class="btn btn-success btn-sm" onclick="window.location.href = '/addsite.php';">Add Business</button>
         <button type="button" class="btn btn-danger btn-sm" onclick="window.location.href = '/help.html';">Help</button>
         <button type="button" class="btn btn-info btn-sm" onclick="window.location.href = '/about.html';">About</button>
      </div>

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

   <main>
      <div id="search">
         <form>
            <input class="form-control" type="text" name="location" placeholder="Location"/>
            <input type="hidden" name="lat"/>
            <input type="hidden" name="lng"/>
         </form>
      </div>
      <div id="map"></div>
   </main>

   <script src="tw.js"></script>

   <!-- JavaScript for Google maps -->
   <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDpsl9O2zNsly1ygES951ava6Ckdtk5Wos&callback=initMap" async defer></script>
</body>
</html>

