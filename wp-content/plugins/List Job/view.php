
<div class="wrap">
  <div class="icon32" id="icon-edit"><br></div>
  <h2><?php _e('List All Apply Job') ?></h2>
  <form method="post" action="?page=persons" id="bor_form_action">
   
    <table class="widefat page fixed" cellpadding="0" width="600">
      <thead>
          <th class="manage-column"><?php _e('User Name')?></th>
          <th class="manage-column"><?php _e('Email')?></th>
          <th class="manage-column"><?php _e('Job Title')?></th>
          <th class="manage-column"><?php _e('Date')?></th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <th class="manage-column"><?php _e('User Name')?></th>
          <th class="manage-column"><?php _e('Email')?></th>
          <th class="manage-column"><?php _e('Job Title')?></th>
          <th class="manage-column"><?php _e('Date')?></th>
        </tr>
      </tfoot>
      <tbody>
        <?php  
			$args = array( 'post_type' => 'applyjob', 'orderby'  => 'post_date', 'order' => 'DESC' );

			$row = query_posts( $args );
			if ( have_posts() ) :
			while ( have_posts() ) : the_post();    ?>
            
      <tr>

          <td><strong><?php the_author(); ?></b></strong></td>
           <td><?php the_author_email();?></td>
          <td><?php the_title();?></td>
          <td><?php echo get_the_date();?></td>
          
          <?php endwhile; endif; wp_reset_query();
		  
		  if(empty($row)){ 
		  
			  echo '<td colspan="4">No items found!</td>';
		} ?>
        
        </tr>
           
      </tbody>
    </table>
   

  </form>
</div>
