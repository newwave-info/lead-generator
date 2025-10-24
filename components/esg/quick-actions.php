<?php
$args = wp_parse_args($args ?? [], ['items'=>[]]);
$items = $args['items'];
if (!$items) return;
?>
<section class="container my-5">
    <div class="card p-4">
        <h3 class="h5 mb-3">Azioni rapide</h3>
        <ul class="list-unstyled mb-0">
            <?php foreach ($items as $it): ?>
                <li class="d-flex align-items-start py-2">
                    <span class="me-2 text-success">✔</span>
                    <div>
                        <div class="fw-semibold"><?php echo esc_html($it['title'] ?? (is_string($it) ? $it : '')); ?></div>
                        <?php if (!empty($it['description'])): ?>
                            <div class="small text-muted"><?php echo esc_html($it['description']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($it['effort']) || !empty($it['impact'])): ?>
                            <div class="small mt-1">
                                <?php if (!empty($it['effort'])): ?><span class="badge text-bg-light me-1">Effort: <?php echo esc_html($it['effort']); ?></span><?php endif; ?>
                                <?php if (!empty($it['impact'])): ?><span class="badge text-bg-light">Impatto: <?php echo esc_html($it['impact']); ?></span><?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
