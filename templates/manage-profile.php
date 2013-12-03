<?php
/**
 * Template Name: Manage Profile
 * Description: 
 *
 * Author: Andrey Brushchenko
 * Date: 10/20/2013
 */
get_header(); ?>

<?php 
	$currentUser = wp_get_current_user();

	//update user info
	if($_POST['op'] == 'info') 
	{
		$args = array();
		
		if($_POST['firstname']) 
			$args['first_name'] = $_POST['firstname'];
			
		if($_POST['lastname']) 
			$args['last_name'] = $_POST['lastname'];
		
		if($_POST['email']) 
			$args['user_email'] = $_POST['email'];
			
		$args['ID'] = $currentUser->ID;
		wp_update_user($args);
    
    $action = 'profile updated';
	}
  elseif($_POST['op'] == 'password') 
  {
    $newPass = $_POST['pass'];
    $newPassConfirm = $_POST['passconfirm'];
    
    if(($newPass == $newPassConfirm) && $newPass != '')
    {
      //wp_set_password($newPass, $currentUser->ID);
      $action = 'password changed';
    }
    else
    {
      $error = 'mismatching passwords';
    }
  }
?>

<!DOCTYPE html>
<html lang="en">
<?php if($action != '') : ?>
	<div id="action-box"><?php echo $action ?></div>
<?php endif ?>

<?php if($error != '') : ?>
	<div id="error-box"><?php echo $error ?></div>
<?php endif ?>

  <form action="" method="post">
    <div id='manage-profile-box'>
      <div id='manage-profile-title'>Edit Profile Information</div>	
      <div id='manage-profile-field'>
        <p class='manage-profile'>Avatar</p>
        <input class="manage-profile" type="file" name="avatar[]">
      </div>
      <div id='manage-profile-field'>
        <p class='manage-profile'>First Name</p>
        <input class='manage-profile' type="text" name="firstname" value="<?php echo $currentUser->user_firstname ?>"><br>
      </div>	
      <div id='manage-profile-field'>
        <p class='manage-profile'>Last Name</p>
        <input class='manage-profile' type="text" name="lastname" value="<?php echo $currentUser->user_lastname ?>"><br>
      </div>	
      <div id='manage-profile-field'>
        <p class='manage-profile'>Email</p>
        <input class='manage-profile' type="text" name="email" value="<?php echo $currentUser->user_email ?>"><br>
      </div>	
      <div id="manage-profile-buttons">
        <input type="hidden" name="op" value="info">
        <input type="submit" value="Submit">
        <a href="<?php echo site_url('/profile/?user=' . $currentUser->ID) ?>"><button type="button">Cancel</button></a>
      </div>
    </div>	
  </form>
     
  <form action="" method="post">
    <div id='manage-profile-box'>
      <div id='manage-profile-title'>Edit Password </div>	
        <div id='manage-profile-field'>
        <p class='manage-profile'>Password</p>
        <input class='manage-profile' type="password" name="pass" required><br>
      </div>	   
      <div id='manage-profile-field'>
        <p class='manage-profile'>Confirm Password</p>
        <input class='manage-profile' type="password" name="passconfirm" required><br>
      </div>	
      <div id="manage-profile-buttons">
        <input type="hidden" name="op" value="password">
        <input type="submit" value="Submit">
        <a href="<?php echo site_url('/profile/?user=' . $currentUser->ID) ?>"><button type="button">Cancel</button></a>
      </div>
    </div>	
  </form>
</html>

<?php get_footer(); ?>