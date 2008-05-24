<?php $theme->display('header'); ?>
<form name="create-content" id="create-content" method="post" action="<?php URL::out( 'admin', 'page=publish' ); ?>">

<div class="publish">
	<div class="container">
	<?php if(Session::has_messages()) {Session::messages_out();} ?>
	</div>

	<div class="container">
		<p>Users and developers hang out here. Ask a question or just say 'hi'.</p>
		<iframe id="irc" scrolling="no" frameborder="0"
		src="http://embed.mibbit.com/?server=irc.freenode.net&channel=%23habari&nick=<?php echo $nick; ?>&noServerNotices=true&noServerMotd=true">
		</iframe>
		<p>type /nick to change your nickname, or /help for available commands</p>

	</div>

</div>

</form>

<?php $theme->display('footer'); ?>
