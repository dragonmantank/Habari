<div<?php echo ($class) ? ' class="' . $class . '"' : ''?><?php echo ($id) ? ' id="' . $id . '"' : ''?>>
<?php foreach($options as $key => $text) : ?>
	<input type="radio" name="<?php echo $field; ?>" value="<?php echo $key; ?>"<?php echo ( ( $value == $key ) ? ' checked' : '' ); ?>><label for="<?php echo $id; ?>"><?php echo htmlspecialchars($text); ?></label>
<?php endforeach; ?>
<?php if($message != '') : ?>
<p class="error"><?php echo $message; ?></p>
<?php endif; ?>
</div>
