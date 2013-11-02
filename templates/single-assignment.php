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

global $gtcs12_db;

if ($_POST) 
{
  $student_id = get_current_user_id(); 
   
  $description = $_POST['description'];
  $title = $_POST['title'];
  $course_id = 0; // TODO why is the course id needed for submissions?

  $submission_id = $gtcs12_db->CreateSubmission($student_id, $course_id, $assignment_id, $description);

  $gtcs12_db->AttachFileToPost($submission_id, 'jar', $title, 'jar', false); 

  if(isset($_FILES['image'])) {
    $gtcs12_db->AttachFileToPost($submission_id, 'image', $title, 'image', true); 
  } 
 
}
?>

<?php $is_teacher = gtcs_user_has_role('author'); ?>
<?php $is_student = gtcs_user_has_role('subscriber'); ?>

<!DOCTYPE html>
<html lang="en">
<!--
  <form action="" method="post">
    <div id='create-assignment-box-left'>
      <div id='pagetitle'>Assignment</div>
      <div id='create-assignment-field'>
        <?php if($is_teacher): ?><a href='#'>edit</a><?php endif; ?>
        <?php echo $assignment->post_title; ?> 
      </div>
      <div id='create-assignment-field' name='description'>
        <?php if($is_teacher): ?><a href='#'>edit</a><?php endif; ?>
        <?php echo $assignment->post_content; ?> 
      </div>
      <div id='create-assignment-field'>
        <?php if($is_teacher): ?><a href='#'>edit</a><?php endif; ?>
        Available Attachments <br />
        attachments go here
      </div>
    </div>
    <div id='create-assignment-box-right'>
      <div id='create-assignment-field'>
        <?php if($is_teacher): ?><a href='#'>edit</a> <br /><?php endif; ?>
        <input class='create-assignment' type="file" name="preview-image[]">
        <img class='create-assignment' src="<?php bloginfo('template_directory'); ?>/images/blank-project.png">
      </div>
    </div>
  </form>
-->
<?php if($is_student): ?>
  <form action="" method="post" enctype="multipart/form-data">
      Submit Assignment 
    <div>
      <label for="title">Title</label>
      <input type="text" name="title" id="title" required><br /> 

      <label for="desc">Description</label>
      <input type="text" name="description" id="desc" required><br /> 
   
      <label for="jar">Jar File</label>
      <input type="file" name="jar" id="jar" required><br />
  
      <label for="image">Image</label>
      <input type="file" name="image" id="image"><br />

      <input type="submit" name="submit-assignment" value="Submit Assignment"/> 
    </div>
  </form>
<?php endif; ?>

  <div id='table'>	
    <table class='manage-courses'>
    <div id='pagetitle'>Submitted Assignments</div>
      <thead class='manage-courses'>
        <tr>
          <th class='manage-courses'>Author</th>
          <th class='manage-courses'>Date Posted</th>
          <th class='manage-courses'>Action</th>
        </tr>
      </thead>
      <tbody class='manage-courses'>

<?php $submissions = $gtcs12_db->GetAllSubmissions($assignment_id); ?>
<?php if(count($submissions) == 0): ?>
        <tr>
          <th class='manage-courses' colspan=3>No Submissions</th>
        </tr>
<?php else: ?>
  <?php foreach($submissions as $submission) : ?> 
        <tr>
          <th class='manage-courses'><?php echo $submission->AuthorName; ?></th>
          <th class='manage-courses'><?php echo $submission->SubmissionDate; ?></th>
          <th class='manage-courses'>
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
