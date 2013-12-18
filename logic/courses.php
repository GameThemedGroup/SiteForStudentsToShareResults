<?php
  $professorId = wp_get_current_user()->ID;

  $operation = ifsetor($_POST['action'], null);

  $userFeedback = "";
  $isEditing = false;
  $course = (object) array('Name' => '', 'Description' => '', 'Quarter' => '');

  if ($operation == 'edit') {
    $userFeedback = editCourseSetup($course, $isEditing);

  } else if ($operation != null) {
    $operations = array(
      'delete' => 'deleteCourse',
      'update' => 'updateCourse',
      'create' => 'addCourse',
    );
    if (array_key_exists($operation, $operations)) {
      $userFeedback = call_user_func($operations[$operation], $professorId);
    } else {
      trigger_error("An invalid action was provided.", E_USER_WARNING);
    }
  }

  $quarterList = array(
    'Fall' => true,
    'Winter' => false,
    'Spring' => false,
    'Summer' => false
  );

  $year = date('Y');
  $yearList[$year - 1] = false; // one year back
  $yearList[$year] = true;
  $yearList[$year + 1] = false; // one year forward

  if ($isEditing) { // reset default selections
    $quarterList['Fall'] = true;
    $quarterList[$course->Quarter] = true;

    $yearList[$year] = false;
    $yearList[$course->Year] = true;
  }

  $courseList = $gtcs12_db->GetCourseByFacultyId($professorId);

  $url = array(
    'courses' => site_url('/courses/'),
    'my-class' => site_url('/courses/')
  );

function editCourseSetup(&$course, &$isEditing)
{
  $courseId = ifsetor($_POST['courseid'], null);
  $isEditing = $courseId != null;

  if ($courseId == null) {
    trigger_error(__FUNCTION__ . "
      - An invalid Course ID was provided.",
      E_USER_WARNING);

    return "There was an error attempting to edit the course.";
  }

  global $gtcs12_db;
  $course = $gtcs12_db->GetCourseByCourseId($courseId);
  return "Your are now editing the course";
}

function addCourse($professorId)
{
  $title       = ifsetor($_POST['title'], null);
  $quarter     = ifsetor($_POST['quarter'], null);
  $year        = ifsetor($_POST['year'], null);
  $description = ifsetor($_POST['description'], null);

  if (   $title == null
      || $quarter == null
      || $year == null
      || $description == null) {
    echo "$title, $quarter, $year, $description";
    return "Invalid input when creating course";
  }

  global $gtcs12_db;
  $gtcs12_db->AddCourse($title, $quarter, $year, $professorId, $description);
  return "course created";
}

function updateCourse($userId)
{
  $title       = ifsetor($_POST['title'], null);
  $quarter     = ifsetor($_POST['qarter'], null);
  $year        = ifsetor($_POST['year'], null);
  $description = ifsetor($_POST['description'], null);
  $courseId    = ifsetor($_POST['courseid'], null);

  if (   $courseId == null
      || $title == null
      || $quarter == null
      || $year == null
      || $description == null) {
    return "Invalid input when editing course";
  }

  $gtcs12_db->UpdateCourse($courseId, $title, $quarter, $year, $userId, $description);
  return "course edited";
}

function deleteCourse($professorId)
{
  $courseId = ifsetor($_POST['courseid'], null);

  global $gtcs12_db;
  $course = $gtcs12_db->GetCourseByCourseId($courseId);

  if ($course) { // course exists
    if ($course->FacultyId == $professorId) { // user owns course
      $gtcs12_db->DeleteCourse($courseId);
      return "course deleted";
    } else {
      return "not owner";
    }
  } else {
    return "course not found";
  }
}
?>
