<?php

function post_updated_do_action( $post_id ) {

    //Get the value of the 'product_id' field
    //get_post_meta ( int $post_id, string $key = '', bool $single = false )
    //Return: (mixed) Will be an array if $single is false. Will be value of meta data field if $single is true. 
    //$product_id = get_post_meta($post_id, 'product_id', false );
    $product_ids = get_post_meta($post_id, 'product_id', false );
    
    if ($product_ids) {
        
        $meta_keys = ['_product_title', '_product_image_url', '_product_price', '_product_description'];

        //Delete all custom fields besides product_id to remove old data
        
        foreach ($meta_keys as $meta_key) {
            delete_post_meta($post_id, $meta_key);   
        }

        foreach ($product_ids as $product_id) {
            
            $product_info = get_product_info($product_id,$meta_keys);

            foreach ($meta_keys as $meta_key) {

                //Function: add_post_meta($post_id, $meta_key, $meta_value, $unique);
                //@param: $post_id (integer) (required) The ID of the post to which a custom field should be added. 
                //@param: $meta_key (string) (required) The key of the custom field which should be added.
                //@param $meta_value (mixed) (required) The value of the custom field which should be added. If an array is given, it will be serialized into a string.
                //@param $unique(boolean) (optional) Whether or not you want the key to stay unique. When set to true, the custom field will not be added if the given key already exists among custom fields of the specified post. Default: false
                //@return: On success, returns the ID of the inserted row, which validates to true. If the $unique argument was set to true and a custom field with the given key already exists, false is returned.  
                add_post_meta($post_id, $meta_key, $product_info[$meta_key], false);

            }
        }
    }

    else {
        error_log("cta_product_scraper: get_post_meta() could not find a field with the 'product_id' name.");
    }


    

}

add_action( 'save_post', 'post_updated_do_action' );

function get_product_info($product_id, $meta_key_array) {

    // create curl resource, which gets the info from the pages
    $ch = curl_init();

    //Create an empty $product_info array
    foreach ($meta_key_array as $meta_key) {
        $product_info[$meta_key] = "";  
    }

    //Create url from $product_id
    $url = "http://www.ctainc.com/product/" . $product_id;
    
    // set curl options
    curl_setopt($ch, CURLOPT_URL, $url); //set url
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //return the transfer as a string

    // get html string
    $html = curl_exec($ch);

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
    
    $product_info[$meta_key_array[0]] = $product_title;

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

    $product_info[$meta_key_array[1]] = $product_image_url;

    /*----------------------------Product Price-----------------------------------------*/
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

    $product_info[$meta_key_array[2]] = $product_price;
    /*----------------------------Product Desc-----------------------------------------*/
    $product_description_div = find_element_with_class($dom, 'div', 'prod-desc');

    if($product_description_div) {
        //Get first <p> element
        $target_p_list = $product_description_div->getElementsByTagName("p");

        if ($target_p_list->length == 0) {
            $product_description = "Product description not found."; 
        } else {
            $target_p = $target_p_list[0];
            $product_description = $target_p->nodeValue;    
        }      
    } else {
        error_log("cta_product_scraper: There was no product description section of this page.");
        $product_description = "Product description not found.";
    }

    $product_info[$meta_key_array[3]] = $product_description;

    /*---------------------------------------------------------------------*/
      
    return $product_info;        

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
        error_log("There were no elements of type " . $tag_name . " with class='" . $class_name);
        return false; 
    }  
}               