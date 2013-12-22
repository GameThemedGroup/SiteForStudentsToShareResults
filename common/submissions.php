<?php
class GTCS_Submissions
{
  public static function DownloadAllSubmissions($assignmentId)
  {
    $allFilePaths = GTCS_Submissions::ListSubmissionJars($assignmentId);
    $zipLocation = GTCS_Submissions::BuildSubmissionZipFile($allFilePaths);

    echo "<br /> Path: " . $zipLocation['path'];
    echo "<br /> Url:  " . $zipLocation['url'];
  }

  private static function ListSubmissionJars($assignmentId)
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

  private static function BuildSubmissionZipFile($allFilePaths)
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

  public static function GetAllSubmissions($assignmentId)
  {
    $args = array(
      'post_parent' => $assignmentId
    );

    $submissionPosts = get_posts($args);

    $submissionList = array();

    foreach ($submissionPosts as $submission) {
      $author = get_user_by('id', (int) $submission->post_author);

      $submissionList[] = (object) array(
        'AuthorId'     => (int) $submission->post_author,
        'AuthorName'   => $author->display_name,
        'SubmissionId' => (int) $submission->ID,
        'Title'        => $submission->post_title,
        'SubmissionDate'=> $submission->post_date
      );
    }

    return $submissionList;
  }

  // @param assignmentId
  // @param courseId
  // @param description
  // @param entryClass
  // @param studentId
  // @param title
  public static function CreateSubmission($args)
  {
    $assignmentId = $args->assignmentId;
    $courseId = $args->courseId;
    $description = $args->description;
    $entryClass = $args->entryClass;
    $userId = $args->studentId;
    $title = $args->title;

/*    if (!gtcs_validate_not_null(__FUNCTION__, __FILE__, __LINE__,
      compact('assignmentId', 'courseId', 'description', 'entryClass',
        'studentId', 'title'))) {

      return -1;
    }
 */
    // save the assignment submission as post
    $submissionArgs = array(
      'post_title'    => $title,
      'post_content'  => $description,
      'post_status'   => 'publish',
      'post_author'   => $userId,
      'comment_status' => 'open',
      'post_parent' => $assignmentId,
      'tags_input' => array('course:' . $courseId)
    );

    $postId = wp_insert_post($submissionArgs);
    if (is_wp_error($postId)) {
      trigger_error(__FUNCTION__ . " - Error submitting assignment.", E_USER_WARNING);
      return $postId;
    }

    add_post_meta($postId, "entryClass", $entryClass);

    global $gtcs_Categories;
    $postCategory = array($gtcs_Categories['submission']);
    wp_set_post_terms($postId, $postCategory, 'category');

    return $postId;
  }

  public static function UpdateSubmission($subId, $description)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $assignmentPost = array();
    $assignmentPost['ID'] = $subId;
    $assignmentPost['post_content'] = $description;

    wp_update_post($assignmentPost);
  }

  public static function GetSubmissions($studentId)
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

  public static function GetSubmissionBySubmissionName($submissionName)
  {
    global $wpdb;
    $wpdb->show_errors(true);
    $posts = $wpdb->prefix . "posts";

    $sql = ("SELECT p.id as SubmissionId FROM $posts WHERE p.post_name = $submissionName AND p.post_parent > 0;");

    $rows = $wpdb->get_results($sql);

    return $rows;
  }
}
?>
