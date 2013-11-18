<?php
/*
 * Template Name: Manage Students 
 * Description: Allows for the creation/deletion of students
 *
 * Author: Andrey Brushchenko
 * Date: 11/18/2013
 */
 
get_header(); ?>
 
<?php
  $current_user = wp_get_current_user();
  $courses = $gtcs12_db->GetCourseByFacultyId($current_user->ID); 
   
  if($_GET['id'] != null)
    {
        $courseId = $_GET['id'];
    }
    else
    {
        $courseId = $courses[0]->Id;
    }
     
  if ($_GET)
  {
    if ($_GET['courseid'])
    {
 
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
    $gtcs12_db->EnrollStudentsViaFile($courseid, "filStudents");
  }
?>
 
<!DOCTYPE html>
<html lang="en">  
  <div id="create-student-box-top">
    <div id='pagetitle'>Create Student</div>
    <div id="create-student-field">               
      <p class="create-student">Username</p>
      <input class='create-student' type="text" name="inptTitle" value="<?php echo $assignment->post_title ?>" required>
    </div>
    <div id="create-student-field">   
      <p class="create-student">First Name</p>
      <input class='create-student' type="text" name="inptTitle" value="<?php echo $assignment->post_title ?>" required>
    </div>
    <div id="create-student-field">   
      <p class="create-student">Last Name</p>
      <input class='create-student' type="text" name="inptTitle" value="<?php echo $assignment->post_title ?>" required>
    </div>  
    <div id="create-student-field">   
      <p class="create-student">Email</p>
      <input class='create-student' type="text" name="inptTitle" value="<?php echo $assignment->post_title ?>" required>
    </div>        
    <input type="submit" value="Create"/> 
    <a href="<?php echo site_url('/my-class/') ?>"><button type="button">Cancel</button></a>
  </div>
 
  <div id="sidebar-menu">
        <div id="sidebar-menu-title">Courses</div>
      <ul class="sidebar-menu">
<?php if($courses) : ?>
<?php   foreach($courses as $course) : ?>
<?php     if($courseId == $course->Id) : ?>
            <li class="sidebar-menu-selected">
              <?php echo $course->Name ?>
              <?php echo '[' . $course->Quarter . ' ' . $course->Year . ']'?>
            </li>
<?php     else : ?> 
            <li class="sidebar-menu">
              <a href="<?php echo site_url('/manage-assignments/?courseid=' . $course->Id) ?>">
                <?php echo $course->Name ?>
                <?php echo '[' . $course->Quarter . ' ' . $course->Year . ']' ?>
              </a>
            </li>
<?php     endif ?>
<?php   endforeach ?>
<?php else : ?>
        <li class="sidebar-menu-center">You have no courses</li>
<?php endif ?>
        </ul>
    </div>
   
  <div id="create-student-box-bottom">
    <div id="create-student-field">   
    Create using file
    <form action="manageenrollments.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="courseid" value="<?php echo $courseid ?>">
        <input type="file" name="filStudents">
        <input type="submit"><input type="reset">
    </form>
    </div>
  </div>
   
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
</html>
 
<?php get_footer() ?>
