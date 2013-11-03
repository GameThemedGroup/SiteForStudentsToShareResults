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
	global $gtcs12_db;

	//$gtcs12_db->AddCourse('testcourse', 'Summer', 1999, 2); // for testing
	$current_user = wp_get_current_user();

	if($_GET['del']) // has a course been marked for deletion
	{
		$course = $gtcs12_db->GetCourse($_GET['del']);
		if($course) // does course exists
		{
			if($course->FacultyId == $current_user->ID) // does logged in user own course
			{
				$courseId = $_GET['del'];
				$gtcs12_db->DeleteCourse($courseId);
				$action = "deleted";
			}
			else 
			{
				$action = "owner error";
			}
		}
		else 
		{
			$action = "found error";
		}
	}

	if ($_POST) 
	{
    $gtcs12_db->AddCourse(
      $_POST['inptTitle'], 
      $_POST['slctQuarter'], 
      $_POST['slctYear'], 
      $current_user->ID, 
      $_POST['description']
    );
	}
?>

<!DOCTYPE html>
<html lang="en">
<?php if($action == "owner error") : ?>
	<div id="error-box">Error:You don't have ownership of that course</div>
<?php elseif($action == "found error") : ?>
	<div id="error-box">Error:Course not found</div>
<?php elseif($action == "deleted") : ?>
	<div id="action-box">Course deleted</div>
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
      <div id='create-course-field' name='inptDescription'>
        Description
        <textarea cols="25" rows="10" autocomplete="off" name="description" required></textarea>
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
          <th>Faculty</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
<?php $courses = $gtcs12_db->GetAllCourses(); ?>
<?php if($courses) : ?>
<?php foreach($courses as $course) : ?> 
        <tr>
					<th><?php echo $course->Name; ?></th>
          <th><?php echo $course->Quarter; ?></th>
          <th><?php echo $course->Year; ?></th>
          <th><?php echo $course->FacultyName; ?></th>
          <th><a href='<?php echo site_url('/manage-courses/') . "?del=" . $course->Id;?>'>Delete</a></th>
        </tr>
<?php endforeach; ?>
<?php else : ?>
				<tr>
					<th>N/A</th>
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
