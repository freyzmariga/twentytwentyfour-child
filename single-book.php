<?php
/**
 * The template for displaying a single "Book" CPT post.
 *
 * This template fetches ACF fields for the book and links to the
 * corresponding WooCommerce product via its SKU (which matches the book's ISBN).
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_Four_Child
 * @since 1.0.0
 */

get_header(); // Loads the theme's header

?>

<main id="wp--skip-link--target" class="wp-site-blocks">
    <div class="wp-block-group is-layout-constrained" style="padding-top:var(--wp--preset--spacing--50); padding-bottom:var(--wp--preset--spacing--50);">
        <div class="wp-block-group is-layout-constrained" style="max-width: var(--wp--preset--spacing--160); margin: auto;">

            <?php
            // Start the WordPress Loop
            while ( have_posts() ) :
                the_post();

                // --- 1. Get Book Details from ACF ---
                // We use get_field() to retrieve the custom field values.
                // We also get the main post title for the heading.
                $book_post_title = get_the_title();
                $acf_book_title  = get_field( 'book_title' ); // This is the ACF field
                $author          = get_field( 'author' );
                $isbn            = get_field( 'isbn' );
                $price           = get_field( 'price' );

                // --- 2. Find the WooCommerce Product ID ---
                // We use the book's ISBN to find the Woo Product ID,
                // as we set the product's SKU to match the ISBN.
                $product_id = 0; // Default to 0
                if ( function_exists( 'wc_get_product_id_by_sku' ) && ! empty( $isbn ) ) {
                    $product_id = wc_get_product_id_by_sku( $isbn );
                }

                // --- 3. Generate the "Buy" Button Link ---
                // We create a direct "add-to-cart" link that also redirects
                // straight to the checkout page for a seamless experience.
                $buy_button_html = '';
                if ( $product_id > 0 ) {
                    // Use home_url() for a reliable path to the checkout page
                    $checkout_url    = add_query_arg( 'add-to-cart', $product_id, wc_get_checkout_url() );
                    $buy_button_html = '<a href="' . esc_url( $checkout_url ) . '" class="wp-block-button__link">Buy with Stripe for $' . esc_html( $price ) . '</a>';
                } else {
                    $buy_button_html = '<span class="book-unavailable">This book is currently unavailable.</span>';
                }

                ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                    <h1 class="entry-title wp-block-post-title"><?php echo esc_html( $book_post_title ); ?></h1>

                    <div class="entry-content wp-block-post-content is-layout-flow">

                        <?php if ( $author ) : ?>
                            <p><strong>Author:</strong> <?php echo esc_html( $author ); ?></p>
                        <?php endif; ?>

                        <?php if ( $isbn ) : ?>
                            <p><strong>ISBN:</strong> <?php echo esc_html( $isbn ); ?></p>
                        <?php endif; ?>

                        <?php if ( $price ) : ?>
                            <p><strong>Price:</strong> $<?php echo esc_html( $price ); ?></p>
                        <?php endif; ?>

                        <?php the_content(); ?>

                        <div class="wp-block-button is-style-fill" style="margin-top: var(--wp--preset--spacing--40);">
                            <?php echo $buy_button_html; // PHPCS: XSS ok. Already escaped. ?>
                        </div>

                    </div></article><?php
            endwhile; // End of the loop.
            ?>

        </div>
    </div>
</main>

<?php
get_footer(); // Loads the theme's footer