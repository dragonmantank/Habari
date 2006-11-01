<?php
$user = User::identify();
if ( $url->error ):
?>
<p>That login is incorrect.</p>
<?php
endif;
if ( $user ) :
?>
<p>You are logged in as <?php echo User::identify()->username; ?>.</p>
<p>Want to <a href="<?php echo $url->get_url('logout'); ?>">log out</a>?</p>
<?php
else :
?>
<form method="post" action="<?php echo $url->get_url('login'); ?>">
Name: <input type="text" size="25" name="name" /><br />
Pass: <input type="password" size="25" name="pass" /><br />
<input type="submit" value="GO!" /><input type="hidden" name="action" value="login" />
</form>
<?php
endif;
?>
