<?php include 'header.php'; ?>
<!-- login -->
  <div class="content">
   <div id="primary">
    <div id="primarycontent" class="hfeed">
<?php include 'loginform.php'; ?>
    </div>
 
   </div>
 
   <hr>
 
   <div class="secondary">
 
    <div id="search">
     <h2>Search</h2>
<?php include 'searchform.php'; ?>
    </div>	
 
    <div class="sb-about">
     <h2>About</h2>
     <p><?php Options::out('about'); ?></p>
    </div>
  
   </div>
 
   <div class="clear"></div>
  </div>
<!-- /login -->
<?php include 'footer.php'; ?>
