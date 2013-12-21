<?php
  $pageState = array();
  initializePageState($pageState);
  extract($pageState);

  function initializePageState(&$pageState)
  {
    $action = ifsetor($_POST['action'], null);

    $actionList = array(
      'update'   => 'updateProfileInformation',
      'changePassword'  => 'changePassword',
    );

    $userFeedback = '';
    if ($action == null) {
    } else if (array_key_exists($action, $actionList)) {
      $userFeedback = call_user_func($actionList[$action], &$pageState);
    } else {
      trigger_error("An invalid action was provided.", E_USER_WARNING);
    }

    setupProfileView($pageState);
    $pageState = array_merge($pageState, compact(
      'userFeedback'
    ));
  }

  function setupProfileView(&$pageState)
  {
	  $user = wp_get_current_user();

    $pageState = array_merge($pageState, compact(
      'user'
    ));
  }

  function updateProfileInformation(&$pageState)
  {
    $firstName = $_POST['firstname'];
    $lastName = $_POST['lastname'];
    $email = $_POST['email'];

    if (!gtcs_validate_not_null(__FUNCTION__, __FILE__, __LINE__,
      compact('firstName', 'lastName', 'email'))) {

      return "Invalid values when editing profile.";
    }

    $id = get_current_user_id();
    $args = array(
      'first_name' => $firstName,
      'last_name'  => $lastName,
      'email'      => $email,
      'ID'         => $id
    );

		wp_update_user($args);

    return 'Your profile has been updated.';
  }

  function changePassword(&$pageState)
  {
    $newPass = $_POST['pass'];
    $newPassConfirm = $_POST['passconfirm'];

    if (!gtcs_validate_not_null(__FUNCTION__, __FILE__, __LINE__,
      compact('newPass', 'newPassConfirm'))) {

      return "Invalid values when updating password.";
    }

    if(($newPass == $newPassConfirm) && $newPass != '') {
      //wp_set_password($newPass, $currentUser->ID);
      $userFeedback = 'password changed';
    } else {
      $userFeedback = 'mismatching passwords';
    }
  }
?>
