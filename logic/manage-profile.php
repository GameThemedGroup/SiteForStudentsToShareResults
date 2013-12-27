<?php
  $pageState = new stdClass();
  initializePageState($pageState);
  extract((array) $pageState);

  function initializePageState(&$ps)
  {
    verifyPermissionOrDie();

    $action = ifsetor($_POST['action'], null);

    $actionList = array(
      'update'   => 'updateProfileInformation',
      'changePassword'  => 'changePassword',
      'unlinkFacebook'  => 'unlinkFacebookAccount',
    );

    $userFeedback = '';
    if ($action == null) {
    } else if (array_key_exists($action, $actionList)) {
      $userFeedback = call_user_func($actionList[$action], $ps);
    } else {
      trigger_error("An invalid action was provided.", E_USER_WARNING);
    }

    setupProfileView($ps);
    $ps->userFeedback = $userFeedback;
  }

  function verifyPermissionOrDie()
  {
    $profileId = ifsetor($_GET['user'], null);
    $userId = get_current_user_id();

    // TODO generalize this error message
    if ($profileId != $userId) {
      echo "You do not have permission to view this page.";
      $homeUrl = home_url();
      header("Refresh: 5; url={$homeUrl}");
      exit();
    }
  }

  function setupProfileView(&$ps)
  {
	  $user = get_user_by('id', get_current_user_id());

    $ps->hasLinkedFb = get_user_meta($user->ID, 'facebook_uid', true) != '';
    $ps->user = $user;
  }

  function unlinkFacebookAccount(&$ps)
  {
    $userId = get_current_user_id();
    delete_user_meta($userId, 'facebook_uid');
    $ps->hasLinkedFb = false;
  }

  function updateProfileInformation(&$ps)
  {
    $profileId = ifsetor($_GET['user'], null);
    $userId = get_current_user_id();

    if ($profileId != $userId) {
      return "You do not have permission to perform this action.";
    }

    $firstName = $_POST['firstname'];
    $lastName = $_POST['lastname'];
    $displayName = $_POST['displayname'];
    $email = $_POST['email'];

    if (!gtcs_validate_not_null(__FUNCTION__, __FILE__, __LINE__,
      compact('displayName', 'firstName', 'lastName', 'email'))) {

      return "Invalid values when editing profile.";
    }

    $id = get_current_user_id();
    $args = array(
      'display_name' => $displayName,
      'first_name' => $firstName,
      'last_name'  => $lastName,
      'email'      => $email,
      'ID'         => $id
    );

		wp_update_user($args);

    return 'Your profile has been updated.';
  }

  function changePassword(&$ps)
  {
    $profileId = ifsetor($_GET['user'], null);
    $userId = get_current_user_id();

    if ($profileId != $userId) {
      return "You do not have permission to perform this action.";
    }

    $currentPass = $_POST['currentpass'];
    $newPass = $_POST['pass'];
    $newPassConfirm = $_POST['passconfirm'];

    if (!gtcs_validate_not_null(__FUNCTION__, __FILE__, __LINE__,
      compact('newPass', 'newPassConfirm', 'currentPass'))) {

      return "Invalid values when updating password.";
    }

    $user = get_user_by('id', $userId);
    if (!wp_check_password($currentPass, $user->data->user_pass, $user->ID)) {
      return "Incorrect password entered!";
    }

    if ($newPass != $newPassConfirm) {
      return "Your passwords do not match!";
    }

    // logs the user out
    wp_set_password($newPass, $userId);
    $homeUrl = home_url();
    header("Refresh: 2; url={$homeUrl}");
    return "Password successfully changed. Please log back in.";
  }
?>
