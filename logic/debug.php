<?php
$isAdmin = gtcs_user_has_role('administrator');

if(!$isAdmin) {
  echo "You do not have permission to view this page. <br />";
  return;
}

$operation = $_GET['op'];
$results = '';

if ($operation) {
  if ($operation == 'reset') {
    resetTestData();
    loadTestData(get_template_directory() . '/tests/data/', 'test_data.xml');
  } else {
    $results = 'unknown operation ' . $operation;
  }
}

function resetTestData()
{
  include_once(get_template_directory() . '/common/admin.php');
  GTCS_Admin::RecreateTables();

  if (!function_exists('wp_delete_user')) {
    include(ABSPATH . './wp-admin/includes/user.php');
  }

  $students = get_users(array('role' => 'Subscriber'));
  $professors = get_users(array('role' => 'Author'));
  $usersToDelete = array_merge($students, $professors);

  foreach ($usersToDelete as $user) {
    wp_delete_user($user->ID);
  }

  echo "All users deleted. <br />";
}

function loadTestData($testDir, $testFile)
{
  $testData = $testDir . $testFile;

  $xml_string = file_get_contents($testData);

  if ($xml_string === FALSE) {
    trigger_error(__FUNCTION__ . ": Error reading {$testData}.", E_USER_WARNING);
    return;
  }

  // convert xml into array
  $xml = json_decode(json_encode((array) simplexml_load_string($xml_string)), 1);

  $studentIds = createUsersFromXml($xml['students'], 'subscriber');
  $professorIds = createUsersFromXml($xml['professors'], 'author');

  $courseIds = createCoursesFromXml($xml['courses'], $studentIds, $professorIds);

  $assignmentIds = createAssignmentsFromXml(
    $xml['assignments'],
    $courseIds,
    $professorIds,
    $testDir
  );
  $submissionIds = createSubmissionsFromXml(
    $xml['submissions'],
    $assignmentIds,
    $studentIds,
    $testDir
  );

  createSubmissionCommentsFromXml($xml['comments'], $submissionIds, $studentIds);
  echo "Loading test data: complete. <br />";
}

function createSubmissionCommentsFromXml($xml, $submissionIds, $userIds)
{
  foreach ($xml['comment'] as $comment) {
    $submissionid = $submissionIds[$comment['submission']];
    $userid = $userIds[$comment['user']];
    $commentText = $comment['text'];

    $commentid = createSubmissionComment($submissionid, $userid, $commentText);

    if (!is_wp_error($commentid)) {
      echo "comment created: <br />";
    } else {
      htmldump($commentid, '10em');
      echo "Error creating comment: <br />";
    }
  }
}

function createSubmissionComment($submissionid, $userid, $comment)
{
  $user = get_user_by('id', $userid);

  $args = array(
    'comment_post_ID' => $submissionid,
    'comment_author' => $user->display_name,
    'comment_author_email' => $user->user_email,
    'comment_content' => $comment,
    'user_id' => $userid,
    'comment_approved' => 1
  );

  return wp_insert_comment($args);
}

function createSubmissionsFromXml($xml, $assignmentIds, $studentIds, $dir)
{
  $submissionIds = array();
  foreach ($xml['submission'] as $submission) {
    $assignmentid = $assignmentIds[$submission['assignment']];
    $studentid = $studentIds[$submission['student']];

    $submissionid = createSubmission($submission, $assignmentid, $studentid, $dir);

    if (!is_wp_error($submissionid)) {
      $submissionIds[$submission['title']] = $submissionid;
      echo "submission created: ";
    } else {
      htmldump($userid, '10em');
      echo "Error creating: ";
    }

    echo "{$submission['assignment']}({$assignmentid}) -
          {$submission['student']}({$studentid}) -
          {$submission['title']}({$submissionid}). <br />";

  }

  return $submissionIds;
}

function createSubmission($submission, $assignmentid, $studentid, $dir)
{
  include_once(get_template_directory() . '/common/submissions.php');
  $submissionid = GTCS_Submissions::CreateSubmission(
    $submission['title'],
    $studentid,
    0, // why is course id needed?
    $assignmentid,
    $submission['title']
  );

  if ($submission['image']) {
    $file = $dir . 'files/' . $submission['image'];
    attachFileToSubmission($file, $submission['title'], $submissionid, $studentid, true);
  }

  if ($submission['jar']) {
    $file = $dir . 'files/' . $submission['jar'];
    attachFileToSubmission($file, $submission['title'], $submissionid, $studentid, false);
  }

  return $submissionid;
}

function attachFileToSubmission($file, $title, $submissionid, $userid, $isImage)
{
  require_once(get_template_directory() . '/services/fileimport.php');
  $fileAttributes = gtcs_handle_import_file($file, $submissionid);

  include_once(get_template_directory() . '/common/attachments.php');
  $attachmentType = $isImage ? 'image' : 'jar';
  $attachmentid = GTCS_Attachments::attachFileToPost(
    $submissionid,
    $fileAttributes,
    $title,
    $attachmentType,
    $isImage,
    $userid
  );
}

function createAssignmentsFromXml($xml, $courseIds, $professorIds, $dir)
{
  $assignmentIds = array();
  foreach ($xml['assignment'] as $assignment) {
    $courseid = $courseIds[$assignment['course']];
    $professorid = $professorIds[$assignment['professor']];

    $assignmentid = createAssignment($assignment, $courseid, $professorid);

    if ($assignment['image']) {
      $file = $dir . 'files/' . $assignment['image'];
      attachFileToSubmission($file, $assignment['title'], $assignmentid, $professorid, true);
    }

    if (!is_wp_error($assignmentid)) {
      $assignmentIds[$assignment['title']] = $assignmentid;
      echo "assignment created: ";
      echo "{$assignment['professor']}({$professorid}) -
          {$assignment['course']}({$courseid}) -
          {$assignment['title']}({$assignmentid}). <br />";
    }
  }

  return $assignmentIds;
}

function createAssignment($assignment, $courseid, $professorid)
{
  include_once(get_template_directory() . '/common/assignments.php');
  $assignment['courseId'] = $courseid;
  $assignment['professorId'] = $professorid;
  $assignmentid = GTCS_Assignments::CreateAssignment((object) $assignment);

  return $assignmentid;
}

function createCoursesFromXml($xml, $studentIds, $professorIds)
{
  $courseIds = array();
  foreach ($xml['course'] as $course) {
    $professorid = $professorIds[$course['professor']];
    $courseid = createCourse($course, $professorid);

    if (!is_wp_error($courseid)) {
      updateStudentEnrollments($courseid, $course['student'], $studentIds);
      $courseIds[$course['title']] = $courseid;
      echo "Course created: ";
    } else {
      htmldump($userid, '10em');
      echo "Error creating: ";
    }

    echo "{$course['professor']}({$professorid}) - {$course['title']}. <br />";
  }

  return $courseIds;
}

function updateStudentEnrollments($courseid, $students, $studentIds)
{
  include_once(get_template_directory() . '/common/users.php');
  foreach ($students as $student) {
    $studentid = $studentIds[$student];
    GTCS_Users::UpdateStudentEnrollment($courseid, $studentid, true);
  }
}

function createUsersFromXml($xml, $role)
{
  $userIds = array();
  foreach ($xml[$role] as $user) {
    $userid = createUser($user, $role);

    if (!is_wp_error($userid)) {
      $userIds[$user['login']] = $userid;
      echo "user created: {$user['login']}. <br />";
    } else {
      echo "Error creating {$user['login']}. <br />";
      htmldump($userid, '10em');
      continue;
    }
  }

  return $userIds;
}

function createCourse($course, $professorid)
{
  include_once(get_template_directory() . '/common/courses.php');
  $course['professorId'] = $professorid;
  $courseid = GTCS_Courses::addCourse((object) $course);

  return $courseid;
}

function createUser($user, $role)
{
  $user['password'] = "password";
  $user['role'] = $role;

  include_once(get_template_directory() . '/common/users.php');
  $user_id = GTCS_Users::AddUser(
    $user['login'],
    $user['password'],
    $user['user_email'],
    $user['first_name'],
    $user['last_name'],
    $user['role']
  );

  return $user_id;
}

?>
