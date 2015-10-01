<?php

/*$url1 = "http://www.ctainc.com/product/2406/Shop-By-Product_Prayer-Journals";
get_product_info($url1);

$url2 = "http://www.ctainc.com/product/2774";
get_product_info($url2);*/

/*$url3 = "http://www.ctainc.com/product/2937";
get_product_info($url3);

$url4 = "http://www.ctainc.com/product/1969";
get_product_info($url4);*/

/*$url5 = "http://www.ctainc.com/product/2006";
get_product_info($url5);*/



function get_product_info($content) {
                
                $url = "http://www.ctainc.com/product/2006";
                //$url = $content;
                
                //echo "<p>" . $url . "</p>";
                //var_dump($content);
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
                $dom->loadHTML($html); //loads the html string into a DOMDocument
            
                
                /*----------------------------------Product Title-----------------------------------------*/
                //echo "<p>Product Title Section</p>";
                //echo "<h2>Product Title Section</h2>";
                $product_content_div = find_element_with_class($dom, 'div', 'content');
                
                //Store the first h1 tag
                $target_h1_list = $product_content_div->getElementsByTagName("h1");
                $target_h1 = $target_h1_list[0];
                $product_title = $target_h1->nodeValue;
                /*echo "<p>Product title</p>";
                print_object($product_title);*/

                $content = $content . $product_title;

                /*----------------------------------Product Image-----------------------------------------*/

                //echo "<h2>Product Image Section</h2>";
                $product_image_img = find_element_with_class($dom, 'img', 'product_image');
                if($product_image_img) {
                    $src_node = $product_image_img->attributes->getNamedItem('src');
                    $product_url = $src_node->value; 
                    /*echo "<p>Product image url</p>";
                    print_object($product_url);*/
                } else {
                    //echo "The product image search returned no matches.";
                }

                /*----------------------------Product Price-----------------------------------------*/
                //echo "<h2>Product Price Section</h2>";
                $price_div = find_element_with_class($dom, 'div', 'price');

                //Get 2nd child div of $price_div
                $child_div_list = $price_div->getElementsByTagName("div");
                $second_child_div = $child_div_list[1];


                //Find <input> in 2nd child div
                $target_input_list = $second_child_div->getElementsByTagName("input");
                $target_input = $target_input_list[0];

                //Get value of attribute "value"
                $attributes = $target_input->attributes;
                $value_attribute = $attributes->getNamedItem('value');
                $product_price = $value_attribute->value;
                /*echo "<p>product_price</p>";
                print_object($product_price);*/

                /*----------------------------Product Desc-----------------------------------------*/
                //echo "<h2>Product Description Section</h2>";
                $product_description_div = find_element_with_class($dom, 'div', 'prod-desc');

                //Get first <p> element
                $target_p_list = $product_description_div->getElementsByTagName("p");
                $target_p = $target_p_list[0];
                $product_description = $target_p->nodeValue;
                /*echo "<p>Product Description</p>";
                print_object($product_description);*/

                return $content;


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
@returns: The first DOMElement that matches the $tag_name and $class_name.  
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

//add_action( 'the_post', 'get_product_info' );
//add_filter( 'content_edit_pre', 'get_product_info', 10, 2 );
add_filter( 'the_content', 'get_product_info', 10, 1 ); //the one argument passed in is $content

?>

                
                