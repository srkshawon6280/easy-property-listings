<?php
/**
 * Adds filters for search and ordering to admin pages
 *
 * @package     EPL
 * @subpackage  Meta
 * @copyright   Copyright (c) 2014, Merv Barrett
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


// Query filter for property_address_suburb custom field sortable in posts listing
add_filter( 'request', 'epl_property_address_suburb_column_orderby' );
function epl_property_address_suburb_column_orderby( $vars ) {
	if ( isset( $vars['orderby'] ) && 'property_address_suburb' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
			'meta_key' => 'property_address_suburb',
			'orderby' => 'meta_value'
		) );
	}

	return $vars;
}

// Add custom filters to post type posts listings
add_action( 'restrict_manage_posts', 'epl_custom_restrict_manage_posts' );
add_filter( 'parse_query', 'epl_admin_posts_filter' );

function epl_custom_restrict_manage_posts() {
	global $post_type;
	if($post_type == 'property' || $post_type == 'rental' || $post_type == 'land' || $post_type == 'commercial' || $post_type == 'rural' || $post_type == 'business' || $post_type == 'holiday_rental' || $post_type == 'commercial_land') {
	
		//Filter by property_status
		$fields = array(
			'current'	=>	__('Current', 'epl'),
			'withdrawn'	=>	__('Withdrawn', 'epl'),
			'offmarket'	=>	__('Off Market', 'epl')
		);
		
		if($post_type != 'rental' && $post_type != 'holiday_rental') {
			$fields['sold'] = apply_filters( 'epl_sold_label_status_filter' , __('Sold', 'epl') );
		}
		
		if($post_type == 'rental' || $post_type == 'holiday_rental' || $post_type == 'commercial' || $post_type == 'business' || $post_type == 'commercial_land') {
			$fields['leased'] = apply_filters( 'epl_leased_label_status_filter' , __('Leased', 'epl') );
		}
		
		if(!empty($fields)) {
			$_GET['property_status'] = isset($_GET['property_status'])?sanitize_text_field($_GET['property_status']):'';
			echo '<select name="property_status">';
				echo '<option value="">'.__('Filter By Type', 'epl').'</option>';
				foreach($fields as $k=>$v) {
					$selected = ($_GET['property_status'] == $k ? 'selected="selected"' : '');
					echo '<option value="'.$k.'" '.$selected.'>'.__($v, 'epl').'</option>';
				}
			echo '</select>';
		}
		
		$property_author =	isset($_GET['property_author']) ? intval($_GET['property_author']) : '';
		// filter by authors
		wp_dropdown_users(
			array(
				'name' 			=> 'property_author',
				'selected'		=> $property_author,
				'show_option_all'	=> __('All Users','epl')
			)
		); 
		
		$custom_search_fields = array(
			'property_address_suburb'	=>	epl_display_label_suburb(),
			'property_office_id'		=>	__('Office ID', 'epl'),
			'property_agent'		=>	__('Listing Agent', 'epl'),
			'property_second_agent'		=>	__('Second Listing Agent', 'epl'),
		);
		$custom_search_fields = apply_filters('epl_admin_search_fields',$custom_search_fields);
		
		if(!empty($custom_search_fields)) {
			$_GET['property_custom_fields'] = isset($_GET['property_custom_fields'])?sanitize_text_field($_GET['property_custom_fields']):'';
			echo '<select name="property_custom_fields">';
				echo '<option value="">'.__('Search For :', 'epl').'</option>';
				foreach($custom_search_fields as $k=>$v) {
					$selected = ($_GET['property_custom_fields'] == $k ? 'selected="selected"' : '');
					echo '<option value="'.$k.'" '.$selected.'>'.__($v, 'epl').'</option>';
				}
			echo '</select>';
		}
		
		//Filter by Suburb
		if(isset($_GET['property_custom_value'])) {
			$val = stripslashes($_GET['property_custom_value']);
		} else {
			$val = '';
		}
		echo '<input type="text" name="property_custom_value" placeholder="'.__('Search Value.', 'epl').'" value="'.$val.'" />';
	}
}

function epl_admin_posts_filter( $query ) {
	global $pagenow;
	if( is_admin() && $pagenow == 'edit.php' ) {
		$meta_query = $query->get('meta_query');
		
		if(isset($_GET['property_status']) && $_GET['property_status'] != '') {
			$meta_query[] = array(
				'key'       => 'property_status',
				'value'     => $_GET['property_status']
			);
		}
		
		if(isset($_GET['property_author']) && $_GET['property_author'] != '') {
			$query->set( 'author', intval($_GET['property_author']) );
		}


		if( isset($_GET['property_custom_fields']) && trim($_GET['property_custom_fields']) != '' && isset($_GET['property_custom_value']) && trim($_GET['property_custom_value']) != '') {
		
			$meta_query[] = array(
				'key'       => sanitize_text_field( $_GET['property_custom_fields'] ),
				'value'     => sanitize_text_field( $_GET['property_custom_value'] ),
				'compare'   => 'LIKE',
			);
			
		}
		
		if(!empty($meta_query)) {
			$query->set('meta_query', $meta_query);
		}
	}
}

/**
 * Manage Property Columns Sorting
 *
 * @since 1.0
 */
function epl_manage_listings_sortable_columns( $columns ) {
	$columns['property_price'] = __('property_price', 'epl');
	return $columns;
}

$epl_posts = array('property','land', 'commercial', 'business', 'commercial_land' , 'location_profile','rental','rural');

foreach($epl_posts  as $epl_post ) {
	add_filter( 'manage_edit-'.$epl_post.'_sortable_columns', 'epl_manage_listings_sortable_columns' );
}

function epl_custom_orderby( $query ) {
	if( ! is_admin() )  
	return;  

	$orderby = $query->get( 'orderby');  

	if( 'property_price' == $orderby ) {  
		$query->set('meta_key','property_price');  
		$query->set('orderby','meta_value_num');  
	}  
	
}
// handle sorting of admin columns
add_filter( 'pre_get_posts', 'epl_custom_orderby' );
