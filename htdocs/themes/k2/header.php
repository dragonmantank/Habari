<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
	<title>Whitespace, powered by Habari</title>
	<meta http-equiv="Content-Type" content="text/html;" />
	<meta name="generator" content="Habari" />
	
  	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="/rss2.php" />
  	<link rel="alternate" type="application/atom+xml" title="Atom 0.3" href="/atom.php" />

	<link rel="stylesheet" type="text/css" media="screen" href="themes/k2/style.css" />

	<?php /* if ( $user->is_author() ) { */?>
	<style type="text/css">
		.eip_editable { background-color: #ff9; }
		.eip_savebutton { background-color: #36f; color: #fff; }
		.eip_cancelbutton { background-color: #000; color: #fff; }
		.eip_saving { background-color: #903; color: #fff; }
	</style>

	<script type="text/javascript" src="scripts/prototype.js"></script>
	<script type="text/javascript" src="scripts/EditInPlace.js"></script>

	<script type="text/javascript">
	Event.observe(window, 'load', init, false);
	function init() {
		EditInPlace.makeEditable( {
			type: 'text',
			id: 'entry-title',
			save_url: ''
		} );
		EditInPlace.makeEditable( {
			type: 'textarea',
			id: 'entry-content',
			save_url: ''
		} );
	}
	</script>
	<?php /* } */ ?>
</head>
<body class="home">
<div id="page">
	<div id="header">
		<h1><a href="<?php echo $options->base_url; ?>"><?php echo $options->blog_title; ?></a></h1>
		<p class="description"><?php echo $options->tag_line; ?></p>
		<ul class="menu">
			<li><a href="http://code.google.com/p/habari/" title="Habari Project">Habari Project</a></li>
		</ul>
	</div>
		<hr />
