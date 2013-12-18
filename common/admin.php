<?php
class GTCS_Admin
{
  public static function DeleteTables()
  {
    global $wpdb;
    $wpdb->show_errors(true);

    // delete child table first
    $tablename = $wpdb->prefix . "enrollments";
    $sql = "DROP TABLE IF EXISTS $tablename;";
    $wpdb->query($sql);

    // delete parent table later
    $tablename = $wpdb->prefix . "courses";
    $sql = "DROP TABLE IF EXISTS $tablename;";
    $wpdb->query($sql);

    return "Tables deleted";
  }

  public static function CreateTables()
  {
    global $wpdb;
    $wpdb->show_errors(true);

    $usersTableName = $wpdb->prefix . "users";

    // create parent table first
    $coursesTableName = $wpdb->prefix . "courses";
    $sql = "CREATE TABLE $coursesTableName (
      id bigint(20) NOT NULL AUTO_INCREMENT,
      name varchar(40) NOT NULL,
      description longtext NOT NULL DEFAULT '',
      quarter varchar(20) NOT NULL,
      year smallint unsigned NOT NULL,
      facultyid bigint(20) unsigned,
      PRIMARY KEY id (id),
      FOREIGN KEY (facultyid) REFERENCES $usersTableName (id)
    );";
    $wpdb->query($sql);

    // create child tables later
    $enrollmentsTableName = $wpdb->prefix . "enrollments";
    $sql = "CREATE TABLE $enrollmentsTableName (
      id bigint(20) NOT NULL AUTO_INCREMENT,
      courseid bigint(20),
      studentid bigint(20) unsigned,
      PRIMARY KEY id (id),
      FOREIGN KEY (courseid) REFERENCES $coursesTableName (id),
      FOREIGN KEY (studentid) REFERENCES $usersTableName (id)
    );";
    $wpdb->query($sql);
  }

  public static function RecreateTables()
  {
    self::DeleteTables();
    self::CreateTables();

    return "Tables recreated<br/>";
  }
}

?>
