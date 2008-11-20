<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html">
	<title><?php if($request->display_entry && isset($post)) { echo "{$post->title} - "; } ?><?php Options::out( 'title' ) ?></title>
	<meta name="generator" content="Habari">

	<link rel="edit" type="application/atom+xml" title="Atom Publishing Protocol" href="<?php URL::out( 'atompub_servicedocument' ); ?>">
	<link rel="alternate" type="application/atom+xml" title="Atom" href="<?php $theme->feed_alternate(); ?>">
	<link rel="EditURI" type="application/rsd+xml" title="RSD" href="<?php URL::out( 'rsd' ); ?>">
	<link rel="stylesheet" type="text/css"  media="print" href="<?php Site::out_url( 'habari'); ?>/3rdparty/blueprint/print.css">
	<link rel="stylesheet" type="text/css" media ="screen" href="<?php Site::out_url( 'habari'); ?>/3rdparty/blueprint/screen.css">
	<link rel="stylesheet" type="text/css" media="screen" href="<?php Site::out_url( 'theme' ); ?>/style.css">
	<link rel="Shortcut Icon" href="<?php Site::out_url( 'theme' ); ?>/favicon.ico">
	<?php $theme->header(); ?>
</head>
<body>
	<!--begin wrapper-->
	<div id="wrapper" class="container prepend-1 append-1">
		<!--begin masthead-->
		<div id="masthead"  class="span-15 pull-1">
			<div id="branding">
				<h1><a href="<?php Site::out_url( 'habari'); ?>" title="<?php Options::out( 'title' ); ?>"> <?php Options::out( 'title' ); ?></a></h1>
				<h3 class="prepend-1"><em><?php Options::out( 'tagline' ); ?></em></h3>
			</div>
		</div>
	<!--end masthead-->


