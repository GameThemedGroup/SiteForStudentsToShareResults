<?php
/**
 * Template Name: Debug 
 * Description: 
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
get_header() ?>

<?php
    global $gtcs12_db;

    $operation = $_GET['op'];
    $results = '';

    if ($operation) {
        if ($operation == 'recreate') {
            $results = $gtcs12_db->RecreateTables();
        } else if ($operation == 'del') {
            $results = $gtcs12_db->DeleteTables();
        } else if ($operation == 'upload_courses') {
            $results = upload_courses();
        } else if ($operation == 'upload_users') {
            $results = upload_users();
        } else {
            $results = 'unknown operation ' . $operation;
        }
    }


function add_user_from_data($user)
{
  global $gtcs12_db;
  $user_id = $gtcs12_db->AddUser(
    $user->login, 
    $user->password, 
    $user->email, 
    $user->firstname, 
    $user->lastname, 
    $user->role
  );
  return $user_id;
}

function upload_users()
{
  $file = $_FILES;
  // *TODO* document and check errors from 
  // http://www.php.net/manual/en/features.file-upload.common-pitfalls.php
  if($file["file"]["error"] > 0)
    return "error uploading: " . $file["file"]["error"];

  $data = json_decode(file_get_contents($file["file"]["tmp_name"]));
  foreach ($data->users as $user) {
    add_user_from_data($user);
  }

  return "Users created.";
}

function upload_courses()
{
  $file = $_FILES;
  // *TODO* document and check errors from 
  // http://www.php.net/manual/en/features.file-upload.common-pitfalls.php
  if($file["file"]["error"] > 0)
    return "error uploading: " . $file["file"]["error"];

  $data = json_decode(file_get_contents($file["file"]["tmp_name"]));

  // weep because you're not using php >=5.3 and can't use json_last_error()
  if($data == NULL)
    return "error decoding json";

  global $gtcs12_db;  

  foreach ($data->courses as $course) {
    //*TODO* check for non-existent professor
    $professor = get_user_by('login', $course->professor);
    $professor_id = $professor->id;

    $course_id = $gtcs12_db->AddCourse(
      $course->title, 
      $course->quarter, 
      $course->year, 
      $professor_id,
      $course->description 
    );

    foreach ($course->assignments as $assignment) {
      $assignment_id = $gtcs12_db->CreateAssignment(
        $professor_id, 
        $course_id, 
        $assignment->title,
        $assignment->description
      );
    }

    foreach ($course->students as $student) {
      $student_id = add_user_from_data($student);
      $gtcs12_db->UpdateStudentEnrollment($course_id, $student_id, true);
    }
  }
  return "Course Successfully Added";
}
?>
<html>
    <head></head>
    <body>
        <table>
            <tr>
                <th>Database operation</th>
                <th>Operation link</th>
            </tr>
            <tr>
                <td>Upload Users</td>
                <td>
                    <form action="?op=upload_users" method="post" enctype="multipart/form-data">
                    <label for="file">User Data</label>
                    <input type="file" name="file" id="file">
                    <input type="submit" name="upload-users" value="Upload">
                    </form>
                </td>
            </tr>
            <tr>
                <td>Upload Courses</td>
                <td>
                    <form action="?op=upload_courses" method="post" enctype="multipart/form-data">
                    <label for="file">Course Data</label>
                    <input type="file" name="file" id="file">
                    <input type="submit" name="upload-courses" value="Upload">
                    </form>
                </td>
            </tr>
                        <tr>
                <td>Recreate Tables</td>
                <td><a href="<?php echo site_url('/debug/'); ?>?op=recreate">Recreate Tables</a></td>
            </tr>
            <tr>
                <td>Delete Tables</td>
                <td><a href="<?php echo site_url('/debug/'); ?>?op=del">Delete Tables</a></td>
            </tr>
            <tr>
                <td>Status</td>
                <td>
                    <?php
                        print_r($results);
                    ?>
                </td>
            </tr>
        </table>
    </body>
</html>

<?php get_footer() ?>
