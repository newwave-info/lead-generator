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

// Mappa campi ACF
$analysis_fields = [
    'gaps_weaknesses'             => 'Gap e Debolezze',
    'opportunities_identified'    => 'Opportunità Identificate',
];

if (empty($agents)) return;
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

                // Recupera tutti i campi
                $quality_score = $has_analysis ? get_field('quality_score', $analysis_id) : null;
                $confidence_score = $has_analysis ? get_field('confidence_score', $analysis_id) : null;
                $core_promise = $has_analysis ? get_field('core_promise', $analysis_id) : null;

                $numero_oppurtunita = $has_analysis ? get_field('numero_oppurtunita', $analysis_id) : null;
                $quick_wins = $has_analysis ? get_field('quick_wins', $analysis_id) : null;
                $punti_di_forza = $has_analysis ? get_field('punti_di_forza', $analysis_id) : null;
                $debolezze = $has_analysis ? get_field('debolezze', $analysis_id) : null;
                $gap_rilevati = $has_analysis ? get_field('gap_rilevati', $analysis_id) : null;

                $insights_summary = $has_analysis ? get_field('insights_summary', $analysis_id) : null;
                $riassunto_esecutivo = $has_analysis ? get_field('riassunto_esecutivo', $analysis_id) : null;
                $gaps_weaknesses = $has_analysis ? get_field('gaps_weaknesses', $analysis_id) : null;
                $opportunities_identified = $has_analysis ? get_field('opportunities_identified', $analysis_id) : null;

                $debolezze_html = $has_analysis ? get_field('debolezze_html', $analysis_id) : null;
                $punti_di_forza_html = $has_analysis ? get_field('punti_di_forza_html', $analysis_id) : null;
                $azioni_rapide = $has_analysis ? get_field('azioni_rapide', $analysis_id) : null; ?>

                <div class="tab-pane fade <?php echo $first ? 'show active' : ''; ?> pt-5" id="<?php echo $tab_id; ?>" role="tabpanel" aria-labelledby="<?php echo $tab_id; ?>-tab">

                    <div class="container-fluid">

                        <div class="row gy-2 mb-2 align-items-center">
                            <div class="col-auto" data-bs-toggle="tooltip" title="Data esecuzione">
                                <span class="badge text-bg-light"><?php echo $has_analysis ? get_the_time( 'Y-d-m H:i', $analysis_id ) : '—'; ?></span>
                            </div>
                        </div>
                        <div class="row gy-2 mb-2 align-items-center">
                            <div class="col-auto">
                                <h2 class="title"><?php the_title() ?> - <?php echo esc_html($agent_config['name']); ?></h2>
                            </div>
                            <div class="col-auto px-1" data-bs-toggle="tooltip" title="Quality score">
                                <span class="badge text-bg-dark">QS <?php echo ($quality_score !== null && $quality_score !== '') ? esc_html($quality_score) : '—'; ?></span>
                            </div>
                            <div class="col-auto px-1" data-bs-toggle="tooltip" title="Confidence score">
                                <span class="badge text-bg-dark">CS <?php echo ($confidence_score !== null && $confidence_score !== '') ? esc_html($confidence_score) : '—'; ?></span>
                            </div>
                        </div>
                        <hr class=" mb-5">


                        <div class="row gy-4">
                            <div class="col-md-6 col-lg-5 col-xxl-4 order-md-last">
                                <div class="sticky-top" style="top:12px">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="text">
                                                <h4 class="mb-2">Riassunto esecutivo</h4>
                                                <?php echo $riassunto_esecutivo ? wp_kses_post($riassunto_esecutivo) : '—'; ?>
                                            </div>
                                            <hr>
                                            <div class="row gy-2 mt-2">
                                                <div class="col">
                                                    <div class="number-item">
                                                        <div class="number-val"><?php echo ($numero_oppurtunita !== null && $numero_oppurtunita !== '') ? esc_html($numero_oppurtunita) : '—'; ?></div>
                                                        <div class="number-label">Opportunità</div>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="number-item">
                                                        <div class="number-val"><?php echo ($quick_wins !== null && $quick_wins !== '') ? esc_html($quick_wins) : '—'; ?></div>
                                                        <div class="number-label">Quick Wins</div>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="number-item">
                                                        <div class="number-val"><?php echo ($punti_di_forza !== null && $punti_di_forza !== '') ? esc_html($punti_di_forza) : '—'; ?></div>
                                                        <div class="number-label">Punti di forza</div>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="number-item">
                                                        <div class="number-val"><?php echo ($debolezze !== null && $debolezze !== '') ? esc_html($debolezze) : '—'; ?></div>
                                                        <div class="number-label">Debolezze</div>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="number-item">
                                                        <div class="number-val"><?php echo ($gap_rilevati !== null && $gap_rilevati !== '') ? esc_html($gap_rilevati) : '—'; ?></div>
                                                        <div class="number-label">Gap rilevati</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Accordion per Debolezze, Punti di forza, Azioni rapide -->
                                    <div class="accordion mt-4" id="<?php echo $accordion_id; ?>">

                                        <!-- Core promise -->
                                        <?php if ($debolezze_html = get_field('debolezze_html', $analysis_id)) : ?>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $accordion_id; ?>-core" aria-expanded="false" aria-controls="<?php echo $accordion_id; ?>-debolezze">
                                                    Core promise
                                                </button>
                                            </h2>
                                            <div id="<?php echo $accordion_id; ?>-core" class="accordion-collapse collapse" data-bs-parent="#<?php echo $accordion_id; ?>">
                                                <div class="accordion-body">
                                                    <div class="text"><?php echo wp_kses_post($core_promise); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Debolezze -->
                                        <?php if ($debolezze_html = get_field('debolezze_html', $analysis_id)) : ?>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $accordion_id; ?>-debolezze" aria-expanded="false" aria-controls="<?php echo $accordion_id; ?>-debolezze">
                                                    Debolezze
                                                </button>
                                            </h2>
                                            <div id="<?php echo $accordion_id; ?>-debolezze" class="accordion-collapse collapse" data-bs-parent="#<?php echo $accordion_id; ?>">
                                                <div class="accordion-body">
                                                    <div class="text"><?php echo wp_kses_post($debolezze_html); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Punti di forza -->
                                        <?php if ($punti_forza = get_field('punti_di_forza_html', $analysis_id)) : ?>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $accordion_id; ?>-punti-forza" aria-expanded="false" aria-controls="<?php echo $accordion_id; ?>-punti-forza">
                                                    Punti di forza
                                                </button>
                                            </h2>
                                            <div id="<?php echo $accordion_id; ?>-punti-forza" class="accordion-collapse collapse" data-bs-parent="#<?php echo $accordion_id; ?>">
                                                <div class="accordion-body">
                                                    <div class="text"><?php echo wp_kses_post($punti_forza); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Azioni rapide -->
                                        <?php if ($azioni_rapide = get_field('azioni_rapide', $analysis_id)) : ?>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $accordion_id; ?>-azioni-rapide" aria-expanded="false" aria-controls="<?php echo $accordion_id; ?>-azioni-rapide">
                                                    Azioni rapide
                                                </button>
                                            </h2>
                                            <div id="<?php echo $accordion_id; ?>-azioni-rapide" class="accordion-collapse collapse" data-bs-parent="#<?php echo $accordion_id; ?>">
                                                <div class="accordion-body">
                                                    <div class="text"><?php echo wp_kses_post($azioni_rapide); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                    </div><!-- .accordion -->
                                </div>
                            </div><!--col-->

                            <div class="col-md-6 col-lg-7 col-xxl-8">




                                <!-- Gap e Debolezze -->
                                <div class="mb-5">
                                    <div class="text">
                                        <h4 class="mb-2">Gap e Debolezze</h4>
                                        <?php echo $gaps_weaknesses ? wp_kses_post($gaps_weaknesses) : '—'; ?>
                                    </div>
                                </div>
                                <hr>
                                <!-- Opportunità Identificate -->
                                <div class="mb-5">
                                    <div class="text">
                                        <h4 class="mb-2">Opportunità Identificate</h4>
                                        <?php echo $opportunities_identified ? wp_kses_post($opportunities_identified) : '—'; ?>
                                    </div>
                                </div>

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
