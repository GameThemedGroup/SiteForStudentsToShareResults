<?php
/**
 * Template Name: Project Search
 * Description: Search for projects using specified criteria
 *
 * Author: Andrey Brushchenko
 * Date: 10/1/2013
 */
get_header(); ?>

<?php 
	$defaultposts = '5'; 		 // default number of projects per page
	$defaultorder = 'DES';   // default sort order
	$defaultfilter = 'date'; // default sorting method	

	// post offset used to determine what posts show up on what page
	if($_GET['x'] > -1)
		$offset = $_GET['x'];
	else
		$offset = 0;

	// makes current page bold in page bar
	if($_GET['x'] > -1) 		
		$currentpage = $_GET['x'];
	else 
		$currentpage = 0;

	// gets the whole url for proper redirection to results page
	if($_GET['category'])
		$category = "?category=" . $_GET['category'];
	else
		$category = "?category=" . $defaultfilter;
	if($_GET['order'])
		$order = "&order=" . $_GET['order'];
	else
		$order = "&order=" . $defaultorder ;
	if($_GET['results'])
		$results = "&results=" . $_GET['results'];
	else
		$results = "&results=" . $defaultposts ;

	// retrieve project posts
	if($_GET['results'] && $_GET['order'] && $_GET['category']) {
		$args = array(
			'posts_per_page' => $_GET['results'], 
			'offset' => $offset * $_GET['results'],
			'order' => $_GET['order'], 
			'orderby' => $_GET['category'],
			'post_status' => 'publish',
			'suppress_filters' => true );
	}
	else {
		$args = array(
			'posts_per_page' => 5, 
			'offset' => $offset * $defaultposts,
			'order' => $defaultorder, 
			'orderby' => $defaultfilter,
			'post_status' => 'publish',
			'suppress_filters' => true );
	}
	$postslist = get_posts($args); 

	// gets number of search result pages
	$count_posts = wp_count_posts()->publish;
	if($_GET['results'] > 0)
		$num_pages = $count_posts / $_GET['results'];
	else
		$num_pages = $count_posts / $defaultposts;
	$num_pages = ceil($num_pages);
?>

<html>
<div id="projectsearch">
	<div id="pagetitle">Projects</div>
	<?php foreach($postslist as $post): setup_postdata($post)?>
	<div id="projectsearchbox">
		<div id="projectsearchheader">
			<div id="projectsearchtitle">
				<a class="projectsearch" href="<?php the_permalink(); ?>">
					<?php the_title() ?>
				</a>
			</div>
			<div id="projectsearchauthor">
				<a class="projectsearch" href="<?php echo site_url('/profile/?user='); echo the_author_meta('ID') ?>">
					<?php the_author() ?> 
				</a> 
			</div>
		</div>
		<a id="projectsearchimage" href="<?php the_permalink(); ?>">
			<?php if(has_post_thumbnail()) {?>
				<?php the_post_thumbnail(array(140,140)); ?>
			<?php } else { ?> 
				<img src="<?php bloginfo('template_directory'); ?>/images/blank-project.png" width="140" height="140" />
			<?php } ?>
		</a>
		<div id="projectsearchmeta">
			<div id="rating" class="projectsearchmetatag"><?php if(function_exists('the_ratings')) { the_ratings(); } ?></div>
			<div class="projectsearchmetatag">
				<?php echo wp_count_comments(get_the_ID())->approved . " Comments"; ?>
			</div>
			<div class="projectsearchmetatag">
				University of Washington
			</div>	
			<div class="projectsearchmetatag">
				<?php the_time('m/d/y') ?>
			</div>
			<div id="projectsearchdescription">	
				<?php the_content() ?>
			</div>
		</div>
	</div>
	<?php endforeach; ?>
</div>

<div id="projectsearchfilter">
	<div id="pagetitle">Search</div>
	<form method="get">
		<input type="hidden" name="page_id" value=<?php echo $_GET['page_id'] ?>>
		<div class="filteroption">
			Filter
			<select class="filterselect" name="category">
  				<option value="date" <?php echo ($_GET['category'] == 'date')?"selected":""; ?>>Newest</option>
 				<option value="comment_count" <?php echo ($_GET['category'] == 'comment_count')?"selected":""; ?>>Most Comments</option>
  				<option value="rating" <?php echo ($_GET['category'] == 'rating')?"selected":""; ?>>Highest Rating</option>
			</select>
		</div>
		<div class="filteroption">
			Order
			<select class="filterselect" name="order">
 				<option value="DES" <?php echo ($_GET['order'] == 'DES')?"selected":""; ?>>Descending</option>
  				<option value="ASC" <?php echo ($_GET['order'] == 'ASC')?"selected":""; ?>>Ascending</option>
			</select>
		</div>
		<div class="filteroption">
			Results Per Page
			<select class="filterselect" name="results">
  				<option value=5 <?php echo ($_GET['results'] == '5')?"selected":""; ?>>5</option>
 				<option value=10 <?php echo ($_GET['results'] == '10')?"selected":""; ?>>10</option>
				<option value=20 <?php echo ($_GET['results'] == '20')?"selected":""; ?>>20</option>
			</select>
		</div>
		<input type="submit" value="Search">
	</form>
</div>

<div id="resultspages">
	Page
	<?php
	$count = 0;
	while($num_pages > $count) :
		if($count == $currentpage)
			echo "[";
		echo "<a href=\"" . site_url('/project-search/') . $category . $order . $results . '&x=' . $count . "\">" . ($count + 1) . "</a>";
		if($count == $currentpage)
			echo "]";
		$count++;
	endwhile;
	?>
</div>
<html>

<?php get_footer(); ?>
