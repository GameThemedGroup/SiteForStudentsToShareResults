<?php
/*
 * Template Name: Manage Enrollments
 */

get_header(); ?>

<?php
	global $gtcs12_db;

  $courseid = $_GET['courseid'];
	
	//$gtcs12_db->UpdateStudentEnrollment(29, 3, FALSE);
    
  if ($_GET)
  {
    if ($_GET['courseid'])
    {
      $courseid = $_GET['courseid'];

      if ($_GET['studentid'] && $_GET['op'])
      {
        $studentid = $_GET['studentid'];
        $op = $_GET['op'];

        if ($op == 'enroll')
        {
          $gtcs12_db->UpdateStudentEnrollment($courseid, $studentid, TRUE);
        }
        else if ($op == 'cancel')
        {
          $gtcs12_db->UpdateStudentEnrollment($courseid, $studentid, FALSE);
        }
      }
    }
  }
  else if ($_POST)
  {
    $courseid = $_POST['courseid'];

    $gtcs12_db->EnrollStudentsViaFile($courseid, "filStudents");
  }
?>

<!DOCTYPE html>
<html lang="en">	
	<div id='table'>
    <div id='pagetitle'>Manage Students</div>
    <table>
      <thead>
        <tr>
          <th>Student</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
<?php $students = $gtcs12_db->GetStudents($courseid); ?>
<?php if($students) : ?>
<?php foreach($students as $student) : ?> 
        <tr>
					<th><?php echo $student->Name; ?></th>
<?php if ($student->StudentId == NULL) : ?>
          <th><a href='<?php echo site_url('manage-students/?courseid=' . $courseid . '&studentid=' . $student->Id . '&op=enroll') ?>'>Enroll</a></th>
<?php else : ?>
					<th><a href='<?php echo site_url('manage-students/?courseid=' . $courseid . '&studentid=' . $student->Id . '&op=cancel') ?>'>Cancel</a></th>
<?php endif ?>
        </tr>
<?php endforeach; ?>
<?php else : ?>
				<tr>
					<th>N/A</th>
          <th>N/A</th>
        </tr>
<?php endif ?>
      </tbody>
    </table>
  </div>
	
	<div id="options-box">
		<div id="options-title">Courses</div>
		<ul class="options">
<?php $courses = $gtcs12_db->GetAllCourses(); ?>
<?php foreach($courses as $course) : ?>
<?php if($courseid == $course->Id) : ?>
			<li class="options-selected">
				<?php echo '[' . $course->Quarter . ']' ?>
				<?php echo $course->Name ?>
			</li>
<?php else : ?> 
			<li class="options">
				<a href="<?php echo site_url('/manage-students/?courseid=' . $course->Id) ?>">
					<?php echo '[' . $course->Quarter . ']' ?>
					<?php echo $course->Name ?>
				</a>
			</li>
<?php endif ?>
<?php endforeach ?>
		</ul>
	</div> 
	
        <br/>
        <h2>Enroll via file upload</h2>
        <form action="manageenrollments.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="courseid" value="<?php echo $courseid ?>">
            <input type="file" name="filStudents">
            <input type="submit"><input type="reset">
        </form>
    </body>
</html>