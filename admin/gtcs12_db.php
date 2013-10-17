<?php

class GTCS12_DB 
{
  function DeleteTables()
  {
    global $wpdb;
    $wpdb->show_errors(true);

    // delete child table first 
    $tablename = $wpdb->prefix . "enrollments";
    $sql = "DROP TABLE IF EXISTS $tablename;";
    $wpdb->query($sql);

    // delete parent table later
    $tablename = $wpdb->prefix . "courses";
    $sql = "DROP TABLE IF EXISTS $tablename;";
    $wpdb->query($sql);

    return "Tables deleted";
  }

  function CreateTables()
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $usersTableName = $wpdb->prefix . "users";

    // create parent table first
    $coursesTableName = $wpdb->prefix . "courses";
    $sql = "CREATE TABLE $coursesTableName (
      id bigint(20) NOT NULL AUTO_INCREMENT,
      name varchar(40) NOT NULL,
      quarter varchar(20) NOT NULL,
      year smallint unsigned NOT NULL,
      facultyid bigint(20) unsigned,
      PRIMARY KEY id (id),
      FOREIGN KEY (facultyid) REFERENCES $usersTableName (id)
    );";
    $wpdb->query($sql);

    // create child tables later
    $enrollmentsTableName = $wpdb->prefix . "enrollments";
    $sql = "CREATE TABLE $enrollmentsTableName (
      id bigint(20) NOT NULL AUTO_INCREMENT,
      courseid bigint(20),
      studentid bigint(20) unsigned,
      PRIMARY KEY id (id),
      FOREIGN KEY (courseid) REFERENCES $coursesTableName (id),
      FOREIGN KEY (studentid) REFERENCES $usersTableName (id)
    );";
    $wpdb->query($sql);
  }

  function RecreateTables() 
  {
    self::DeleteTables();
    self::CreateTables();

    return "Tables recreated<br/>";
  }

  function GetAllFaculty()
  {
    $result = get_users("role=author");
    return $result;
  }

  function GetCourse($courseId)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $tablename = $wpdb->prefix . "courses";

    $rows = $wpdb->get_row("SELECT c.name as Name, c.quarter as Quarter, c.year as Year, c.facultyid as FacultyId
      FROM $tablename as c WHERE c.id = $courseId");

    return $rows;
  }

  function GetAllCourses()
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $courseTable = $wpdb->prefix . "courses";
    $userTable = $wpdb->prefix . "users";

    $rows = $wpdb->get_results("SELECT c.Id as Id, c.name as Name, c.quarter as Quarter, c.year as Year, u.display_name as FacultyName
      FROM $courseTable as c INNER JOIN $userTable u
      ON c.facultyid = u.id");

    return $rows;
  }

  function AddCourse($courseName, $quarter, $year, $facultyId)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $tablename = $wpdb->prefix . "courses";

    $wpdb->insert($tablename, array ('name' => $courseName, 'quarter' => $quarter, 'year' => $year, 'facultyid' => $facultyId));
    return $wpdb->insert_id;
  }

  function DeleteCourse($courseId)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $tablename = $wpdb->prefix . "courses";

    $wpdb->query("DELETE FROM $tablename WHERE id = $courseId");
  }

  function UpdateCourse($courseId, $courseName, $quarter, $year, $facultyId)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $tablename = $wpdb->prefix . "courses";

    $wpdb->update($tablename, array ('name' => $courseName, 'quarter' => $quarter, 'year' => $year, 'facultyid' => $facultyId), array( 'Id' => $courseId ));
  }

  function GetStudents($courseId)
  {
    global $wpdb;
    $wpdb->show_errors(true);
    $enrollments = $wpdb->prefix . "enrollments";
    $users       = $wpdb->prefix . "users";
    $userMeta    = $wpdb->prefix . "usermeta";
    $capabilities= $wpdb->prefix . "capabilities";

    $sql = "SELECT u.ID as Id, u.display_name as Name, 
      (select studentid from $enrollments where courseid = " . $courseId . " AND studentid = u.id) as StudentId 
      FROM $users u INNER JOIN $userMeta up 
      ON u.id = up.user_id 
      WHERE up.meta_key = $capabilities AND up.meta_value LIKE '%contributor%'";
 
    $rows = $wpdb->get_results($sql);
    return $rows;
  }

  function UpdateStudentEnrollment($courseId, $studentId, $enroll)
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

  function EnrollStudentsViaFile($courseId, $fileVariable)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $tempFileName = $_FILES[$fileVariable]['tmp_name'];

    // read comma separated file into a string
    $contents = file_get_contents($tempFileName);

    // replace each , with ',' and beginning with ', 
    // so a,b,c becomes 'a','b','c'
    $contents = "'" . $contents . "'";
    $contents = str_replace(",","','", $contents);

    $enrollments  = $wpdb->prefix . "enrollments";
    $users        = $wpdb->prefix . "users";
    $usermeta     = $wpdb->prefix . "usermeta";
    $capabilities = $wpdb->prefix . "capabilities";

    // insert into enrollments records of those students
    // who are not enrolled yet in specified course
    // for those who are already enrolled, nothing to be done
    $sql = "INSERT INTO {$enrollments} (courseId, studentId) SELECT {$courseId}, u.Id from {$users} u INNER JOIN ".
      "{$usermeta} up on u.Id = up.user_id WHERE up.meta_key = {$capabilities} AND up.meta_value LIKE '%contributor%'".
      "AND u.user_email in {$contents} AND u.Id NOT IN (SELECT studentId from {$enrollments} WHERE courseId={$courseId});";

    $wpdb->query($sql);
  }

  function GetAllAssignments($courseId)
  {
    global $wpdb;
    $wpdb->show_errors(true);
    
    $users        = $wpdb->prefix . "users";
    $posts        = $wpdb->prefix . "posts";
    $term_relationships = $wpdb->prefix . "term_relationships";
    $terms = $wpdb->prefix . "terms";

    // an assignment means who has no parent post
    // a submission means who has a parent post
    $sql = "SELECT p.id as AssignmentId, p.post_title as Title, p.post_date as Date, a.id as AuthorId, a.display_name as AuthorName 
      FROM {$posts} p INNER JOIN {$users} a ON p.post_author = a.id
      WHERE p.post_parent = 0 
      AND p.post_status = 'publish'
      AND p.id IN (SELECT object_id FROM {$term_relationships} 
      WHERE term_taxonomy_id = (SELECT term_id FROM {$terms} WHERE name = 'course:{$courseId}'));";

    $rows = $wpdb->get_results($sql);

    return $rows;
  }

  function UploadFiles($fileVariable, $destFileNames)
  {
    $destFilePaths = array();

    for($i = 0; $i < count($_FILES[$fileVariable]); $i++)
    {
      if ($_FILES[$fileVariable]['error'][$i] == 0)
      {
        $tempFileName = $_FILES[$fileVariable]['tmp_name'][$i];

        if ($tempFileName)
        {
          $destFileName = $destFileNames[$i];

          $destPath = "./uploads/$destFileName";                    

          // move uploaded file
          move_uploaded_file($tempFileName, $destPath);
          // echo "uploaded from " . $tempFileName . " to " . $destPath . "<br/>";

          array_push($destFilePaths, $destPath);
        }
      }
    }

    return $destFilePaths;
  }

  function GetAssignmentFilePaths($authorId, $courseId,  $assignmentId)
  {
    $imageFileName = $authorId . '_' . $courseId . '_' . $assignmentId . '.png'; // only .png supported now
    $jarFileName = $authorId . '_' . $courseId . '_' . $assignmentId . '.jar';

    $paths = array();

    array_push($paths, "./uploads/" . $imageFileName);
    array_push($paths, "./uploads/" . $jarFileName);

    return  $paths;
  }

  function CreateAssignment($authorId, $courseId, $title, $description)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    // save the assignment submission as post
    $assignmentPost = array(
      'post_title'    => $title,
      'post_content'  => $description,
      'post_status'   => 'publish',
      'post_author'   => $authorId,
      'comment_status' => 'open',
      'tags_input' => array('course:' . $courseId)
    );

    // save post and get its id
    $postId = wp_insert_post($assignmentPost);    

    return $postId;
  }

  function UpdateAssignment($assignmentId, $authorId, $courseId, $title, $description)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $assignmentPost = array();
    $assignmentPost['ID'] = $assignmentId;
    $assignmentPost['post_title'] = $title;
    $assignmentPost['post_content'] = $description;

    wp_update_post($assignmentPost);
  }

  function DeleteAssignment($assignmentId)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    wp_trash_post($assignmentId);
  }

  function GetAssignment($assignmentId)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    return get_post($assignmentId);
  }

  function GetAllSubmissions($assignmentId)
  {
    global $wpdb;
    $wpdb->show_errors(true);
    $posts  = $wpdb->prefix . "posts";
    $users  = $wpdb->prefix . "users";

    $sql = "SELECT p.id as SubmissionId, a.display_name as AuthorName, p.post_date as SubmissionDate
      FROM {$posts} p INNER JOIN {$users} a ON p.post_author = a.id
      WHERE p.post_parent = {$assignmentId} AND p.post_status = 'publish';";

    $rows = $wpdb->get_results($sql);

    return $rows;
  }

  function CreateSubmission($authorId, $courseId, $assignmentId, $description)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    // save the assignment submission as post
    $assignmentPost = array(
      'post_title'    => 'Assignment submission',
      'post_content'  => $description,
      'post_status'   => 'publish',
      'post_author'   => $authorId,
      'comment_status' => 'open',
      'post_parent' => $assignmentId,
      'tags_input' => array('course:' . $courseId)
    );

    // save post and get its id
    $postId = wp_insert_post($assignmentPost);    

    return $postId;
  }

  function UpdateSubmission($subId, $description)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $assignmentPost = array();
    $assignmentPost['ID'] = $subId;
    $assignmentPost['post_content'] = $description;

    wp_update_post($assignmentPost);
  }
  
  function AddUser($login, $password, $email, $firstname, $lastname, $role)
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
}
?>
