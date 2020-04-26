#!/bin/bash

rm /tmp/allaccess.log
rm /tmp/nreport.html
/bin/zcat -f /var/log/nginx/access.log* > /tmp/allaccess.log
/usr/bin/goaccess /tmp/allaccess.log -o /tmp/nreport.html --log-format=COMBINED
sed -i -e 's/<\/body><\/html>//g' /tmp/nreport.html
cat <<EOT >> /tmp/nreport.html

<style>
@media screen and (min-width: 800px) {
  .stable {
     position: relative;
     left: 5em;
     width: 50%;
  }
}
</style>
<div class="row clearfix table-wrapper stable">
<div class="col-md-12">
<?php 
   require_once "/var/www/html/stats.php";
   printStatsTables();
?> 
</div>
</div>
</body></html>
EOT

if test -f "/tmp/nreport.html"; then
  mv /tmp/nreport.html /var/www/html/nreport.php
fi

