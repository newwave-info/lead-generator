<?php
$args = wp_parse_args($args ?? [], ['cert'=>[]]);
$c = is_array($args['cert']) ? $args['cert'] : [];
$missing = $c['certifications_missing_high_impact'] ?? [];
$present = $c['certifications_present'] ?? [];
?>
<?php if ($missing || $present): ?>
<section class="container">
    <div class="title fw-medium">Certificazioni</div>
    <?php if ($present): ?>
        <div class="text mt-3">
            <p>Presenti: <?php echo esc_html(implode(', ', $present)); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($missing): ?>
        <div class="accordion" id="certificationsAccordion">
            <?php foreach ($missing as $i => $cert): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading-<?php echo esc_attr($i); ?>">
                        <button class="accordion-button show" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo esc_attr($i); ?>" aria-expanded="true" aria-controls="collapse-<?php echo esc_attr($i); ?>">
                            <?php echo esc_html($cert['certification'] ?? ''); ?>
                        </button>
                    </h2>
                    <div id="collapse-<?php echo esc_attr($i); ?>" class="accordion-collapse collapse show" aria-labelledby="heading-<?php echo esc_attr($i); ?>">
                        <div class="accordion-body">
                            <ul class="list-unstyled mb-2">
                                <li><strong>Rilevanza:</strong> <?php echo isset($cert['relevance_score']) ? round($cert['relevance_score'] * 100) . '%' : 'N/D'; ?></li>
                                <li><strong>Valore comunicativo:</strong> <?php echo esc_html($cert['communication_value'] ?? 'N/D'); ?></li>
                                <li><strong>Investimento stimato:</strong> €<?php echo number_format((int)($cert['investment_min'] ?? 0)); ?> – €<?php echo number_format((int)($cert['investment_max'] ?? 0)); ?></li>
                                <li><strong>Timeline:</strong> <?php echo esc_html($cert['timeline_months'] ?? 'N/D'); ?> mesi</li>
                                <li><strong>Strategia di comunicazione:</strong> <?php echo esc_html($cert['communication_strategy'] ?? 'N/D'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php endif; ?>
