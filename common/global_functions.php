<?php
// Returns the first argument if it is isset, otherwise returns the second
// retrieved from: https://wiki.php.net/rfc/ifsetor
//
// Short hand for the following code construct
//    $var = isset($var) ? $var : default;
//
// @param variable the value to test for isset
// @param default  the value to return if varialbe is not set
// @return the variable if isset, else returns default
function ifsetor(&$variable, $default = null) {
  if (isset($variable)) {
    $tmp = $variable;
  } else {
    $tmp = $default;
  }
  return $tmp;
}

/**
 * Checks if a particular user has a role.
 * Returns true if a match was found.
 * credit: www.appthemes.com
 *
 * @param string $role Role name.
 * @param int $user_id (Optional) The ID of a user. Defaults to the current
 *        user.
 * @return bool
 */
function gtcs_user_has_role($role, $user_id = null) {

  if (is_numeric($user_id))
    $user = get_userdata($user_id);
  else
    $user = wp_get_current_user();

  if (empty($user))
    return false;

  return in_array($role, (array)$user->roles);
}

function htmldump($variable, $height="15em") {
  echo "<pre style=\"border: 1px solid #000; height: {$height}; overflow: auto;
  margin: 0.5em;\">";
  var_dump($variable);
  echo "</pre>\n";
}

?>
