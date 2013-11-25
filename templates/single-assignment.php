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
  if($_GET) {
    $assignment_id = $_GET["id"];
  } 

  if($assignment_id) {
    $assignment = get_post($assignment_id);
  }


  if ($_POST) 
  {
    $student_id = get_current_user_id(); 
     
    $description = $_POST['description'];
    $title = $_POST['title'];
    $course_id = 0; // TODO why is the course id needed for submissions?

    $submission_id = $gtcs12_db->CreateSubmission($title, $student_id, $course_id, $assignment_id, $description);

    $gtcs12_db->AttachFileToPost($submission_id, 'jar', $title, 'jar', false); 

    if(isset($_FILES['image'])) {
      $gtcs12_db->AttachFileToPost($submission_id, 'image', $title, 'image', true); 
    } 
  }
  
  $submissions = $gtcs12_db->GetAllSubmissions($assignment_id); 
  $submissionCount = count($submissions);
?>

<?php $is_teacher = gtcs_user_has_role('author'); ?>
<?php $is_student = gtcs_user_has_role('subscriber'); ?>

<!DOCTYPE html>
<html lang="en">
  <div id="assignment-box">
    <div id="assignment-left">
      <img src="<?php bloginfo('template_directory'); ?>/images/blank-project.png" width="200" height="200">
      <div id="assignment-meta"><?php echo $submissionCount ?> Submissions</div>
      <div id="assignment-meta"><?php echo date('F d, Y', strtotime($assignment->post_date)) ?> </div>
      <div id="assignment-meta"><a>Source</a></div>
    </div>
    <div id="assignment-right">
      <div id="assignment-title"><?php echo $assignment->post_title; ?> </div>
      <?php echo $assignment->post_content; ?> 
    </div>
  
  </div>

  

  <div id='table'>	
    <div id='table-title'>Manage enrolled students</div>
    <table>
      <thead>
        <tr>
          <th>Title</th>
          <th>Author</th>
          <th>Date Posted</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
<?php if(count($submissions) == 0): ?>
        <tr>
          <th class="center" colspan="4">There are no submissions</th>
        </tr>
<?php else: ?>
  <?php foreach($submissions as $submission) : ?> 
        <tr>
          <th><?php echo $submission->Title; ?></th>
          <th><?php echo $submission->AuthorName; ?></th>
          <th><?php echo $submission->SubmissionDate; ?></th>
          <th>
            <a href="<?php echo site_url('?p=') . $submission->SubmissionId; ?>">view</a>
          </th>
        </tr>
  <?php endforeach; ?>
<?php endif; ?>
      </tbody>
    </table>
  </div>
</html>

<?php get_footer() ?>
