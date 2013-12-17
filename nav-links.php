<?php if ( $user_ID ) : ?>
<?php wp_head();
	global $user_identity;
	//global $wp_roles;
	$user_info = wp_get_current_user();
	//echo $user_info->user_level;
?>

<div id="loginbar">
	<b>Hello</b>, <?php echo $user_identity; ?>.
	<br>[<a href="<?php echo site_url('/profile/?user=') . get_current_user_id(); ?>">My Profile</a>]
	<?php if(gtcs_user_has_role('author')):?>
		<br>[<a href="<?php echo site_url('/courses/') ?>" title="Manage Courses">Manage Courses</a>]
		<br>[<a href="<?php echo site_url('/assignments/') ?>" title="Manage Assignments">Manage Assignments</a>]
		<br>[<a href="<?php echo site_url('/students/') ?>" title="Manage Students">Manage Students</a>]
	<?php endif; ?>
	<br>[<a href="<?php echo wp_logout_url(get_permalink()); ?>" title="Log out of this account">Log Out</a>]
</div>

<?php else : ?>
<div id="loginbar">
	<form name="loginform" id="loginform" action="<?php echo get_option('siteurl'); ?>/wp-login.php" method="post">
		Username <input value="Username" class="input" type="text" size="20" tabindex="10" name="log" id="user_login" onfocus="if (this.value == 'Username') {this.value = '';}" onblur="if (this.value == '') {this.value = 'Username';}" />
		Password <input value="Password" class="input" type="password" size="20" tabindex="20" name="pwd" id="user_pass" onfocus="if (this.value == 'Password') {this.value = '';}" onblur="if (this.value == '') {this.value = 'Password';}" />
		<input name="rememberme" id="rememberme" value="forever" tabindex="90" type="checkbox"> Remember Me? <br />
		<input name="wp-submit" id="wp-submit" value="Log In" tabindex="100" type="submit">
		<input name="redirect_to" value="<?php echo get_option('siteurl'); ?>/wp-admin/" type="hidden">
		<input name="testcookie" value="1" type="hidden">
	</form>
</div>
<?php endif; ?>
