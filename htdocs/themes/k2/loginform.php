<?php
$user = User::identify();
if ( isset( $url->settings['error'] ) ):
?>
<p>That login is incorrect.</p>
<?php
endif;
if ( $user ) :
?>
<p>You are logged in as <?php echo User::identify()->username; ?>.</p>
<p>Want to <a href="<?php URL::out('logout'); ?>">log out</a>?</p>
<?php
else :
?>
<form method="post" action="<?php URL::out('login', false); ?>">
Name: <input type="text" size="25" name="name" /><br />
Pass: <input type="password" size="25" name="pass" /><br />
<input type="submit" value="GO!" /><input type="hidden" name="action" value="login" />
</form>
<?php
endif;
?>
