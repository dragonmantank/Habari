<?php include 'header.php'; ?>
		<div id="content">
			<div class="post">
				<h2 class="title"><a href="<?php echo $post->permalink; ?>" rel="bookmark" title="Permanent Link to <?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h2>
				<div class="entry">
					<ul>
						<?php 
						foreach(Tags::get() as $tag) {
							echo '<li><a href="' . URL::get('display_entries_by_tag', array('tag'=>$tag->slug)) . '">' . $tag->tag . '</a> ('. $tag->count . ')</li>';
						}
						?>
					</ul>
					<?php if ( $user->loggedin ) { ?>
						<p class="meta">
							<a href="<?php URL::out( 'admin', 'page=publish&id=' . $post->id); ?>" title="Edit post">Edit</a>
						</p>
					<?php } ?>
				</div>
			</div>
		</div>
<?php include 'footer.php'; ?>
