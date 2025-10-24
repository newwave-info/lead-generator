<?php
$args = wp_parse_args($args ?? [], ['data' => []]);
$items = $args['data']['opportunities'] ?? [];
if (!$items) return;

// Ordina per priority (high > medium > low)
$priorityOrder = ['high' => 0, 'medium' => 1, 'low' => 2];
usort($items, function($a, $b) use ($priorityOrder) {
    return ($priorityOrder[$a['priority'] ?? 'low'] ?? 9) <=> ($priorityOrder[$b['priority'] ?? 'low'] ?? 9);
});
?>
<section class="container py-5">
    <h2 class="h3 mb-4">Opportunità</h2>
    <div class="row g-4">
        <?php foreach ($items as $it): ?>
            <div class="col-md-6 col-lg-4">
                <article class="card h-100 p-4">
                    <span class="badge text-bg-dark me-2"><?php echo esc_html(ucfirst($it['priority'] ?? 'n/a')); ?></span>
                    <span class="badge text-bg-success"><?php echo esc_html(ucfirst($it['impact'] ?? '')); ?></span>
                    <h3 class="h5 mt-3"><?php echo esc_html($it['title'] ?? ''); ?></h3>
                    <p class="mb-3"><?php echo esc_html($it['description'] ?? ''); ?></p>
                    <ul class="list-unstyled small mb-3">
                        <li>Timeline: <?php echo esc_html($it['timeline_weeks'] ?? 'n.d.'); ?> settimane</li>
                        <li>Investimento: €<?php echo esc_html(number_format((float) ($it['investment_min'] ?? 0))); ?> – €<?php echo esc_html(number_format((float) ($it['investment_max'] ?? 0))); ?></li>
                    </ul>
                    <?php if (!empty($it['deliverables'])): ?>
                        <details class="mt-auto">
                            <summary>Deliverable</summary>
                            <ul class="mt-2">
                                <?php foreach ($it['deliverables'] as $d): ?>
                                    <li><?php echo esc_html($d); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </details>
                    <?php endif; ?>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
</section>
