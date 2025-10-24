<?php
$args = wp_parse_args($args ?? [], ['ass'=>[],'comp'=>[]]);
$maturity = $args['ass']['maturity_level'] ?? '';
$commMat = $args['ass']['communication_maturity'] ?? '';
$benchmark = pct($args['ass']['sector_benchmark_communication'] ?? null);
$position = $args['comp']['competitive_position'] ?? '';
$potential = $args['ass']['improvement_potential'] ?? '';

function pct($v) { return is_numeric($v) ? round($v * 100) : null; }
?>

<section class="container my-5">
    <div class="card p-4">
        <h3 class="h5 mb-4">Benchmark e Maturità</h3>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="d-flex justify-content-between mb-2">
                    <span>Maturità comunicazione</span>
                    <span class="badge text-bg-primary"><?php echo esc_html(ucfirst($maturity)); ?></span>
                </div>
                <?php if ($benchmark !== null): ?>
                    <div class="progress mb-3">
                        <div class="progress-bar" style="width: <?php echo esc_attr($benchmark); ?>%"></div>
                    </div>
                    <small class="text-muted">vs. media settore: <?php echo esc_html($benchmark); ?>%</small>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <?php if ($position): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Posizione competitiva</span>
                        <span class="badge text-bg-secondary"><?php echo esc_html(ucfirst($position)); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($potential): ?>
                    <div class="d-flex justify-content-between">
                        <span>Potenziale miglioramento</span>
                        <span class="badge text-bg-success"><?php echo esc_html(ucfirst($potential)); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
