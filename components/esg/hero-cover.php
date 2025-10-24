<?php
$args  = wp_parse_args($args ?? [], ['title'=>'','cover'=>'','flags'=>[]]);
?>
<section class="esg-hero">
    <figure class="figure-bg">
        <!-- <img class="img-cover" src="https://static.photos/nature/1200x630" loading="lazy" /> -->
        <img class="img-cover" src="<?php echo get_template_directory_uri() ?>/common/img/esg-hero.jpg" loading="lazy" />
    </figure>
    <div class="container py-7 position-relative">
        <h1 class="display-4 fw-semibold"><?php echo esc_html($args['title']); ?></h1>
        <div class="d-flex gap-2 mt-3 justify-content-center">
            <?php if (!empty($args['flags']['greenwashing_risk'])): ?>
                <span class="badge text-bg-warning">Rischio greenwashing</span>
            <?php endif; ?>
            <?php if (!empty($args['flags']['sustainability_page_absent'])): ?>
                <span class="badge text-bg-danger">Sustainability page assente</span>
            <?php endif; ?>
            <?php if (!empty($args['flags']['certification_gap_high'])): ?>
                <span class="badge text-bg-secondary">Certificazioni mancanti</span>
            <?php endif; ?>
        </div>
    </div>
    <a href="#" class="esg-hero-link"><span>Highlights</span><svg xmlns="http://www.w3.org/2000/svg" width="52" height="52" fill="none" viewBox="0 0 52 52"><circle cx="25.734" cy="25.734" r="25.734" fill="#fff"/><path fill="#000" fill-rule="evenodd" d="M25.187 16.429h1.79v15.97l6.02-5.86 1.249 1.28-8.173 7.957-7.972-7.972 1.266-1.266 5.82 5.82v-15.93Z" clip-rule="evenodd"/></svg></a>
</section>
