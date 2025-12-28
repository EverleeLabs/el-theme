    <?php
    /**
     * Hook: el_theme_before_footer
     * Allows Beaver Themer parts to be inserted before the footer
     */
    do_action( 'el_theme_before_footer' );
    ?>

    <footer class="site-footer" role="contentinfo">
        <?php
        /**
         * Hook: el_theme_footer
         * This is where Beaver Themer footers will be rendered
         * If no Beaver Themer footer exists, the default theme footer will display
         */
        do_action( 'el_theme_footer' );
        ?>
    </footer>

    <?php
    /**
     * Hook: el_theme_after_footer
     * Allows Beaver Themer parts to be inserted after the footer
     */
    do_action( 'el_theme_after_footer' );
    ?>
</div>

<?php wp_footer(); ?>
</body>
</html>

