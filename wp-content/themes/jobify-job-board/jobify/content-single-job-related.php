<?php
/**
 * Related jobs
 */

global $post;

$tags = get_the_terms( $post->ID, 'job_listing_category' );

if ( ! $tags || is_wp_error( $tags ) || ! is_array( $tags ) )
	return;

$tags = array_keys( $tags );

$related_args = array(
	'post_type' => 'job_listing',
	'orderby'   => 'rand',
	'posts_per_page' => 3,
	'post_status' => 'publish',
	'post__not_in' => array( $post->ID ),
	'tax_query' => array(
		array(
			'taxonomy' => 'job_listing_category',
			'field'    => 'id',
			'terms'    => $tags
		)
	)
);

$related = new WP_Query( apply_filters( 'jobify_related_job_args', $related_args ) );

if ( ! $related->have_posts() )
	return;
?>

<h2><?php _e( 'Related Jobs', 'jobify' ); ?></h2>

<ul class="job_listings related">

	<?php while ( $related->have_posts() ) : $related->the_post(); ?>

		<?php get_job_manager_template_part( 'content', 'job_listing' ); ?>

	<?php endwhile; ?>

</ul>

<?php wp_reset_query(); ?>
