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
	require('DBHandler.php');
    $dbHandler = new DBHandler;

	$coursenum = 4;

	$course = $dbHandler->GetCourse($coursenum);
	$professor = get_userdata($course->FacultyId);
	$professor_link = site_url('/profile/?user=') . $course->FacultyId;
	$students = $dbHandler->GetStudents(2);
	$assignments = $dbHandler->GetAllAssignments($coursenum);

	//print_r($assignments);
	//$dbHandler->CreateAssignment(4, 6, 'Assignment Three', 'this is a description');
	//$dbHandler->AddCourse("CSS 300", "Summer", 2013, 4);
	//$dbHandler->DeleteCourse(8)
	//$dbHandler->CreateAssignment(4, 4, "Assignment One", "this is a description this is a description this is a description");
?>

<html>
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

<div id="studentsidebar">
	<div id="header1">Students</div>
	<ul class='studentlist'>
		<?php foreach($students as $student)
			echo "<li><a href=\"" . site_url('/profile/?user=') . $student->Id . "\">" . $student->Name . '</a></li>';
		?>
	</ul>
</div><!-- studentsidebar -->

<div id="assignmentbox">
	<table class="assignment-table">
		<thead class="assignment-table">
			<tr>
				<th class="assignment-table">Assignment</th>
				<th class="assignment-table">Date Posted</th>
			</tr>
		</thead>
		<tbody class="assignment-table">
			<?php 
				if($assignments) {
					foreach($assignments as $assignment) {
						$assignlink = site_url('/assignment/?assign=') . $assignment->AssignmentId;
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
</div><!-- assignmentbox -->
</html>

<?php get_footer(); ?>