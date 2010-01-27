<?php
/*
Template Name: Links
*/
?>
<?php get_header(); ?>
		<div id="content">
			<h2 class="title">Links</h2>
			<div id="entries">
				<ul>
					<?php get_links(-1, '<li>', '</li>', '<br>', FALSE, 'id', TRUE, TRUE, -1, TRUE); ?>
				</ul>
			</div>
		</div>
<?php get_footer(); ?>