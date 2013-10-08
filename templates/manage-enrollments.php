<?php
/**
 * Template Name: Manage Enrollments
 */

get_header(); ?>

<?php
    require('./wp-blog-header.php');
    require('DBHandler.php');

    $dbHandler = new DBHandler;

    $courseid = '';
    
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
                    $dbHandler->UpdateStudentEnrollment($courseid, $studentid, TRUE);
                }
                else if ($op == 'cancel')
                {
                    $dbHandler->UpdateStudentEnrollment($courseid, $studentid, FALSE);
                }
            }
        }
    }
    else if ($_POST)
    {
        $courseid = $_POST['courseid'];

        $dbHandler->EnrollStudentsViaFile($courseid, "filStudents");
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title></title>
    </head>
    <body>
        <h2>Manage Enrollments</h2>
        <table border="1">
            <tr>
                <th>Student Name</th>
                <th>Enroll / Cancel</th>
            </tr>
            <?php
                $rows = $dbHandler->GetStudents(6);

                if ($rows)
                {
                    foreach($rows as $row)
                    {
                        echo "<tr>";
                        echo "<td>" . $row->Name . "</td>";

                        if ($row->StudentId == NULL)
                        {
                            echo "<td><a href='manageenrollments.php?courseid=" . $courseid . "&studentid=" . $row->Id . "&op=enroll'>Enroll</a></td>";
                        }
                        else
                        {
                            echo "<td><a href='manageenrollments.php?courseid=" . $courseid . "&studentid=" . $row->Id . "&op=cancel'>Cancel</a></td>";
                        }
                    }
                }
            ?>
        </table>
        <br/>
        <h2>Enroll via file upload</h2>
        <form action="manageenrollments.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="courseid" value="<?php echo $courseid ?>">
            <input type="file" name="filStudents">
            <input type="submit"><input type="reset">
        </form>
    </body>
</html>