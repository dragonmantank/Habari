<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
	<title><?php Options::out('title'); ?></title>
	<meta http-equiv="Content-Type" content="text/html" />
	<meta name="generator" content="Habari" />
	<link type="application/atom+xml" rel="service.post" href="<?php URL::out('collection', array('index'=>'1')); ?>" title="<?php Options::out('blog_title'); ?>"/>
	
	<link rel="alternate" type="application/atom+xml" title="Atom" href="<?php URL::out('collection', array('index'=>'1')); ?>" />
	
	<link rel="EditURI" type="application/rsd+xml" href="<?php URL::out('rsd'); ?>" title="RSD" />

	<link rel="stylesheet" type="text/css" media="screen" href="<?php Options::out('base_url'); ?>user/themes/<?php Options::out('theme_dir'); ?>/style.css" />

	<?php if ( User::identify() ) { // Still needs to check for edit permissions ?>
	<script type="text/javascript" src="<?php Options::out('base_url'); ?>scripts/jquery.js"></script>
	<?php } ?>
</head>
<body class="home">
<div id="page">
	<div id="header">
		<h1><a href="<?php Options::out('base_url'); ?>"><?php Options::out('title'); ?></a></h1>
		<p class="description"><?php Options::out('tagline'); ?></p>
		<ul class="menu">
			<li><a href="http://habariproject.org/" title="Habari Project">Habari Project</a></li>
			<?php if ( user::identify() ) { ?><li class="admintab"><a href="<?php Options::out('base_url'); ?>admin" title="Admin area">Admin</a></li><?php } ?>
		</ul>
	</div>
	<hr />
