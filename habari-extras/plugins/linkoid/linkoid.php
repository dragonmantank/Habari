<!-- Linkoid:  To modify this template, copy it from the plugin directory to your current theme directory. -->
	  <div class="sb-linkoid">
    	<h2>Linkoid</h2>
    	<ul>
<?php foreach($linkoids as $post): ?>
			<li><p><a href="<?php echo $post->permalink; ?>" style="font-weight: bold;"><?php echo $post->title; ?></a> -
			<?php echo $post->content; ?>
			</p></li>
<?php endforeach; ?>
			</ul>
		</div>
