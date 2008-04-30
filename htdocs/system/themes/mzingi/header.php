<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" 
"http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title><?php if($request->display_entry && isset($post)) { echo "{$post->title} - "; } ?><?php Options::out( 'title' ) ?></title>
	<meta http-equiv="Content-Type" content="text/html">
	<meta name="generator" content="Habari">
	
	<link rel="edit" type="application/atom+xml" title="Atom Publishing Protocol" href="<?php URL::out( 'atompub_servicedocument' ); ?>">
	<link rel="alternate" type="application/atom+xml" title="Atom" href="<?php $theme->feed_alternate(); ?>">
	<link rel="EditURI" type="application/rsd+xml" title="RSD" href="<?php URL::out( 'rsd' ); ?>">
	<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.1/build/fonts/fonts-min.css"> 
	<link rel="stylesheet" type="text/css" media="screen" href="<?php Site::out_url( 'theme' ); ?>/style.css">
	<link rel="Shortcut Icon" href="/favicon.ico">
	<?php $theme->header(); ?>
</head>
<body>
	<!--begin wrapper-->
	<div id="wrapper">
		<!--begin masthead-->
		<div id="masthead">
			<div id="branding">
				<h1><?php Options::out( 'title' ); ?></h1>
				<h3><?php Options::out( 'tagline' ); ?></h3>
			</div>	
		</div>
	<!--end masthead-->
	
		