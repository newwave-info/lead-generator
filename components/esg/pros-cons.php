<?php
$args = wp_parse_args($args ?? [], ['ins'=>[]]);
$pros = array_filter($args['ins']['strengths'] ?? []);
$cons = array_filter($args['ins']['weaknesses'] ?? []);
if (!$pros && !$cons) return;
?>
<section class="container my-5">
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100 p-4">
                <h3 class="h5 mb-3">Punti di forza</h3>
                <ul class="mb-0">
                    <?php foreach ($pros as $p): ?><li><?php echo esc_html($p); ?></li><?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 p-4">
                <h3 class="h5 mb-3">Criticità</h3>
                <ul class="mb-0">
                    <?php foreach ($cons as $c): ?><li><?php echo esc_html($c); ?></li><?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</section>
