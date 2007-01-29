<?php include('header.php'); ?>
<div id="content-area">
	<div class="dashboard-column" id="left-column">
		<div class="dashboard-block c3" id="welcome">
			<h1>Welcome back, <?php echo User::identify()->username; ?>!</h1>
			<p>Good to see you round these parts again.&nbsp; Before you get back to creating your masterpiece of blogginess, you might want to take a moment to catch up on things around <?php Options::out('title'); ?>.</p>
		</div>
		<div class="dashboard-block" id="stats">
			<h4>Site Statistics</h4>
				<ul id="site-stats">
					<li><span class="right">567</span> Visits Today</li>
					<li><span class="right">10067</span> Visits Past Week</li>
					<li><span class="right"><?php echo Posts::count_total(); ?></span> Total Posts</li>
					<li><span class="right"><?php echo Posts::count_by_author( User::identify()->id ); ?></span> Number of Your Posts</li>
					<li><span class="right"><?php echo Comments::count_total(); ?></span> Number of Comments</li>			
				</ul>
		</div>
		<div class="dashboard-block" id="system-info">
			<h4>System Health</h4>
			<ul>
				<li>&raquo; You are running Habari <?php Options::out('version'); ?>.</li>
				<li>&raquo; An <a href="#" title="Go to the release notes">Update for Habari</a> is available.</li>
				<li>&raquo; There are <a href="plugins" title="Plugin updates">3 Updates for Plugins</a> available.</li>
				<li>&raquo; An <a href="#" title="Download the updated theme">Update for your Theme</a> is available</li>
			</ul>
		</div>
		<div class="dashboard-block" id="incoming">
			<h4>Incoming Links (<a href="http://technorati.com/search/<?php Options::out('hostname') ?>" title="More incoming links">more</a> &raquo;)</h4>
			<ul id="incoming-links">
				<li>
					<img class="favicon" src="http://drbacchus.com/journal/favicon.ico" alt="favicon" /> <a href="http://wooga.drbacchus.com/journal" title="Dr Bacchus' Journal">Dr Bacchus' Journal</a>
				</li>
				<li>
					<img class="favicon" src="http://skippy.net/blog/favicon.ico" alt="favicon" /> <a href="http://skippy.net" title="Skippy dot net">Skippy Dot Net</a>
				</li>
				<li>
					<img class="favicon" src="http://brokenkode.com/favicon.ico" alt="favicon" /> <a href="http://brokenkode.com" title="Broken Kode">Broken Kode</a>
				</li>
				<li>
					<img class="favicon" src="http://asymptomatic.net/favicon.ico" alt="favicon" /> <a href="http://asymptomatic.net/" title="Asymptomatic">Asymptomatic</a>
				</li>
				<li>
					<img class="favicon" src="http://www.chrisjdavis.org/favicon.ico" alt="favicon" /> <a href="http://www.chrisjdavis.org" title="Sillyness Spelled Wrong Intentionally">Sillyness Spelled Wrong Intentionally</a>
				</li>
			</ul>
		</div>
		<div class="dashboard-block" id="drafts">
				<h4>Drafts (<a href="manage/drafts" title="View Your Drafts">more</a> &raquo;)</h4>
				<ul id="site-drafts">
				<?php 
					if ( Posts::count_total( Post::STATUS_DRAFT ) ) {
						foreach ( Posts::by_status( Post::STATUS_DRAFT ) as $draft ) {
				?>
					<li>
						<span class="right">
							<a href="<?php echo $draft->permalink; ?>" title="View <?php echo $draft->title; ?>">
								<img src="<?php Options::out('base_url'); ?>system/admin/images/view.png" alt="View this draft" />
							</a>
							<a href="<?php URL::out('admin', 'page=publish&slug=' . $draft->slug); ?>" title="Edit <?php echo $draft->title; ?>">
								<img src="<?php Options::out('base_url'); ?>system/admin/images/edit.png" alt="Edit this draft" />
							</a>
						</span>
						<?php echo $draft->title; ?>
					</li>
				<?php
						}
				?>
				</ul>
				<?php
					}
					else {
						_e('<p>There are currently no drafts in process</p>');
					}
				?>
		</div>
		<div class="dashboard-block" id="recent-comments">
			<h4>Recent Comments 
				<?php
					if ( Comments::count_total( Comment::STATUS_UNAPPROVED ) ) {
				?>
				(<a href="<?php URL::out('admin', array('page'=>'moderate', 'option'=>'comments'));?>" title="View Comments Awaiting Moderation "><?php echo Comments::count_total( Comment::STATUS_UNAPPROVED ); ?> comments awaiting moderation</a> &raquo;)
				<?php
					}
				?>
			</h4>
			<?php
				if ( Comments::count_total( Comment::STATUS_APPROVED ) ) {
			?>
				<table name="comment-data" width="100%" cellspacing="0">
					<thead>
						<tr>
							<th colspan="1" align="left">Post</th>
							<th colspan="1" align="left">Name</th>
							<th colspan="1" align="left">URL</th>
							<th colspan="1" align="center">Action</th>
						</tr>
					</thead>
					<?php
						foreach ( Comments::get( array( 'status' => Comment::STATUS_APPROVED, 'limit' => 5, 'orderby' => 'date DESC' ) ) as $recent ) {
							$post= Post::get( array( 'id' => $recent->post_id, ) );
					?>
					<tr>
						<td><?php echo $post->title; ?></td>
						<td><?php echo $recent->name; ?></td>
						<td><?php echo $recent->url; ?></td>
						<td align="center">
							<a href="<?php Options::out('base_url'); ?><?php echo $post->post_slug; ?>" title="View this post"><img src="<?php Options::out('base_url'); ?>system/admin/images/view.png" alt="View this comment" /></a>
							<img src="<?php Options::out('base_url'); ?>system/admin/images/edit.png" alt="Edit this comment" />
						</td>
					</tr>
					<?php } ?>
				</table>
				<?php
				}
				else {
					_e('<p>There are no comments to display</p>');
				}
				?>
		</div>
	</div>
	<div class="dashboard-column" id="right-column">
		<div class="dashboard-block" id="options">
			<ul id="options-list">
				<li> 
					<span class="right">
						<small>(<a href="#" title="">help</a>)</small>
					</span>
					<a href="<?php URL::out('admin', 'page=options'); ?>" title="Options">Options</a>
				</li>
				<li> 
					<span class="right">
						<small>(<a href="#" title="">help</a>)</small>
					</span>
					<a href="<?php URL::out('admin', 'page=plugins'); ?>" title="Plugins">Plugins</a>
				</li>
				<li>
					<span class="right">
						<small>(<a href="#" title="">help</a>)</small>
					</span>
					<a href="<?php URL::out('admin', 'page=themes'); ?>" title="Themes">Themes</a> 
				</li>
				<li>
					<span class="right">
						<small>(<a href="#" title="">help</a>)</small>
					</span>
					<a href="<?php URL::out('admin', 'page=users'); ?>" title="Users">Users</a> 
				</li>
			</ul>
			<p><small>Support &amp; Help</small></p>
			<ul id="help-items">
				<script type="text/javascript">
				function popUp() {
				 	window.open ("/system/help/index.html", "mywindow",
								 "location=0,status=1,scrollbars=1, width=700,height=500");
				}
				</script>
				<li>&raquo; <a href="javascript:popUp();" title="The Habari Help Center">The Help Center</a></li>
				<li>&raquo; <a href="http://habariblog.org/support" title="Support Forums">Support Forums</a></li>
			</ul>
		</div>
	</div>
</div>
<?php include('footer.php'); ?>