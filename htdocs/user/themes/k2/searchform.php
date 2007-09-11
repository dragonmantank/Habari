<!-- searchform -->
<?php Plugins::act( 'theme_searchform_before' ); ?>
     <form method="get" id="searchform" action="<?php URL::out('search'); ?>">
      <p><input type="text" id="s" name="criteria" value=""> <input type="submit" id="searchsubmit" value="Go!"></p>
     </form>
<?php Plugins::act( 'theme_searchform_after' ); ?>
<!-- /searchform -->
