<?php
/**
 * The main template file
 *
 * @package EL_Theme
 */

get_header();
?>

<main id="content" class="site-content">
    <?php
    /**
     * Hook: el_theme_before_content
     * Allows Beaver Themer parts to be inserted before the content
     */
    do_action( 'el_theme_before_content' );
    ?>

    <?php if ( el_theme_is_builder_layout() ) : ?>

        <?php
        // Beaver Builder layout: output the layout only, no title or width constraint.
        while ( have_posts() ) :
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <?php the_content(); ?>
            </article>
        <?php endwhile; ?>

    <?php else : ?>

        <div class="el-container">
            <?php if ( have_posts() ) : ?>

                <?php if ( is_home() && is_front_page() ) : ?>
                    <?php // Blog on the front page: the page's <h1> is the site name, read by screen readers only. ?>
                    <h1 class="screen-reader-text"><?php bloginfo( 'name' ); ?></h1>
                <?php elseif ( ! is_singular() ) : ?>
                    <header class="page-header">
                        <h1 class="page-title">
                            <?php
                            if ( is_search() ) {
                                /* translators: %s: search query. */
                                printf( esc_html__( 'Search results for: %s', 'el-theme' ), esc_html( get_search_query() ) );
                            } elseif ( is_home() ) {
                                single_post_title();
                            } else {
                                echo wp_kses_post( get_the_archive_title() );
                            }
                            ?>
                        </h1>
                    </header>
                <?php endif; ?>

                <?php while ( have_posts() ) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class( is_singular() ? '' : 'el-archive-item' ); ?>>
                        <header class="entry-header">
                            <?php if ( is_singular() ) : ?>
                                <h1 class="entry-title"><?php the_title(); ?></h1>
                            <?php else : ?>
                                <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                            <?php endif; ?>

                            <?php if ( 'post' === get_post_type() ) : ?>
                                <div class="entry-meta">
                                    <span class="posted-on"><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></span>
                                    <span class="byline">
                                        <?php
                                        /* translators: %s: post author name. */
                                        printf( esc_html__( 'by %s', 'el-theme' ), esc_html( get_the_author() ) );
                                        ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </header>

                        <?php if ( is_singular() ) : ?>
                            <div class="entry-content">
                                <?php
                                the_content();

                                wp_link_pages( array(
                                    'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Page', 'el-theme' ) . '">' . esc_html__( 'Pages:', 'el-theme' ),
                                    'after'  => '</nav>',
                                ) );
                                ?>
                            </div>
                        <?php else : ?>
                            <div class="entry-summary">
                                <?php the_excerpt(); ?>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endwhile; ?>

                <?php if ( ! is_singular() ) : ?>
                    <?php the_posts_pagination(); ?>
                <?php endif; ?>

            <?php else : ?>
                <section class="no-results">
                    <h1 class="page-title">
                        <?php
                        if ( is_404() ) {
                            esc_html_e( 'Page not found', 'el-theme' );
                        } elseif ( is_search() ) {
                            /* translators: %s: search query. */
                            printf( esc_html__( 'No results for: %s', 'el-theme' ), esc_html( get_search_query() ) );
                        } else {
                            esc_html_e( 'Nothing found', 'el-theme' );
                        }
                        ?>
                    </h1>
                    <p><?php esc_html_e( "It seems we can't find what you're looking for. Try a search instead.", 'el-theme' ); ?></p>
                    <?php get_search_form(); ?>
                </section>
            <?php endif; ?>
        </div>

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
