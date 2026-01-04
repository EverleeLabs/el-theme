<?php
/**
 * EL Theme Functions
 *
 * @package EL_Theme
 */

/**
 * Declare Beaver Themer support for headers, footers, and parts
 */
add_action( 'after_setup_theme', 'el_theme_header_footer_support' );

function el_theme_header_footer_support() {
    add_theme_support( 'fl-theme-builder-headers' );
    add_theme_support( 'fl-theme-builder-footers' );
    add_theme_support( 'fl-theme-builder-parts' );
    
    // Register navigation menus
    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'el-theme' ),
    ) );
    
    // Remove block patterns support
    remove_theme_support( 'core-block-patterns' );
}

/**
 * Render Beaver Themer headers and footers
 * Removes default theme header/footer when Beaver Themer layouts exist
 */
add_action( 'wp', 'el_theme_header_footer_render' );

function el_theme_header_footer_render() {
    // Check if Beaver Themer is active
    if ( ! class_exists( 'FLThemeBuilderLayoutData' ) ) {
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
    ?>
    <div class="site-branding">
        <h1 class="site-title">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                <?php bloginfo( 'name' ); ?>
            </a>
        </h1>
        <?php
        $description = get_bloginfo( 'description', 'display' );
        if ( $description || is_customize_preview() ) :
            ?>
            <p class="site-description"><?php echo $description; ?></p>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Default theme footer (displayed when no Beaver Themer footer exists)
 */
function el_theme_default_footer() {
    ?>
    <div class="site-info">
        <p>&copy; <?php echo date( 'Y' ); ?> <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>. All rights reserved.</p>
    </div>
    <?php
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

