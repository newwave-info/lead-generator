<?php
$args = wp_parse_args($args ?? [], ['methodology'=>[]]);
$m = $args['methodology'];
$conf = isset($m['confidence']) ? (is_numeric($m['confidence']) ? $m['confidence'] : round($m['confidence']*100)) : null;
$qual = isset($m['quality']) ? (is_numeric($m['quality']) ? $m['quality'] : round($m['quality']*100)) : null;
?>
<section class="container my-5">
  <div class="card bg-light p-4">
    <h3 class="h6 mb-3">Metodologia e fonti</h3>
    <div class="row g-3">
      <div class="col-md-4">
        <?php if ($conf!==null): ?><div class="small">Affidabilità analisi: <span class="fw-semibold"><?php echo $conf; ?>%</span></div><?php endif; ?>
        <?php if ($qual!==null): ?><div class="small">Qualità dati: <span class="fw-semibold"><?php echo $qual; ?>%</span></div><?php endif; ?>
      </div>
      <div class="col-md-8">
        <?php if (!empty($m['notes'])): ?><p class="small mb-2"><?php echo esc_html($m['notes']); ?></p><?php endif; ?>
        <?php if (!empty($m['sources'])): ?><div class="small"><strong>Fonti:</strong> <?php foreach($m['sources'] as $i=>$u){ echo '<span class="me-1">['.($i+1).']</span>'; } ?></div><?php endif; ?>
      </div>
    </div>
  </div>
</section>
