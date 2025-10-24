<?php
defined('ABSPATH') || exit;
get_header();

$raw  = get_field('analysis_result_json');
$data = json_decode((string)$raw, true) ?: [];

function jget($a, $p, $d=null){ foreach(explode('.', $p) as $k){ if(!is_array($a) || !array_key_exists($k,$a)) return $d; $a=$a[$k]; } return $a; }
function pct01($v){ return is_numeric($v) ? round($v*100) : null; }

$esg     = jget($data,'agent_specific.esg_sustainability',[]); // RADICE ESG CORRETTA [attached_file:6]
$ass     = $esg['esg_overall_assessment'] ?? [];                                                // [attached_file:6]
$green   = $esg['greenwashing_assessment'] ?? ( $data['greenwashing_assessment'] ?? [] );       // [attached_file:6]
$cert    = $esg['certification_communication_strategy'] ?? [];                                   // [attached_file:6]
$story   = $esg['storytelling_opportunities'] ?? [];                                             // [attached_file:6]
$comp    = $esg['competitive_esg_positioning'] ?? [];                                           // [attached_file:6]
$flags   = $data['flags'] ?? [];                                                                 // [attached_file:4]
$ins     = $data['insights'] ?? [];                                                              // [attached_file:4]
$opps    = $data['opportunities'] ?? [];                                                         // [attached_file:6]
$gaps    = $data['gaps'] ?? [];                                                                  // [attached_file:6]
$meta    = $data['meta'] ?? [];                                                                  // [attached_file:5]

$scores = [
    'total' => pct01($ass['total_esg_communication_score'] ?? null),
    'E'     => pct01($ass['environmental_communication'] ?? null),
    'S'     => pct01($ass['social_communication'] ?? null),
    'G'     => pct01($ass['governance_communication'] ?? null),
    'gap'   => pct01(jget($ass,'communication_gap.actions_vs_communication_gap')),
    'gap_severity' => jget($ass,'communication_gap.gap_severity'),
];

$practices = [
    'total' => pct01($ass['total_esg_practices_score'] ?? null),
    'E'     => pct01($ass['environmental_practices'] ?? null),
    'S'     => pct01($ass['social_practices'] ?? null),
    'G'     => pct01($ass['governance_practices'] ?? null),
];

$title = get_the_title();
$cover = get_field('cover_image') ?: get_the_post_thumbnail_url(get_the_ID(),'full');

?>
<main id="esg_landing">
    <?php get_template_part('components/esg/hero-cover', null, compact('title','cover','flags')); ?>
    <?php get_template_part('components/esg/summary-blue', null, ['scores'=>$scores,'ins'=>$ins,'oppsN'=>count($opps),'gapsN'=>count($gaps)]); ?>
    <?php get_template_part('components/esg/quote-black', null, ['ins'=>$ins]); ?>


    <?php // get_template_part('components/esg/kpi-grid', null, ['scores'=>$scores,'flags'=>$flags,'oppsN'=>count($opps),'gapsN'=>count($gaps)]); ?>
    <?php
    $esg  = jget($data,'agent_specific.esg_sustainability',[]);
    $ass  = $esg['esg_overall_assessment'] ?? [];
    $cert = $esg['certification_communication_strategy'] ?? [];

    get_template_part('components/esg/kpi-grid', null, [
        'scores' => $scores,       // come già calcolati (percentuali 0-100)
        'oppsN'  => count($data['opportunities'] ?? []),
        'ass'    => $ass,
        'cert'   => $cert,
    ]); ?>

    <?php if (!empty($cert)): ?>
        <?php get_template_part('components/esg/certifications', null, ['cert'=>$cert]); ?>
    <?php endif; ?>


    <?php if (!empty($ass['maturity_level']) || isset($ass['sector_benchmark_communication'])): ?>
        <?php get_template_part('components/esg/benchmark-maturity', null, ['ass'=>$ass,'comp'=>$comp]); ?>
    <?php endif; ?>

    <?php if ($practices['total'] !== null): ?>
        <?php get_template_part('components/esg/practices-vs-communication', null, ['practices'=>$practices,'scores'=>$scores]); ?>
    <?php endif; ?>

    <?php if (!empty($ins['strengths']) || !empty($ins['weaknesses'])): ?>
        <?php get_template_part('components/esg/pros-cons', null, ['ins'=>$ins]); ?>
    <?php endif; ?>

    <?php if (!empty($ins['quick_actions'])): ?>
        <?php get_template_part('components/esg/quick-actions', null, ['items'=>$ins['quick_actions']]); ?>
    <?php endif; ?>

    <?php if (!empty($green)): ?>
        <?php get_template_part('components/esg/greenwashing-assessment', null, ['green'=>$green]); ?>
    <?php endif; ?>

    <?php if (!empty($story)): ?>
        <?php get_template_part('components/esg/storytelling', null, ['story'=>$story]); ?>
    <?php endif; ?>

    <?php if (!empty($opps)): ?>
        <?php get_template_part('components/esg/opportunities', null, ['items'=>$opps]); ?>
    <?php endif; ?>

    <?php if (!empty($gaps)): ?>
        <?php get_template_part('components/esg/gaps', null, ['items'=>$gaps]); ?>
    <?php endif; ?>

    <?php // Pillars detail con estrazione robusta dai sottoalberi ?>
    <?php
    $pillars = [
        'E' => $esg['environmental_communication'] ?? [],
        'S' => $esg['social_responsibility_communication'] ?? [],
        'G' => $esg['governance_transparency_communication'] ?? [],
    ];
    ?>
    <?php if (!empty($pillars['E']) || !empty($pillars['S']) || !empty($pillars['G'])): ?>
        <?php get_template_part('components/esg/pillars-detail', null, ['pillars'=>$pillars]); ?>
    <?php endif; ?>

    <?php
    $methodology = [
        'confidence' => $data['confidence_score'] ?? null,
        'quality'    => $data['quality_score'] ?? null,
        'notes'      => $meta['schema_notes'] ?? '',
        'sources'    => $meta['source_urls'] ?? [],
    ];
    ?>
    <?php if (!empty($methodology['confidence']) || !empty($methodology['quality']) || !empty($methodology['notes']) || !empty($methodology['sources'])): ?>
        <?php get_template_part('components/esg/methodology-notes', null, ['methodology'=>$methodology]); ?>
    <?php endif; ?>
</main>
<?php get_footer();
