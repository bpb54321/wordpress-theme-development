<?php

/*function test_action() {
	echo "<p>This is an edited print statement.</p>";
}

add_action( 'the_post', 'test_action' );*/

function content_test_filter($content, $post_id) {
	
	$content = "Look at me I've changed the post content!";

	return $content;
}

add_filter( 'content_edit_pre', 'content_test_filter', 10, 2 );

?>