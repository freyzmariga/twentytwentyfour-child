<?php
/**
 * Child theme functions and definitions.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Custom Post Type: Book.
 *
 * @link https://developer.wordpress.org/reference/functions/register_post_type/
 */
function cpt_book_init() {
	$labels = array(
		'name'                  => _x( 'Books', 'Post type general name', 'twentytwentyfour-child' ),
		'singular_name'         => _x( 'Book', 'Post type singular name', 'twentytwentyfour-child' ),
		'menu_name'             => _x( 'Books', 'Admin Menu text', 'twentytwentyfour-child' ),
		'name_admin_bar'        => _x( 'Book', 'Add New on Toolbar', 'twentytwentyfour-child' ),
		'add_new'               => __( 'Add New', 'twentytwentyfour-child' ),
		'add_new_item'          => __( 'Add New Book', 'twentytwentyfour-child' ),
		'new_item'              => __( 'New Book', 'twentytwentyfour-child' ),
		'edit_item'             => __( 'Edit Book', 'twentytwentyfour-child' ),
		'view_item'             => __( 'View Book', 'twentytwentyfour-child' ),
		'all_items'             => __( 'All Books', 'twentytwentyfour-child' ),
		'search_items'          => __( 'Search Books', 'twentytwentyfour-child' ),
		'parent_item_colon'     => __( 'Parent Books:', 'twentytwentyfour-child' ),
		'not_found'             => __( 'No books found.', 'twentytwentyfour-child' ),
		'not_found_in_trash'    => __( 'No books found in Trash.', 'twentytwentyfour-child' ),
		'featured_image'        => _x( 'Book Cover Image', 'Overrides the “Featured Image” phrase for this post type.', 'twentytwentyfour-child' ),
		'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type.', 'twentytwentyfour-child' ),
		'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type.', 'twentytwentyfour-child' ),
		'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type.', 'twentytwentyfour-child' ),
		'archives'              => _x( 'Book archives', 'The post type archive label.', 'twentytwentyfour-child' ),
		'insert_into_item'      => _x( 'Insert into book', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media).', 'twentytwentyfour-child' ),
		'uploaded_to_this_item' => _x( 'Uploaded to this book', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post).', 'twentytwentyfour-child' ),
		'filter_items_list'     => _x( 'Filter books list', 'Screen reader text for the filter links heading on the post type listing screen.', 'twentytwentyfour-child' ),
		'items_list_navigation' => _x( 'Books list navigation', 'Screen reader text for the pagination heading on the post type listing screen.', 'twentytwentyfour-child' ),
		'items_list'            => _x( 'Books list', 'Screen reader text for the items list heading on the post type listing screen.', 'twentytwentyfour-child' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'book' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => 5, // Below Posts
		'supports'           => array( 'title', 'editor', 'thumbnail' ), // Added editor for description, thumbnail for cover
		'menu_icon'          => 'dashicons-book-alt',
		'show_in_rest'       => true, // Important for Gutenberg and REST API
	);

	register_post_type( 'book', $args );
}
add_action( 'init', 'cpt_book_init' );

/**
 * Flush rewrite rules on theme activation to ensure CPT slugs work.
 */
function twentytwentyfour_child_activate() {
	cpt_book_init();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'twentytwentyfour_child_activate' );


/**
 * Helper function to get the WooCommerce Product ID based on the Book's ISBN (SKU).
 * @param int $post_id The ID of the current Book CPT post.
 * @return int The corresponding WooCommerce Product ID, or 0 if not found.
 */
function get_woo_product_id_by_book_post( $post_id ) {
    if ( ! function_exists( 'wc_get_product_id_by_sku' ) || ! function_exists( 'get_field' ) ) {
        return 0; // Exit if WooCommerce or ACF is not active.
    }
    
    // Get the ISBN from the current Book CPT post's ACF field.
    $isbn = get_field( 'isbn', $post_id );
    
    if ( ! empty( $isbn ) ) {
        // Find the WooCommerce product where the SKU matches the ISBN.
        $product_id = wc_get_product_id_by_sku( $isbn );
        return $product_id;
    }
    return 0;
}
/**
 * Displays the "Buy with Stripe" button on the Book Archive/Listing Page.
 * This function hooks into the content display for the 'book' post type.
 */
function display_buy_button_on_book_archive() {
    // Only run this on the 'book' custom post type archive and only for the current post in the loop.
    if ( 'book' === get_post_type() && is_archive( 'book' ) ) {
        
        $post_id    = get_the_ID();
        $product_id = get_woo_product_id_by_book_post( $post_id );
        $price      = get_field( 'price', $post_id );
        
        if ( $product_id > 0 ) {
            // Get the URL to add the product to the cart and redirect straight to checkout.
            $checkout_url = add_query_arg( 'add-to-cart', $product_id, wc_get_checkout_url() );
            
            // Output clean HTML for the button.
            echo '<div class="book-archive-button-wrap" style="text-align:center; margin-top: 15px;">';
            echo '<a href="' . esc_url( $checkout_url ) . '" class="wp-block-button__link archive-buy-button">';
            echo 'Buy Now ($' . esc_html( $price ) . ')';
            echo '</a>';
            echo '</div>';
            
        } else {
            // Optional: Display a message if the product link is broken.
            echo '<p style="text-align:center;">Product Link Missing</p>';
        }
    }
}
// Hook into a standard archive loop action (may require theme-specific adjustment).
// This hook is a general choice for block themes.
add_action( 'the_post', 'display_buy_button_on_book_archive' );