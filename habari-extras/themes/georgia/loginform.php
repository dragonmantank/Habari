<?php
if ( isset( $error ) && ( $error == 'Bad credentials' ) ) {
?>
	<ul>
		<li>That login is incorrect.</li>
	</ul>

<?php
}
if ( isset( $user ) && ( $user instanceOf User ) ) {
?>
    <ul> 
		<li>You are logged in as <a href="<?php URL::out( 'admin', 'page=user&user=' . $user->username ) ?>" title="Edit Your Profile"><?php echo $user->username; ?></a>.</li>
		<li>Want to <a href="<?php Site::out_url( 'habari' ); ?>/user/logout">log out</a>?</li>
	</ul>
<?php
}
else {
?>
	<?php Plugins::act( 'theme_loginform_before' ); ?>
     <form method="post" action="<?php URL::out( 'user', array( 'page' => 'login' ) ); ?>" id="loginform">
      <p class="clearfix">
       <label for="habari_username">User:</label>
       <input type="text" size="25" name="habari_username" value="Name" id="habari_username">
      </p>
      <p class="clearfix">
       <label for="habari_password">Pass:</label>
       <input type="password" size="25" name="habari_password" value="Password" id="habari_password"> <input id="signinsubmit" type="submit" value="Sign in">
      </p>
      <?php Plugins::act( 'theme_loginform_controls' ); ?>
     </form>
     <?php Plugins::act( 'theme_loginform_after' ); ?>
<?php
}
?>