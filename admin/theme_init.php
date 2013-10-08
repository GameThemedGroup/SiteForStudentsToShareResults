<?php

add_action('init', 'create_default_pages', 9999);
function create_default_pages()
{
  $default_pages = array(
    'myclass'    =>  array(
      'post_name'     =>  'my-class',
      'post_title'    =>  'My Class',
      'post_status'   =>  'publish',
      'post_type'     =>  'page',
      'meta'          =>  array(
        '_wp_page_template'  => 'templates/my-class.php',
      ),
    ),
    'projects'         =>  array(
      'post_name'     =>  'projects',
      'post_title'    =>  'Projects',
      'post_status'   =>  'publish',
      'post_type'     =>  'page',
      'meta'          =>  array(
        '_wp_page_template'  => 'templates/project-search.php',
      ),
    ),
    'profile'         =>  array(
      'post_name'     =>  'profile',
      'post_title'    =>  'Profile',
      'post_status'   =>  'publish',
      'post_type'     =>  'page',
      'meta'          =>  array(
        '_wp_page_template'  => 'templates/profile.php',
      ),
    ),   
    'managecourses'         =>  array(
      'post_name'     =>  'manage-courses',
      'post_title'    =>  'Manage Courses',
      'post_status'   =>  'publish',
      'post_type'     =>  'page',
      'meta'          =>  array(
        '_wp_page_template'  => 'templates/manage-courses.php',
      ),
    ), 
    'manageassignments'         =>  array(
      'post_name'     =>  'manage-assignments',
      'post_title'    =>  'Manage Assignments',
      'post_status'   =>  'publish',
      'post_type'     =>  'page',
      'meta'          =>  array(
        '_wp_page_template'  => 'templates/manage-assignments.php',
      ),
    ),
    'managestuents'         =>  array(
      'post_name'     =>  'manage-students',
      'post_title'    =>  'Manage Students',
      'post_status'   =>  'publish',
      'post_type'     =>  'page',
      'meta'          =>  array(
        '_wp_page_template'  => 'templates/manage-students.php',
      ),
    ),
    'assignment'         =>  array(
      'post_name'     =>  'assignment',
      'post_title'    =>  'Assignment',
      'post_status'   =>  'publish',
      'post_type'     =>  'page',
      'meta'          =>  array(
        '_wp_page_template'  => 'templates/assignment.php',
      ),
    ),
  );

  foreach( $default_pages as $key => $page ) {
    if(get_page_by_title($page['post_title']) != null) // do not need to recreate page
      continue;

    if ( $id = wp_insert_post( $page ) ) {
      if ( !empty( $page['meta'] ) ) {
        foreach( $page['meta'] as $meta_key => $meta_value ) {
          update_post_meta( $id, $meta_key, $meta_value );
        }
      }
    }
  }
}
?>
