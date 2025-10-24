<?php get_header(); ?>

<main id="page" role="main">
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="title"><?php _e('404 – Page not found', 'nw'); ?></h1>
                <div class="text"><p><?php echo wp_kses_post( sprintf( __( 'Sorry, the page you’re looking for can’t be found.<br><br>Go back to the <a href="%s">homepage</a> or keep browsing.', 'nw' ), esc_url( home_url( '/' ) ) ) ); ?>
                </p></div>
            </div><!--col-->
        </div><!--row-->
    </div><!--container-->
</main><!--page-->

<?php get_footer(); ?>