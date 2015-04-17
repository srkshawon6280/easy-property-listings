<?php
/**
 * Register post type :: Rental
 *
 * @package     EPL
 * @subpackage  Meta
 * @copyright   Copyright (c) 2014, Merv Barrett
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
 
/**
 * Registers and sets up the Rental custom post type
 *
 * @since 1.0
 * @return void
 */
function epl_register_custom_post_type_rental() {

	$archives = defined( 'EPL_RENTAL_DISABLE_ARCHIVE' ) && EPL_RENTAL_DISABLE_ARCHIVE ? false : true;
	$slug     = defined( 'EPL_RENTAL_SLUG' ) ? EPL_RENTAL_SLUG : 'rental';
	$rewrite  = defined( 'EPL_RENTAL_DISABLE_REWRITE' ) && EPL_RENTAL_DISABLE_REWRITE ? false : array('slug' => $slug, 'with_front' => false);

	$labels = apply_filters( 'epl_rental_labels', array(
		'name'			=>	__('Rentals', 'epl'),
		'singular_name'		=>	__('Rental', 'epl'),
		'menu_name'		=>	__('Rentals', 'epl'),
		'add_new'		=>	__('Add New', 'epl'),
		'add_new_item'		=>	__('Add New Rental', 'epl'),
		'edit_item'		=>	__('Edit Rental', 'epl'),
		'new_item'		=>	__('New Rental', 'epl'),
		'update_item'		=>	__('Update Rental', 'epl'),
		'all_items'		=>	__('All Rentals', 'epl'),
		'view_item'		=>	__('View Rental', 'epl'),
		'search_items'		=>	__('Search Rentals', 'epl'),
		'not_found'		=>	__('Rental Not Found', 'epl'),
		'not_found_in_trash'	=>	__('Rental Not Found in Trash', 'epl'),
		'parent_item_colon'	=>	__('Parent Rental:', 'epl')
	) );
	$rental_args = array(
		'labels'		=>	$labels,
		'public'		=>	true,
		'publicly_queryable'	=>	true,
		'show_ui'		=>	true,
		'show_in_menu'		=>	true,
		'query_var'		=>	true,
		'rewrite'		=>	$rewrite,
		'menu_icon'		=>	'dashicons-admin-home',
		'capability_type'	=>	'post',
		'has_archive'		=>	$archives,
		'hierarchical'		=>	false,
		'menu_position'		=>	'26.5',
		'taxonomies'		=>	array( 'location', 'tax_feature' ),
		'supports'		=>	apply_filters( 'epl_rental_supports', array( 'title', 'editor', 'author', 'thumbnail', 'excerpt' , 'comments' ) ),
	);
	epl_register_post_type( 'rental', 'Rental', apply_filters( 'epl_rental_post_type_args', $rental_args ) );
}
add_action( 'init', 'epl_register_custom_post_type_rental', 0 );
 
/**
 * Manage Admin Rental Post Type Columns
 *
 * @since 1.0
 * @return void
 */
if ( is_admin() ) {
	/**
	 * Manage Admin Rental Post Type Columns: Heading
	 *
	 * @since 1.0
	 * @return void
	 */
	function epl_manage_rental_columns_heading( $columns ) {
		// Geocode Debug Option
		$columns = array(
			'cb' 			=> '<input type="checkbox" />',
			'property_thumb'	=> __('Image', 'epl'),
			'property_rent'		=> __('Rent', 'epl'),
			'title'			=> __('Address', 'epl'),
			'listing'		=> __('Listing Details', 'epl'),
			'geo'			=> __('Geocoded', 'epl'),
			'property_status'	=> __('Status', 'epl'),
			'agent'			=> __('Agent', 'epl'),
			'date'			=> __('Date', 'epl')
		);
		
		$geo_debug = 0;
		global $epl_settings;
		if(!empty($epl_settings) && isset($epl_settings['debug'])) {
			$geo_debug = $epl_settings['debug'];
		}
		if ( $geo_debug != 1 ) {
			unset($columns['geo']);
		}
		return $columns;
	}
	add_filter( 'manage_edit-rental_columns', 'epl_manage_rental_columns_heading' ) ;
	
	/**
	 * Manage Admin Rental Post Type Columns: Row Contents
	 *
	 * @since 1.0
	 */
	function epl_manage_rental_columns_value( $column, $post_id ) {
		global $post,$property;
		global $epl_settings;
		switch( $column ) {	
			/* If displaying the 'Featured' image column. */
			case 'property_thumb' :
				/* Get the featured Image */
				if( function_exists('the_post_thumbnail') ) {
					$thumb_size = isset($epl_settings['epl_admin_thumb_size'])? $epl_settings['epl_admin_thumb_size'] : 'admin-list-thumb';
					the_post_thumbnail($thumb_size);
				}
				break;
				
			case 'listing' :
				/* Get the post meta. */
				$property_address_suburb	= get_the_term_list( $post->ID, 'location', '', ', ', '' );
				$heading			= get_post_meta( $post_id, 'property_heading', true );
				$beds				= get_post_meta( $post_id, 'property_bedrooms', true );
				$baths				= get_post_meta( $post_id, 'property_bathrooms', true );
				$rooms				= get_post_meta( $post_id, 'property_rooms', true );
				$homeopen 			= get_post_meta( $post_id, 'property_inspection_times', true );

				if ( empty( $heading) ) {
					echo '<strong>'.__( 'Important! Set a Heading', 'epl' ).'</strong>';
				} else {
					echo '<div class="type_heading"><strong>' , $heading , '</strong></div>';
				}		
				
				echo '<div class="type_suburb">' , $property_address_suburb , '</div>';
				
				if ( !empty( $beds ) || !empty( $baths ) ) {
					echo '<div class="epl_meta_beds_baths">';
						echo '<span class="epl_meta_beds">' , $beds , ' ' , __( 'Beds', 'epl' ) , ' | </span>';
						echo '<span class="epl_meta_baths">' , $baths , ' ' , __( 'Baths', 'epl' ) , '</span>';
					echo '</div>';
				}
				
				if ( !empty( $rooms ) ) {
					if ( $rooms == 1 ) {
						echo '<div class="epl_meta_rooms">' , $rooms , ' ' , __( 'Room', 'epl' ) , '</div>';
					} else {
						echo '<div class="epl_meta_rooms">' , $rooms , ' ' , __( 'Rooms', 'epl' ) , '</div>';
					}
				}
					
				if ( !empty( $homeopen) ) {
					$homeopen = array_filter(explode( "\n", $homeopen ));
						$homeopen_list =  '<ul class="epl_meta_home_open">';
						foreach ( $homeopen as $num => $item ) {
						  $homeopen_list .= '<li>' . htmlspecialchars( $item ) . '</li>';
						}
						$homeopen_list .= '</ul>';
					echo '<div class="epl_meta_home_open_label"><span class="home-open"><strong>'.$epl_settings['label_home_open'].'</strong></span>' , $homeopen_list , '</div>';
				} 
			
				break;
		
			/* If displaying the 'Geocoding' column. */
			case 'geo' :
				/* Get the post meta. */
				$property_address_coordinates = get_post_meta( $post_id, 'property_address_coordinates', true );
				/* If no duration is found, output a default message. */
				if (  $property_address_coordinates == ',' )
					_e('NO','epl') ;
				/* If there is a duration, append 'minutes' to the text string. */
				else
					echo $property_address_coordinates;
				break;
				
			/* If displaying the 'Rent' column. */
			case 'property_rent' :
				/* Get the post meta. */
				$rent = get_post_meta( $post_id, 'property_rent', true );
						
				$d_bond = '';
				if( !empty($epl_settings) && isset( $epl_settings['display_bond'] )) {
					$d_bond = $epl_settings['display_bond'];
					$bond = get_post_meta( $post_id, 'property_bond', true );
				}
				$max_price = '2000';
				if(isset($epl_settings['epl_max_graph_sales_price' ])) {
					$max_price = (int) $epl_settings['epl_max_graph_rent_price' ];
				}

				$property_status = ucfirst( get_post_meta( $post_id, 'property_status', true ) );
				$sold_price = get_post_meta( $post_id, 'property_sold_price', true );
				
				if ( !empty( $property_under_offer) && 'yes' == $property_under_offer ) {
					$class = 'bar-under-offer';
				}elseif ( $property_status == 'Current' ) {
					$class = 'bar-home-open';
				}elseif($property_status == 'Sold'){
					$class = 'bar-home-sold';
				}elseif($property_status == 'Leased'){
					$class = 'bar-home-sold';
				}else{
					$class = '';
				}
				if($sold_price != ''){
					$barwidth = $max_price == 0 ? 0: $sold_price/$max_price * 100;
				} else {
					$barwidth = $max_price == 0 ? 0: $rent/$max_price * 100;
				}
				echo '
					<div class="epl-price-bar '.$class.'">
						<span style="width:'.$barwidth.'%"></span>
					</div>';


				/* If no duration is found, output a default message. */
				if ( empty( $rent) )
					echo ''; //'<strong>'.__( 'No Rent Set', 'epl' ).'</strong>';
				/* If there is a duration, append 'minutes' to the text string. */
				else
					 echo '<div class="epl_meta_rent">' , epl_currency_formatted_amount( $rent ) , '</div>';
					if ( $d_bond == 1 ) { 
						echo '<div class="epl_meta_bond">Bond: ' , epl_currency_formatted_amount( $bond ) , '</div>'; 
					}
				break;
			/* If displaying the 'real-estate' column. */
			case 'property_status' :
				/* Get the genres for the post. */
				$property_status = get_post_meta( $post_id, 'property_status', true );
				$labels_property_status = apply_filters (  'epl_labels_property_status_filter', array(
					'current' 	=> __('Current', 'epl'),
					'withdrawn' => __('Withdrawn', 'epl'),
					'offmarket' => __('Off Market', 'epl'),
					'sold'  	=> __('Sold', 'epl'),
					'leased'  	=> $property->label_leased
					)
				);
				echo '<span class="type_'.strtolower($property_status).'">'.$labels_property_status[$property_status].'</span>';

				break;
				
				case 'agent':
				
				printf( '<a href="%s">%s</a>',
					esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'author' => get_the_author_meta( 'ID' ) ), 'edit.php' )),
					get_the_author()
				);
				
				$property_second_agent = $property->get_property_meta('property_second_agent');
				if ( '' != $property_second_agent ) {
					$second_author = get_user_by( 'login' , $property_second_agent );
					if($second_author !== false){
						printf( '<br><a href="%s">%s</a>',
							esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'author' => $second_author->ID ), 'edit.php' )),
							get_the_author_meta('display_name', $second_author->ID) 
						);

					}
					epl_reset_post_author();
				}
				break;

			/* Just break out of the switch statement for everything else. */
			default :
				break;
		}
	}
	add_action( 'manage_rental_posts_custom_column', 'epl_manage_rental_columns_value', 10, 2 );
	
	/**
	 * Manage Rental Columns Sorting
	 *
	 * @since 1.0
	 */
	function epl_manage_rental_sortable_columns( $columns ) {
		$columns['property_status'] = 'property_status';
		$columns['property_rent'] = 'property_rent';
		return $columns;
	}
	add_filter( 'manage_edit-rental_sortable_columns', 'epl_manage_rental_sortable_columns' );
}
