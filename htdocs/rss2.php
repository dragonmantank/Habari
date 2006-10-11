<?php include_once( "init.php" ); ?>
<?php header('Content-type: text/xml; charset="utf-8"'); ?>
<?php echo '<?xml version="1.0" encoding="utf-8" ?>'; ?>
<!-- generator="habari" -->
<rss version="2.0"
xmlns:content="http://purl.org/rss/1.0/modules/content/"
xmlns:wfw="http://wellformedweb.org/CommentAPI/"
xmlns:dc="http://purl.org/dc/elements/1.1/"
>
<channel>
<title>Whitespace, powered by Habari</title>
<link>http://www.fearsome-engine.com</link>
<description>Spread the news</description>
<pubDate>Tuesday, 10 Oct 2006 21:48:44 +0000</pubDate>
<generator>http://spreadthenews.org/v?=scrary-alpha</generator>
<language>en</language>
<?php $rssobjs = posts::retrieve(); ?>
	<?php foreach ( $rssobjs as $rssobj ) { ?>
<item>
<title><?php echo $rssobj->title; ?></title> 
<link>
http://www.fearsome-engine.com/<?php echo $rssobj->slug; ?>
</link>
<comments>
http://www.fearsome-engine.com/<?php echo $rssobj->slug; ?>/#comments
</comments>
<pubDate><?php echo $rssobj->pubdate; ?></pubDate>
<guid isPermaLink="false">
<?php echo $rssobj->guid; ?>
</guid>
<description>
	<![CDATA[<?php echo $rssobj->content; ?>]]>
</description>
<content:encoded>
	<![CDATA[<?php echo $rssobj->content; ?>]]>
</content:encoded>
<wfw:commentRSS>
http://www.fearsome-engine.com/<?php echo $rssobj->slug; ?>/feed/
</wfw:commentRSS>
</item>
<?php } ?>
</channel>
</rss>