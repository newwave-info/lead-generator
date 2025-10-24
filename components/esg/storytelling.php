<?php
$args = wp_parse_args($args ?? [], ['story'=>[]]);
$st = $args['story']['sustainability_narrative_potential'] ?? [];
$stories = $st['potential_stories'] ?? [];
$gaps    = $st['storytelling_gaps'] ?? [];
$prio    = $args['story']['content_creation_priorities'] ?? [];
?>
<?php if ($stories || $prio || $gaps): ?>
<section class="container my-5">
  <div class="card p-4">
    <h3 class="h5 mb-3">Opportunità di Storytelling</h3>
    <div class="row g-3">
      <?php foreach ($stories as $s): ?>
        <div class="col-md-6">
          <div class="border rounded p-3 h-100">
            <div class="fw-semibold"><?php echo esc_html($s['title'] ?? $s['story'] ?? 'Storia'); ?></div>
            <?php if (!empty($s['description'])): ?><p class="small mb-2"><?php echo esc_html($s['description']); ?></p><?php endif; ?>
            <div class="small">
              <?php if (!empty($s['impact_potential'])): ?><span class="badge text-bg-success me-1"><?php echo esc_html(ucfirst($s['impact_potential'])); ?></span><?php endif; ?>
              <?php if (!empty($s['effort'])): ?><span class="badge text-bg-light"><?php echo esc_html(ucfirst($s['effort'])); ?></span><?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if ($prio): ?>
      <hr class="my-4" />
      <h4 class="h6">Priorità contenuti</h4>
      <ul class="list-unstyled mb-0">
        <?php foreach ($prio as $p): ?>
          <li class="py-1">
            <span class="fw-semibold me-2"><?php echo esc_html($p['title'] ?? $p['content_type'] ?? ''); ?></span>
            <?php if (!empty($p['impact'])): ?><span class="badge text-bg-success me-1"><?php echo esc_html(ucfirst($p['impact'])); ?></span><?php endif; ?>
            <?php if (!empty($p['effort'])): ?><span class="badge text-bg-warning me-1"><?php echo esc_html(ucfirst($p['effort'])); ?></span><?php endif; ?>
            <?php if (isset($p['investment_min'],$p['investment_max'])): ?><span class="text-muted small">€<?php echo number_format((int)$p['investment_min']); ?>–€<?php echo number_format((int)$p['investment_max']); ?></span><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>
