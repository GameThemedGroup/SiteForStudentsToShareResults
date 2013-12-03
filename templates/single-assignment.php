<?php
/**
 * Template Name: Single Assignment 
 * Description: Shows assignment details, allows professor to edit, allows student to submit assignments 
 *
 * Author: Rodelle Ladia Jr. 
 * Date: 27 October 2013 
 */

get_header(); ?>

<?php 
  $currentUser = wp_get_current_user();
  $isTeacher = gtcs_user_has_role('author'); 
  $isStudent = gtcs_user_has_role('subscriber'); 
    
  if($_GET["assignid"]) 
  {
    $assignmentId = $_GET["assignid"];
    $assignment = get_post($assignmentId);
    //$submissionCount = count($submissions);
  } 
  
  //$terms = wp_get_post_terms($assignmentId);
  //var_dump($terms);
  
  $courseId = 4; 
  $course = $gtcs12_db->GetCourseByCourseId(4);
  
  // check if logged in user is a teacher and owner of assignment
  if($isTeacher)
  {
    if($assignment->post_author == $currentUser->ID) 
    {
      $isOwner = true;
      $submissions = $gtcs12_db->GetAllSubmissions($assignmentId); 
    }
  }
  
  // check if logged in user is student and enrolled in this course
  if($isStudent)
  {
    $isEnrolled = true; // needs to be changed, only checks if user is student
    $submissions = $gtcs12_db->GetAllSubmissions($assignmentId); 
  }
  
  // toggle opening/closing assignment
  if($_GET['op'] == 'open')
    update_post_meta($assignmentId, 'isEnabled', 1, 0);
  else if($_GET['op'] == 'close')
    update_post_meta($assignmentId, 'isEnabled', 0, 1);
  
  $status = get_post_meta($assignmentId, 'isEnabled', true);
?>

<!DOCTYPE html>
<html lang="en">
  <div id="assignment-whole">
    <a class="link" href="<?php echo site_url('/my-class/?id=' . $courseId) ?>">Back to course</a>
<?php if($isOwner) : ?>
    <a class="link" href="<?php echo site_url('/manage-assignment/?op=edit&assignid=' . $assignmentId . '&courseid=' . $courseId) ?>">Edit this Assignment</a>
<?php endif ?>
    <div id="assignnment-title">
<?php if($assignment->post_title)
        echo $assignment->post_title; 
      else
        echo 'Empty';
?>
    </div>
    <div id="assignment-top">
      <img src="<?php bloginfo('template_directory'); ?>/images/blank project.png" width="155" height="155">
      <p class="assignment-meta"><b>Course </b><?php echo $course->Name ?></p>
      <p class="assignment-meta"><b>Created </b><?php echo date('F d, Y', strtotime($assignment->post_date)) ?></p>
      <p class="assignment-meta"><b>Status</b>
        
<?php if($status) : ?>
        Open to Submissions
<?php   if($isOwner) : ?>
        <a href="<?php echo site_url('/assignment/?assignid=' . $assignmentId . '&op=close') ?>">[Close]</a>
<?php   endif ?> 
<?php else : ?>
        Closed to Submissions
<?php   if($isOwner) : ?>
        <a href="<?php echo site_url('/assignment/?assignid=' . $assignmentId . '&op=open') ?>">[Open]</a>
<?php   endif ?> 
<?php endif ?> 
      </p>
      <div id="assignment-buttons">
        <div id="assignment-button">Source</div>
      </div>
    </div>
    <div id="assignment-description">
      <?php echo nl2br($assignment->post_content); ?>
    </div>
  </div>
  
  <div id='table'>	
    <div id='table-title'>
<?php 
  if($isOwner)
    echo 'Submissions';
  elseif($isEnrolled)
    echo 'My Submissions';
  else
    echo 'Empty';
?>
    </div>
    <table>
      <thead>
        <tr>
          <th>Title</th>
          <th>Author</th>
          <th>Date Submitted</th>
<?php if($isOwner) : ?>
          <th>Action</th>
<?php endif ?>          
        </tr>
      </thead>
      <tbody>
<?php if(count($submissions) == 0): ?>
        <tr>
          <th class="center" colspan="3">There are no submissions</th>
        </tr>
<?php else: ?>
<?php   foreach($submissions as $submission) : ?> 
        <tr>
          <th>
            <a href="<?php echo site_url('?p=') . $submission->SubmissionId ?>"><?php echo $submission->Title ?></a>
          </th>
          <th>
            <a href="<?php echo site_url('/profile/?user=') . $submission->AuthorId ?>"><?php echo $submission->AuthorName ?>
          </th>
          <th><?php echo date('n/j/y (g:i)', strtotime($submission->SubmissionDate)) ?></th>
<?php if($isOwner) : ?>
          <th>
            <form action="<?php echo site_url('/manage-assignments/') ?>" method="get">
              <select name="op">
                <option disabled="disabled" selected>Choose an action</option>
                <option value="edit">Edit</option>
                <option value="delete">Delete</option>
              </select>           
              <input type="hidden" name="assignid" value="<?php echo $submission->SubmissionId ?>">
              <input type="hidden" name="courseid" value="<?php echo $courseId ?>">
              <input type="submit" value="Confirm"/>  
            </form>
          </th>
<?php endif ?>
        </tr>
<?php   endforeach; ?>
<?php   if($isEnrolled && $status) : ?>
        <tr>
          <th class="action" colspan="3">
            <a class="action" href="<?php echo site_url('/manage-assignments/?courseid=' . $courseId) ?>">Submit Assignment</a>
          </th>
        </tr>
<?php   endif ?>
<?php endif; ?>
      </tbody>
    </table>
  </div>
</html>

<?php get_footer() ?>
