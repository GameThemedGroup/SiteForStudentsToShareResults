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
      description longtext NOT NULL DEFAULT '',
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

  function GetCourseByCourseId($courseId)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $tablename = $wpdb->prefix . "courses";

    $rows = $wpdb->get_row("SELECT c.name as Name, c.quarter as Quarter, c.year as Year, c.facultyid as FacultyId, c.description as Description
      FROM $tablename as c WHERE c.id = '$courseId'");

    return $rows;
  }

  function GetCourseByStudentId($studentId)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $courses = $wpdb->prefix . "courses";
    $enrollments = $wpdb->prefix . "enrollments";

    $rows ="SELECT c.id as Id, c.name as Name, c.quarter as Quarter, c.facultyid as FacultyId, c.year as Year FROM $courses as c INNER JOIN $enrollments as e ON c.id = e.courseid WHERE e.studentid = $studentId;";
    $result = $wpdb->get_results($rows);

    return $result;
  }

  function GetCourseByFacultyId($facultyId)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $courses = $wpdb->prefix . "courses";

    $rows = "SELECT c.id as Id, c.name as Name, c.quarter as Quarter, c.year as Year FROM $courses as c WHERE c.facultyid = $facultyId;";
    $result = $wpdb->get_results($rows);

    return $result;
  }

  function GetAllCourses()
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $courseTable = $wpdb->prefix . "courses";
    $userTable = $wpdb->prefix . "users";

    $rows = $wpdb->get_results("SELECT c.Id as Id, c.name as Name, c.quarter as Quarter, c.year as Year, u.display_name as FacultyName
      FROM $courseTable as c INNER JOIN $userTable u ON c.facultyid = u.id;");

    return $rows;
  }

  function AddCourse($courseName, $quarter, $year, $facultyId, $description)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $tablename = $wpdb->prefix . "courses";

    $wpdb->insert($tablename,
      array (
        'name'        => $courseName,
        'quarter'     => $quarter,
        'year'        => $year,
        'facultyid'   => $facultyId,
        'description' => $description,
      ));

    return $wpdb->insert_id;
  }

  function DeleteCourse($courseId)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $tablename = $wpdb->prefix . "courses";

    $wpdb->query("DELETE FROM $tablename WHERE id = $courseId");
  }

  function UpdateCourse($courseId, $courseName, $quarter, $year, $facultyId, $description)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $tablename = $wpdb->prefix . "courses";

    $wpdb->update($tablename, array ('name' => $courseName, 'quarter' => $quarter, 'year' => $year, 'facultyid' => $facultyId, 'description' => $description), array( 'Id' => $courseId ));
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
      (select studentid from {$enrollments} where courseid = '{$courseId}' AND studentid = u.id) as StudentId
      FROM {$users} u INNER JOIN {$userMeta} up
      ON u.id = up.user_id
      WHERE up.meta_key = '{$capabilities}' AND up.meta_value LIKE '%subscriber%';";

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

  // Creates ane enrolls students into the course using the CSV file in $_FILES
  //
  // The CSV file begins with a header row and contains the student's data in the
  // following format:
  //    Login, Password, Email Address, First Name, Last Name, Display Name
  //    bbob, p@ss!1, bbob@example.com, Billy, Bob, TheAwesomeBillyBob
  //
  // @param courseid  the course id of the course to enroll the student into
  // @param fileindex the index in $_FILES of the csv containing the student data
  function EnrollStudentsViaFile($courseid, $fileindex)
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
      $role = 'subscriber'; // student role

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
      $this->UpdateStudentEnrollment($courseid, $studentid, $doEnroll);
    }
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

  // Uploads a file from $_FILES and returns its absolute file path
  //
  // @param file_index the index of $_FILES where the file is located
  // TODO check and handle errors
  function UploadFile($file_index)
  {
    //TODO find out if there are any problems using ABSPATH
    if (!function_exists('wp_handle_upload'))
      require_once( ABSPATH . 'wp-admin/includes/file.php' );

    $file = $_FILES[$file_index];

    $upload_overrides = array('test_form' => false);

    // TODO handle errors
    $uploaded_file = wp_handle_upload($file, $upload_overrides);

    // TODO limit file types here?

    return $uploaded_file['file'];
  }

  // Uploads a file from $_FILES and attaches it to a post
  //
  // @param post_id       the id of the post the media is being added to
  // @param file_index    the index in $_FILES where the file is located
  // @param title         the title to be used by the wordpress media library
  // @param type_value    the value for the post's 'type' meta_key
  // @param is_featured_image   if true, the file will be used as the post's featured image
  function AttachFileToPost($post_id, $file_index, $title, $type_value, $is_featured_image)
  {
    $file_name = $_FILES[$file_index]['name'];

    $uploaded_file_type = wp_check_filetype(basename($file_name));

    $file_type = $uploaded_file_type['type'];

    if($file_type['ext'] == false) // TODO add error log here
      return; // attachment type not supported

    $attachment_args = array(
      'post_mime_type' => $file_type,
      'post_title' => $title,
      'post_content' => '',
      'post_status' => 'inherit'
    );

    $file_location = $this->UploadFile($file_index);

    $attach_id = wp_insert_attachment($attachment_args, $file_location, $post_id);
    $meta_key = "type";
    $meta_value = $type_value;
    update_post_meta($attach_id, $meta_key, $meta_value);

    if($is_featured_image) {
      // TODO handle errors
      require_once(ABSPATH . 'wp-admin/includes/image.php');
      $attach_data = wp_generate_attachment_metadata($attach_id, $file_location);
      wp_update_attachment_metadata($attach_id, $attach_data);

      update_post_meta($post_id, '_thumbnail_id', $attach_id);
    }

    return $attach_id;
  }

  // Returns an array of all attachment with the a meta value "type":$attachmentType
  // ex. "type":"jar" or "type":"image"
  //
  // @param assignmentId   the id of the post containing the attachment
  // @param attachmentType the type of attachments to return
  function GetAttachments($assignmentId, $attachmentType)
  {
    $attachment_query = array(
      'post_type'   => 'attachment',
      'meta_key'    => 'type',
      'meta_value'  => $attachmentType,
      'post_status' => 'any',
      'post_parent' => $assignmentId,
    );

    return get_posts($attachment_query);
  }

  function CreateAssignment($authorId, $courseId, $title, $description, $link = "", $isEnabled = true)
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
    add_post_meta($postId, "link", $link);
    add_post_meta($postId, "isEnabled", $isEnabled);

    return $postId;
  }

  function UpdateAssignment($assignmentId, $authorId, $courseId, $title, $description, $link = "", $isEnabled = true)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $assignmentPost = array();
    $assignmentPost['ID'] = $assignmentId;
    $assignmentPost['post_title'] = $title;
    $assignmentPost['post_content'] = $description;

    update_post_meta($assignmentId, "link", $link);
    update_post_meta($assignmentId, "isEnabled", $isEnabled);

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

  function DownloadAllSubmissions($assignmentId)
  {
    $allFilePaths = $this->ListSubmissionJars($assignmentId);
    $zipLocation = $this->BuildSubmissionZipFile($allFilePaths);

    echo "<br /> Path: " . $zipLocation['path'];
    echo "<br /> Url:  " . $zipLocation['url'];
  }

  private function ListSubmissionJars($assignmentId)
  {
    $submissions = $this->GetAllSubmissions($assignmentId);
    if(count($submissions) == 0)
      return;

    foreach($submissions as $submission) {
      $jarQuery = array(
        'post_type'   => 'attachment',
        'meta_key'    => 'type',
        'meta_value'  => 'jar',
        'numberposts' => 1,
        'post_status' => 'any',
        'post_parent' => $submission->SubmissionId,
      );

      $jarAttachments = get_posts($jarQuery);
      $jarFile = $jarAttachments[0];
      $allFilePaths[] = get_attached_file($jarFile->ID);
    }
    return $allFilePaths;
  }

  private function BuildSubmissionZipFile($allFilePaths)
  {
    ini_set('max_execution_time', 0);

    $zipPath = ABSPATH . get_option('upload_path') . "submissions.zip";
    $zipDownloadLink = get_site_url() . trailingslashit(get_option('upload_path')) . 'submissions.zip';

    $zipLocation['path'] = $zipPath;
    $zipLocation['url'] = $zipDownloadLink;

    $filesToZip = $allFilePaths;
    if(count($filesToZip)){//check we have valid files

      $zip = new ZipArchive;
      $opened = $zip->open($zipPath, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);

      if( $opened !== true ){
        die("cannot open zip file for writing. Please try again in a moment.");
      }

      foreach ($filesToZip as $file) {
        $shortName = basename($file);
        $zip->addFile($file, $shortName);
      }

      $zip->close();
    }

    return $zipLocation;
  }

  function GetAllSubmissions($assignmentId)
  {
    global $wpdb;
    $wpdb->show_errors(true);
    $posts  = $wpdb->prefix . "posts";
    $users  = $wpdb->prefix . "users";

    $sql = "SELECT p.id as SubmissionId, p.post_title as Title, a.display_name as AuthorName, p.post_date as SubmissionDate
      FROM {$posts} p INNER JOIN {$users} a ON p.post_author = a.id
      WHERE p.post_parent = {$assignmentId} AND p.post_status = 'publish';";

    $rows = $wpdb->get_results($sql);

    return $rows;
  }

  function CreateSubmission($title, $authorId, $courseId, $assignmentId, $description)
  {
    // save the assignment submission as post
    $assignmentPost = array(
      'post_title'    => $title,
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

  function GetSubmissions($studentId)
  {
    global $wpdb;
    $wpdb->show_errors(true);
    $posts  = $wpdb->prefix . "posts";
    $users  = $wpdb->prefix . "users";

    $sql = ("SELECT p.id as SubmissionId, p.post_title as AssignmentName, p.post_date as Date
      FROM {$posts} p WHERE p.post_author = $studentId AND p.post_status = 'publish' AND p.post_type = 'post';");

    $rows = $wpdb->get_results($sql);

    return $rows;
  }

  function GetSubmissionBySubmissionName($submissionName)
  {
    global $wpdb;
    $wpdb->show_errors(true);
    $posts = $wpdb->prefix . "posts";

    $sql = ("SELECT p.id as SubmissionId FROM $posts WHERE p.post_name = $submissionName AND p.post_parent > 0;");

    $rows = $wpdb->get_results($sql);

    return $rows;
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
