<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title><?php Options::out( 'title' ); ?></title>
	<meta http-equiv="Content-Type" content="text/html">
	<meta name="generator" content="Habari">
	
	<link rel="edit" type="application/atom+xml" title="<?php Options::out( 'blog_title' ); ?>" href="<?php URL::out( 'introspection' ); ?>">
	<link rel="alternate" type="application/atom+xml" title="Atom" href="<?php URL::out( 'collection', array( 'index' => '1' ) ); ?>">
	<link rel="EditURI" type="application/rsd+xml" title="RSD" href="<?php URL::out( 'rsd' ); ?>">
	
	<link rel="stylesheet" type="text/css" media="screen" href="<?php Site::out_url( 'theme' ); ?>/style.css">

	<?php if ( $user ) { // Still needs to check for edit permissions ?>
	<script type="text/javascript" src="<?php Site::out_url( 'habari' ); ?>/scripts/jquery.js"></script>
	<?php } ?>
</head>

<body class="home">
	<div id="page">
		<div id="header">
			<h1><a href="<?php Site::out_url( 'habari' ); ?>"><?php Options::out( 'title' ); ?></a></h1>
			<p class="description"><?php Options::out( 'tagline' ); ?></p>
			<ul class="menu">
				<li><a href="http://habariproject.org/" title="Habari Project">Habari Project</a></li>
				<?php
				// Menu tabs
				foreach ( $pages as $tab ) {
					echo '<li><a href="' . $tab->permalink . '" title="' . $tab->title . '">' . $tab->title . '</a></li>' . "\n";
				}
				?>
				<?php if ( $user ) { ?>
					<li class="admintab"><a href="<?php Site::out_url( 'admin' ); ?>" title="Admin area">Admin</a></li>
				<?php } ?>
			</ul>
		</div>
		<hr>
