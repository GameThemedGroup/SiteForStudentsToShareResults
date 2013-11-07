<?php
/**
 * Template Name: Manage Courses
 * Description: Allows for the creation/deletion of courses.
 *
 * Author: Andrey Brushchenko
 * Date: 10/6/2013
 */
get_header(); ?>

<?php
//$gtcs12_db->AddCourse('CSS 600', 'Autumn', 2003, 2, "sdasd ad sadasdad asdasdad asdd dadasd adad adsadasd sada"); // for testing
$current_user = wp_get_current_user();

if($_GET['del']) // has a course been marked for deletion
{
  $course = $gtcs12_db->GetCourseByCourseId($_GET['del']);
  if($course) // does course exists
  {
    if($course->FacultyId == $current_user->ID) // does user own course
    {
      $courseId = $_GET['del'];
      $gtcs12_db->DeleteCourse($courseId);
      $action = "course deleted";
    }
    else 
    {
      $action = "not owner";
    }
  }
  else 
  {
    $action = "course not found";
  }
}

if ($_POST) 
{
  if(gtcs_user_has_role('author')) // is this user a professor
  {
    $gtcs12_db->AddCourse($_POST['inptTitle'], $_POST['slctQuarter'], $_POST['slctYear'], $current_user->ID, $_POST['inptDescription']);
    $action = "course created";
  }
  else
  {
    $action = "invalid role";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php if($action == "not owner") : ?>
  <div id="error-box">Error:You don't have ownership of that course</div>
<?php elseif($action == "course not found") : ?>
  <div id="error-box">Error:Course not found</div>
<?php elseif($action == "invalid role") : ?>
  <div id="error-box">Error:You don't have permission to create a course</div>
<?php elseif($action == "course deleted") : ?>
  <div id="action-box">Course deleted</div>
<?php elseif($action == "course created") : ?>
  <div id="action-box">Course created</div>
<?php endif ?>

  <form action="<?php echo site_url('/manage-courses/') ?>" method="post">
    <div id='create-course-box'>
      <div id='pagetitle'>Create Course</div>   
      <div id='create-course-field'>
        <p class="create-course">Title</p>
        <input class='create-course' type="text" name="inptTitle" required><br>
      </div>
      <div id='create-course-field'>
        <p class="create-course">Quarter</p>
        <select class='create-course' name='slctQuarter'>
          <option value="Autumn">Autumn</option>
          <option value="Winter">Winter</option>
          <option value="Spring">Spring</option>
          <option value="Summer">Summer</option>        
        </select>
      </div>
      <div id='create-course-field'>
        <p class="create-course">Year</p>
        <select class='create-course' name='slctYear'>
<?php for($x = 0; $x < 10; $x++)
  {
    echo "<option value=\"" . (date("Y") - $x) . "\">" . (date("Y") - $x) . "</option>";
  }
?>      
        </select>
      </div>
      <div id='create-course-field'>
        Description
        <textarea cols="25" rows="10" autocomplete="off" name="inptDescription" required></textarea>
      </div>
      <input type="submit" value="Submit"/> 
    </div>
  </form>

  <div id='table'>
    <div id='pagetitle'>Manage Courses</div>
    <table>
      <thead>
        <tr>
          <th>Title</th>
          <th>Quarter</th>
          <th>Year</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
<?php $courses = $gtcs12_db->GetCourseByFacultyId($current_user->ID); ?>
<?php if($courses) : ?>
<?php foreach($courses as $course) : ?> 
        <tr>
          <th><?php echo $course->Name; ?></th>
          <th><?php echo $course->Quarter; ?></th>
          <th><?php echo $course->Year; ?></th>
          <th><a href='<?php echo site_url('/manage-courses/') . "?del=" . $course->Id;?>'>Delete</a></th>
        </tr>
<?php endforeach; ?>
<?php else : ?>
        <tr>
          <th>N/A</th>
          <th>N/A</th>
          <th>N/A</th>
          <th>N/A</th>
        </tr>
<?php endif ?>
      </tbody>
    </table>
  </div>
</html>

<?php get_footer() ?>
