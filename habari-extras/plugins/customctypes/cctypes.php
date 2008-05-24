<?php $this->display( 'header' ); ?>
<div class="container">
<div class="column prepend-1 span-22 append-1 last">

<div class="column span-4">
<form method="post" action="">
<h3>Available Types</h3>
<ul>
<?php foreach($posttypes as $type_id => $type): ?>
<li><a href="<?php echo $theme->admin_edit_ctype_url($type_id); ?>"><?php echo $type; ?></a></li>
<?php endforeach; ?>
<li><input type="text" id="newtype" name="newtype"><button>Add</button></li>
</ul>
<input type="hidden" name="cct_action" value="addtype">
</form>
</div>

<?php if(isset($edit_type)): ?>
<div class="column span-18 last">
<h3><?php echo $edit_type_name; ?></h3>


</div>
<?php endif; ?>

</div>
</div>
<?php $this->display( 'footer' ); ?>