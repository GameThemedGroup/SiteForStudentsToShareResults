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
  global $gtcs12_db;
	$current_user = wp_get_current_user();

	//update user info
	if($_POST && $_GET['user'] == $current_user->ID) 
	{
		$args = array();
		
		if($_POST['firstname']) 
			$args ['first_name'] = $_POST['firstname'];
			
		if($_POST['lastname']) 
			$args ['last_name'] = $_POST['lastname'];
		
		if($_POST['email']) 
			$args ['user_email'] = $_POST['email'];

    if($_POST['alternateEmail'])
      $args['alternate_email']= $_POST['alternateEmail'];

		$args['ID'] = $current_user->ID;

    $PreexistingAlternateEmail = $gtcs12_db->HasSameAlternateEmail($_POST['alternateEmail']);
    if($PreexistingAlternateEmail = 'false')
    {
     add_user_meta($current_user->ID,AlternateEmail,$_POST['alternateEmail']);
    }
    else
    {
     echo "The Alternate Email is already registerd please choose a different one";
    }

		wp_update_user($args);
	}
	
	$user_info = get_userdata($_GET['user']);
?>

<!DOCTYPE html>
<html lang="en">
	<?php if($_GET['user'] == $current_user->ID) : ?>
		<form action="" method="post">
			<div id='manage-profile-box'>
				<div id='pagetitle'>Edit Profile</div>	
				<div id='manage-profile-field'>
					<p class='manage-profile'>Avatar</p>
					<input class="manage-profile" type="file" name="avatar[]">
				</div>
				<div id='manage-profile-field'>
					<p class='manage-profile'>First Name</p>
					<input class='manage-profile' type="text" name="firstname" value="<?php echo $user_info->user_firstname ?>"><br>
				</div>	
				<div id='manage-profile-field'>
					<p class='manage-profile'>Last Name</p>
					<input class='manage-profile' type="text" name="lastname" value="<?php echo $user_info->user_lastname ?>"><br>
				</div>	
				<div id='manage-profile-field'>
					<p class='manage-profile'>Email</p>
					<input class='manage-profile' type="text" name="email" value="<?php echo $user_info->user_email ?>"><br>
				</div>
        <div id ='manage-profile-field'>
          <p class='manage-profile'>Alternate Email</p>
          <input class='manage-profile' type="text" name="alternateEmail" value="<?php echo $user_info->AlternateEmail ?>"><br>
        </div>
				<input type="submit" value="Submit"/>
			</div>	
		</form>
	<?php else : ?>
		<div id="notfound">Page Not Found</div>
	<?php endif ?>
</html>

<?php get_footer(); ?>
