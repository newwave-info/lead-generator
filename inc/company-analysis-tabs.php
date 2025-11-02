<?php
/**
 * Template part: Tab analisi multi-agente
 *
 * @package PSIP_Theme
 */

if (!defined('ABSPATH')) exit;

$helpers_path = get_theme_file_path('inc/company-analysis-helpers.php');
if (file_exists($helpers_path)) {
    require_once $helpers_path;
}

$post_id = get_the_ID();
$agents = psip_get_agents();
$analisi_for_company = psip_get_company_analyses($post_id);

if (empty($agents)) return;

$placeholder_text = 'Lorem ipsum';

?>

<section id="analysis-tabs" class="analysis-suite">
    <div class="container-fluid">
        <div class="analysis-suite__nav nav nav-tabs" id="analysisTabs" role="tablist">
            <?php
            $first = true;
            foreach ($agents as $slug => $agent_config) :
                $tab_id = 'tab-' . esc_attr($slug);
                $analysis_info = $analisi_for_company[$slug] ?? null;
                $has_analysis = $analysis_info !== null;
                $last_run_display = $analysis_info['last_run']['display'] ?? '';
                $status_class = $has_analysis ? 'is-completed' : 'is-pending';
                $status_text = $has_analysis && $last_run_display !== ''
                    ? sprintf(__('Ultima esecuzione: %s', 'psip'), $last_run_display)
                    : __('Analisi non ancora eseguita', 'psip');
            ?>
                <button class="analysis-suite__nav-link nav-link <?php echo $first ? 'active' : ''; ?> <?php echo esc_attr($status_class); ?>" id="<?php echo $tab_id; ?>-tab" data-bs-toggle="tab" data-bs-target="#<?php echo $tab_id; ?>" type="button" role="tab" aria-controls="<?php echo $tab_id; ?>" aria-selected="<?php echo $first ? 'true' : 'false'; ?>">
                    <span class="analysis-suite__nav-icon dashicons <?php echo esc_attr($agent_config['icon']); ?>"></span>
                    <span class="analysis-suite__nav-text">
                        <span class="analysis-suite__nav-title"><?php echo esc_html($agent_config['name']); ?></span>
                        <span class="analysis-suite__nav-subtitle"><?php echo esc_html($status_text); ?></span>
                    </span>
                </button>
            <?php
                $first = false;
            endforeach;
            ?>
        </div>

        <div class="tab-content" id="analysisTabsContent">
            <?php
            $first = true;
            foreach ($agents as $slug => $agent_config) :
                $tab_id = 'tab-' . esc_attr($slug);
                $analysis_info = $analisi_for_company[$slug] ?? null;
                $has_analysis = $analysis_info !== null;
                $analysis_id = $analysis_info['id'] ?? null;
                $analysis_last_run = $analysis_info['last_run']['display'] ?? '';
                $status_class = $has_analysis ? 'is-completed' : 'is-pending';
                $status_text = $has_analysis && $analysis_last_run !== ''
                    ? sprintf(__('Ultima esecuzione: %s', 'psip'), $analysis_last_run)
                    : __('Analisi non ancora eseguita', 'psip');

                $quality_score_raw = $has_analysis ? get_field('voto_qualita_analisi', $analysis_id) : '';
                $quality_score_normalized = psip_theme_normalize_scalar($quality_score_raw);
                $quality_score = ($quality_score_normalized !== '') ? $quality_score_normalized : null;

                $summary = $has_analysis ? psip_theme_normalize_scalar(get_field('riassunto', $analysis_id)) : '';
                $deep_research_raw = $has_analysis ? get_field('analisy_perplexity_deep_research', $analysis_id) : '';
                $analysis_review_raw = $has_analysis ? get_field('revisione_analisi_completa', $analysis_id) : '';
                $strengths = $has_analysis ? psip_theme_normalize_scalar(get_field('punti_di_forza', $analysis_id)) : '';
                $weaknesses = $has_analysis ? psip_theme_normalize_scalar(get_field('punti_di_debolezza', $analysis_id)) : '';
                $opportunities = $has_analysis ? psip_theme_normalize_scalar(get_field('opportunita', $analysis_id)) : '';
                $quick_actions = $has_analysis ? psip_theme_normalize_scalar(get_field('azioni_rapide', $analysis_id)) : '';

                $count_strengths = $has_analysis ? get_field('numero_punti_di_forza', $analysis_id) : null;
                $count_strengths = ($count_strengths !== null && $count_strengths !== '' && is_numeric($count_strengths)) ? (int) $count_strengths : null;
                $count_weaknesses = $has_analysis ? get_field('numero_punti_di_debolezza', $analysis_id) : null;
                $count_weaknesses = ($count_weaknesses !== null && $count_weaknesses !== '' && is_numeric($count_weaknesses)) ? (int) $count_weaknesses : null;
                $count_opportunities = $has_analysis ? get_field('numero_opportunita', $analysis_id) : null;
                $count_opportunities = ($count_opportunities !== null && $count_opportunities !== '' && is_numeric($count_opportunities)) ? (int) $count_opportunities : null;
                $count_quick_actions = $has_analysis ? get_field('numero_azioni_rapide', $analysis_id) : null;
                $count_quick_actions = ($count_quick_actions !== null && $count_quick_actions !== '' && is_numeric($count_quick_actions)) ? (int) $count_quick_actions : null;

                // Parse items as arrays instead of HTML strings
                $parse_items = function($text) {
                    if ($text === null || $text === '') {
                        return [];
                    }
                    $segments = preg_split('/,\s*(?=[A-ZÀ-ÖØ-Ý])/u', $text);
                    if (!$segments || count($segments) === 1) {
                        return [$text];
                    }
                    $segments = array_map('trim', $segments);
                    return array_filter($segments, function($s) { return $s !== ''; });
                };

                $weaknesses_items = $parse_items($weaknesses);
                $strengths_items = $parse_items($strengths);
                $opportunities_items = $parse_items($opportunities);
                $quick_actions_items = $parse_items($quick_actions);
                $deep_research_html = psip_theme_format_markdown_bold($deep_research_raw);
                $analysis_review_html = psip_theme_format_markdown_bold($analysis_review_raw); ?>

                <?php
                $structured_raw = $has_analysis ? get_field('json_dati_strutturati', $analysis_id) : '';
                $structured_insights = psip_theme_prepare_structured_insights($structured_raw);
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
                ?>

                <div class="tab-pane fade <?php echo $first ? 'show active' : ''; ?>" id="<?php echo $tab_id; ?>" role="tabpanel" aria-labelledby="<?php echo $tab_id; ?>-tab">

                <?php
                $analysis_last_run_display = $has_analysis && $analysis_last_run !== ''
                    ? $analysis_last_run
                    : __('Dato non disponibile', 'psip');
                $quality_score_display = ($quality_score !== null && $quality_score !== '')
                    ? $quality_score
                    : '—';
                ?>

                <div class="analysis-suite__panel">
                    <header class="analysis-suite__panel-header">
                        <div class="analysis-suite__panel-heading">
                            <span class="analysis-suite__panel-tag"><?php the_title(); ?></span>
                            <h3 class="analysis-suite__panel-title"><?php echo esc_html($agent_config['name']); ?></h3>
                        </div>
                        <div class="analysis-suite__panel-meta">
                            <span class="analysis-suite__status-chip <?php echo esc_attr($status_class); ?>">
                                <?php echo esc_html($status_text); ?>
                            </span>
                        </div>
                    </header>

                    <div class="analysis-suite__hero-section">
                        <div class="analysis-suite__hero-summary">
                            <h4 class="analysis-suite__hero-title">Riassunto</h4>
                            <div class="analysis-suite__hero-content">
                                <?php echo $summary
                                    ? wp_kses_post(wpautop($summary))
                                    : wp_kses_post(wpautop($placeholder_text)); ?>
                            </div>
                        </div>
                        <div class="analysis-suite__hero-score">
                            <span class="analysis-suite__hero-score-label">Quality Score</span>
                            <span class="analysis-suite__hero-score-value"><?php echo esc_html((string) $quality_score_display); ?></span>
                            <span class="analysis-suite__hero-score-max">/10</span>
                            <?php if ($analysis_last_run_display): ?>
                                <span class="analysis-suite__hero-score-hint"><?php echo esc_html($analysis_last_run_display); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($highlight_widgets)): ?>
                        <div class="analysis-suite__highlight-widgets">
                            <?php foreach ($highlight_widgets as $widget): ?>
                                <div class="analysis-suite__highlight-widget">
                                    <span class="analysis-suite__highlight-widget-value"><?php echo esc_html($widget['value']); ?></span>
                                    <?php if (!empty($widget['label'])): ?>
                                        <span class="analysis-suite__highlight-widget-label"><?php echo esc_html($widget['label']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($widget['source'])): ?>
                                        <span class="analysis-suite__highlight-widget-source"><?php echo esc_html($widget['source']); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="analysis-suite__metrics-row">
                        <div class="analysis-suite__metric-widget analysis-suite__metric-widget--strength">
                            <div class="analysis-suite__metric-icon">
                                <span class="dashicons dashicons-awards"></span>
                            </div>
                            <div class="analysis-suite__metric-content">
                                <span class="analysis-suite__metric-value"><?php echo ($count_strengths !== null && $count_strengths !== '') ? esc_html($count_strengths) : '0'; ?></span>
                                <span class="analysis-suite__metric-label">Punti di forza</span>
                            </div>
                        </div>

                        <div class="analysis-suite__metric-widget analysis-suite__metric-widget--weakness">
                            <div class="analysis-suite__metric-icon">
                                <span class="dashicons dashicons-warning"></span>
                            </div>
                            <div class="analysis-suite__metric-content">
                                <span class="analysis-suite__metric-value"><?php echo ($count_weaknesses !== null && $count_weaknesses !== '') ? esc_html($count_weaknesses) : '0'; ?></span>
                                <span class="analysis-suite__metric-label">Punti di debolezza</span>
                            </div>
                        </div>

                        <div class="analysis-suite__metric-widget analysis-suite__metric-widget--opportunity">
                            <div class="analysis-suite__metric-icon">
                                <span class="dashicons dashicons-thumbs-up"></span>
                            </div>
                            <div class="analysis-suite__metric-content">
                                <span class="analysis-suite__metric-value"><?php echo ($count_opportunities !== null && $count_opportunities !== '') ? esc_html($count_opportunities) : '0'; ?></span>
                                <span class="analysis-suite__metric-label">Opportunità</span>
                            </div>
                        </div>

                        <div class="analysis-suite__metric-widget analysis-suite__metric-widget--action">
                            <div class="analysis-suite__metric-icon">
                                <span class="dashicons dashicons-star-filled"></span>
                            </div>
                            <div class="analysis-suite__metric-content">
                                <span class="analysis-suite__metric-value"><?php echo ($count_quick_actions !== null && $count_quick_actions !== '') ? esc_html($count_quick_actions) : '0'; ?></span>
                                <span class="analysis-suite__metric-label">Azioni rapide</span>
                            </div>
                        </div>
                    </div>

                    <div class="analysis-suite__lists-grid">
                        <div class="analysis-suite__list-container analysis-suite__list-container--strength">
                            <div class="analysis-suite__list-header">
                                <span class="dashicons dashicons-awards"></span>
                                <h4>Punti di forza</h4>
                                <span class="analysis-suite__list-count"><?php echo ($count_strengths !== null && $count_strengths !== '') ? esc_html($count_strengths) : '0'; ?></span>
                            </div>
                            <div class="analysis-suite__list-content">
                                <?php if (!empty($strengths_items)): ?>
                                    <?php foreach ($strengths_items as $index => $item): ?>
                                        <div class="analysis-suite__list-item">
                                            <span class="analysis-suite__list-number"><?php echo esc_html($index + 1); ?></span>
                                            <span class="analysis-suite__list-text"><?php echo esc_html($item); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="analysis-suite__list-empty">Nessun dato disponibile</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="analysis-suite__list-container analysis-suite__list-container--weakness">
                            <div class="analysis-suite__list-header">
                                <span class="dashicons dashicons-warning"></span>
                                <h4>Punti di debolezza</h4>
                                <span class="analysis-suite__list-count"><?php echo ($count_weaknesses !== null && $count_weaknesses !== '') ? esc_html($count_weaknesses) : '0'; ?></span>
                            </div>
                            <div class="analysis-suite__list-content">
                                <?php if (!empty($weaknesses_items)): ?>
                                    <?php foreach ($weaknesses_items as $index => $item): ?>
                                        <div class="analysis-suite__list-item">
                                            <span class="analysis-suite__list-number"><?php echo esc_html($index + 1); ?></span>
                                            <span class="analysis-suite__list-text"><?php echo esc_html($item); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="analysis-suite__list-empty">Nessun dato disponibile</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="analysis-suite__list-container analysis-suite__list-container--opportunity">
                            <div class="analysis-suite__list-header">
                                <span class="dashicons dashicons-thumbs-up"></span>
                                <h4>Opportunità</h4>
                                <span class="analysis-suite__list-count"><?php echo ($count_opportunities !== null && $count_opportunities !== '') ? esc_html($count_opportunities) : '0'; ?></span>
                            </div>
                            <div class="analysis-suite__list-content">
                                <?php if (!empty($opportunities_items)): ?>
                                    <?php foreach ($opportunities_items as $index => $item): ?>
                                        <div class="analysis-suite__list-item">
                                            <span class="analysis-suite__list-number"><?php echo esc_html($index + 1); ?></span>
                                            <span class="analysis-suite__list-text"><?php echo esc_html($item); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="analysis-suite__list-empty">Nessun dato disponibile</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="analysis-suite__list-container analysis-suite__list-container--action">
                            <div class="analysis-suite__list-header">
                                <span class="dashicons dashicons-star-filled"></span>
                                <h4>Azioni rapide</h4>
                                <span class="analysis-suite__list-count"><?php echo ($count_quick_actions !== null && $count_quick_actions !== '') ? esc_html($count_quick_actions) : '0'; ?></span>
                            </div>
                            <div class="analysis-suite__list-content">
                                <?php if (!empty($quick_actions_items)): ?>
                                    <?php foreach ($quick_actions_items as $index => $item): ?>
                                        <div class="analysis-suite__list-item">
                                            <span class="analysis-suite__list-number"><?php echo esc_html($index + 1); ?></span>
                                            <span class="analysis-suite__list-text"><?php echo esc_html($item); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="analysis-suite__list-empty">Nessun dato disponibile</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="analysis-suite__panel-body">
                        <div class="analysis-suite__column analysis-suite__column--narrative">
                            <div class="analysis-suite__card">
                                <h4 class="analysis-suite__card-title">Revisione dell'analisi</h4>
                                <div class="analysis-suite__card-content analysis-suite__card-content--scrollable">
                                    <?php echo $analysis_review_html !== ''
                                        ? $analysis_review_html
                                        : wp_kses_post(wpautop($placeholder_text)); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($deep_research_html !== ''): ?>
                        <details class="analysis-suite__deep-research-accordion">
                            <summary class="analysis-suite__deep-research-summary">
                                <span class="dashicons dashicons-search"></span>
                                <span class="analysis-suite__deep-research-title">Analisi approfondita</span>
                                <span class="analysis-suite__deep-research-hint">Clicca per espandere il contenuto completo</span>
                            </summary>
                            <div class="analysis-suite__deep-research-content">
                                <?php echo $deep_research_html; ?>
                            </div>
                        </details>
                    <?php endif; ?>

                    <?php if ($structured_has_body): ?>
                        <div class="analysis-suite__structured">
                            <div class="analysis-suite__structured-header">
                                <h4 class="analysis-suite__structured-title"><?php esc_html_e('Dati strutturati', 'psip'); ?></h4>
                                <p class="analysis-suite__structured-subtitle"><?php esc_html_e('Snapshot dei dataset eterogenei esportati dall’agente per questa azienda.', 'psip'); ?></p>
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
                                            <?php if (isset($metric['percentage'])): ?>
                                                <div class="analysis-suite__structured-metric-bar">
                                                    <span style="width: <?php echo esc_attr((string) max(0, min(100, $metric['percentage']))); ?>%;"></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($structured_insights['chips'])): ?>
                                <div class="analysis-suite__structured-chips">
                                    <?php foreach ($structured_insights['chips'] as $chip_group): ?>
                                        <div class="analysis-suite__structured-chip-group">
                                            <span class="analysis-suite__structured-chip-title"><?php echo esc_html($chip_group['title']); ?></span>
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
                                            <span class="analysis-suite__structured-collection-title"><?php echo esc_html($collection['title']); ?></span>
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
                                            <div class="analysis-suite__structured-table-title"><?php echo esc_html($table['title']); ?></div>
                                            <div class="analysis-suite__structured-table-wrapper">
                                                <table>
                                                    <thead>
                                                        <tr>
                                                            <?php foreach ($table['headers'] as $header): ?>
                                                                <th><?php echo esc_html($header); ?></th>
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

            </div><!--tab-pane-->
            <?php
                $first = false;
            endforeach;
            ?>
        </div>
    </div>
</section>
