<?php $theme->display ( 'header' ); ?>
<!--begin content-->
	<div id="content">
		<!--begin primary content-->
		<div id="primaryContent">
			<!--begin single post navigation-->
			<div id="post-nav">
				<?php if ( $previous= $post->ascend() ): ?>
				<span class="left"> &laquo; <a href="<?php echo $previous->permalink ?>" title="<?php echo $previous->slug ?>"><?php echo $previous->title ?></a></span>
				<?php endif; ?>
				<?php if ( $next= $post->descend() ): ?>
				<span class="right"><a href="<?php echo $next->permalink ?>" title="<?php echo $next->slug ?>"><?php echo $next->title ?></a> &raquo;</span>
				<?php endif; ?>
			</div>
			<!--begin loop-->			
				<div id="post-<?php echo $post->id; ?>" class="<?php echo $post->statusname; ?>">
						<h2><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h2>
						<div class="cal">
							<?php $date= Utils::getdate( strtotime( $post->pubdate ) ); ?> <span class="calyear"><?php echo $date['year']; ?></span><br><span class="calday"><?php echo $date['mday']; ?></span><br><span class="calmonth"><?php echo $date['month']; ?></span>
						</div>	
						<div class="entry">
						<?php echo $post->content_out; ?>
					</div>
					<div class="entryMeta">	
						<?php if ( is_array( $post->tags ) ) { ?>
						<div class="tags">Tagged: <?php echo $post->tags_out; ?></div>
						<?php } ?>
					</div><br>
						<?php if ( $user ) { ?>
						<a href="<?php URL::out( 'admin', 'page=publish&slug=' . $post->slug); ?>" title="Edit post">Edit</a>
						<?php } ?>
					
				</div>
				
			<!--end loop-->
			<?php include 'commentform.php'; ?>
			</div>
		<!--end primary content-->
		<?php $theme->display ( 'sidebar' ); ?>
	</div>
	<!--end content-->
	<?php $theme->display ( 'footer' ); ?>