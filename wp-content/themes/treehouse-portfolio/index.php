<?php get_header(); ?>

<section class="row">
      <div class="small-12 columns text-center">
        <div class="leader">
        
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
          <h1><?php the_title(); ?></h1>
          <p><?php the_content(); ?></p> 
          <h2>Related Products</h2>
          <p><?php the_field('product_title'); ?></p>
          <?php 
            $product_image_url = get_field('product_image_url');
            echo '<img src="' . htmlspecialchars($product_image_url) . '" alt="Product image"></img>';
          ?>
          <p><?php the_field('product_price'); ?></p>
          <p><?php the_field('product_description'); ?></p>

        <?php endwhile; else : ?>
    		<p><?php _e( 'Sorry, no posts matched your criteria.' ); ?></p>
        <?php endif; ?>
        
        </div>
      </div>
</section>

<?php get_footer(); ?>