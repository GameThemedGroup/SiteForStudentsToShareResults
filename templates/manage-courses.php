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
    require('DBHandler.php');
    $dbHandler = new DBHandler;
	
	if ($_POST)
	{
		//echo $_POST['title'];
		//echo $_POST['quarter'];
		//echo $_POST['year'];
		//echo $_POST['faculty'];
		//echo $_POST['description'];
		$dbHandler->AddCourse($_POST['title'], $_POST['quarter'], $_POST['year'], $_POST['faculty']);
	}
	
	$rows = $dbHandler->GetAllCourses();
	$professors = $dbHandler->GetAllFaculty();  
?>

<!DOCTYPE html>
<html lang="en">
	<form action="" method="post">
		<div id='create-course-box'>
			<div id='pagetitle'>Create Course</div>		
			<div id='create-course-field'>Title
				<input class='create-course' type="text" name="title" required><br>
			</div>
			<div id='create-course-field'>
				Quarter 
				<select class='create-course' name='quarter'>
					<option value="Autumn">Autumn</option>
					<option value="Winter">Winter</option>
					<option value="Spring">Spring</option>
					<option value="Summer">Summer</option>				
				</select>
			</div>
			<div id='create-course-field'>
				Year 
				<select class='create-course' name='year'>
					<?php for($x = 0; $x < 10; $x++)
					{
						echo "<option>" . (date("Y") - $x) . "</option>";
					}
					?>			
				</select>
			</div>
			<div id='create-course-field'>
				Faculty 
				<select class='create-course' name='faculty'>
					<?php foreach($professors as $professor): ?>
						<option value="<?php echo $professor->Name ?>"><?php echo $professor->Name ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div id='create-course-field' name='description'>
				Description
				<textarea cols="25" rows="10" autocomplete="off" name="description" required></textarea>
			</div>
			<input type="submit" value="Submit"/>	
		</div>
	</form>
	
	<div id='table'>
		<div id='pagetitle'>Manage Courses</div>
		<table class='manage-courses'>
			<thead class='manage-courses'>
				<tr>
					<th class='manage-courses'>Title</th>
					<th class='manage-courses'>Quarter</th>
					<th class='manage-courses'>Year</th>
					<th class='manage-courses'>Faculty</th>
					<th class='manage-courses'>Action</th>
				</tr>
			</thead>
			<tbody class='manage-courses'>
				<tr>
					<th class='manage-courses'>CSS 161: Fundamentals of Computing</th>
					<th class='manage-courses'>Summer</th>
					<th class='manage-courses'>2013</th>
					<th class='manage-courses'>Kelvin Sung</th>
					<th class='manage-courses'>Delete</th>
				</tr>
			</tbody>
		</table>
	</div>
</html>

<?php get_footer() ?>