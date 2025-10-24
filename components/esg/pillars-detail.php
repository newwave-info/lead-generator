<?php
$args = wp_parse_args($args ?? [], ['pillars'=>[]]);
$p = $args['pillars'];
function listify($node, $map){
  $out = [];
  foreach ($map as $label => $path){
    $v = $node; foreach(explode('.', $path) as $k){ $v = is_array($v)&&array_key_exists($k,$v)?$v[$k]:null; }
    if ($v === null) continue;
    if (is_bool($v)) { $out[] = ($v?'✔ ':'✖ ').$label; }
    elseif (is_numeric($v)) { $out[] = "$label: ".(round($v*100)).'%'; }
    elseif (is_string($v) && $v!=='') { $out[] = "$label: $v"; }
  }
  return $out;
}
$E = listify($p['E'] ?? [], [
  'Pagina sostenibilità' => 'sustainability_page_presence',
  'Sezione ambientale'   => 'environmental_section_website',
  'Disclosure CO2'       => 'climate_action_communicated.carbon_footprint_disclosure',
  'Target riduzione'     => 'climate_action_communicated.emission_reduction_targets_stated',
  'Rinnovabili menzionate'=> 'climate_action_communicated.renewable_energy_mentioned',
]);
$S = listify($p['S'] ?? [], [
  'Pagina responsabilità' => 'social_responsibility_page',
  'Welfare comunicato'    => 'employee_welfare_communicated.communication_score',
  'DEI comunicata'        => 'diversity_inclusion_narrative.communication_score',
  'Comunità'              => 'community_engagement_storytelling.communication_score',
]);
$G = listify($p['G'] ?? [], [
  'Pagina governance'     => 'governance_page_presence',
  'Board visibile'        => 'corporate_governance_disclosed.board_structure_visible',
  'Leadership visibile'   => 'corporate_governance_disclosed.leadership_team_showcased',
  'Report sostenibilità'  => 'reporting_transparency.sustainability_report_published',
  'Codice etico'          => 'compliance_ethics_communicated.code_of_ethics_visible',
]);
?>
<?php if ($E || $S || $G): ?>
<section class="container my-5">
  <div class="row g-4">
    <?php foreach ([['Ambientale',$E],['Sociale',$S],['Governance',$G]] as [$label,$list]): if (!$list) continue; ?>
      <div class="col-md-4">
        <div class="card p-4 h-100">
          <h3 class="h6 mb-3"><?php echo esc_html($label); ?></h3>
          <ul class="mb-0"><?php foreach ($list as $li){ echo '<li>'.esc_html($li).'</li>'; } ?></ul>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
