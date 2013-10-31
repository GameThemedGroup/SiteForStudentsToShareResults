<?php
/**
 * Template Name: Manage Assignments
 * Description: Allows for the creation/deletion of assignments.
 *
 * Author: Andrey Brushchenko
 * Date: 10/6/2013
 */
get_header(); ?>

<?php
	global $gtcs12_db;
	
	$current_user = wp_get_current_user();
	$course_ID = $_GET['id']; // for testing purposes only, will need to be changed depending on database 
	
	//$assignmentId = $gtcs12_db->CreateAssignment($current_user->ID, 7, 'assignment5', 'description5');
	//$assignments = get_posts('&orderby=date&tag=course:' . $course_ID);
	//var_dump($assignments);
	//echo $_POST['title'] . ' ' . $_POST['description'] . ' ' . $_POST['sample-file'] . ' ' . $_POST['preview-image'];
	
	if ($_GET['del']) 
	{
		$assignment_ID = $_GET['del'];
		$assignment_info = get_post($assignment_ID);
		if($assignment_info) 
		{
			if($assignment_info->post_author == $current_user->ID)
			{
				wp_delete_post($assignment_ID);
				$action = 'deleted';
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
		// create assignment post
		$assignmentId = $gtcs12_db->CreateAssignment($current_user->ID, $course_ID, $_POST['title'], $_POST['description']);
	
		// prepare paths of images and jar files
		$imageFileName = $authorId . '_' . $courseId . '_' . $assignmentId . '.png'; // only .png supported now
		$jarFileName = $authorId . '_' . $courseId . '_' . $assignmentId . '.jar';

		// upload the files and get their destination paths
		$destFilePaths = $gtcs12_db->UploadFiles("filAssignment", array($imageFileName, $jarFileName));
	}
?>

<!DOCTYPE html>
<html lang="en">
<?php if($action == "owner error") : ?>
	<div id="error-box">Error:You don't have ownership of that assignment</div>
<?php elseif($action == "found error") : ?>
	<div id="error-box">Error:Assignment not found</div>
<?php elseif($action == "deleted") : ?>
	<div id="action-box">Assignment deleted</div>
<?php endif ?>

	<form action="" method="post">
		<div id='create-assignment-box-left'>
			<div id='pagetitle'>Create Assignment</div>
			<div id='create-assignment-field'>
				Title
				<input class='create-assignment' type="text" name="title" required><br>
			</div>
			<div id='create-assignment-field' name='description'>
				Description
				<textarea cols="25" rows="10" autocomplete="off" name="description" required></textarea>
			</div>
			<div id='create-assignment-field'>
				Sample File
				<input class='create-assignment' type="file" name="sample-file[]">
			</div>
			<div id='create-assignment-field'>
				Preview Image
				<input class='create-assignment' type="file" name="preview-image[]">
			</div>
			<input type="submit" value="Submit"/>
		</div>
	</form>
	
	<div id="options-box">
		<div id="options-title">Courses</div>
		<ul class="options">
<?php $courses = $gtcs12_db->GetAllCourses(); ?>
<?php foreach($courses as $course) : ?>
<?php if($course_ID  == $course->Id) : ?>
			<li class="options-selected">
				<?php echo '[' . $course->Quarter . ']' ?>
				<?php echo $course->Name ?>
			</li>
<?php else : ?> 
			<li class="options">
				<a href="<?php echo site_url('/manage-assignments/?id=' . $course->Id) ?>">
					<?php echo '[' . $course->Quarter . ']' ?>
					<?php echo $course->Name ?>
				</a>
			</li>
<?php endif ?>
<?php endforeach ?>
		</ul>
	</div> 
	
	<div id='table'>	
		<table class='manage-courses'>
		<div id='pagetitle'>Manage Assignments</div>
			<thead class='manage-courses'>
				<tr>
					<th>Title</th>
					<th>Date Posted</th>
					<th >Action</th>
				</tr>
			</thead>
			<tbody>
<?php $assignments = get_posts('&orderby=date&tag=course:' . $course_ID); ?>
<?php if($assignments) : ?>
<?php foreach($assignments as $assignment) : ?> 
				<tr>
          <th><a href="<?php echo site_url('assignment?id=') . $assignment->ID; ?>">
            <?php echo $assignment->post_title; ?></a>
          </th>
					<th><?php echo date('F d, Y', strtotime($assignment->post_date)); ?></th>
					<th><a href='<?php echo site_url('/manage-assignments/?id=' . $course_ID . '&del=' . $assignment->ID) ?>'>Delete</a></th></th>
				</tr>
<?php endforeach; ?>
<?php else : ?>
				<tr>
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
