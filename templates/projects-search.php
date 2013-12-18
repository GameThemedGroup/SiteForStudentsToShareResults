<?php
/**
 * Template Name: Project Search
 * Description: Search for projects using specified criteria
 *
 * Author: Andrey Brushchenko
 * Date: 12/14/2013
 */
get_header(); ?>

<?php include_once(get_template_directory() . '/logic/projects-search.php'); ?>

<html>
  <div id="projectsearchfilter">
    <div id="projectsearchtitle">Search</div>
    <form method="get">
      <div class="filteroption">
        Filter
        <select class="filterselect" name="category">
          <option value="date" <?php echo ($category == 'date')?"selected":""; ?>>Newest</option>
          <option value="comment_count" <?php echo ($category == 'comment_count')?"selected":""; ?>>Most Comments</option>
          <option value="rating" <?php echo ($category == 'rating')?"selected":""; ?>>Highest Rating</option>
        </select>
      </div>
      <div class="filteroption">
        Order
        <select class="filterselect" name="order">
          <option value="DES" <?php echo ($order == 'DES')?"selected":""; ?>>Descending</option>
          <option value="ASC" <?php echo ($order == 'ASC')?"selected":""; ?>>Ascending</option>
        </select>
      </div>
      <div class="filteroption">
        Results Per Page
        <select class="filterselect" name="results">
          <option value=10 <?php echo ($resultsPerPage == '10')?"selected":""; ?>>10</option>
          <option value=25 <?php echo ($resultsPerPage == '25')?"selected":""; ?>>25</option>
          <option value=50 <?php echo ($resultsPerPage == '50')?"selected":""; ?>>50</option>
        </select>
      </div>
      <div class="filteroption">
        Name
        <input class="filterselect" type="text" name="search" value=<?php echo $search ?>><br>
      </div>
      <div id="projectfilterbuttons">
        <input type="submit" value="Search">
      </div>
    </form>
  </div>

  <div id="project-search-whole">
    <div id="project-search-title">Projects</div>
<?php if($numPosts > 0) : ?>
    <div id="resultspages">
      Page
      <?php
      $count = 1;
      while($numPages + 1 > $count)
      {
        if($count == $currentPage)
        {
          echo "<u>" . $count . "</u>";
        }
        else
        {
          echo "<a href=\"" . site_url('/projects/?category=' . $category . '&order=' . $order .  '&results=' . $resultsPerPage . '&x=' . $count . '&search=' . $search) . "\">" . $count . "</a>";
        }
        $count++;
        echo ' ';
      }
      ?>
    </div>
<?php endif ?>

<?php foreach($postslist as $post): setup_postdata($post)?>
    <div id="project-box">
      <div id="project-header">
        <a class="project-search" href="<?php the_permalink(); ?>">
          <div id="project-title">
            <?php the_title() ?>
          </div>
        </a>
        <a class="project-search" href="<?php echo site_url('/profile/?user='); echo the_author_meta('ID') ?>">
          <div id="project-author">
            <?php the_author_meta('user_login') ?>
          </div>
        </a>
      </div>
      <a id="project-image" href="<?php the_permalink(); ?>">
        <?php if(has_post_thumbnail()) : ?>
          <?php the_post_thumbnail(array(140,140)); ?>
        <?php else : ?>
          <img src="<?php bloginfo('template_directory'); ?>/images/blank-project.png" width="144" height="144">
        <?php endif ?>
      </a>
      <div id="project-meta-box">
        <div class="project-metatag">
          Posted on <?php the_time('m/d/y') ?>
        </div>
        <div class="project-metatag">
          <?php echo wp_count_comments(get_the_ID())->approved . " Comments"; ?>
        </div>
        <div id="rating" class="project-metatag">
          <?php if(function_exists('the_ratings')) { the_ratings(); } ?>
        </div>
        <div id="project-description">
          <?php the_content('Read more...', true) ?>
        </div>
      </div>
    </div>
<?php endforeach; ?>

<?php if($numPages == 0) : ?>
    <div id="project-empty">There are no matching results</div>
<?php elseif($numPosts > 0) : ?>
    <div id="resultspages">
      Page
      <?php
      $count = 1;
      while($numPages + 1 > $count)
      {
        if($count == $currentPage)
        {
          echo "<u>" . $count . "</u>";
        }
        else
        {
          echo "<a href=\"" . site_url('/projects/?category=' . $category . '&order=' . $order .  '&results=' . $resultsPerPage . '&x=' . $count . '&search=' . $search) . "\">" . $count . "</a>";
        }
        $count++;
        echo ' ';
      }
      ?>
    </div>
<?php endif ?>
  </div>
<html>

<?php get_footer(); ?>
