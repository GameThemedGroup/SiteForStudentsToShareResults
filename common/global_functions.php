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

// Returns false and triggers a warning for any value in $args that is null
//
// @param function The name of the calling function, ie. __FUNCTION__
// @param filePath The path to the file of the calling function, ie. __FILE__
// @param line     The line number of calling function, ie. __LINE__
// @param args     associative array containing variableName => value of the
//                 values to validate
// Example
//   $id    = ifsetor($_GET['id', null);
//   $title = ifsetor($_GET['title', null);
//
//   $isValid = gtcs_validate_not_null(__FUNCTION__, __FILE__, __LINE__,
//      compact('id', 'title'));
function gtcs_validate_not_null($function, $filePath, $line, $args)
{
  // limit the display of the filePath
  // ex. re/al/ly/lo/ng/pa/th/file.php
  // becomes pa/th/file.php
  $numParentDirs = 3;
  $dirs = explode('/', $filePath);
  $file = implode('/', array_slice($dirs, -$numParentDirs, $numParentDirs, true));

  $isValid = true;
  foreach ($args as $variable => $value) {
    if ($value == null) {
      trigger_error("{$variable} not provided. {$function} in {$file}:{$line}",
        E_USER_WARNING);

      $isValid = false;
    }
  }

  return $isValid;
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
