<?php
/*
Template Name: apply name
 */

get_header(); ?>

	<header class="page-header">
		<h1 class="page-title"><?php echo get_option( 'page_for_posts' ) ? get_the_title( get_option( 'page_for_posts' ) ) : _x( 'Blog', 'blog page title', 'jobify' ); ?></h1>
	</header>

	<div id="primary" class="content-area">
		<div id="content" class="site-content full" role="main">
            
			<div class="blog-archive">
                
			  <?php         
              
              $args = array(
                  'orderby'          => 'post_date',
                  'order'            => 'DESC',
                  'post_type'        => 'applyjob',
                  'post_status'      => 'publish',
                  'suppress_filters' => true );
              
              $myposts = get_posts( $args );
			 // print_r($myposts);?>
			  <table width="600" border="0" cellspacing="2" cellpadding="10">
                <tr>
                  <td scope="col">Name</td>
                  <td scope="col">Job Title</td>
                  <td scope="col">Date</td>
                </tr><?php 
			  	query_posts( $args );

				while ( have_posts() ) : the_post();?>
                 
                <tr>
                  <td><?php the_author() ?></td>
                  <td><?php the_title(); ?></td>
                  <td><?php the_date('Y-m-d'); ?></td>
                </tr>
              
              <?php endwhile; 
              wp_reset_query();?>
              </table>
              
			</div>
			
		</div><!-- #content -->

		<?php do_action( 'jobify_loop_after' ); ?>
	</div><!-- #primary -->

<?php get_footer(); ?>