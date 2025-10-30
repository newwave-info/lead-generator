<?php
/**
 * Template part: Tab analisi multi-agente
 *
 * @package PSIP_Theme
 */

if (!defined('ABSPATH')) exit;

$post_id = get_the_ID();
$agents = psip_get_agents();
$analisi_for_company = psip_get_company_analyses($post_id);

if (empty($agents)) return;

$placeholder_text = 'Lorem ipsum';

if (!function_exists('psip_theme_format_list_text')) {
    /**
     * Suddivide il testo su virgola seguita da maiuscola e restituisce HTML con <br>.
     */
    function psip_theme_format_list_text($text) {
        if ($text === null || $text === '') {
            return '';
        }

        $segments = preg_split('/,\s*(?=[A-ZÀ-ÖØ-Ý])/u', $text);
        if (!$segments || count($segments) === 1) {
            return esc_html($text);
        }

        $segments = array_map(function ($segment) {
            $segment = trim($segment);
            return $segment !== '' ? esc_html($segment) : null;
        }, $segments);

        $segments = array_filter($segments, static function ($segment) {
            return $segment !== null;
        });

        return $segments ? implode('<br>', $segments) : esc_html($text);
    }
}

if (!function_exists('psip_theme_format_markdown_bold')) {
    function psip_theme_format_markdown_bold($text) {
        if ($text === null || $text === '') {
            return '';
        }

        $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*(.+?)\*/s', '<strong>$1</strong>', $text);

        return wpautop(wp_kses_post($text));
    }
}
?>

<section id="analysis-tabs">
    <div class="container-fluid">
        <ul class="nav nav-tabs" id="analysisTabs" role="tablist">
            <?php
            $first = true;
            foreach ($agents as $slug => $agent_config) :
                $tab_id = 'tab-' . esc_attr($slug);
            ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $first ? 'active' : ''; ?>" id="<?php echo $tab_id; ?>-tab" data-bs-toggle="tab" data-bs-target="#<?php echo $tab_id; ?>" type="button" role="tab" aria-controls="<?php echo $tab_id; ?>" aria-selected="<?php echo $first ? 'true' : 'false'; ?>">
                        <span class="dashicons <?php echo esc_attr($agent_config['icon']); ?>"></span> <?php echo esc_html($agent_config['name']); ?>
                </button>
                </li>
            <?php
                $first = false;
            endforeach;
            ?>
        </ul>

        <div class="tab-content" id="analysisTabsContent">
            <?php
            $first = true;
            foreach ($agents as $slug => $agent_config) :
                $tab_id = 'tab-' . esc_attr($slug);
                $accordion_id = 'accordion-' . esc_attr($slug);
                $has_analysis = isset($analisi_for_company[$slug]);
                $analysis_id = $has_analysis ? $analisi_for_company[$slug] : null;

                $quality_score_raw = $has_analysis ? get_field('voto_qualita_analisi', $analysis_id) : '';
                $quality_score_normalized = psip_theme_normalize_scalar($quality_score_raw);
                $quality_score = ($quality_score_normalized !== '') ? $quality_score_normalized : null;

                $summary = $has_analysis ? psip_theme_normalize_scalar(get_field('riassunto', $analysis_id)) : '';
                $deep_research_raw = $has_analysis ? get_field('analisy_perplexity_deep_research', $analysis_id) : '';
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

                $weaknesses_html = psip_theme_format_list_text($weaknesses);
                $strengths_html = psip_theme_format_list_text($strengths);
                $opportunities_html = psip_theme_format_list_text($opportunities);
                $quick_actions_html = psip_theme_format_list_text($quick_actions);
                $deep_research_html = psip_theme_format_markdown_bold($deep_research_raw); ?>

                <div class="tab-pane fade <?php echo $first ? 'show active' : ''; ?> pt-5" id="<?php echo $tab_id; ?>" role="tabpanel" aria-labelledby="<?php echo $tab_id; ?>-tab">

                    <div class="container-fluid">

                        <div class="row gy-2 mb-2 align-items-center">
                            <div class="col-auto" data-bs-toggle="tooltip" title="Data esecuzione">
                                <span class="badge text-bg-light"><?php echo $has_analysis ? get_the_time('Y-m-d H:i', $analysis_id) : esc_html($placeholder_text); ?></span>
                            </div>
                        </div>
                        <div class="row gy-2 mb-2 align-items-center">
                            <div class="col-auto">
                                <h2 class="title"><?php the_title() ?> - <?php echo esc_html($agent_config['name']); ?></h2>
                            </div>
                            <div class="col-auto px-1" data-bs-toggle="tooltip" title="Voto qualità analisi">
                                <span class="badge text-bg-dark">
                                    Voto <?php echo ($quality_score !== null && $quality_score !== '') ? esc_html($quality_score) : esc_html($placeholder_text); ?>/100
                                </span>
                            </div>
                            <div class="col-auto px-1" data-bs-toggle="tooltip" title="Conteggio punti di forza">
                                <span class="badge text-bg-primary">
                                    Forza <?php echo ($count_strengths !== null && $count_strengths !== '') ? esc_html($count_strengths) : esc_html($placeholder_text); ?>
                                </span>
                            </div>
                        </div>
                        <hr class=" mb-5">


                        <div class="row gy-4">
                            <div class="col-md-6 col-lg-5 col-xxl-4 order-md-last">
                                <div class="sticky-top" style="top:12px">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="text">
                                                <h4 class="mb-2">Riassunto</h4>
                                                <?php echo $summary ? wp_kses_post(wpautop($summary)) : wp_kses_post(wpautop($placeholder_text)); ?>
                                            </div>
                                            <hr>
                                            <div class="row g-2 small">
                                                <div class="col-6">
                                                    <div class="px-3 py-2 bg-white border rounded">
                                                        <span class="text-uppercase text-muted d-block fw-semibold">Punti di forza</span>
                                                        <span class="fs-4 d-block"><?php echo ($count_strengths !== null && $count_strengths !== '') ? esc_html($count_strengths) : esc_html($placeholder_text); ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="px-3 py-2 bg-white border rounded">
                                                        <span class="text-uppercase text-muted d-block fw-semibold">Punti di debolezza</span>
                                                        <span class="fs-4 d-block"><?php echo ($count_weaknesses !== null && $count_weaknesses !== '') ? esc_html($count_weaknesses) : esc_html($placeholder_text); ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="px-3 py-2 bg-white border rounded">
                                                        <span class="text-uppercase text-muted d-block fw-semibold">Opportunità</span>
                                                        <span class="fs-4 d-block"><?php echo ($count_opportunities !== null && $count_opportunities !== '') ? esc_html($count_opportunities) : esc_html($placeholder_text); ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="px-3 py-2 bg-white border rounded">
                                                        <span class="text-uppercase text-muted d-block fw-semibold">Azioni rapide</span>
                                                        <span class="fs-4 d-block"><?php echo ($count_quick_actions !== null && $count_quick_actions !== '') ? esc_html($count_quick_actions) : esc_html($placeholder_text); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div><!--col-->

                            <div class="col-md-6 col-lg-7 col-xxl-8">
                                <div class="accordion mt-4" id="<?php echo $accordion_id; ?>">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $accordion_id; ?>-debolezze" aria-expanded="false" aria-controls="<?php echo $accordion_id; ?>-debolezze">
                                                Punti di debolezza
                                            </button>
                                        </h2>
                                        <div id="<?php echo $accordion_id; ?>-debolezze" class="accordion-collapse collapse" data-bs-parent="#<?php echo $accordion_id; ?>">
                                            <div class="accordion-body">
                                                <div class="text">
                                                    <?php echo $weaknesses_html !== '' ? wp_kses_post($weaknesses_html) : wp_kses_post(wpautop($placeholder_text)); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $accordion_id; ?>-forza" aria-expanded="false" aria-controls="<?php echo $accordion_id; ?>-forza">
                                                Punti di forza
                                            </button>
                                        </h2>
                                        <div id="<?php echo $accordion_id; ?>-forza" class="accordion-collapse collapse" data-bs-parent="#<?php echo $accordion_id; ?>">
                                            <div class="accordion-body">
                                                <div class="text">
                                                    <?php echo $strengths_html !== '' ? wp_kses_post($strengths_html) : wp_kses_post(wpautop($placeholder_text)); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $accordion_id; ?>-opportunita" aria-expanded="false" aria-controls="<?php echo $accordion_id; ?>-opportunita">
                                                Opportunità
                                            </button>
                                        </h2>
                                        <div id="<?php echo $accordion_id; ?>-opportunita" class="accordion-collapse collapse" data-bs-parent="#<?php echo $accordion_id; ?>">
                                            <div class="accordion-body">
                                                <div class="text">
                                                    <?php echo $opportunities_html !== '' ? wp_kses_post($opportunities_html) : wp_kses_post(wpautop($placeholder_text)); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $accordion_id; ?>-azioni" aria-expanded="false" aria-controls="<?php echo $accordion_id; ?>-azioni">
                                                Azioni rapide
                                            </button>
                                        </h2>
                                        <div id="<?php echo $accordion_id; ?>-azioni" class="accordion-collapse collapse" data-bs-parent="#<?php echo $accordion_id; ?>">
                                            <div class="accordion-body">
                                                <div class="text">
                                                    <?php echo $quick_actions_html !== '' ? wp_kses_post($quick_actions_html) : wp_kses_post(wpautop($placeholder_text)); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $accordion_id; ?>-approfondita" aria-expanded="false" aria-controls="<?php echo $accordion_id; ?>-approfondita">
                                                Analisi approfondita
                                            </button>
                                        </h2>
                                        <div id="<?php echo $accordion_id; ?>-approfondita" class="accordion-collapse collapse" data-bs-parent="#<?php echo $accordion_id; ?>">
                                            <div class="accordion-body">
                                                <div class="text">
                                                    <?php echo $deep_research_html !== '' ? $deep_research_html : wp_kses_post(wpautop($placeholder_text)); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- .accordion -->

                            </div><!--col-->
                        </div><!--row-->
                    </div><!--container-->

                </div><!--tab-pane-->
            <?php
                $first = false;
            endforeach;
            ?>
        </div>
    </div>
</section>
