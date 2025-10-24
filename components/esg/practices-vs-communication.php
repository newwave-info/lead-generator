<?php
$args = wp_parse_args($args ?? [], ['practices'=>[],'scores'=>[]]);
$pract = $args['practices'];
$comm = $args['scores'];
$pillars = [
    'E' => ['label'=>'Ambientale','practices'=>$pract['E'],'communication'=>$comm['E']],
    'S' => ['label'=>'Sociale','practices'=>$pract['S'],'communication'=>$comm['S']],
    'G' => ['label'=>'Governance','practices'=>$pract['G'],'communication'=>$comm['G']]
];
?>

<section class="container my-5">
    <div class="card p-4">
        <h3 class="h5 mb-4">Pratiche vs Comunicazione</h3>
        <div class="row g-4">
            <?php foreach ($pillars as $p): if ($p['practices'] === null || $p['communication'] === null) continue; ?>
                <div class="col-md-4">
                    <h4 class="h6"><?php echo esc_html($p['label']); ?></h4>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small">
                            <span>Pratiche</span>
                            <span><?php echo esc_html($p['practices']); ?>%</span>
                        </div>
                        <div class="progress mb-1" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: <?php echo esc_attr($p['practices']); ?>%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small">
                            <span>Comunicazione</span>
                            <span><?php echo esc_html($p['communication']); ?>%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" style="width: <?php echo esc_attr($p['communication']); ?>%"></div>
                        </div>
                    </div>
                    <?php $delta = $p['practices'] - $p['communication']; ?>
                    <div class="small <?php echo $delta > 0 ? 'text-warning' : 'text-success'; ?>">
                        Gap: <?php echo $delta > 0 ? '+' : ''; ?><?php echo esc_html($delta); ?>%
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
