<?php
$user = User::identify();
if (isset($error)):
?>
<p>That login is incorrect.</p>
<?php
endif;
if ($user) :
?>
<p>You are logged in as <?php echo $user->username; ?>.</p>
<p>Want to <a href="<?php URL::out('user', array('page'=>'logout')); ?>">log out</a>?</p>
<?php
else :
?>
<form method="post" action="<?php URL::out('user', array('page'=>'login')); ?>">
Name: <input type="text" size="25" name="name" /><br />
Pass: <input type="password" size="25" name="pass" /><br />
<input type="submit" value="GO!" />
</form>
<?php
endif;
?>
