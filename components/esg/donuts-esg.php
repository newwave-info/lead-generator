<?php
$args = wp_parse_args($args ?? [], ['data' => []]);
$d = $args['data'];
$ass = $d['agent_specific']['esg_sustainability']['esg_overall_assessment'] ?? null;
if (!$ass) return;

$vals = [
    'Comunicazione' => (float) ($ass['total_esg_communication_score'] ?? 0) * 100,
    'Ambientale'    => (float) ($ass['environmental_communication'] ?? 0) * 100,
    'Sociale'       => (float) ($ass['social_communication'] ?? 0) * 100,
    'Governance'    => (float) ($ass['governance_communication'] ?? 0) * 100,
];

?>
<section class="container py-5">
    <div class="row g-4 align-items-stretch">
        <?php foreach ($vals as $label => $val): ?>
            <div class="col-6 col-md-3">
                <div class="card h-100 text-center p-4">
                    <div class="ratio ratio-1x1">
                        <svg viewBox="0 0 36 36" role="img" aria-label="<?php echo esc_attr($label); ?>">
                            <path d="M18 2 a 16 16 0 1 1 0 32 a 16 16 0 1 1 0 -32"
                                  fill="none" stroke="#eee" stroke-width="4"/>
                            <path d="M18 2 a 16 16 0 1 1 0 32 a 16 16 0 1 1 0 -32"
                                  fill="none" stroke="#00a37a" stroke-width="4"
                                  stroke-dasharray="<?php echo esc_attr(round($val)); ?>,100"/>
                        </svg>
                    </div>
                    <h3 class="h6 mt-3 mb-1"><?php echo esc_html($label); ?></h3>
                    <div class="fs-4 fw-semibold"><?php echo esc_html(number_format($val, 0)); ?>%</div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
