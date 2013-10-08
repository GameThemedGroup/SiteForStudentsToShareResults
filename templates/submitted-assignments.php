<?php
/**
 * Template Name: Submitted Assignments
 * Description: Shows information for an assignment and submissions.
 *
 * Author: Andrey Brushchenko
 * Date: 10/6/2013
 */
get_header(); ?>

<?php 
	require('DBHandler.php');
    $dbHandler = new DBHandler;

	//$dbHandler->CreateSubmission(5, 4, 320, "submission9");
	$user_info = get_userdata($_GET['user']); 	
	$submissions = $dbHandler->GetAllSubmissions($_GET['assign']);
 	$assignment = get_post($_GET['assign']);
?>

<html>
	<div id="assignment-info">
		<div id="pagetitle"><?php echo $assignment->post_title ?></div>
		<?php echo $assignment->post_content ?>	
	</div>
	<div id='assignment-submissions'>
		<div id='header1'>Submissions</div>
		<div id="submission-header">
			<div id='submission-name'><b>Submitted By</b></div>
			<div id='submission-date'><b>Submission Date</b></div>
		</div>
		<?php if($submissions) { 
				foreach($submissions as $submission) {
					$link = site_url('/?p=') . $submission->SubmissionId;
					echo "<div id='submission'>";
						echo "<div id='submission-name'><a href=" . $link . ">" . $submission->AuthorName . "</a></div>";
						echo "<div id='submission-date'>" . date('F d, Y', strtotime($submission->SubmissionDate)) . "</div>";
					echo "</div>";
				}
			}
			else {
				echo "<div id='submission'><div id='submission-name'>" . 'There are no submissions' . "</div></div>";

			}
		?>
	</div>
</html>

<?php get_footer(); ?>