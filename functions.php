<?php
/**
 * EL Theme Functions
 *
 * @package EL_Theme
 */

require_once get_template_directory() . '/inc/updater.php';

/**
 * Theme setup: Beaver Themer support and core WordPress features.
 */
add_action( 'after_setup_theme', 'el_theme_header_footer_support' );

function el_theme_header_footer_support() {
    add_theme_support( 'fl-theme-builder-headers' );
    add_theme_support( 'fl-theme-builder-footers' );
    add_theme_support( 'fl-theme-builder-parts' );

    // Let WordPress manage the document title
    add_theme_support( 'title-tag' );

    // Add theme support for post thumbnails
    add_theme_support( 'post-thumbnails' );

    // Add HTML5 markup support
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );

    // Add support for automatic feed links
    add_theme_support( 'automatic-feed-links' );

    // Register navigation menus
    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'el-theme' ),
    ) );

    // Remove block patterns support
    remove_theme_support( 'core-block-patterns' );
}

/**
 * Enqueue the theme stylesheet.
 *
 * Uses the parent theme's style.css and version so a child theme can't
 * accidentally replace it, and each release busts browser caches.
 */
add_action( 'wp_enqueue_scripts', 'el_theme_enqueue_styles' );

function el_theme_enqueue_styles() {
    wp_enqueue_style(
        'el-theme',
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme( get_template() )->get( 'Version' )
    );
}

/**
 * Whether the current request is a singular post built with Beaver Builder.
 *
 * Builder layouts get no theme title or width constraint so full-width rows
 * stay full width.
 *
 * @return bool
 */
function el_theme_is_builder_layout() {
    if ( ! is_singular() || ! class_exists( 'FLBuilderModel' ) ) {
        return false;
    }

    return (bool) FLBuilderModel::is_builder_enabled( get_queried_object_id() );
}

/**
 * Add a body class on Beaver Builder layouts.
 */
add_filter( 'body_class', 'el_theme_body_classes' );

function el_theme_body_classes( $classes ) {
    if ( el_theme_is_builder_layout() ) {
        $classes[] = 'el-builder-layout';
    }

    return $classes;
}

/**
 * Render Beaver Themer headers and footers
 * Removes default theme header/footer when Beaver Themer layouts exist
 */
add_action( 'wp', 'el_theme_header_footer_render' );

function el_theme_header_footer_render() {
    // Without Beaver Themer, always use the default header and footer.
    if ( ! class_exists( 'FLThemeBuilderLayoutData' ) ) {
        add_action( 'el_theme_header', 'el_theme_default_header' );
        add_action( 'el_theme_footer', 'el_theme_default_footer' );
        return;
    }

    // Get the header ID
    $header_ids = FLThemeBuilderLayoutData::get_current_page_header_ids();

    // If we have a Beaver Themer header, remove the default theme header and hook in Beaver Themer's renderer
    if ( ! empty( $header_ids ) ) {
        remove_action( 'el_theme_header', 'el_theme_default_header' );
        add_action( 'el_theme_header', 'FLThemeBuilderLayoutRenderer::render_header' );
    } else {
        // If no Beaver Themer header, show default theme header
        add_action( 'el_theme_header', 'el_theme_default_header' );
    }

    // Get the footer ID
    $footer_ids = FLThemeBuilderLayoutData::get_current_page_footer_ids();

    // If we have a Beaver Themer footer, remove the default theme footer and hook in Beaver Themer's renderer
    if ( ! empty( $footer_ids ) ) {
        remove_action( 'el_theme_footer', 'el_theme_default_footer' );
        add_action( 'el_theme_footer', 'FLThemeBuilderLayoutRenderer::render_footer' );
    } else {
        // If no Beaver Themer footer, show default theme footer
        add_action( 'el_theme_footer', 'el_theme_default_footer' );
    }
}

/**
 * Default theme header (displayed when no Beaver Themer header exists)
 */
function el_theme_default_header() {
    // The site name is never the <h1>; each template outputs its own <h1>
    // so every page has exactly one, with or without a Themer header.
    ?>
    <header class="el-default-header">
        <div class="site-branding">
            <p class="site-title">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"<?php echo is_front_page() ? ' aria-current="page"' : ''; ?>><?php bloginfo( 'name' ); ?></a>
            </p>
            <?php
            $description = get_bloginfo( 'description', 'display' );
            if ( $description || is_customize_preview() ) :
                ?>
                <p class="site-description"><?php echo esc_html( $description ); ?></p>
            <?php endif; ?>
        </div>

        <?php if ( has_nav_menu( 'primary' ) ) : ?>
            <nav class="el-primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'el-theme' ); ?>">
                <?php
                wp_nav_menu( array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'el-menu',
                    'fallback_cb'    => false,
                ) );
                ?>
            </nav>
        <?php endif; ?>
    </header>
    <?php
}

/**
 * Default theme footer (displayed when no Beaver Themer footer exists)
 */
function el_theme_default_footer() {
    ?>
    <footer class="el-default-footer">
        <p class="site-info">
            &copy; <?php echo esc_html( wp_date( 'Y' ) ); ?>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>.
            <?php esc_html_e( 'All rights reserved.', 'el-theme' ); ?>
        </p>
    </footer>
    <?php
}

/**
 * Use the theme's wrapper for Beaver Themer content layouts.
 *
 * Themer's own template outputs the layout in a plain <div>, so singular,
 * archive and 404 layouts would have no <main> landmark, no skip link target,
 * and the Before/After Content part hooks would never fire.
 */
add_filter( 'fl_theme_builder_template_include', 'el_theme_themer_template' );

function el_theme_themer_template( $template ) {
    $theme_template = locate_template( 'themer-content.php' );

    return $theme_template ? $theme_template : $template;
}

/**
 * Register part hooks for Beaver Themer
 * This allows Part layout types to be rendered at various locations in the theme
 */
add_filter( 'fl_theme_builder_part_hooks', 'el_theme_register_part_hooks' );

function el_theme_register_part_hooks() {
    return array(
        array(
            'label' => 'Header',
            'hooks' => array(
                'el_theme_before_header' => 'Before Header',
                'el_theme_after_header'  => 'After Header',
            )
        ),
        array(
            'label' => 'Content',
            'hooks' => array(
                'el_theme_before_content' => 'Before Content',
                'el_theme_after_content'  => 'After Content',
            )
        ),
        array(
            'label' => 'Footer',
            'hooks' => array(
                'el_theme_before_footer' => 'Before Footer',
                'el_theme_after_footer'  => 'After Footer',
            )
        )
    );
}
