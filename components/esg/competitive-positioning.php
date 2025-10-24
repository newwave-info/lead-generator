<?php
$args = wp_parse_args($args ?? [], ['comp'=>[]]);
$c = $args['comp'];
$vs = isset($c['vs_sector_avg_communication']) ? round($c['vs_sector_avg_communication']*100) : null;
?>
<?php if (!empty($c)): ?>
<section class="container my-5">
  <div class="card p-4">
    <h3 class="h5 mb-3">Posizionamento competitivo</h3>
    <?php if ($vs !== null): ?><p class="mb-3">Scostamento vs media settore: <?php echo $vs>0?'+':''; echo $vs; ?>%</p><?php endif; ?>
    <div class="row g-4">
      <div class="col-md-6">
        <?php if (!empty($c['competitive_advantages_communication'])): ?>
          <h4 class="h6 text-success">Vantaggi</h4>
          <ul><?php foreach ($c['competitive_advantages_communication'] as $it){ echo '<li>'.esc_html(is_array($it)?($it['advantage']??implode(' ',$it)):$it).'</li>'; } ?></ul>
        <?php endif; ?>
      </div>
      <div class="col-md-6">
        <?php if (!empty($c['competitive_gaps_communication'])): ?>
          <h4 class="h6 text-warning">Gap</h4>
          <ul><?php foreach ($c['competitive_gaps_communication'] as $it){ echo '<li>'.esc_html(is_array($it)?($it['gap']??implode(' ',$it)):$it).'</li>'; } ?></ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>
