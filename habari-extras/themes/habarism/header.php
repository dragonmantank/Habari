<!-- header -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?php if($request->display_entry && isset($post)) { echo "{$post->title} - "; } ?><?php Options::out( 'title' ) ?></title>
<meta http-equiv="Content-Type" content="text/html">
<meta name="generator" content="Habari">

<link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="<?php $theme->feed_alternate(); ?>">
<link rel="edit" type="application/atom+xml" title="Atom Publishing Protocol" href="<?php URL::out( 'atompub_servicedocument' ); ?>">
<link rel="EditURI" type="application/rsd+xml" title="RSD" href="<?php URL::out( 'rsd' ); ?>">

<link rel="stylesheet" type="text/css" media="screen" href="<?php Site::out_url( 'theme' ); ?>/style.css">

<?php $theme->header(); ?>
</head>

<body>
	<?php
	if(isset($_REQUEST['grid'])) { ?>
		<div id="grid">&nbsp;</div>
	<?php }	?>
	<div id="wrapper" class="clearfix">
		<div id="sidebar">
			<div class="block" id="main">
				<h1 id="title"><a href="<?php Site::out_url( 'habari' ); ?>"><?php Options::out( 'title' ); ?></a></h1>
				<h2 id="subtitle"><?php Options::out( 'tagline' ); ?></h2>
				<p id="description"><?php Options::out('about'); ?></p>
			</div>
			<div class="block" id="menu">
				<ul>
					<li><a href="<?php Site::out_url( 'habari' ); ?>" title="<?php Options::out( 'title' ); ?>">Home</a></li>
					<?php
					foreach ( $pages as $tab ) {
					?>
					    <li><a href="<?php echo $tab->permalink; ?>" title="<?php echo $tab->title; ?>"><?php echo $tab->title; ?></a></li>
					<?php
					}
					if ( $user ) { ?>
					    <li><a href="<?php Site::out_url( 'admin' ); ?>" title="Admin area">Admin</a></li>
					<?php } ?>
				</ul>
			</div>	
<!-- /header -->