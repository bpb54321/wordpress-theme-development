<?php

function post_updated_do_action( $post_id ) {

    //Get the value of the 'product_url' field
    //get_post_meta ( int $post_id, string $key = '', bool $single = false )
    //Return: (mixed) Will be an array if $single is false. Will be value of meta data field if $single is true. 
    $product_url_value = get_post_meta($post_id, 'product_url', true );

    //Add data in 'product_url' field to 'product_title' field
    /* 
        function update_post_meta ( int $post_id, string $meta_key, mixed $meta_value, mixed $prev_value = '' )
        Parameters
            $post_id

                (int) (Required) Post ID.
            $meta_key

                (string) (Required) Metadata key.
            $meta_value

                (mixed) (Required) Metadata value. Must be serializable if non-scalar.
            $prev_value

                (mixed) (Optional) Previous value to check before removing.

                Default value: ''
    */

    update_post_meta($post_id, 'product_title', $product_url_value);


}
add_action( 'save_post', 'post_updated_do_action' );

/*function test_action() {
	echo "<p>This is an edited print statement.</p>";
}

add_action( 'the_post', 'test_action' );*/

/*function content_test_filter($content, $post_id) {
	
	$content = "Look at me I've changed the post content!";

	return $content;
}

add_filter( 'content_edit_pre', 'content_test_filter', 10, 2 );*/

/*function my_acf_update_value( $value, $post_id, $field  )
{
    $value = "Custom value";

    // do something else to the $post object via the $post_id

    return $value;
}

// acf/update_value/name={$field_name} - filter for a specific field based on it's name
add_filter('acf/update_value/name=product_url', 'my_acf_update_value', 10, 3);*/

/*function my_acf_save_post( $post_id ) {
    
    // bail early if no ACF data
    if( empty($_POST['acf']) ) {
        
        return;
        
    }
    
    // array of field values
    $fields = $_POST['acf'];

    var_dump($fields);


    // specific field value
    //$field = $_POST['acf']['field_abc123'];
    
}

// run before ACF saves the $_POST['acf'] data
add_action('acf/save_post', 'my_acf_save_post', 1);*/




?>