<?php
class GTCS_Users
{
  public static function addUser($login, $password, $email, $firstname, $lastname, $role)
  {
    global $wpdb;
    $userdata = array(
      'user_login' => $login,
      'user_pass' => $password,
      'user_email' => $email,
      'first_name' => $firstname,
      'last_name' => $lastname,
      'role' => $role,
    );

    $id = wp_insert_user($userdata);
    $user = new WP_User($id);
    $user->add_role($role);

    return $id;
  }

  function getStudents($courseId)
  {
    global $wpdb;
    $wpdb->show_errors(true);
    $enrollments = $wpdb->prefix . "enrollments";
    $users       = $wpdb->prefix . "users";

    $sql = "SELECT studentid from {$enrollments} WHERE courseid={$courseId};";
    $results = $wpdb->get_results($sql);

    $studentList = array();
    foreach($results as $id) {
      $studentList[] = (int) $id->studentid;
    }

    return $studentList;
  }

  public static function updateStudentEnrollment($courseId, $studentId, $enroll)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $tablename = $wpdb->prefix . "enrollments";

    if ($enroll)
    {
      $wpdb->insert($tablename, array ('courseid' => $courseId, 'studentid' => $studentId));
    }
    else
    {
      $wpdb->query("DELETE FROM $tablename WHERE courseid = $courseId AND studentid = $studentId");
    }
  }

  // Creates ane enrolls students into the course using the CSV file in $_FILES
  //
  // The CSV file begins with a header row and contains the student's data in the
  // following format:
  //    Login, Password, Email Address, First Name, Last Name, Display Name
  //    bbob, p@ss!1, bbob@example.com, Billy, Bob, TheAwesomeBillyBob
  //
  // @param courseid  the course id of the course to enroll the student into
  // @param fileindex the index in $_FILES of the csv containing the student data
  public static function enrollStudentsViaFile($courseid, $fileindex)
  {
    if(!$courseid) {
      trigger_error(__FUNCTION__ . " - Course ID not provided.", E_USER_WARNING);
      return;
    }

    $file = $_FILES[$fileindex]['tmp_name'];

    if(!$file) {
      trigger_error(__FUNCTION__ . " - file was not provided.", E_USER_WARNING);
      return;
    }

    // breaks the file into an array containing each line of the file
    $fileContents = explode("\n", file_get_contents($file));
    array_shift($fileContents); // remove the header values

    foreach($fileContents as $studentData) {
      // parses the csv for one student's data
      list($login, $password, $email, $firstname, $lastname, $displayname) = explode(",", $studentData);
      $role = 'student'; // student role

      $userdata = array(
        'user_login' => $login,
        'user_pass' => $password,
        'user_email' => $email,
        'first_name' => $firstname,
        'last_name' => $lastname,
        'display_name' => $displayname,
        'role' => $role,
      );

      // Creates the user
      $studentid = wp_insert_user($userdata);

      if(is_wp_error($studentid)) {
        if(array_key_exists('existing_user_login', $studentid->errors)) {
          echo "Sorry, the username {$login} already exists! <br />";
        }
        continue;
      }

      $user = new WP_User($studentid);
      $user->add_role($role);

      // Enrolls the user
      $doEnroll = true;
      GTCS_Users::UpdateStudentEnrollment($courseid, $studentid, $doEnroll);
    }
  }
}
?>
