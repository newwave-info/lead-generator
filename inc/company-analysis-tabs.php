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

                // Recupera i nuovi campi ACF
                $riassunto = $has_analysis ? get_field('riassunto', $analysis_id) : null;
                $punti_di_forza = $has_analysis ? get_field('punti_di_forza', $analysis_id) : null;
                $punti_di_debolezza = $has_analysis ? get_field('punti_di_debolezza', $analysis_id) : null;
                $opportunita = $has_analysis ? get_field('opportunita', $analysis_id) : null;
                $azioni_rapide = $has_analysis ? get_field('azioni_rapide', $analysis_id) : null;
                
                $numero_punti_di_forza = $has_analysis ? get_field('numero_punti_di_forza', $analysis_id) : null;
                $numero_punti_di_debolezza = $has_analysis ? get_field('numero_punti_di_debolezza', $analysis_id) : null;
                $numero_opportunita = $has_analysis ? get_field('numero_opportunita', $analysis_id) : null;
                $numero_azioni_rapide = $has_analysis ? get_field('numero_azioni_rapide', $analysis_id) : null;
                $voto_qualita_analisi = $has_analysis ? get_field('voto_qualita_analisi', $analysis_id) : null;
                ?>

                <div class="tab-pane fade <?php echo $first ? 'show active' : ''; ?> pt-5" id="<?php echo $tab_id; ?>" role="tabpanel" aria-labelledby="<?php echo $tab_id; ?>-tab">

                    <div class="container-fluid">

                        <div class="row gy-2 mb-2 align-items-center">
                            <div class="col-auto" data-bs-toggle="tooltip" title="Data esecuzione">
                                <span class="badge text-bg-light"><?php echo $has_analysis ? get_the_time( 'Y-m-d H:i', $analysis_id ) : '—'; ?></span>
                            </div>
                        </div>
                        <div class="row gy-2 mb-2 align-items-center">
                            <div class="col-auto">
                                <h2 class="title"><?php the_title() ?> - <?php echo esc_html($agent_config['name']); ?></h2>
                            </div>
                            <div class="col-auto px-1" data-bs-toggle="tooltip" title="Voto qualità analisi">
                                <span class="badge text-bg-dark">Voto <?php echo ($voto_qualita_analisi !== null && $voto_qualita_analisi !== '') ? esc_html($voto_qualita_analisi) : '—'; ?>/100</span>
                            </div>
                            <div class="col-auto px-1" data-bs-toggle="tooltip" title="[PLACEHOLDER: Confidence score - campo rimosso]">
                                <span class="badge text-bg-secondary">[CS - RIMOSSO]</span>
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
                                                <?php echo $riassunto ? wp_kses_post(wpautop($riassunto)) : '—'; ?>
                                            </div>
                                            <hr>
                                            <div class="row gy-2 mt-2">
                                                <div class="col">
                                                    <div class="number-item">
                                                        <div class="number-val"><?php echo ($numero_opportunita !== null && $numero_opportunita !== '') ? esc_html($numero_opportunita) : '—'; ?></div>
                                                        <div class="number-label">Opportunità</div>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="number-item">
                                                        <div class="number-val"><?php echo ($numero_azioni_rapide !== null && $numero_azioni_rapide !== '') ? esc_html($numero_azioni_rapide) : '—'; ?></div>
                                                        <div class="number-label">Azioni rapide</div>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="number-item">
                                                        <div class="number-val"><?php echo ($numero_punti_di_forza !== null && $numero_punti_di_forza !== '') ? esc_html($numero_punti_di_forza) : '—'; ?></div>
                                                        <div class="number-label">Punti di forza</div>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="number-item">
                                                        <div class="number-val"><?php echo ($numero_punti_di_debolezza !== null && $numero_punti_di_debolezza !== '') ? esc_html($numero_punti_di_debolezza) : '—'; ?></div>
                                                        <div class="number-label">Debolezze</div>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="number-item">
                                                        <div class="number-val">[RIMOSSO]</div>
                                                        <div class="number-label">Gap rilevati</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Accordion per Debolezze, Punti di forza, Azioni rapide -->
                                    <div class="accordion mt-4" id="<?php echo $accordion_id; ?>">

                                        <!-- Core promise - RIMOSSO -->
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $accordion_id; ?>-core" aria-expanded="false" aria-controls="<?php echo $accordion_id; ?>-core">
                                                    [PLACEHOLDER: Core promise - campo rimosso]
                                                </button>
                                            </h2>
                                            <div id="<?php echo $accordion_id; ?>-core" class="accordion-collapse collapse" data-bs-parent="#<?php echo $accordion_id; ?>">
                                                <div class="accordion-body">
                                                    <div class="text">[Campo core_promise non più presente nei nuovi campi ACF]</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Debolezze -->
                                        <?php if ($punti_di_debolezza) : ?>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $accordion_id; ?>-debolezze" aria-expanded="false" aria-controls="<?php echo $accordion_id; ?>-debolezze">
                                                    Punti di debolezza
                                                </button>
                                            </h2>
                                            <div id="<?php echo $accordion_id; ?>-debolezze" class="accordion-collapse collapse" data-bs-parent="#<?php echo $accordion_id; ?>">
                                                <div class="accordion-body">
                                                    <div class="text"><?php echo wp_kses_post(wpautop($punti_di_debolezza)); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Punti di forza -->
                                        <?php if ($punti_di_forza) : ?>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $accordion_id; ?>-punti-forza" aria-expanded="false" aria-controls="<?php echo $accordion_id; ?>-punti-forza">
                                                    Punti di forza
                                                </button>
                                            </h2>
                                            <div id="<?php echo $accordion_id; ?>-punti-forza" class="accordion-collapse collapse" data-bs-parent="#<?php echo $accordion_id; ?>">
                                                <div class="accordion-body">
                                                    <div class="text"><?php echo wp_kses_post(wpautop($punti_di_forza)); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Azioni rapide -->
                                        <?php if ($azioni_rapide) : ?>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $accordion_id; ?>-azioni-rapide" aria-expanded="false" aria-controls="<?php echo $accordion_id; ?>-azioni-rapide">
                                                    Azioni rapide
                                                </button>
                                            </h2>
                                            <div id="<?php echo $accordion_id; ?>-azioni-rapide" class="accordion-collapse collapse" data-bs-parent="#<?php echo $accordion_id; ?>">
                                                <div class="accordion-body">
                                                    <div class="text"><?php echo wp_kses_post(wpautop($azioni_rapide)); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                    </div><!-- .accordion -->
                                </div>
                            </div><!--col-->

                            <div class="col-md-6 col-lg-7 col-xxl-8">

                                <!-- Opportunità -->
                                <div class="mb-5">
                                    <div class="text">
                                        <h4 class="mb-2">Opportunità</h4>
                                        <?php echo $opportunita ? wp_kses_post(wpautop($opportunita)) : '—'; ?>
                                    </div>
                                </div>
                                <hr>
                                
                                <!-- PLACEHOLDER: Gaps e Debolezze - Sezione rimossa -->
                                <div class="mb-5">
                                    <div class="alert alert-warning">
                                        <h4 class="mb-2">[PLACEHOLDER: Gap e Debolezze]</h4>
                                        <p>Campo "gaps_weaknesses" non più presente. Ora usato "punti_di_debolezza" nell'accordion laterale.</p>
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
