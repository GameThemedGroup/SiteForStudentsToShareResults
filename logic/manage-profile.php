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
		$args = array();

		if ($_POST['firstname'])
			$args['first_name'] = $_POST['firstname'];

		if ($_POST['lastname'])
			$args['last_name'] = $_POST['lastname'];

		if ($_POST['email'])
			$args['user_email'] = $_POST['email'];

		$args['ID'] = $currentUser->ID;
		wp_update_user($args);

    return 'Your profile has been updated.';
  }

  function changePassword(&$pageState)
  {
    $newPass = $_POST['pass'];
    $newPassConfirm = $_POST['passconfirm'];

    if(($newPass == $newPassConfirm) && $newPass != '') {
      //wp_set_password($newPass, $currentUser->ID);
      $userFeedback = 'password changed';
    } else {
      $userFeedback = 'mismatching passwords';
    }
  }
?>
