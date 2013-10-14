<?php

// On theme initialization
// Creates the database tables
// *TODO* check if they already exist
// *TODO* add versioning system for upgrades
add_action('after_switch_theme', 'initialize_tables');
function initialize_tables()
{
  global $gtcs12_db;

  $gtcs12_db->RecreateTables();
}


add_action('after_switch_theme', 'create_default_pages');
function create_default_pages()
{


  $default_pages = array(
    'main'    =>  array(
      'post_name'     =>  'main',
      'post_title'    =>  'Main',
      'post_status'   =>  'publish',
      'post_type'     =>  'page',
      'meta'          =>  array(
        '_wp_page_template'  => 'templates/main.php',
      ),
    ),
    'debug'    =>  array(
      'post_name'     =>  'debug',
      'post_title'    =>  'Debug',
      'post_status'   =>  'publish',
      'post_type'     =>  'page',
      'meta'          =>  array(
        '_wp_page_template'  => 'templates/debug.php',
      ),
    ),
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
        '_wp_page_template'  => 'templates/projects-search.php',
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
    'manageassignments' =>  array(
      'post_name'     =>  'manage-assignments',
      'post_title'    =>  'Manage Assignments',
      'post_status'   =>  'publish',
      'post_type'     =>  'page',
      'meta'          =>  array(
        '_wp_page_template'  => 'templates/manage-assignments.php',
      ),
    ),
    'managestudents'   =>  array(
      'post_name'     =>  'manage-students',
      'post_title'    =>  'Manage Students',
      'post_status'   =>  'publish',
      'post_type'     =>  'page',
      'meta'          =>  array(
        '_wp_page_template'  => 'templates/manage-enrollments.php',
      ),
    ),
    'assignment'      =>  array(
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

add_action('after_switch_theme', 'create_default_menus');
function create_default_menus()
{
  $page_id = array();
  $page_id['main'] = get_page_by_title('Main')->ID;
  $page_id['myclass'] = get_page_by_title('My Class')->ID;
  $page_id['projects'] = get_page_by_title('Projects')->ID;

  $default_menus = array(
    'Main Menu' =>  array(
      'location' => 'primary',
      'menu-items' =>  array(
        'Home' =>  array(
          'menu-item-type'      =>  'post_type',
          'menu-item-title'     =>  'Home',
          'menu-item-object'    =>  'page',
          'menu-item-object-id' =>  $page_id['main'],
          'menu-item-status'    =>  'publish'
        ),
        'My Class' =>  array(
          'menu-item-type'      =>  'post_type',
          'menu-item-title'     =>  'Members',
          'menu-item-object'    =>  'page',
          'menu-item-object-id' =>  $page_id['myclass'],
          'menu-item-status'    =>  'publish'
        ),
        'Projects' =>  array(
          'menu-item-type'      =>  'post_type',
          'menu-item-title'     =>  'Projects',
          'menu-item-object'    =>  'page',
          'menu-item-object-id' =>  $page_id['projects'],
          'menu-item-status'    =>  'publish'
        ),
      )
    )
  );

  // Creates the Default Menus
  $menu_locations = array();
  foreach($default_menus as $menu_name => $menu_args) {
    $menu_exists = wp_get_nav_menu_object($menu_name);
    
    if(!$menu_exists) {
      $menu_id = wp_create_nav_menu($menu_name);
      foreach( $menu_args['menu-items'] as $menu_item ) {
        // assigns the page, title, etc to the menu item
        wp_update_nav_menu_item( $menu_id, 0, $menu_item );
      }
      $menu_locations[$menu_args['location']] = $menu_id;
    }
  }

  if (isset($menu_locations)) {
    // Assigns the menu to the correct menu location (Primary Menu, Footer Menu, etc.)
    set_theme_mod('nav_menu_locations', array_map('absint', $menu_locations));
  }
}

// Changes the permalink settings to /%postname%/
add_action('after_switch_theme', 'update_permalinks');
function update_permalinks()
{
  if (get_option('permalink_structure') != '/%postname%/') {
    global $wp_rewrite;
    $wp_rewrite->set_permalink_structure('/%postname%/');
  }
}
?>
