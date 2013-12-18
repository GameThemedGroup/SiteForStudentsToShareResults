<?php
/**
 * Template Name: My Class 
 * Description: Shows course information, assignments, and other students.
 *
 * Author: Andrey Brushchenko
 * Date: 10/6/2013
 */
get_header(); ?>

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

<!DOCTYPE html>
<html lang="en">
<?php if(isset($course)) : ?>
  <div id="class-whole">
    <div id="class-title"><?php echo $course->Name ?>             
<?php if($isOwner) : ?>
      <a id="link" href='<?php echo site_url('/manage-courses/') . "?op=edit&courseid=" . $courseId;?>'>Edit Course</a>
<?php endif ?>
    </div>
    <div id="class-info1">
<?php 
echo "<b>Professor </b>";
echo $professor->last_name . ', ' . $professor->first_name . ' '; 
echo "[<a href=\"" . $professor_link . "\">" . $professor->user_login . '</a>]';
?>
    </div>
    <div id="class-info2">
      <?php echo '<b>Email </b>' . $professor->user_email; ?>
    </div>
    <div id="class-info1">
      <?php echo '<b>Quarter </b>' . $course->Quarter . ", " . $course->Year ?>
    </div>
    <div id="class-description">
      <b><p>Description</p></b>
      <?php echo ($course->Description ? nl2br($course->Description)  : "This course has no description") ?>
    </div>
  </div>

<?php if($isOwner) : ?>
  <div id="sidebar-menu">
    <div id="sidebar-menu-title">My Courses</div>
    <ul class="sidebar-menu">
<?php foreach($courses as $course) : ?>
<?php   if($courseId == $course->Id) : ?>
      <li class="sidebar-menu-selected">
<?php   else : ?> 
      <li class="sidebar-menu">
<?php   endif ?>
        <a href="<?php echo site_url('/my-class/?courseid=' . $course->Id) ?>">
          <p class="sidebar-menu-top"><?php echo $course->Name ?></p>
          <p class="sidebar-menu-bottom"><?php echo $course->Quarter . ', ' . $course->Year ?></p>
        </a>
      </li>
<?php endforeach ?>
      <li class="sidebar-menu-center"><a class="action" href="<?php echo site_url('/manage-courses/') ?>">Create course</a></li>
    </ul>
  </div>
<?php endif ?>

  <div id="sidebar-menu">
    <div id="sidebar-menu-title">Students</div>
      <ul class="sidebar-menu">
<?php $students = $gtcs12_db->GetStudents($courseId); ?>
<?php if ($students) : ?>
<?php   foreach($students as $student) : ?>
<?php     if($student->StudentId != null) : ?>
              <li class="sidebar-menu">
                <p class="sidebar-menu-middle"><a href="<?php echo site_url('/profile/?user=' . $student->Id) ?>"><?php echo $student->Name ?></a></p>
              </li>
<?php     endif ?>
<?php   endforeach ?>
<?php endif ?>
<?php if($isOwner) : ?>
        <li class="sidebar-menu-center"><a class="action" href="<?php echo site_url('/manage-students/?courseid=' . $courseId) ?>">Add students</a></li>
<?php else : ?>
        <li class="sidebar-menu-center">There are no enrolled students</li>
<?php endif ?>
        </ul>
    </div>
  <div id="table">
    <div id='table-title'>Assignments</div>
      <table>
        <thead>
          <tr>
            <th>Assignment</th>
            <th>Date Posted</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
<?php 
if($assignments) 
{
  foreach($assignments as $assignment) 
  {
    $status = get_post_meta($assignment->AssignmentId, 'isEnabled', true);
    $assignlink = site_url('/assignment/?assignid=' . $assignment->AssignmentId);
    echo "<tr>";      
    echo "<th><a href=" . $assignlink . ">" . $assignment->Title . "</a></th>";
    echo "<th>" . date('F d, Y', strtotime($assignment->Date)) . "</th>";
    
    if($status)
      echo "<th>Open</th>";
    else 
      echo "<th>Closed</th>";
      
    echo "</tr>";
  }
}
elseif($isOwner == false)
{
  echo "<tr>";      
  echo "<th class=\"center\" colspan=\"2\">There are no assignments</th>";
  echo "</tr>";
}
?>
<?php   if($isOwner) : ?>
        <tr class="break">
          <th></th>
          <th></th>
          <th></th>
        </tr>
        <tr>
          <th class="action" colspan="3">
            <a class="action" href="<?php echo site_url('/manage-assignments/?courseid=' . $courseId) ?>">Create an assignment</a>
          </th>
        </tr>
<?php   endif ?>
      </tbody>
    </table>
  </div>
<?php else : ?>
  <div id="action-box">Course can not be found</div>
<?php endif ?>
</html>

<?php get_footer(); ?>
