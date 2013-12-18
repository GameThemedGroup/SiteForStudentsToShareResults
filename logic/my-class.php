<?php
  $current_user = wp_get_current_user();
  $is_student = gtcs_user_has_role('subscriber');
  $is_professor = gtcs_user_has_role('author');

  if(isset($_GET['courseid']))
  {
    $courseId = $_GET['courseid'];
    $courses = $gtcs12_db->GetCourseByFacultyId($current_user->ID);
  }
  else
  {
    if($is_student) {
      $courses = $gtcs12_db->GetCourseByStudentId($current_user->ID);
    }
    elseif($is_professor) {
      $courses = $gtcs12_db->GetCourseByFacultyId($current_user->ID);
    }

    if($courses)
    {
      $courseId = $courses[0]->Id;
    }
  }

  if(isset($courseId))
  {
    $course = $gtcs12_db->GetCourseByCourseId($courseId);
  }

  if(isset($course))
  {
    $professor = get_userdata($course->FacultyId);
    $professor_link = site_url('/profile/?user=') . $course->FacultyId;
    $assignments = $gtcs12_db->GetAllAssignments($courseId);

    if($is_professor)
    {
      $isOwner = ($course->FacultyId == $current_user->ID);
    }
  }
?>
