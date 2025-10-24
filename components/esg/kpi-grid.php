<?php
$args = wp_parse_args($args ?? [], [
    'scores' => [], 'flags' => [], 'oppsN' => 0, 'gapsN' => 0,
    'ass' => [], 'comp' => [], 'cert' => []
]);

// Helpers
$vsSector = null;
if (isset($args['ass']['sector_benchmark_communication'])) {
    $vsSector = round(($args['scores']['total'] ?? 0) - round(($args['ass']['sector_benchmark_communication'] ?? 0) * 100));
}
$gap = $args['scores']['gap'] ?? null;
$opps = (int) ($args['oppsN'] ?? 0);
$certMissing = isset($args['cert']['certifications_missing_high_impact'])
    ? count((array) $args['cert']['certifications_missing_high_impact'])
    : 0;

// Tiles (3 + 1)
$tiles = [
    [
        'type' => 'metric',
        'value' => $args['scores']['total'] ?? null,
        'label' => 'Comunicazione ESG',
        'sub'   => $vsSector !== null ? sprintf('Δ vs settore: %s%d%%', $vsSector > 0 ? '+' : '', $vsSector) : null
    ],
    [
        'type' => 'donut',
        'value' => $gap,
        'label' => 'Gap azioni‑comunicazione'
    ],
    [
        'type' => 'metric',
        'value' => $opps,
        'label' => 'Opportunità'
    ],
];
$tileAccent = [
    'type' => 'accent',
    'value' => $certMissing,
    'label' => 'Certificazioni ad alto impatto'
]; ?>

<section class="esg-kpi">
    <div class="container">
        <div class="row align-items-stretch g-3">
            <div class="col-md-4">
                <figure class="figure">
                    <!-- <img class="img-cover" src="https://static.photos/abstract/1200x630" loading="lazy" /> -->
                    <img class="img-cover" src="<?php echo get_template_directory_uri() ?>/common/img/esg-ppl.jpg" loading="lazy" />
                </figure>
            </div>
            <div class="col-md-8">
                <div class="row g-3">
                    <?php foreach (array_slice($tiles, 0, 3) as $t): ?>
                        <?php if ($t['value'] === null || $t['value'] === '') continue; ?>
                        <div class="col-6">
                            <div class="card">
                                <?php if ($t['type'] === 'donut'): ?>
                                    <div></div>
                                <?php else: ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" fill="none" viewBox="0 0 81 81">
                                      <circle cx="40.315" cy="40.315" r="40.315" fill="#fff"/>
                                      <path fill="#000" stroke="#fff" d="M24.275 51.24v4.673c0 .964.789 1.752 1.753 1.752.964 0 1.752-.788 1.752-1.752V51.24c0-.964-.788-1.752-1.752-1.752s-1.753.788-1.753 1.752Zm9.346-4.673v9.346c0 .964.789 1.753 1.752 1.753.964 0 1.753-.79 1.753-1.753v-9.346c0-.964-.789-1.752-1.752-1.752-.964 0-1.753.788-1.753 1.752Zm9.346 2.921v6.425c0 .964.788 1.752 1.752 1.752s1.753-.788 1.753-1.752v-6.425c0-.964-.789-1.753-1.753-1.753-.964 0-1.752.789-1.752 1.753Zm9.346-5.258v11.683c0 .964.788 1.752 1.752 1.752s1.753-.788 1.753-1.752V44.231c0-.964-.79-1.753-1.753-1.753-.964 0-1.752.789-1.752 1.753Zm-28.908-1.395a1.773 1.773 0 0 0 2.114-.287l7.349-7.348c.42-.42 1.098-.42 1.518 0l4.072 4.072c.817.817 1.892 1.372 3.054 1.442a4.55 4.55 0 0 0 3.529-1.331l9.614-9.615v5.017c0 .953.695 1.759 1.577 1.846a1.765 1.765 0 0 0 1.577-.689c.24-.31.35-.707.35-1.098V27.88a4.086 4.086 0 0 0-4.088-4.089h-6.91c-.952 0-1.758.695-1.846 1.577a1.745 1.745 0 0 0 .69 1.578c.309.24.706.35 1.103.35h5.07l-9.614 9.615c-.42.42-1.098.42-1.519 0l-4.071-4.072c-.818-.817-1.898-1.372-3.055-1.442a4.548 4.548 0 0 0-3.528 1.331l-7.418 7.419c-.252.25-.421.584-.444.94-.047.748.303 1.449.876 1.758v-.011Z"/>
                                    </svg>
                                <?php endif; ?>

                                <div>
                                    <?php if ($t['type'] === 'metric'): ?>
                                        <div class="fs-1 fw-semibold"><?php echo esc_html($t['value']); ?><?php echo $t['label']==='Comunicazione ESG' ? '%' : ''; ?></div>
                                        <div class="small text-muted"><?php echo esc_html($t['label']); ?></div>
                                        <?php if (!empty($t['sub'])): ?>
                                            <div class="small opacity-75 mt-1"><?php echo esc_html($t['sub']); ?></div>
                                        <?php endif; ?>
                                    <?php elseif ($t['type'] === 'donut'):
                                        $val = max(0, min(100, (int)$t['value']));
                                        $circ = 2 * M_PI * 16;
                                        $dash = ($val / 100) * $circ;
                                    ?>
                                        <div class="d-flex justify-content-center">
                                            <svg width="200" height="200" viewBox="0 0 36 36" role="img" aria-label="<?php echo esc_attr($t['label']); ?>">
                                                <circle cx="18" cy="18" r="16" fill="none" stroke="#fff" stroke-width="2"/>
                                                <circle cx="18" cy="18" r="16" fill="none" stroke="#22b07d" stroke-width="4"
                                                        stroke-dasharray="<?php echo esc_attr($dash); ?> <?php echo esc_attr($circ); ?>"
                                                        transform="rotate(-90 18 18)"/>
                                            </svg>
                                        </div>
                                        <div class="mt-4">
                                            <div class="fs-3 fw-semibold"><?php echo esc_html($val); ?>%</div>
                                            <div class="small text-muted"><?php echo esc_html($t['label']); ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div><!--row-->
            </div>
        </div>
        <?php if ($tileAccent['value'] !== null): ?>
            <div class="row g-3">
                <div class="col-4 ms-auto">
                    <div class="card esg-accent-tile">
                        <div class="small opacity-75 mb-1">Focus</div>
                        <div class="d-flex align-items-end justify-content-between">
                            <div>
                                <div class="fs-1 fw-semibold"><?php echo esc_html($tileAccent['value']); ?></div>
                                <div class="fs-2 fw-semibold"><?php echo esc_html($tileAccent['label']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

