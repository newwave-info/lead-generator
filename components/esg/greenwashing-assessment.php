<?php
$args = wp_parse_args($args ?? [], ['green'=>[]]);
$g = is_array($args['green']) ? $args['green'] : [];
$risk = $g['greenwashing_risk_level'] ?? null;
$auth = isset($g['authenticity_score']) ? round($g['authenticity_score']*100) : null;
?>
<?php if ($risk): ?>
<section class="container my-5">
  <div class="card p-4">
    <h3 class="h5 mb-3">Assessment Greenwashing</h3>
    <div class="alert alert-<?php echo $risk==='high'?'danger':($risk==='medium'?'warning':'success'); ?>">
      Livello rischio: <?php echo esc_html(ucfirst($risk)); ?><?php if($auth!==null): ?> — Autenticità: <?php echo $auth; ?>%<?php endif; ?>
    </div>
    <?php if (!empty($g['assessment'])): ?><p class="mb-3"><?php echo esc_html($g['assessment']); ?></p><?php endif; ?>
    <div class="row g-4">
      <div class="col-md-6"><?php if (!empty($g['vague_claims_identified'])): ?><h4 class="h6">Claims vaghi</h4><ul><?php foreach($g['vague_claims_identified'] as $it){ echo '<li>'.esc_html($it).'</li>'; } ?></ul><?php endif; ?></div>
      <div class="col-md-6"><?php if (!empty($g['unsubstantiated_statements'])): ?><h4 class="h6">Affermazioni non supportate</h4><ul><?php foreach($g['unsubstantiated_statements'] as $it){ echo '<li>'.esc_html($it).'</li>'; } ?></ul><?php endif; ?></div>
    </div>
  </div>
</section>
<?php endif; ?>
