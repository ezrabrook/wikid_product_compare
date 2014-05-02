<?php

/*

Plugin Name: Wikid Compare Prices

Description: Comparison Module for datafeedr Don't delete me!

License: GPL v3



This program is free software: you can redistribute it and/or modify

it under the terms of the GNU General Public License as published by

the Free Software Foundation, either version 3 of the License, or

(at your option) any later version.



This program is distributed in the hope that it will be useful,

but WITHOUT ANY WARRANTY; without even the implied warranty of

MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the

GNU General Public License for more details.



You should have received a copy of the GNU General Public License

along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/



/** Add your custom code BELOW this line **/



/**

 * Adds gets posts by title and compares them.

 * This is compatible with http://www.woothemes.com/products/brands/ */







add_action( 'woocommerce_external_add_to_cart', 'wikid_get_compare_product_ids' );

function wikid_get_compare_product_ids() {





	global $post;

	

	/**Get the product title and separate on characters to then use the first portion to perform a search*/

	$search_product = '';

	

	$parent_title = get_the_title();

	echo $parent_title;

		//if title has comma

		if (strpos($parent_title, ',') !== FALSE){



			$search_title = explode(',', $parent_title);

			$search_product = $search_title[0];

			$first_string = $search_title[0];

			

			//if first part of title also has hyphen

			if (strpos($first_string, '&#8211;') !== FALSE){

			

				$search_title = explode('&#8211;', $first_string);

				$search_product = $search_title[0];

			}

			//if first part of title does not have hyphen

			elseif (strpos($search_title[0], '&#8211;' === FALSE)){

			

				$search_product = $search_title[0];

			}

		}

		//if title has hyphen and no comma

		if ((strpos($parent_title,',') === FALSE ) && (strpos($parent_title, '&#8211;') !== FALSE)){

			echo 'I have a hyphen';

			$search_title = explode('&#8211;', $parent_title);

			$search_product = $search_title[0];

			

		}

		//if title has & and no hyphen or comma 

		if((strpos($parent_title, '&') !== FALSE) && (strpos($parent_title, '&#8211;') === FALSE) && (strpos($parent_title, ',') !== FALSE)) {

			$search_title = explode('&' , $parent_title);

			$search_product = $search_title[0];

		}

		//if title has no comma and no hyphen just search with full title

		elseif((strpos($parent_title,',') === FALSE ) && (strpos($parent_title, '&#8211;') === FALSE)){

			$search_title = explode(" ", $parent_title);

			print_r ($search_title);

			//Titles are super long, so this is a way to only search the first 4 words

			 if(isset($search_title[3])){

				$search_product = $search_title[0] .' '. $search_title[1] .' '. $search_title[2]. ' '. $search_title[3];

			}

			elseif((isset($search_title[2])) && (empty($search_title[3]))){

				$search_product = $search_title[0] .' '. $search_title[1] .' '. $search_title[2];

			}

			elseif((isset($search_title[1])) && (!isset($search_title[2]))){

				$search_product = $search_title[0] .' '. $search_title[1];

			}

			else {

				$search_product = $parent_title;

			} 

		}

	echo $search_product;

		

		

	$finalArgs =  array (

        'posts_per_page'=> 10,

        'order' => 'ASC',

        'post_type' => 'product'

    );



    /*Search again with a WHERE is = to the product title we want*/

	

    $searchProducts = new WP_Query( $finalArgs );

	

	global $wpdb;

	

    $myproductids = $wpdb->get_col("select ID from $wpdb->posts where post_title LIKE '".$search_product."%' AND post_status = 'publish'");

	

    $args = array(

        'post__in'=> $myproductids,

        'post_type'=>'product',

        'orderby'=>'title',

        'order'=>'ASC'

    );



    $res = new WP_Query($args);



	while( $res->have_posts() ) : $res->the_post();

		//Get all the post meta

		$product_info = get_post_meta($post->ID);

		//var_dump($product_info);

		$price = get_post_meta($post->ID, "_price", true);

		$sale_price = get_post_meta($post->ID, "_sale_price", true);

		$regular_price = get_post_meta($post->ID, "_regular_price", true);

		$sku = get_post_meta($post->ID, "_sku", true);

		$url = get_post_meta($post->ID, "_product_url", true);

		/*******************************************

		* Build the product URL

		*******************************************/

		if (strpos($url, 'netrition')!== false){

			$buy_link = url; //no change to netrition link

		}

		if (strpos($url,'shareasale')!== FALSE){

			$buy_link = str_replace('@@@', '743702', $url); //add id

		}

		if (strpos($url,'bodybuilding')!== FALSE){

			$buy_link = str_replace('@@@', '59729', $url); //add id

		}

		if ((strpos($url, 'click')!== FALSE) && (strpos($url, '10799155') !== FALSE)){

			$buy_link = str_replace('@@@', '3947054', $url);//add id

		}

		

		//get the rest of the product meta

		$text = get_post_meta($post->ID, "_button_text", true);

		$brand = get_post_meta($post->ID, "brand", true);

		$size = get_post_meta($post->ID, "size", true);

		/**************************************************

		* Get Thumbnail if API doesn't have it

		***************************************************/

		print '<div class="comparison_item"><div class="comparison_thumbs">';

		

		$brand_button = strpos($url, "netrition");

		if($brand_button !== FALSE){

			$text = 'Buy now from Netrition';

			$brand_image = '/wp-content/themes/mystile-child/images/net_logo.png';

			print '<img class="merchant_logo" src="'. $brand_image. '" alt="'.$text.'"/>';

		}

		else{

				$text = 'Buy Now';

		}

		global $product;

		if ( dfrpswc_is_dfrpswc_product( $product->id ) ) {

			$postmeta = get_post_meta( $product->id, '_dfrps_product', true );

			$merchant_id = $postmeta['merchant_id'];

			$logo_url = 'http://factory3.datafeedr.com/c/m/' . $postmeta['merchant_id'];

			$headers = get_headers( $logo_url, 1 );

			if ( $headers[0] == 'HTTP/1.1 200 OK' ) {

				echo '<img src="' . $logo_url . '" alt="' . esc_attr( $postmeta['merchant'] ) . '" title="' . esc_attr( $postmeta['merchant'] ) . '" class="merchant_logo" />';

			}



		}

		print '</div>';





		//echo $price;

		//echo $sale_price;

		//echo $regular_price;

		//echo $sku;

		//echo $text;

		//echo $url;

		//echo $buy_link;

		//echo $size;

		//echo $brand;

		?>



		<div class="comparison_product"><a class="compare_product_link" href='<?php the_permalink()?>'><?php the_title();?></a><br/>

		<?php

		if ((isset($regular_price)) && ($regular_price != $price) && ($regular_price != $sale_price) && ($regular_price != NULL)){

			print '<span class="reg_price"><del> $' . $regular_price . '</del></span>';

		}

		if (isset($price)){

			print '<span class="price"> $'. $price .'</span>';

		}

		elseif(isset($sale_price)){

			print '<span class="sale_price"> $' . $sale_price. '</span>';

		}

		if (isset($size)){

			print '<span class="size"> '. $size . '</span>';

		}

		print '</div>';

		if (isset($buy_link)){

			print '<div class="comparison_button"><a class="single_add_to_cart_button button-pill button-flat-primary alt modified-cart" href="'.$buy_link.'">'. $text .'</a></div>';

		}





		?>

		<?php





		print '<br/>';

		print '</div>';



		echo get_post_format();



	endwhile;







}







