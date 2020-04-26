<?php
require_once("/var/opt/webapp/nogit/creds.php");

function printStatsTables(){
   global $servername;
   global $dbname;
   global $username;
   global $password;
   
   try {
      $dbh = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
   } catch (PDOException $e) {
      die("Connection failed: " . $e->getMessage() . "\n");
   }

   $cumulative = 0;

   $fontColor="white";
   $tdcolor="#ffffff";
   if (array_key_exists ("dark" , $_GET)){
      if ($_GET['dark'] == 0){
         $fontColor="black";
         $tdcolor="#000000";
      }
   }   
   $all   = getStats($dbh, "all");
   $mdata = getStats($dbh, "Moderator");
   $ddata = getStats($dbh, "Driver");
   $fdata = getStats($dbh, "Facility Owner");
   $odata = getStats($dbh, "Other");
   
   echo "<style>table td{color:$tdcolor;} th{color:$tdcolor;}</style>\n";

   $searchRes = getSearches($dbh);

   echo "<br><font color=$fontColor><B>Search Results (15 days)</b></font><br/>";
   echo "<TABLE class=\"table table-borderless table-hover table-condensed\" width=100%>";
   echo "<thead><tr><th>Date</th><th>Searches</th><th>Results</th></tr></thead><tbody>";
   $sri=0;
   foreach ($searchRes as $key => $val){
      echo "<tr><td>$key</td><td>" . $val["searches"] . "</td><td>" . $val["results"]. "</td></tr>";
   }
   echo "</tbody></table>";

   echo "<br><font color=$fontColor><B>Total entries: $all</B></font><BR><BR>";
   echo "<TABLE class=\"table table-borderless table-hover table-condensed\" width=100%>";
   echo "<thead><tr><th>Date</th><th>Moderator</th><th>Driver</th><th>Owner</th><th>Other</th><th>Cumulative</th></tr></thead><tbody>";

   for ($d=14; $d>=0; $d--){
      $stime = strtotime("-$d days");
      $dstr = date("Y-m-d", $stime);
      $tdstr = date("m-d", $stime);
      
      $cumulative = $cumulative + $mdata['Moderator'][$dstr] + $ddata['Driver'][$dstr] + $fdata['Facility Owner'][$dstr] + $odata['Other'][$dstr];
      echo "<TR>";
      echo "<TD>$tdstr</TD>";
      echo "<TD>".$mdata['Moderator'][$dstr]."</TD>";
      echo "<TD>".$ddata['Driver'][$dstr]."</TD>";
      echo "<TD>".$fdata['Facility Owner'][$dstr]."</TD>";
      echo "<TD>".$odata['Other'][$dstr]."</TD>";
      echo "<TD>".$cumulative."</TD>";
      echo "</TR>";
   }
   echo "</tbody></TABLE>";  
}
   
function getSearches($dbh){ 
   
   $result = array();
   
   $sth = $dbh->prepare('SELECT COUNT(*) as COUNT, date(searchtime) as date, SUM(numentries) as numentries from searches where searchtime >= date_sub(curdate(), interval 15 day) group by date');
   if ($sth->execute([]) === TRUE && $sth->rowCount()) {
      while($row = $sth->fetch(PDO::FETCH_ASSOC))
      {
         $result[$row['date']]['searches'] = $row['COUNT'];
         $result[$row['date']]['results'] = $row['numentries'];
      }      
   }
   return $result;
}


function getStats($dbh, $stype){
   
   if ($stype == "all"){
      $sth = $dbh->prepare('SELECT COUNT(*) as COUNT from facilities where approval_status="approved"');
      if ($sth->execute([]) === TRUE && $sth->rowCount()) {
         $row = $sth->fetch(PDO::FETCH_ASSOC);
         $entries = $row['COUNT'];
         return $entries;
      }
      return 0;
   }
   
   $result = array();
   
   for ($d=0; $d<15; $d++){
      $stime = strtotime("-$d days");
      $dstr = date("Y-m-d", $stime);
      $result[$stype][$dstr] = 0;
   }
   
   $sth = $dbh->prepare('SELECT COUNT(*) as COUNT, date(created_on) as date from facilities where submitter_type="'. $stype . '" AND created_on >= date_sub(curdate(), interval 15 day) group by date');
   if ($sth->execute([]) === TRUE && $sth->rowCount()) {
      while($row = $sth->fetch(PDO::FETCH_ASSOC))
      {
         $result[$stype][$row['date']] = $row['COUNT'];
      }
   }
   return $result;
}

?>