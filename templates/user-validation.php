<?php
/**
* Template Name: validateSocialUser
* Description: Validates if the user's social login is registerd or not. If the * the user is registered then redirects to home page of the user.
*
* Author: Agranee
*/

global $gtcs12_db;
$current_user = wp_get_current_user();
$email = $current_user->user_email;
$uid = $gtcs12_db->GetUserByAlternateEmail($email);
error_log("agranee user-validateion\n", 3, "error_log.txt");
if ($uid >= 0)
{
  if (switchToUser(1)) {
    wp_redirect("../wordpress/main/");
    exit;
  }
}

echo "Please register your social login/username as AlternateEmail in your profile";
?>
