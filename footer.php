<footer id="footer" role="contentinfo">
    <?php if( have_rows('footer_cta','option') && !is_page_template( 'tmpl-contacts.php' ) ): ?>
        <?php while( have_rows('footer_cta','option') ): the_row();
            $title = get_sub_field('titolo');
            $link = get_sub_field('link');
            if ( !empty($title) || !empty($link) ): ?>
                <section id="footer_cta">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col">
                                <?php if (!empty($title)): ?>
                                    <div class="cta-title"><?php echo wp_kses_post($title); ?></div>
                                <?php endif; ?>
                                <?php
                                if( $link ):
                                    $link_url = $link['url'];
                                    $link_title = $link['title'];
                                    $link_target = $link['target'] ? $link['target'] : '_self'; ?>
                                    <a class="btn btn-dark mt-4" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><span><?php echo esc_html( $link_title ); ?></span></a>
                                <?php endif; ?>
                            </div><!--col-->
                        </div><!--row-->
                    </div><!--container-->
                </section><!--footer_cta-->
            <?php endif; ?>
        <?php endwhile; ?>
    <?php endif; ?>

    <section id="footer_main">
        <div class="container-fluid">
            <div class="row gy-5">
                <?php
                $title = 'Perspect srl';
                $text = 'In Perspect fondiamo dati e idee, immaginazione e tecnologia per dare forma alla vostra visione del domani, e tradurla in progetti dinamici che sanno parlare alle persone, oggi.'; ?>
                <div class="col-md-3 me-md-auto">
                    <?php if (!empty($title)): ?>
                        <div class="footer-title"><?php echo wp_kses_post($title); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($text)): ?>
                        <div class="footer-text"><?php echo wp_kses_post($text); ?></div>
                    <?php endif; ?>
                </div><!--col-->

                <?php for($i=1;$i<=3;$i++):
                    if ( has_nav_menu('footer_'.$i) ) : ?>
                        <div class="col-6 col-md-2">
                            <div class="footer-nav-title"><?php echo wp_get_nav_menu_name('footer_'.$i); ?></div>
                            <?php wp_nav_menu( array(
                                'theme_location'  => 'footer_'.$i,
                                'depth'           => 1,
                                'container'       => 'nav',
                                'container_class' => 'footer-nav',
                                'container_id'    => '',
                                'menu_class'      => '',
                            ) ); ?>
                        </div><!--col-->
                    <?php endif; ?>
                <?php endfor; ?>
                <div class="col-6 col-md-2 col-xl-1">
                    <div class="footer-nav-title">Follow us</div>
                    <a href="https://www.linkedin.com/company/perspect-branding/" target="_blank" rel="noopener noreferrer">LinkedIn</a><br><a href="https://www.instagram.com/perspect.it/" target="_blank" rel="noopener noreferrer">Instagram</a>
                </div><!--col-->
            </div><!--row-->
        </div><!--container-->
    </section><!--footer_main-->
    <section id="footer_colophon">
        <div class="container-fluid">
            <div class="row gy-2 justify-content-between">
                <div class="col-md">
                    <div>© <?php echo date("Y"); ?> Perspect srl - P. Iva 04224740276 - Via del Gazzato 20/10 Venezia Mestre</div>
                </div><!--col-->
                <div class="col-md text-md-end">
                    <a href="<?php the_permalink(3); ?>">Privacy Policy</a>&emsp;<a href="<?php the_permalink(7); ?>">Cookie Policy</a>
                </div><!--col-->
            </div><!--row-->
        </div><!--container-->
    </section><!--footer_colophon-->
</footer>

<?php wp_footer(); ?>

<!-- ================================================== -->
<script type="text/javascript" src="<?php echo get_template_directory_uri() ?>/common/js/core.js"></script>
<script type="text/javascript" src="<?php echo get_template_directory_uri() ?>/common/js/custom.js"></script>
<script type="text/javascript">
    jQuery(window).on("load", function() {
        start();
    });
</script>
</body>

</html>