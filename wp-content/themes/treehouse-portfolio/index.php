<?php get_header(); ?>

<section class="row">
      <div class="small-12 columns text-center">
        <div class="leader">
        
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
          <h1><?php the_title(); ?></h1>
          <p><?php the_content(); ?></p> 
          <h2>Related Products</h2>

            <?php
              $post_id = $post->ID; 
              $product_ids = get_post_meta($post_id, 'product_id', false );

              //error_log("product_ids array:");
              //error_log(print_r($product_ids,true));

              if ($product_ids) {

                $product_titles = get_post_meta($post_id, '_product_title', false );
                $product_image_urls = get_post_meta($post_id, '_product_image_url', false );
                $product_prices = get_post_meta($post_id, '_product_price', false );
                $product_descriptions = get_post_meta($post_id, '_product_description', false );

                $i = 0;
                foreach ($product_ids as $product_id) {
                  
                  if ($product_titles) {
                    echo '<p>' . $product_titles[$i] . ' </p>';  
                  }
                  if ($product_image_urls) {
                    echo '<img src="' . htmlspecialchars($product_image_urls[$i]) . '" alt="' . $product_titles[$i] . '"></img>'; 
                  }
                  if ($product_prices) {
                    echo '<p>' . $product_prices[$i] . ' </p>';  
                  }
                  if ($product_descriptions) {
                    echo '<p>' . $product_descriptions[$i] . ' </p>';  
                  }

                  $i++;
                }
              }
            ?>

        <?php endwhile; else : ?>
    		<p><?php _e( 'Sorry, no posts matched your criteria.' ); ?></p>
        <?php endif; ?>
        
        </div>
      </div>
</section>

<?php get_footer(); ?>

