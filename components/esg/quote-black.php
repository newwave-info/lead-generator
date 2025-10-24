<?php
$args = wp_parse_args($args ?? [], ['ins'=>[]]);
$txt  = $args['ins']['executive_summary'] ?? '';
if (!$txt && !empty($args['ins']['markdown'])) {
    $txt = wp_strip_all_tags(wp_trim_words((string)$args['ins']['markdown'], 60, '…'));
}
if (!$txt) return;
?>
<section class="container">
    <div class="row">
        <div class="col">
            <div class="small text-muted">02</div>
            <div class="title fw-medium"><?php the_title(); ?></div>
        </div>
        <div class="col-md-8 ms-auto">
            <div class="card bg-black text-white p-5">
                <div class="row">
                    <div class="col-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" width="61" height="62" fill="none" viewBox="0 0 61 62"><rect width="61" height="62" fill="#fff" rx="15"/><path fill="#000" d="M41 40h-8.07c-.68-2.84-1.02-5.491-1.02-7.955C31.91 25.348 34.836 22 40.687 22v4.018c-2.508 0-3.762 1.527-3.762 4.58v1.688H41V40Zm-11.91 0h-8.071C20.339 37.16 20 34.509 20 32.045 20 25.348 22.925 22 28.776 22v4.018c-2.507 0-3.761 1.527-3.761 4.58v1.688h4.075V40Z"/></svg>
                    </div>
                    <div class="col">
                        <div class="text"><?php echo esc_html($txt); ?></div>
                        <div class="small opacity-75 mt-5">Analisi ESG</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
