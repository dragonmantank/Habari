<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="<?php Options::out('locale') ?>">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title><?php if ( $request->display_entry && isset($post) ) { echo "{$post->title} - "; } ?><?php Options::out( 'title' ) ?></title>
		<link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="<?php $theme->feed_alternate(); ?>">
		<link rel="edit" type="application/atom+xml" title="Atom Publishing Protocol" href="<?php URL::out( 'atompub_servicedocument' ); ?>">
		<link rel="EditURI" type="application/rsd+xml" title="RSD" href="<?php URL::out( 'rsd' ); ?>">
		<link rel="stylesheet" type="text/css" media="screen" href="<?php Site::out_url( 'theme' ); ?>/style.css">
		<?php $theme->header(); ?>
	</head>
	<body>
		<div id="header">
			<div id="siteinfo">
				<h1><?php Options::out( 'title' ); ?></h1>
				<p id="tagline"><?php Options::out( 'tagline' ); ?></p>
			</div>
			<ul id="menu">
				<li>
					<a href="<?php Site::out_url( 'habari' ); ?>">Home</a>
				</li>
					<?php
						foreach ( array_filter($pages->getArrayCopy(), create_function('$a', 'return !in_array($a->slug, array("tag", "archives"));')) as $tab ) {
							?>
								<li>
									<a href="<?php echo $tab->permalink; ?>" title="<?php echo $tab->title; ?>"><?php echo $tab->title; ?></a>
								</li>
							<?php
						}
				
						if ( User::identify()->loggedin ) { 
							?>
								<li class="admintab"><a href="<?php Site::out_url( 'admin' ); ?>" title="Admin area">Admin</a></li>
							<?php
						}
					?>
			
			</ul>
		</div>
		
