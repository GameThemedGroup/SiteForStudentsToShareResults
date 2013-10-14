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
    
    require('./wp-blog-header.php');
    require('DBHandler.php');

    $dbHandler = new DBHandler;

    $operation = $_GET['op'];
    $results = '';

    if ($operation) {
        if ($operation == 'recreate') {
            $results = $dbHandler->RecreateTables();
        } else if ($operation == 'del') {
            $results = $dbHandler->DeleteTables();
        } else if ($operation == 'upload_test_data') {
            $results = upload_test_data();
        } else {
            $results = 'unknown operation ' . $operation;
        }
    }

function upload_test_data()
{
  $file = $_FILES;
  if($file["file"]["error"] > 0)
    return "error uploading: " . $file["file"]["error"];
  // *TODO* document and check errors from http://www.php.net/manual/en/features.file-upload.common-pitfalls.php

  $data = json_decode(file_get_contents($file["file"]["tmp_name"]));

  // weep because you're not using php >=5.3 and can't use json_last_error()
  if($data == NULL)
    return "error decoding json";

  global $gtcs12_db;  

  $professors = array(
    4 => array(
      'login' => 'ksung',
      'password' => 'password',
      'email' => 'ksung@mail.com',
      'firstname' => 'Kelvin',
      'lastname' => 'Sung',
      'role' => 'author',
    ),
    1 => array(
      'login' => 'czander',
      'password' => 'password',
      'email' => 'czander@mail.com',
      'firstname' => 'Carol',
      'lastname' => 'Zander',
      'role' => 'author',
    ),
    2 => array(
      'login' => 'tstewart',
      'password' => 'password',
      'email' => 'tstewart@mail.com',
      'firstname' => 'Timothy',
      'lastname' => 'Stewart',
      'role' => 'author',
    ),
    3 => array(
      'login' => 'mbernstein',
      'password' => 'password',
      'email' => 'mbernstein@mail.com',
      'firstname' => 'Morris',
      'lastname' => 'Bernstein',
      'role' => 'author',
    ),
  );

  $i = 0;

  foreach ($data->courses as $course) {
    $i ++;
    $professor = $professors[$i]; 

    $professor_id = $gtcs12_db->AddUser(
      $professor['login'], 
      $professor['password'], 
      $professor['email'], 
      $professor['firstname'], 
      $professor['lastname'], 
      $professor['role']
    );

    //*TODO* check for non-existent professor
    $professor = get_user_by('login', $course->professor);
    $professor_id = $professor->id;

    $course_id = $gtcs12_db->AddCourse(
      $course->title, 
      $course->quarter, 
      $course->year, 
      $professor_id
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
      $student_id = $gtcs12_db->AddUser(
        $student->login, 
        $student->password, 
        $student->email, 
        $student->firstname, 
        $student->lastname, 
        $student->role
      );
    }
  }
  return "Data Successfully Added";
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
                <td>Upload Test Data</td>
                <td>
                    <form action="?op=upload_test_data" method="post" enctype="multipart/form-data">
                    <label for="file">Test Data</label>
                    <input type="file" name="file" id="file">
                    <input type="submit" name="submit" value="Create">
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
