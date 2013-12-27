<?php
/**
 * Template Name: Manage Profile
 * Description:
 *
 * Author: Andrey Brushchenko
 * Date: 10/20/2013
 */
get_header(); ?>

<?php include_once(get_template_directory() . '/logic/manage-profile.php'); ?>

<?php if ($userFeedback != ''): ?>
	<div id="action-box"><?php echo $userFeedback; ?></div>
<?php endif; ?>


<!-- Edit Profile Form -->
<form action="" method="post">
  <div id='manage-profile-box'>
    <div id='manage-profile-title'>Edit Profile Information</div>
    <div id='manage-profile-field'>
      <p class='manage-profile'>Avatar</p>
      <input class="manage-profile" type="file" name="avatar[]">
    </div>
    <div id='manage-profile-field'>
      <p class='manage-profile'>Public Name</p>
      <input class='manage-profile' type="text" name="displayname"
        value="<?php echo $user->display_name; ?>" /><br />
    </div>
    <div id='manage-profile-field'>
      <p class='manage-profile'>First Name</p>
      <input class='manage-profile' type="text" name="firstname"
        value="<?php echo $user->user_firstname; ?>" /><br />
    </div>
    <div id='manage-profile-field'>
      <p class='manage-profile'>Last Name</p>
      <input class='manage-profile' type="text" name="lastname"
        value="<?php echo $user->user_lastname; ?>" /><br />
    </div>
    <div id='manage-profile-field'>
      <p class='manage-profile'>Email</p>
      <input class='manage-profile' type="text" name="email"
        value="<?php echo $user->user_email; ?>" /><br />
    </div>
    <div id="manage-profile-buttons">
      <input type="hidden" name="action" value="update">
      <input type="submit" value="Submit">
      <a href="<?php echo site_url("/profile/?user={$user->ID}"); ?>">
        <button type="button">Cancel</button></a>
    </div>
  </div>
</form>
<!-- Edit Profile Form -->

<!-- Edit Password Form -->
<form action="" method="post">
  <div id='manage-profile-box'>
    <div id='manage-profile-title'>Edit Password </div>
    <div id='manage-profile-field'>
      <p class='manage-profile'>Current Password</p>
      <input class='manage-profile' type="password" name="currentpass" required><br>
    </div>
    <div id='manage-profile-field'>
      <p class='manage-profile'>Password</p>
      <input class='manage-profile' type="password" name="pass" required><br>
    </div>
    <div id='manage-profile-field'>
      <p class='manage-profile'>Confirm Password</p>
      <input class='manage-profile' type="password" name="passconfirm" required><br>
    </div>
    <div id="manage-profile-buttons">
      <input type="hidden" name="action" value="changePassword">
      <input type="submit" value="Submit">
      <a href="<?php echo site_url("/profile/?user={$user->ID}"); ?>">
        <button type="button">Cancel</button></a>
    </div>
  </div>
</form>
<!-- Edit Password Form -->

<form action="" method="post">
  <div id="manage-profile-box">
    <div id='manage-profile-title'>Facebook Account</div>

    <div id='manage-profile-buttons'>
      <?php if ($hasLinkedFb): ?>
        <input type="hidden" name="action" value="unlinkFacebook">
        <input type="submit" value="Unlink">
      <?php else: ?>
        <div class="fb-login-button" data-max-rows="1" data-show-faces="false"
          onlogin="jfb_js_login_callback();">
          Link Facebook Account
        </div>
      <?php endif; ?>
    </div>

  </div>
</form>

<?php get_footer(); ?>
