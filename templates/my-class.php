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
if(isset($_GET))
  $course_ID = $_GET['id'];
else
  $course_ID = 1;

	$course = $gtcs12_db->GetCourseByCourseID($course_ID);
	$professor = get_userdata($course->FacultyId);
	$professor_link = site_url('/profile/?user=') . $course->FacultyId;
	$assignments = $gtcs12_db->GetAllAssignments($course_ID);
	
	//$gtcs12_db->UpdateStudentEnrollment($course_ID, 3, true)
	//$dbHandler->UpdateStudentEnrollment(4, 7, false);
	//print_r($assignments);
	//$dbHandler->CreateAssignment(4, 6, 'Assignment Three', 'this is a description');
	//$dbHandler->AddCourse("CSS 300", "Summer", 2013, 4);
	//$dbHandler->DeleteCourse(8)
	//$dbHandler->CreateAssignment(4, 4, "Assignment One", "this is a description this is a description this is a description");
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
			<?php echo '<b>Email </b>' . $professor->user_email; ?>
		</div>
		<div id="quarter">
			<?php echo '<b>Quarter </b>' . $course->Quarter . ", " . $course->Year ?>
		</div>
		<div id="classdescription">
			<b>Description </b>
			description description description descri ption description description
			de scriptio n descri ption  scr n descri p tion  description descri ption descr ipt ion description 
			desc ription description description descript ion descriptio n description description descript ion 
			description description description descri ption description description
			de scriptio n descri ption  scr n descri p tion  description descri ption descr ipt ion description 
			desc ription description description descript ion descriptio n description description descript ion 
		</div>
	</div><!-- myclass -->

	<div id="options-box">
		<div id="options-title">Courses</div>
		<ul class="options">
<?php $courses = $gtcs12_db->GetAllCourses(); ?>
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

	<div id="options-box">
		<div id="options-title">Students</div>
		<ul class="options">
<?php $students = $gtcs12_db->GetStudents($course_ID); ?>
<?php if ($students) : ?>
<?php foreach($students as $student) : ?>
			<li class="options">
				<a href="<?php echo site_url('/profile/?user=' . $student->Id) ?>">
					<?php echo $student->Name ?>
				</a>
			</li>
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
