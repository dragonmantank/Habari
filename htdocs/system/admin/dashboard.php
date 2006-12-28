<div id="content-area">
	<div id="left-column">
			<h1>Welcome back <?php echo User::identify()->username; ?>!</h1>
			<p>Good to see you round these parts again.&nbsp; Before you get back to creating your masterpiece of blogginess, you might want to take a moment to catch up on things around <?php Options::out('title'); ?>.</p>
		<div class="stats">
			<h4>Site Statistics</h4>
				<ul id="site-stats">
					<li><span class="right">567</span> Visits Today</li>
					<li><span class="right">10067</span> Visits Past Week</li>
					<li><span class="right"><?php echo Posts::count_total(); ?></span> Total Posts</li>
					<li><span class="right"><?php echo Posts::count_by_author( User::identify()->id ); ?></span> Number of Your Posts</li>
					<li><span class="right"><?php echo Comments::count_total(); ?></span> Number of Comments</li>			
				</ul>
		</div>
		<div class="system-info">
			<h4>System Health</h4>
			<ul>
				<li>&raquo; You are running Habari <?php Options::out('version'); ?>.</li>
				<li>&raquo; A Habari <a href="http://habariblog.org/download" title="A habari Update is available">Update is Available</a>.</li>
				<li>&raquo; <a href="plugins" title="Updates Ready">3 plugins</a> have updates ready.</li>
				<li>&raquo; An <a href="http://www.chrisjdavis.org/download/believe2.zip" title="An Update is Available">Update is Available</a> for your Theme.</li>
			</ul>
		</div>
		<div class="drafts">
				<h4>Drafts (<a href="manage/drafts" title="View Your Drafts">more</a> &raquo;)</h4>
				<ul id="site-drafts">
				<?php 
					if( Posts::count_total( Post::STATUS_DRAFT ) ) {
						foreach( Posts::by_status( Post::STATUS_DRAFT ) as $draft ) {
				?>
					<li>
						<span class="right">
							<a href="<?php echo $draft->permalink; ?>" title="View <?php echo $draft->title; ?>">
								<img src="/system/admin/images/view.png" alt="View this draft" />
							</a>
							<a href="<?php URL::out('admin', 'page=publish&slug=' . $draft->slug); ?>" title="Edit <?php echo $draft->title; ?>">
								<img src="/system/admin/images/edit.png" alt="Edit this draft" />
							</a>
						</span>
						<?php echo $draft->title; ?>
					</li>
				<?php } ?>
				</ul>
				<?php } else {
					_e('<p>There are currently no drafts in process</p>');
				} ?>
		</div>
		<div class="incoming">
			<h4>Incoming Links (<a href="http://technorati.com/search/myintarweb.com" title="More incoming links">more</a> &raquo;)</h4>
			<ul id="incoming-links">
				<li>
					<img src="http://drbacchus.com/journal/favicon.ico" alt="favicon" /> <a href="http://wooga.drbacchus.com/journal" title="Dr Bacchus' Journal">Dr Bacchus' Journal</a>
				</li>
				<li>
					<img src="http://skippy.net/blog/favicon.ico" alt="favicon" /> <a href="http://skippy.net" title="Skippy dot net">Skippy Dot Net</a>
				</li>
				<li>
					<img src="http://brokenkode.com/favicon.ico" alt="favicon" /> <a href="http://brokenkode.com" title="Broken Kode">Broken Kode</a>
				</li>
				<li>
					<img src="http://asymptomatic.net/favicon.ico" alt="favicon" /> <a href="http://asymptomatic.net/" title="Asymptomatic">Asymptomatic</a>
				</li>
				<li>
					<img src="http://www.chrisjdavis.org/favicon.ico" alt="favicon" /> <a href="http://www.chrisjdavis.org" title="Sillyness Spelled Wrong Intentionally">Sillyness Spelled Wrong Intentionally</a>
				</li>
			</ul>
		</div>
		<div class="recent-comments">
			<h4>Recent Comments 
				<?php if( Comments::count_total( Comment::STATUS_UNAPPROVED ) ) { ?>
				(<a href="manage/comments" title="View Comments Awaiting Moderation "><?php echo Comments::count_total( Comment::STATUS_UNAPPROVED ); ?> comments awaiting moderation</a> &raquo;)
				<?php } ?>
			</h4>
			<?php
			if( Comments::count_total( Comment::STATUS_APPROVED ) ) {
			?>
				<table name="comment-data" width="100%" cellspacing="0">
					<thead>
						<th colspan="1" align="left">Post</th>
						<th colspan="1" align="left">Name</th>
						<th colspan="1" align="left">URL</th>
						<th colspan="1" align="center">Action</th>
					</thead>
					<?php foreach( Comments::get( array( 'status' => Comment::STATUS_APPROVED, 'limit' => 5, 'orderby' => 'date DESC' ) ) as $recent ) { ?>
					<tr>
						<td><?php echo $recent->post_slug; ?></td>
						<td><?php echo $recent->name; ?></td>
						<td><?php echo $recent->url; ?></td>
						<td align="center">
							<a href="<?php Options::out('base_url'); ?><?php echo $recent->post_slug; ?>" title="View this post"><img src="/system/admin/images/view.png" alt="View this comment" /></a>
							<img src="/system/admin/images/edit.png" alt="Edit this comment" />
						</td>
					</tr>
					<?php } ?>
				</table>
				<?php } else {
					_e('<p>There are no comments to display</p>');
				}?>
		</div>
	</div>
	<div id="right-column">
		<div class="options">
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
				<li>&raquo; <a href="javascript: popUp();" title="The Habari Help Center">The Help Center</a></li>
				<li>&raquo; <a href="http://habariblog.org/support" title="Support Forums">Support Forums</a></li>
			</ul>
		</div>
	</div>
</div>
