<?php
class GTCS_Courses
{
  /////////////////////////////////////////////////////////////////////////////
  /// Returns a list of courses that match the given criteria
  ///
  /// @param args - an object containing at least one of the following fields
  ///   courseId
  ///   studentId
  ///   professorId
  ///
  /// @return an array containing the course information
  ///   Name        - the name of the course
  ///   Quarter     - the academic quarter the course took place
  ///   Year        - the year the course took place
  ///   FacultyId   - the Id of the professor who owns the course
  ///   Description - text description of the course
  /////////////////////////////////////////////////////////////////////////////
  public static function getCourseList($args)
  {
    $courseId = ifsetor($args->courseId, null);
    $studentId = ifsetor($args->studentId, null);
    $professorId = ifsetor($args->professorId, null);

    $results = array();

    if ($courseId != null)
      $results = array_merge($result, GTCS_Courses::getCourseByCourseId($courseId));
    if ($studentId != null)
      $results = array_merge($result, GTCS_Courses::getCourseByStudentId($studentId));
    if ($professoId != null)
      $results = array_merge($result, GTCS_Courses::getCourseByProfessorId($professorId));
  }

  public static function getCourseByCourseId($courseId)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $tablename = $wpdb->prefix . "courses";

    $rows = $wpdb->get_row("SELECT c.name as Name, c.quarter as Quarter, c.year as Year, c.facultyid as FacultyId, c.description as Description
      FROM $tablename as c WHERE c.id = '$courseId'");

    return $rows;
  }

  public static function getCourseByStudentId($studentId)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $courses = $wpdb->prefix . "courses";
    $enrollments = $wpdb->prefix . "enrollments";

    $rows ="SELECT c.id as Id, c.name as Name, c.quarter as Quarter, c.facultyid as FacultyId, c.year as Year FROM $courses as c INNER JOIN $enrollments as e ON c.id = e.courseid WHERE e.studentid = $studentId;";
    $result = $wpdb->get_results($rows);

    return $result;
  }

  public static function getCourseByFacultyId($facultyId)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $courses = $wpdb->prefix . "courses";

    $rows = "SELECT c.id as Id, c.name as Name, c.quarter as Quarter, c.year as Year FROM $courses as c WHERE c.facultyid = $facultyId;";
    $result = $wpdb->get_results($rows);

    return $result;
  }

  /////////////////////////////////////////////////////////////////////////////
  /// Adds a course to the available list of courses owned by the professor
  ///
  /// @param args - an object containing the following fields
  ///    title       - the title of the course
  ///    quarter     - the academic quarter the course takes place {Fall,
  ///                  Summer, Spring, Winter}
  ///    year        - (optional) the academic year of the course takes place.
  ///                  Defaults to current year.
  ///    professorId - (optional) the id of the professor the course belongs
  ///                  to. Defaults to id of logged in user.
  ///    description - (optional) text description of the course.
  ///                  Defaults to ""
  ///
  /// @return the rowId of the inserted course, -1 on error
  /////////////////////////////////////////////////////////////////////////////
  public static function addCourse($args)
  {
    if (!gtcs_user_has_role('author')) {
      trigger_error(__FUNCTION__ . " - User does not have permission to
        perform this action.", E_USER_WARNING);
      return -1;
    }

    $isValid = GTCS_Courses::validateParameters($args);
    if (!$isValid)
      return -1;

    global $wpdb;
    $wpdb->show_errors(true);

    $tablename = $wpdb->prefix . "courses";

    $wpdb->insert($tablename,
      array (
        'name'        => $args->title,
        'quarter'     => $args->quarter,
        'year'        => $args->year,
        'facultyid'   => $args->professorId,
        'description' => $args->description,
      ));

    return $wpdb->insert_id;
  }

  private static function validateParameters(&$args)
  {
    $title = ifsetor($args->title, null);
    $quarter = ifsetor($args->quarter, null);

    if ($title === null || $quarter === null) {
      if ($title === null)
        trigger_error(__FUNCTION__ . " - Title not provided.", E_USER_WARNING);
      if ($quarter === null)
        trigger_error(__FUNCTION__ . " - Quarter not provided.", E_USER_WARNING);

      return false;
    }

    $validQuarters = array("Winter", "Spring", "Summer", "Fall");
    if (!in_array($quarter, $validQuarters)) {
      trigger_error(__FUNCTION__ . " - Invalid quarter provided.", E_USER_WARNING);
      return false;
    }

    $year = ifsetor($args->title, date("Y"));
    $decription = ifsetor($args->title, "");

    $professorId = ifsetor($args->title, null);
    if ($professorId === null)
      $professorId = wp_get_current_user()->ID;

    return true;
  }

  public static function deleteCourse($courseId)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $tablename = $wpdb->prefix . "courses";

    $wpdb->query("DELETE FROM $tablename WHERE id = $courseId");
  }

  public static function updateCourse(
    $courseId, $courseName, $quarter, $year, $facultyId, $description)
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $tablename = $wpdb->prefix . "courses";

    $wpdb->update(
      $tablename,
      array (
        'name' => $courseName,
        'quarter' => $quarter,
        'year' => $year,
        'facultyid' => $facultyId,
        'description' => $description),
      array( 'Id' => $courseId )
    );
  }

}
?>
