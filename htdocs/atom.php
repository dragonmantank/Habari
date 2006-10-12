<?php include_once( 'system/init.php' ); ?>
<?php header('Content-type: application/atom+xml; charset="utf-8"'); ?>
<?php echo '<?xml version="1.0" encoding="utf-8"?>'; ?>
<feed version="0.3"
  xmlns="http://purl.org/atom/ns#"
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  xml:lang="en"
  >
	<title>Whitespace, powered by Habari</title>
	<link rel="alternate" type="text/html" href="http://www.fearsome-engine.com" />
	<tagline>Spread the News</tagline>
	<modified>Tuesday, 10 Oct 2006 21:48:44 +0000</modified>
	<copyright>Copyright <?php echo date('Y'); ?> Tuesday, 10 Oct 2006</copyright>
	<generator url="http://code.google.com/p/habari/" version="Habari"></generator>
	<?php $rssobjs = posts::retrieve(); ?>
		<?php foreach ( $rssobjs as $rssobj ) { ?>
	<entry>
	  	<author>
			<name>Chris J. Davis</name>
		</author>
		<title type="text/html" mode="escaped"><![CDATA[<?php echo $rssobj->title; ?>]]></title>
		<link rel="alternate" type="text/html" href="http://www.fearsome-engine.com/<?php echo $rssobj->slug; ?>" />
		<id><?php echo $rssobj->slug; ?></id>
		<modified><?php echo $rssobj->pubdate; ?></modified>
		<issued><?php echo $rssobj->pubdate; ?></issued>
		<?php //the_category_rss('') ?> 
		<summary type="text/plain" mode="escaped"><![CDATA[<?php echo $rssobj->content; ?>]]></summary>
		<content type="XHTML" mode="escaped" xml:base="http://www.fearsome-engine.com/<?php echo $rssobj->slug; ?>"><![CDATA[<?php echo $rssobj->content; ?>]]></content>
	</entry>
<?php } ?>
</feed>