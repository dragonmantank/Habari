<?php
$max = intval( $max );
function tag_weight( $count, $max )
{
	return round( 10 * log($count + 1) / log($max + 1) );
}
?>
<?php foreach ( $tags as $tag ) : ?>
		<li id="<?php echo 'tag_' . $tag->id ?>" class="item tag wt<?php echo tag_weight($tag->count, $max); ?>"> 
		 	<span class="checkbox"><input type="checkbox" class="checkbox" name="checkbox_ids[<?php echo $tag->id; ?>]" id="checkbox_ids[<?php echo $tag->id; ?>]"></span><label for="checkbox_ids[<?php echo $tag->id; ?>]"><?php echo $tag->tag; ?></label><span class="count"><a href="<?php URL::out( 'admin', array( 'page' => 'posts', 'search' => 'tag:'. $tag->tag_text_searchable) ); ?>" title="<?php echo Utils::htmlspecialchars( _t( 'Manage posts tagged %1$s', array( $tag->tag_text ) ) ); ?>"><?php echo $tag->count; ?></a></span>
		 </li>
<?php endforeach; ?>
