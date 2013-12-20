<?php
// services/gtcs_assignments.php
// Functions used for creating and modifying assignments

class GTCS_Assignments
{
  public static function createAssignmentsFromXml($xml, $professorId, $courseId)
  {
    // TODO verify the xml schema before continuing

    if (!gtcs_user_has_role('author')) {
      trigger_error(__FUNCTION__ . " - User does not have permission to
        perform this action.", E_USER_WARNING);
      return array();
    }

    if (!isset($professorId) || !isset($courseId)) {
      if (!isset($professorId))
        trigger_error(__FUNCTION__ . " - Professor Id not provided.", E_USER_WARNING);
      if (!isset($courseId))
        trigger_error(__FUNCTION__ . " - Course Id not provided.", E_USER_WARNING);

      return array();
    }

    // TODO fix the conversion back and forth between objects and arrays
    $assignmentData = json_decode(json_encode((array) simplexml_load_string($xml)), 1);

    if (isset($assignmentData['assignment']->title)) { // single xml element
      $assignments[] = $assignmentData['assignment'];
    } else {
      $assignments = $assignmentData['assignment'];
    }

    foreach ($assignments as $assignment) {
      $assignmentArgs = (object) $assignment;
      $assignmentArgs->courseId = $courseId;
      $assignmentArgs->professorId = $professorId;
      $assignmentArgs->isEnabled = true;

      $assignmentId = GTCS_Assignments::CreateAssignment($assignmentArgs);

      // TODO implement jar and image uploads
      if (isset($assignment['image']) || isset($assignments['jar'])) {
        trigger_error(__FUNCTION__ . " - Jar and Image upload not yet
          implemented.", E_USER_WARNING);
        require_once(get_template_directory() . '/common/attachments.php');
        //$file = handleFileUpload();
        //attachFileToSubmission($file, $assignment['title'], $assignmentId, $professorId, true);
      }

      if (is_wp_error($assignmentId)) {
        echo "Error creating assignment. <br />";
        htmldump($assignmentId);
      }
    }
  }

  // @param args - an object containing the following fields
  //    title       - the title of the assignment
  //    courseId    - id of the course the assignment belongs to
  //    description - (optional) text description of the course. Defaults to ""
  //    professorId - (optional) id of the professor. Defaults to
  //                  id of logged in user
  //    courseLink  - (optional) the link to an external website containing
  //                  detailed assignment information. Defaults to ""
  //    isEnabled   - (optional) boolean flag used to determine if the
  //                  assignment still accepts submissions. Defaults to true
  //
  // @return the post_id if successfully created, otherwise wp_error
  public static function createAssignment($args)
  {
    if (!gtcs_user_has_role('author')) {
      trigger_error(__FUNCTION__ . " - User does not have permission to
        perform this action.", E_USER_WARNING);
      return -1;
    }

    $title = ifsetor($args->title, NULL);
    $courseId = ifsetor($args->courseId, NULL);

    if ($title === NULL || $courseId === NULL) {
      if ($title == NULL)
        trigger_error(__FUNCTION__ . " - Title not provided.", E_USER_WARNING);
      if ($courseId == NULL)
        trigger_error(__FUNCTION__ . " - Course Id not provided.", E_USER_WARNING);

      return -1;
    }

    $professorId = ifsetor($args->professorId, NULL);
    if ($professorId === NULL)
      $professorId = wp_get_current_user()->ID;

    $description = ifsetor($args->description, "");

    // save the assignment submission as post
    $assignmentPost = array(
      'post_title'    => $title,
      'post_content'  => $description,
      'post_status'   => 'publish',
      'post_author'   => $professorId,
      'comment_status' => 'open',
      'tags_input' => array('course:' . $courseId)
    );

    $postId = wp_insert_post($assignmentPost);

    if (is_wp_error($postId)) {
      trigger_error(__FUNCTION__ . " - Error creating assignment.", E_USER_WARNING);
      return $postId;
    }

    $courseLink = ifsetor($args->courseLink, "");
    $isEnabled = ifsetor($args->isEnabled, true);

    add_post_meta($postId, "course", $courseId);
    add_post_meta($postId, "link", $courseLink);
    add_post_meta($postId, "isEnabled", $isEnabled);

    global $gtcs_Categories;
    $postCategory = array($gtcs_Categories['assignment']);
    wp_set_post_terms($postId, $postCategory, 'category');

    return $postId;
  }

  public static function getAllAssignments($courseId)
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
}
?>
