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
