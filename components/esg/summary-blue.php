<?php
$args = wp_parse_args($args ?? [], ['scores'=>[], 'ins'=>[], 'oppsN'=>0, 'gapsN'=>0]);
$headline = $args['ins']['core_promise'] ?? ($args['ins']['plain'] ?? '');
?>
<section class="esg-blue">
    <div class="container">
        <div class="blue-wrapper">
            <div class="row position-relative">
                <div class="col-lg-7">
                    <p class="lead fw-medium"><?php echo esc_html($headline); ?></p>
                </div>
            </div>
            <div class="row align-items-end gy-4 position-relative">
                <div class="col-lg-5">
                    <div class="row gy-5">
                        <div class="col-6">
                            <div class="h1 mb-0"><?php echo esc_html($args['scores']['total']); ?>%</div>
                            <div class="small opacity-75">Comunicazione ESG</div>
                        </div>
                        <div class="col-6">
                            <div class="h1 mb-0"><?php echo esc_html($args['scores']['gap']); ?>%</div>
                            <div class="small opacity-75">Gap azioni‑comunicazione</div>
                        </div>
                        <div class="col-4">
                            <div class="h3 mb-0"><?php echo esc_html($args['scores']['E']); ?>%</div>
                            <div class="small">Ambientale</div>
                        </div>
                        <div class="col-4">
                            <div class="h3 mb-0"><?php echo esc_html($args['scores']['S']); ?>%</div>
                            <div class="small">Sociale</div>
                        </div>
                        <div class="col-4">
                            <div class="h3 mb-0"><?php echo esc_html($args['scores']['G']); ?>%</div>
                            <div class="small">Governance</div>
                        </div>
                    </div>
                </div>
                <div class="col-auto me-auto order-lg-first">
                    <div class="mt-4 d-flex gap-3">
                        <span class="badge bg-dark-subtle text-dark">Opportunità: <?php echo (int)$args['oppsN']; ?></span>
                        <span class="badge bg-dark-subtle text-dark">Gap: <?php echo (int)$args['gapsN']; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
