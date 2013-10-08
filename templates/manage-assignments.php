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
    require('DBHandler.php');
    $dbHandler = new DBHandler;
	
	//echo $_POST['title'] . ' ' . $_POST['description'] . ' ' . $_POST['sample-file'] . ' ' . $_POST['preview-image'];
	if ($_POST) 
	{
		$courseID = 2;
		$authorID = 1;

		// create assignment post
		$assignmentId = $dbHandler->CreateAssignment($authorId, $courseId, $_POST['title'], $_POST['description']);
	
		// prepare paths of images and jar files
		$imageFileName = $authorId . '_' . $courseId . '_' . $assignmentId . '.png'; // only .png supported now
		$jarFileName = $authorId . '_' . $courseId . '_' . $assignmentId . '.jar';

		// upload the files and get their destination paths
		$destFilePaths = $dbHandler->UploadFiles("filAssignment", array($imageFileName, $jarFileName));
	}
?>

<!DOCTYPE html>
<html lang="en">
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
				<input class='create-assignment' type="file" name="sample-file[]" required>
			</div>
			<input type="submit" value="Submit"/>
		</div>
		<div id='create-assignment-box-right'>
			<div id='create-assignment-field'>
				Preview Image
				<input class='create-assignment' type="file" name="preview-image[]" required>
				<img class='create-assignment' src="<?php bloginfo('template_directory'); ?>/images/blank-project.png">
			</div>
		</div>
	</form>
	
	<div id='table'>	
		<table class='manage-courses'>
		<div id='pagetitle'>Manage Assignments</div>
			<thead class='manage-courses'>
				<tr>
					<th class='manage-courses'>Title</th>
					<th class='manage-courses'>Date Posted</th>
					<th class='manage-courses'>Action</th>
				</tr>
			</thead>
			<tbody class='manage-courses'>
				<tr>
					<th class='manage-courses'>Lab 1: Draw A Circle</th>
					<th class='manage-courses'>September 23, 2013</th>
					<th class='manage-courses'>Delete</th>
				</tr>
			</tbody>
		</table>
	</div>
</html>

<?php get_footer() ?>