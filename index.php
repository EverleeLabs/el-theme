<?php
/**
 * The main template file
 *
 * @package EL_Theme
 */

get_header();
?>

<main class="site-content" role="main">
    <?php
    /**
     * Hook: el_theme_before_content
     * Allows Beaver Themer parts to be inserted before the content
     */
    do_action( 'el_theme_before_content' );
    ?>

    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                    <?php if ( ! is_page() ) : ?>
                        <div class="entry-meta">
                            <span class="posted-on"><?php echo get_the_date(); ?></span>
                            <span class="byline"> by <?php the_author(); ?></span>
                        </div>
                    <?php endif; ?>
                </header>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <article>
            <h1>Nothing Found</h1>
            <p>It seems we can't find what you're looking for.</p>
        </article>
    <?php endif; ?>

    <?php
    /**
     * Hook: el_theme_after_content
     * Allows Beaver Themer parts to be inserted after the content
     */
    do_action( 'el_theme_after_content' );
    ?>
</main>

<?php
get_footer();

