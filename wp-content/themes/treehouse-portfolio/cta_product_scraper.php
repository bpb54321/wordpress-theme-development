<?php

function post_updated_do_action( $post_id ) {

    //Get the value of the 'product_url' field
    //get_post_meta ( int $post_id, string $key = '', bool $single = false )
    //Return: (mixed) Will be an array if $single is false. Will be value of meta data field if $single is true. 
    //$product_url_value = get_post_meta($post_id, 'product_url', true );
    $product_id = get_post_meta($post_id, 'product_id', true );

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


    //$product_info = get_product_info($product_url_value);
    $product_info = get_product_info($product_id);
    
    $meta_keys = ['product_title', 'product_image_url', 'product_price', 'product_description']; 

    foreach ($meta_keys as $meta_key) {
        /*
        Function: update_post_meta ( int $post_id, string $meta_key, mixed $meta_value, mixed (optional) $prev_value = '' )
        Return: (int|bool) Meta ID if the key didn't exist, true on successful update, false on failure.
        */
        update_post_meta($post_id, $meta_key, $product_info[$meta_key]);    
    }

}

add_action( 'save_post', 'post_updated_do_action' );

function get_product_info($product_id) {

                //Create url from $product_id
                $url = "http://www.ctainc.com/product/" . $product_id;

                //instantiate info array
                $product_info = array(
                    "product_title"  => "",
                    "product_image_url" => "",
                    "product_price" => "",
                    "product_description" => "");
                
                // create curl resource
                $ch = curl_init();

                // set options
                curl_setopt($ch, CURLOPT_URL, $url); //set url
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //return the transfer as a string

                // get html string
                $html = curl_exec($ch);

                // close curl resource to free up system resources
                curl_close($ch); 

                //Use DOMDocument class, which is built into php
                $dom = new DOMDocument;

                libxml_use_internal_errors(true); //libxml_use_internal_errors() allows you to disable standard libxml errors and enable user error handling. 
                $dom->loadHTML($html); //loads the html string into a DOMDocument
            
                
                /*----------------------------------Product Title-----------------------------------------*/
                $product_content_div = find_element_with_class($dom, 'div', 'content');
                if ($product_content_div == false) {
                    $product_title = "No product title found.";    
                } else {
                    $target_h1_list = $product_content_div->getElementsByTagName("h1");
                    if ($target_h1_list->length==0) {
                        $product_title = "No product title found.";    
                    } else {
                        //Store the first h1 tag
                        $target_h1 = $target_h1_list[0];
                        $product_title = $target_h1->nodeValue;    
                    }      
                }
                
                $product_info["product_title"] = $product_title;

                /*----------------------------------Product Image-----------------------------------------*/
                $product_image_img = find_element_with_class($dom, 'img', 'product_image');
                
                if($product_image_img) {
                    
                    $src_attribute = $product_image_img->attributes->getNamedItem('src');

                    if ($src_attribute) {
                        $product_image_url = $src_attribute->value;
                    } else {
                        error_log("cta_product_scraper.php: The <img class='product_image'> had no src attribute. ");
                        $product_image_url = "/";    
                    }

                } else {
                    error_log("cta_product_scraper.php: There was no <img> tag with class 'product_image' ");
                    $product_image_url = "/"; 
                }

                $product_info["product_image_url"] = $product_image_url;
                /*----------------------------Product Price-----------------------------------------*/
                $product_id = substr($url, -4); //This function requires a url that ends with product id 
                $price_id = "price_" . $product_id;
                $price_input_element = $dom->getElementById($price_id); 

                if ($price_input_element == NULL) {
                    $product_price = "Price not found.";
                } else {
                    //Get value of attribute "value"
                    $attributes = $price_input_element->attributes;
                    $value_attribute = $attributes->getNamedItem('value');
                    if ($value_attribute == NULL) { //value attribute doesn't exist
                        $product_price = "Price not found."; 
                    } else {
                        $product_price = $value_attribute->value;    
                    }
                
                }

                $product_info["product_price"] = $product_price;
                /*----------------------------Product Desc-----------------------------------------*/
                $product_description_div = find_element_with_class($dom, 'div', 'prod-desc');

                //Get first <p> element
                $target_p_list = $product_description_div->getElementsByTagName("p");
                $target_p = $target_p_list[0];
                $product_description = $target_p->nodeValue;

                $product_info["product_description"] = $product_description;

                /*---------------------------------------------------------------------*/

                return $product_info;


}

function print_object($object) {
    //echo "<pre>";
    print_r($object,false);
    //echo ($object);
    //echo "</pre>";    
}

/*
Finds elements of a specified tag type that have a specified class name.
@param: $dom = the DOMDocument object that contains your HTML
@param: $tag_name = a string of the HTML tag you are searching for, without the <>, e.g. 'div' 
@param: $class_name = a string of the class you are searching for 
@returns: The first DOMElement that matches the $tag_name and $class_name. False if no DOMElement with the $tag_name and $class_name were found.  
*/
function find_element_with_class($dom, $tag_name, $class_name) {
    //DOMNodeList with matching tags
    $elements = $dom->getElementsByTagName($tag_name);

    $i = 0;
    $found_match = false;
    foreach ($elements as $element) {

        //Get attributes 
        $attributes = $element->attributes;

        //Does the element have a class attribute?
        $class_node = $attributes->getNamedItem('class');
        if ($class_node) { 
            //Is the first class $class_name?
            $class_name_position = strpos($class_node->value, $class_name);

            if (is_int($class_name_position)) {
                $found_match = true;
                return $element;
            } 
        }  

        $i++;            
    }

    if(!$found_match) {
        echo "<p>There were no elements of type " . $tag_name . " with class='" . $class_name . "'</p>";
        return false; 
    }  
}               