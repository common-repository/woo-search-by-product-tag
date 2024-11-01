<?php
/*
  Plugin Name: Woo Search By Product Tag
  Plugin URI: http://www.lifesoftwares.com/
  Description: It is quite surprising but by default woocommerce does not search products by product tags. This is a simple plugin which adds Tag search functionality in woocommerce.
  Author: Praveen Chauhan
  Version: 1.0
  Author URI: http://www.lifesoftwares.com/
 */
 

add_filter('the_posts', 'searching_in_products_tag'); 

function searching_in_products_tag($posts, $myquery = false) {
    if (is_search()) {
              
        $tags = explode(',', get_search_query());

        foreach($tags as $tag)
        {
            //Ignore posts which were already found
            $ignoreafIds = array(0);
            foreach($posts as $post)
            {
                $ignoreafIds[] = $post->ID;
            }
        
            $matchingTags = get_posts_by_tags(trim($tag), $ignoreafIds);

            if($matchingTags) 
            {
                foreach($matchingTags as $product_id)
                {   
                    $posts[] = get_post($product_id->post_id);
                }

            }
        }
        
        return $posts;
    }

    return $posts;
}

function get_posts_by_tags($tag, $ignoreafIds) {

    global $wpdb, $wp_query;
   
  
    $ignoreafIdsForMySql = implode(",", $ignoreafIds);
  
    $myquery = "
            select products.ID as post_id from $wpdb->terms t
            join $wpdb->term_taxonomy tt
            on t.term_id = tt.term_id
            join $wpdb->term_relationships tr
            on tt.term_taxonomy_id = tr.term_taxonomy_id
            join $wpdb->posts products
            on products.ID = tr.object_id
            join $wpdb->postmeta visibility
            on products.ID = visibility.post_id    
            and visibility.meta_key = '_visibility'
            and visibility.meta_value <> 'hidden'
            WHERE 
            tt.taxonomy = 'product_tag' and
            t.name LIKE '%$tag%'
            and products.post_status = 'publish'
            and products.post_type = 'product'
            and (products.post_parent = 0 or products.post_parent is null)
            and products.ID not in ($ignoreafIdsForMySql)
            group by products.ID
";
    
	

    //Search sku of a variation and return the parent.
    $matchingProducts = $wpdb->get_results($myquery) ;
    
    if(is_array($matchingProducts) && !empty($matchingProducts))
    {
		$wp_query->found_posts = sizeof($matchingProducts);		
        return $matchingProducts;   
    }    
    return null;
}

?>
