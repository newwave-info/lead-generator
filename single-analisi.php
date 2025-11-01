<?php
get_header();

$helpers_path = get_theme_file_path('inc/company-analysis-helpers.php');
if (file_exists($helpers_path)) {
    require_once $helpers_path;
}

$placeholder_text = __('Dato non disponibile.', 'psip');

$summary = psip_theme_normalize_scalar(get_field('riassunto'));
$summary_html = $summary !== '' ? wp_kses_post(wpautop($summary)) : '';

$deep_research_raw = get_field('analisy_perplexity_deep_research');
$deep_research_html = psip_theme_format_markdown_bold($deep_research_raw);

$analysis_review_raw = get_field('revisione_analisi_completa');
$analysis_review_html = psip_theme_format_markdown_bold($analysis_review_raw);

$strengths = psip_theme_normalize_scalar(get_field('punti_di_forza'));
$weaknesses = psip_theme_normalize_scalar(get_field('punti_di_debolezza'));
$opportunities = psip_theme_normalize_scalar(get_field('opportunita'));
$quick_actions = psip_theme_normalize_scalar(get_field('azioni_rapide'));

$strengths_html = psip_theme_format_list_text($strengths);
$weaknesses_html = psip_theme_format_list_text($weaknesses);
$opportunities_html = psip_theme_format_list_text($opportunities);
$quick_actions_html = psip_theme_format_list_text($quick_actions);

$count_strengths = get_field('numero_punti_di_forza');
$count_weaknesses = get_field('numero_punti_di_debolezza');
$count_opportunities = get_field('numero_opportunita');
$count_quick_actions = get_field('numero_azioni_rapide');

$format_count = static function ($value) {
    return ($value !== null && $value !== '' && is_numeric($value)) ? (string) (int) $value : '—';
};

$count_strengths_display = $format_count($count_strengths);
$count_weaknesses_display = $format_count($count_weaknesses);
$count_opportunities_display = $format_count($count_opportunities);
$count_quick_actions_display = $format_count($count_quick_actions);

$quality_score_raw = get_field('voto_qualita_analisi');
$quality_score_normalized = psip_theme_normalize_scalar($quality_score_raw);
$quality_score_display = ($quality_score_normalized !== '') ? $quality_score_normalized : '—';

$analysis_updated_display = get_the_modified_date(get_option('date_format'));
$analysis_updated_label = $analysis_updated_display
    ? sprintf(__('Aggiornato il %s', 'psip'), $analysis_updated_display)
    : null;

$structured_raw = get_field('json_dati_strutturati');
$structured_insights = function_exists('psip_theme_prepare_structured_insights')
    ? psip_theme_prepare_structured_insights($structured_raw)
    : ['widgets' => [], 'metrics' => [], 'chips' => [], 'collections' => [], 'tables' => []];

$highlight_widgets = [];
if (!empty($structured_insights['widgets'])) {
    $highlight_widgets = array_slice($structured_insights['widgets'], 0, 3);
    $structured_insights['widgets'] = array_slice($structured_insights['widgets'], 3);
}

$structured_has_body = !empty($structured_insights['widgets'])
    || !empty($structured_insights['metrics'])
    || !empty($structured_insights['chips'])
    || !empty($structured_insights['collections'])
    || !empty($structured_insights['tables']);
    
$kpi_cards = [
    [
        'label' => __('Quality score', 'psip'),
        'value' => $quality_score_display,
        'hint' => $analysis_updated_label,
        'modifier' => 'is-accent',
    ],
    [
        'label' => __('Punti di forza', 'psip'),
        'value' => $count_strengths_display,
        'hint' => null,
        'modifier' => '',
    ],
    [
        'label' => __('Punti di debolezza', 'psip'),
        'value' => $count_weaknesses_display,
        'hint' => null,
        'modifier' => '',
    ],
    [
        'label' => __('Opportunità', 'psip'),
        'value' => $count_opportunities_display,
        'hint' => null,
        'modifier' => '',
    ],
    [
        'label' => __('Azioni rapide', 'psip'),
        'value' => $count_quick_actions_display,
        'hint' => null,
        'modifier' => '',
    ],
];
?>

<main id="single_company" role="main">

    <section id="company_hero">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-12 col-lg-7">
                    <h1 class="company-title"><?php the_title(); ?></h1>
                </div>
            </div>
        </div>
    </section><!--company_hero-->

    <section id="company_content">
        <div class="container-fluid">
            <div class="analysis-suite__panel analysis-suite__panel--single">
                <header class="analysis-suite__panel-header">
                    <div class="analysis-suite__panel-heading">
                        <span class="analysis-suite__panel-tag"><?php esc_html_e('Analisi verticale', 'psip'); ?></span>
                        <h3 class="analysis-suite__panel-title"><?php the_title(); ?></h3>
                    </div>
                    <?php if ($analysis_updated_label): ?>
                        <div class="analysis-suite__panel-meta">
                            <span class="analysis-suite__status-chip is-completed">
                                <?php echo esc_html($analysis_updated_label); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </header>

                <div class="analysis-suite__kpi-section">
                    <div class="analysis-suite__kpi-grid">
                        <?php foreach ($kpi_cards as $card): ?>
                            <div class="analysis-suite__kpi-card <?php echo esc_attr($card['modifier']); ?>">
                                <span class="analysis-suite__kpi-label"><?php echo esc_html($card['label']); ?></span>
                                <span class="analysis-suite__kpi-value"><?php echo esc_html((string) $card['value']); ?></span>
                                <?php if (!empty($card['hint'])): ?>
                                    <span class="analysis-suite__kpi-hint"><?php echo esc_html($card['hint']); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!empty($highlight_widgets)): ?>
                        <div class="analysis-suite__kpi-widgets">
                            <?php foreach ($highlight_widgets as $widget): ?>
                                <div class="analysis-suite__kpi-widget">
                                    <span class="analysis-suite__kpi-widget-value"><?php echo esc_html($widget['value']); ?></span>
                                    <?php if (!empty($widget['label'])): ?>
                                        <span class="analysis-suite__kpi-widget-label"><?php echo esc_html($widget['label']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($widget['source'])): ?>
                                        <span class="analysis-suite__kpi-widget-source"><?php echo esc_html($widget['source']); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="analysis-suite__panel-body">
                    <div class="analysis-suite__column analysis-suite__column--narrative">
                        <div class="analysis-suite__card">
                            <h4 class="analysis-suite__card-title"><?php esc_html_e('Riassunto', 'psip'); ?></h4>
                            <div class="analysis-suite__card-content">
                                <?php echo $summary_html !== ''
                                    ? $summary_html
                                    : wp_kses_post(wpautop($placeholder_text)); ?>
                            </div>
                        </div>
                        <div class="analysis-suite__card">
                            <h4 class="analysis-suite__card-title"><?php esc_html_e('Analisi approfondita', 'psip'); ?></h4>
                            <div class="analysis-suite__card-content">
                                <?php echo $deep_research_html !== ''
                                    ? $deep_research_html
                                    : wp_kses_post(wpautop($placeholder_text)); ?>
                            </div>
                        </div>
                        <div class="analysis-suite__card">
                            <h4 class="analysis-suite__card-title"><?php esc_html_e("Revisione dell'analisi", 'psip'); ?></h4>
                            <div class="analysis-suite__card-content">
                                <?php echo $analysis_review_html !== ''
                                    ? $analysis_review_html
                                    : wp_kses_post(wpautop($placeholder_text)); ?>
                            </div>
                        </div>
                    </div>
                    <div class="analysis-suite__column analysis-suite__column--stack">
                        <div class="analysis-suite__card analysis-suite__card--list">
                            <div class="analysis-suite__card-header">
                                <h4 class="analysis-suite__card-title"><?php esc_html_e('Punti di forza', 'psip'); ?></h4>
                                <span class="analysis-suite__card-badge"><?php echo esc_html($count_strengths_display); ?></span>
                            </div>
                            <div class="analysis-suite__card-content">
                                <?php echo $strengths_html !== ''
                                    ? wp_kses_post(wpautop($strengths_html))
                                    : wp_kses_post(wpautop($placeholder_text)); ?>
                            </div>
                        </div>
                        <div class="analysis-suite__card analysis-suite__card--list">
                            <div class="analysis-suite__card-header">
                                <h4 class="analysis-suite__card-title"><?php esc_html_e('Punti di debolezza', 'psip'); ?></h4>
                                <span class="analysis-suite__card-badge"><?php echo esc_html($count_weaknesses_display); ?></span>
                            </div>
                            <div class="analysis-suite__card-content">
                                <?php echo $weaknesses_html !== ''
                                    ? wp_kses_post(wpautop($weaknesses_html))
                                    : wp_kses_post(wpautop($placeholder_text)); ?>
                            </div>
                        </div>
                        <div class="analysis-suite__card analysis-suite__card--list">
                            <div class="analysis-suite__card-header">
                                <h4 class="analysis-suite__card-title"><?php esc_html_e('Opportunità', 'psip'); ?></h4>
                                <span class="analysis-suite__card-badge"><?php echo esc_html($count_opportunities_display); ?></span>
                            </div>
                            <div class="analysis-suite__card-content">
                                <?php echo $opportunities_html !== ''
                                    ? wp_kses_post(wpautop($opportunities_html))
                                    : wp_kses_post(wpautop($placeholder_text)); ?>
                            </div>
                        </div>
                        <div class="analysis-suite__card analysis-suite__card--list">
                            <div class="analysis-suite__card-header">
                                <h4 class="analysis-suite__card-title"><?php esc_html_e('Azioni rapide', 'psip'); ?></h4>
                                <span class="analysis-suite__card-badge"><?php echo esc_html($count_quick_actions_display); ?></span>
                            </div>
                            <div class="analysis-suite__card-content">
                                <?php echo $quick_actions_html !== ''
                                    ? wp_kses_post(wpautop($quick_actions_html))
                                    : wp_kses_post(wpautop($placeholder_text)); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($structured_has_body): ?>
                    <div class="analysis-suite__structured">
                        <div class="analysis-suite__structured-header">
                            <h4 class="analysis-suite__structured-title"><?php esc_html_e('Dati strutturati', 'psip'); ?></h4>
                            <p class="analysis-suite__structured-subtitle"><?php esc_html_e('Vista sintetica dei dati eterogenei raccolti per questa analisi.', 'psip'); ?></p>
                        </div>

                        <?php if (!empty($structured_insights['widgets'])): ?>
                            <div class="analysis-suite__structured-widgets">
                                <?php foreach ($structured_insights['widgets'] as $widget): ?>
                                    <div class="analysis-suite__structured-widget">
                                        <div class="analysis-suite__structured-widget-value"><?php echo esc_html($widget['value']); ?></div>
                                        <?php if (!empty($widget['label'])): ?>
                                            <div class="analysis-suite__structured-widget-label"><?php echo esc_html($widget['label']); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($widget['source'])): ?>
                                            <div class="analysis-suite__structured-widget-source"><?php echo esc_html($widget['source']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($structured_insights['metrics'])): ?>
                            <div class="analysis-suite__structured-metrics">
                                <?php foreach ($structured_insights['metrics'] as $metric): ?>
                                    <div class="analysis-suite__structured-metric">
                                        <span class="analysis-suite__structured-metric-label"><?php echo esc_html($metric['label']); ?></span>
                                        <span class="analysis-suite__structured-metric-value"><?php echo esc_html($metric['value']); ?></span>
                                        <?php if (!empty($metric['percentage']) || $metric['percentage'] === 0): ?>
                                            <span class="analysis-suite__structured-metric-bar">
                                                <span style="width: <?php echo esc_attr((string) max(0, min(100, (int) $metric['percentage']))); ?>%;"></span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($structured_insights['chips'])): ?>
                            <div class="analysis-suite__structured-chips">
                                <?php foreach ($structured_insights['chips'] as $chip_group): ?>
                                    <div class="analysis-suite__structured-chip-group">
                                        <?php if (!empty($chip_group['title'])): ?>
                                            <div class="analysis-suite__structured-chip-title"><?php echo esc_html($chip_group['title']); ?></div>
                                        <?php endif; ?>
                                        <div class="analysis-suite__structured-chip-list">
                                            <?php foreach ($chip_group['items'] as $chip): ?>
                                                <span class="analysis-suite__chip"><?php echo esc_html($chip); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($structured_insights['collections'])): ?>
                            <div class="analysis-suite__structured-collections">
                                <?php foreach ($structured_insights['collections'] as $collection): ?>
                                    <div class="analysis-suite__structured-collection">
                                        <?php if (!empty($collection['title'])): ?>
                                            <div class="analysis-suite__structured-collection-title"><?php echo esc_html($collection['title']); ?></div>
                                        <?php endif; ?>
                                        <dl class="analysis-suite__structured-detail-list">
                                            <?php foreach ($collection['items'] as $item): ?>
                                                <div class="analysis-suite__structured-detail">
                                                    <?php if (!empty($item['label'])): ?>
                                                        <dt><?php echo esc_html($item['label']); ?></dt>
                                                    <?php endif; ?>
                                                    <dd><?php echo esc_html($item['value']); ?></dd>
                                                </div>
                                            <?php endforeach; ?>
                                        </dl>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($structured_insights['tables'])): ?>
                            <div class="analysis-suite__structured-tables">
                                <?php foreach ($structured_insights['tables'] as $table): ?>
                                    <div class="analysis-suite__structured-table">
                                        <?php if (!empty($table['title'])): ?>
                                            <div class="analysis-suite__structured-table-title"><?php echo esc_html($table['title']); ?></div>
                                        <?php endif; ?>
                                        <div class="analysis-suite__structured-table-wrapper">
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <?php foreach ($table['headers'] as $header): ?>
                                                            <th scope="col"><?php echo esc_html($header); ?></th>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($table['rows'] as $row): ?>
                                                        <tr>
                                                            <?php foreach ($row as $cell): ?>
                                                                <td><?php echo esc_html($cell); ?></td>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
