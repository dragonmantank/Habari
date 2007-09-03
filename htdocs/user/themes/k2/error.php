<?php include 'header.php'; ?>
<!-- error -->
  <div class="content">
   <div id="primary">
    <div id="post-<?php echo $post->id; ?>" class="<?php echo $post->statusname; ?>">

     <div class="entry-head">
      <h3 class="entry-title">Error!</h3>
     </div>

     <div class="entry-content">
      <p>The requested post was not found.</p>
     </div>

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
  
    <div class="sb-user">
     <h2>User</h2>
<?php include 'loginform.php'; ?>
    </div>	
  
   </div>
   
   <div class="clear"></div>
  </div>
<!-- /error -->
<?php include 'footer.php'; ?>
