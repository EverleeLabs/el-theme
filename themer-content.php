<?php
/**
 * Wrapper for Beaver Themer singular, archive and 404 layouts.
 *
 * @package EL_Theme
 */

get_header();
?>

<main id="content" class="site-content">
    <?php
    do_action( 'el_theme_before_content' );

    FLThemeBuilderLayoutRenderer::render_content_layout();

    do_action( 'el_theme_after_content' );
    ?>
</main>

<?php
get_footer();
