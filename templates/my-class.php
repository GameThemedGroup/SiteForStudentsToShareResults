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

$is_student = (gtcs_user_has_role('subscriber'));
$is_professor = (gtcs_user_has_role('author'));

if($_GET['id'] != null)
{
  $course_ID = $_GET['id'];
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
    $course_ID = $courses[0]->Id;
}

$course = $gtcs12_db->GetCourseByCourseId($course_ID);
$professor = get_userdata($course->FacultyId);
$professor_link = site_url('/profile/?user=') . $course->FacultyId;
$assignments = $gtcs12_db->GetAllAssignments($course_ID);

if(gtcs_user_has_role('author')) // author
{
  $isOwner = ($course->FacultyId == $current_user->ID);
}

?>

<!DOCTYPE html>
<html lang="en">
  <div id="myclass">
    <div id="pagetitle"><?php echo $course->Name ?></div>
    <div id="professor">
<?php 
echo "<b>Professor </b>";
echo $professor->last_name . ', ' . $professor->first_name . ' '; 
echo "[<a href=\"" . $professor_link . "\">" . $professor->user_login . '</a>]';
?>
    </div>
    <div id="email">
      <b>Email</b><?php echo $professor->user_email; ?>
    </div>
    <div id="quarter">
      <?php echo '<b>Quarter </b>' . $course->Quarter . ", " . $course->Year ?>
    </div>
    <div id="classdescription">
      <b>Description </b>
      <?php echo $course->Description ?>
    </div>
  </div><!-- myclass -->

<?php if($isOwner): ?>
  <div id="options-box">
    <div id="options-title">Courses</div>
    <ul class="options">
<?php foreach($courses as $course) : ?>
<?php if($course_ID == $course->Id) : ?>
      <li class="options-selected">
        <?php echo '[' . $course->Quarter . ']' ?>
        <?php echo $course->Name ?>
      </li>
<?php else : ?> 
      <li class="options">
        <a href="<?php echo site_url('/my-class/?id=' . $course->Id) ?>">
          <?php echo '[' . $course->Quarter . ']' ?>
          <?php echo $course->Name ?>
        </a>
      </li>
<?php endif ?>
<?php endforeach ?>
    </ul>
  </div> 
<?php endif ?>

  <div id="options-box">
    <div id="options-title">Students</div>
    <ul class="options">
<?php $students = $gtcs12_db->GetStudents($course_ID); ?>
<?php if ($students) : ?>
<?php foreach($students as $student) : ?>
<?php if($student->StudentId != null) : ?>
      <li class="options">
        <a href="<?php echo site_url('/profile/?user=' . $student->Id) ?>">
          <?php echo $student->Name ?>
        </a>
      </li>
<?php endif ?>
<?php endforeach ?>
<?php else : ?>
      <li class="options">
        There are no enrolled students
      </li>
<?php endif ?>
    </ul>
  </div> 

  <div id="table">
    <table>
      <thead>
        <tr>
          <th>Assignment</th>
          <th>Date Posted</th>
        </tr>
      </thead>
      <tbody>
<?php 
if($assignments) {
  foreach($assignments as $assignment) {
    $assignlink = site_url('/assignment/?id=' . $assignment->AssignmentId);
    echo "<tr>";    
    echo "<th class=\"assignment-table\"><a href=" . $assignlink . ">" . $assignment->Title . "</a></th>";
    echo "<th class=\"assignment-table\">" . date('F d, Y', strtotime($assignment->Date)) . "</th>";
    echo "</tr>";
  }
}
else {
  echo "<tr>";    
  echo "<th class=\"assignment-table\">" . 'N/A' . "</th>";
  echo "<th class=\"assignment-table\">" . 'N/A' . "</th>";
  echo "</tr>";
}
?>
      </tbody>
    </table>
  </div>
</html>

<?php get_footer(); ?>
