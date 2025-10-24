<?php
$args = wp_parse_args($args ?? [], ['items'=>[]]);
$items = $args['items'];
if (!$items) return;

// Ordina per severity > urgency
$sev = ['critical'=>3,'high'=>2,'medium'=>1,'low'=>0];
$urg = ['immediate'=>3,'short'=>2,'medium'=>1,'low'=>0];
usort($items, function($a,$b) use($sev,$urg){
    $sa = $sev[$a['severity'] ?? 'low'] ?? 0;
    $sb = $sev[$b['severity'] ?? 'low'] ?? 0;
    if ($sa !== $sb) return $sb <=> $sa;
    $ua = $urg[$a['urgency'] ?? 'low'] ?? 0;
    $ub = $urg[$b['urgency'] ?? 'low'] ?? 0;
    return $ub <=> $ua;
});
?>
<section class="container my-5">
    <h3 class="h5 mb-3">Gap & Evidenze</h3>
    <div class="accordion" id="esgGaps">
        <?php foreach ($items as $i => $g): ?>
            <div class="accordion-item">
                <h4 class="accordion-header">
                    <button class="accordion-button <?php echo $i ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#gap-<?php echo $i; ?>">
                        <?php echo esc_html($g['title'] ?? 'Gap'); ?>
                        <?php if (!empty($g['severity'])): ?>
                            <span class="badge ms-2 text-bg-danger"><?php echo esc_html(ucfirst($g['severity'])); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($g['urgency'])): ?>
                            <span class="badge ms-2 text-bg-warning"><?php echo esc_html(ucfirst($g['urgency'])); ?></span>
                        <?php endif; ?>
                    </button>
                </h4>
                <div id="gap-<?php echo $i; ?>" class="accordion-collapse collapse <?php echo $i ? '' : 'show'; ?>" data-bs-parent="#esgGaps">
                    <div class="accordion-body">
                        <?php if (!empty($g['description'])): ?><p><?php echo esc_html($g['description']); ?></p><?php endif; ?>
                        <?php if (!empty($g['evidence'])): ?>
                            <ul>
                                <?php foreach ($g['evidence'] as $ev): ?><li><?php echo esc_html($ev); ?></li><?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <?php if (!empty($g['recommendation'])): ?>
                            <p class="mb-0"><span class="badge text-bg-success me-2">Suggerito</span><?php echo esc_html($g['recommendation']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
