<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="site-container">
    <?php
    /**
     * Hook: el_theme_before_header
     * Allows Beaver Themer parts to be inserted before the header
     */
    do_action( 'el_theme_before_header' );
    ?>

    <header class="site-header" role="banner">
        <?php
        /**
         * Hook: el_theme_header
         * This is where Beaver Themer headers will be rendered
         * If no Beaver Themer header exists, the default theme header will display
         */
        do_action( 'el_theme_header' );
        ?>
    </header>

    <?php
    /**
     * Hook: el_theme_after_header
     * Allows Beaver Themer parts to be inserted after the header
     */
    do_action( 'el_theme_after_header' );
    ?>

