<?php
get_header();

if ( have_posts() ) :
while ( have_posts() ) : the_post();
    $post_id = get_the_ID();

    // Campi ACF principali
    $status                     = get_field('status', $post_id);
    $growth_stage               = get_field('growth_stage', $post_id);
    $estimated_marketing_budget = get_field('estimated_marketing_budget', $post_id);
    $analysis_date              = get_field('analysis_date', $post_id); // formato d/m/Y g:i a
    $partita_iva                = get_field('partita_iva', $post_id);
    $address                    = get_field('address', $post_id);
    $phone                      = get_field('phone', $post_id);
    $website                    = get_field('website', $post_id);
    $domain                     = get_field('domain', $post_id);
    $business_type              = get_field('business_type', $post_id);
    $sector_specific            = get_field('sector_specific', $post_id);
    $employee_count_est         = get_field('employee_count_est', $post_id);

    $estimated_annual_revenue   = get_field('estimated_annual_revenue', $post_id);
    $estimated_ebitda_percentage= get_field('estimated_ebitda_percentage', $post_id);
    $confidence                 = get_field('confidence', $post_id);
    $budget_tier                = get_field('budget_tier', $post_id);
    $budget_qualified           = (bool) get_field('budget_qualified', $post_id);

    $campaign_name              = get_field('campaign_name', $post_id);
    $data_completeness          = get_field('data_completeness', $post_id);
    $source                     = get_field('source', $post_id);
    $query_used                 = get_field('query_used', $post_id);
    $discovery_id               = get_field('discovery_id', $post_id);
    $run_id                     = get_field('run_id', $post_id);

    $place_id                   = get_field('place_id', $post_id);
    $rating                     = get_field('rating', $post_id);
    $reviews                    = get_field('reviews', $post_id);
    $discovery_date             = get_field('discovery_date', $post_id); // d/m/Y
    $discovery_time             = get_field('discovery_time', $post_id); // g:i a

?>
<main id="single_company" role="main">

    <section id="company_hero">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-12 col-lg-7">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <figure class="figure company-logo">
                                <img class="img-contain" src="https://api.microlink.io/?url=https%3A%2F%2F<?php echo esc_html($domain); ?>&palette=true&embed=logo.url" loading="lazy" />
                            </figure>
                        </div>
                        <div class="col">
                            <h1 class="company-title"><?php the_title(); ?></h1>
                        </div>
                    </div>
                    <?php if ( $industry = $business_type ) : ?>
                        <div class="company-industry mt-4 small text-muted">
                            <?php echo esc_html($industry); ?>
                            <?php if ( $sector_specific ) : ?>
                                <br><small><?php echo esc_html($sector_specific); ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section><!--company_hero-->

    <section id="company_content">
        <div class="container-fluid">

            <div class="row gy-4">

                <!-- Colonna destra: pannello rating -->
                <div class="col-md-6 col-lg-5 col-xxl-4 order-md-last">
                    <div class="sticky-top" style="top:12px">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-4">
                                    <h2 class="h5 mb-3">Qualifica</h2>
                                    <div class="row gy-4">
                                        <div class="col-6">
                                            <div class="small text-muted">Status</div>
                                            <div class="badge"><?php echo $status ? esc_html($status) : '-'; ?></div>
                                        </div>
                                        <div class="col-6">
                                            <div class="small text-muted">Stadio Crescita</div>
                                            <div class="badge"><?php echo $growth_stage ? esc_html($growth_stage) : '-'; ?></div>
                                        </div>
                                        <div class="col-6">
                                            <div class="small text-muted">Budget Marketing stimato</div>
                                            <div class="badge"><?php echo $estimated_marketing_budget ? esc_html($estimated_marketing_budget) : '-'; ?></div>
                                        </div>
                                        <div class="col-6">
                                            <div class="small text-muted">Confidence</div>
                                            <div class="badge"><?php echo $confidence !== '' ? esc_html($confidence) : '-'; ?></div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="d-grid gap-2">
                                        <?php
                                        $screen_home = get_field('screen_home');
                                        if( $screen_home ):
                                            $image_data = wp_get_attachment_image_src($screen_home, 'full'); ?>
                                            <h2 class="h5">Website</h2>
                                            <a class="website-prw" href="<?php echo $image_data[0]; ?>" data-fancybox>
                                                <div class="macos-titlebar">
                                                    <div class="macos-buttons">
                                                        <span class="macos-btn macos-btn-close"></span>
                                                        <span class="macos-btn macos-btn-minimize"></span>
                                                        <span class="macos-btn macos-btn-maximize"></span>
                                                    </div>
                                                </div>
                                                <figure class="figure figure-scroll" style="padding-top: 56.25%;">
                                                    <img class="img-cover" src="<?php echo $image_data[0]; ?>" loading="lazy" />
                                                </figure>
                                            </a>
                                        <?php else: ?>
                                            <h2 class="h5 mt-3 mb-3">Azioni</h2>
                                        <?php endif; ?>

                                        <?php if ( $website ) : ?>
                                            <a class="btn btn-dark" href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener">Visita sito</a>
                                        <?php endif; ?>
                                        <a class="btn btn btn-outline-dark" href="#table_agents">Tutte le analisi</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion mt-4" id="accordionDatiAzienda">
                            <!-- Accordion Item 1: Anagrafica azienda -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAnagrafica" aria-expanded="false" aria-controls="collapseAnagrafica">
                                        Anagrafica azienda
                                    </button>
                                </h2>
                                <div id="collapseAnagrafica" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <div class="row gy-4">
                                            <div class="col-md-6">
                                                <div class="small text-muted">Partita IVA</div>
                                                <div><?php echo $partita_iva ? esc_html($partita_iva) : '-'; ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="small text-muted">Telefono</div>
                                                <div><?php echo $phone ? esc_html($phone) : '-'; ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="small text-muted">Dominio</div>
                                                <div><?php echo $domain ? esc_html($domain) : '-'; ?></div>
                                            </div>
                                            <div class="col-12">
                                                <div class="small text-muted">Indirizzo</div>
                                                <div><?php echo $address ? nl2br( esc_html($address) ) : '-'; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Accordion Item 2: Dati economici -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEconomici" aria-expanded="false" aria-controls="collapseEconomici">
                                        Dati economici
                                    </button>
                                </h2>
                                <div id="collapseEconomici" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <div class="row gy-4">
                                            <div class="col-md-6">
                                                <div class="small text-muted">Fatturato stimato</div>
                                                <div><?php echo $estimated_annual_revenue ? esc_html($estimated_annual_revenue) : '-'; ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="small text-muted">EBITDA % stimato</div>
                                                <div><?php echo $estimated_ebitda_percentage !== '' ? esc_html($estimated_ebitda_percentage) : '-'; ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="small text-muted">Budget Marketing</div>
                                                <div><?php echo $estimated_marketing_budget ? esc_html($estimated_marketing_budget) : '-'; ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="small text-muted">Tier Budget</div>
                                                <div><?php echo $budget_tier ? esc_html($budget_tier) : '-'; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Accordion Item 3: Campagna -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCampagna" aria-expanded="false" aria-controls="collapseCampagna">
                                        Campagna
                                    </button>
                                </h2>
                                <div id="collapseCampagna" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <div class="row gy-4">
                                            <div class="col-md-6">
                                                <div class="small text-muted">Nome</div>
                                                <div><?php echo $campaign_name ? esc_html($campaign_name) : '-'; ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="small text-muted">Query Utilizzata</div>
                                                <div><?php echo $query_used ? esc_html($query_used) : '-'; ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="small text-muted">Discovery ID</div>
                                                <div><?php echo $discovery_id ? esc_html($discovery_id) : '-'; ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="small text-muted">Run ID</div>
                                                <div><?php echo $run_id ? esc_html($run_id) : '-'; ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="small text-muted">Completezza Dati</div>
                                                <div><?php echo $data_completeness !== '' ? esc_html($data_completeness) . '%' : '-'; ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="small text-muted">Fonte</div>
                                                <div><?php echo $source ? esc_html($source) : '-'; ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="small text-muted">Data Analisi</div>
                                                <div>
                                                    <?php
                                                    if ( $analysis_date ) {
                                                        $dt = DateTime::createFromFormat('d/m/Y g:i a', $analysis_date);
                                                        echo $dt ? esc_html( $dt->format('Y-m-d H:i') ) : esc_html($analysis_date);
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3 mt-3">
                                                <div class="small text-muted">Status</div>
                                                <div><?php echo $status ? esc_html($status) : '-'; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Accordion Item 4: Dati Google -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGoogle" aria-expanded="false" aria-controls="collapseGoogle">
                                        Dati Google
                                    </button>
                                </h2>
                                <div id="collapseGoogle" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <div class="row gy-4">
                                            <div class="col-md-6">
                                                <div class="small text-muted">Rating</div>
                                                <div><?php echo $rating !== '' ? esc_html($rating) : '-'; ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="small text-muted">Recensioni</div>
                                                <div><?php echo $reviews !== '' ? esc_html($reviews) : '-'; ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="small text-muted">Data Discovery</div>
                                                <div><?php echo $discovery_date ? esc_html($discovery_date) : '-'; ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="small text-muted">Ora Discovery</div>
                                                <div><?php echo $discovery_time ? esc_html($discovery_time) : '-'; ?></div>
                                            </div>
                                            <div class="col-12">
                                                <div class="small text-muted">Place ID</div>
                                                <div><?php echo $place_id ? esc_html($place_id) : '-'; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!--position-sticky-->
                </div>

                <!-- Colonna sinistra: dettaglio -->
                <div class="col-md-6 col-lg-7 col-xxl-8">

                    <?php
                    // Array degli slug da controllare
                    $term_slugs = array('audit', 'sales_strategy');

                    foreach ($term_slugs as $term_slug) :
                        // Recupera informazioni del termine
                        $term = get_term_by('slug', $term_slug, 'agent_type');
                        $term_name = $term ? $term->name : ucfirst(str_replace('_', ' ', $term_slug));

                        // Query per trovare l'analisi corrispondente
                        $args = array(
                            'post_type'      => 'analisi',
                            'posts_per_page' => 1,
                            'post_status'    => 'publish',
                            'meta_query'     => array(
                                array(
                                    'key'     => 'parent_company_id',
                                    'value'   => $post_id,
                                    'compare' => '='
                                ),
                            ),
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'agent_type',
                                    'field'    => 'slug',
                                    'terms'    => $term_slug,
                                ),
                            ),
                        );

                        $analisi_query = new WP_Query($args);

                        // Recupera core_promise se l'analisi esiste
                        $core_promise = null;
                        if ($analisi_query->have_posts()) {
                            $analisi_query->the_post();
                            $core_promise = get_field('core_promise') ?: 'Campo non compilato.';
                            wp_reset_postdata();
                        }
                        ?>
                        <div class="mb-5">
                            <div class="text">
                                <h2 class="mb-2"><?php echo esc_html($term_name); ?></h2>
                                <div>
                                    <?php
                                    if ($core_promise) {
                                        echo nl2br(esc_html($core_promise));
                                    } else {
                                        echo 'Analisi non ancora effettuata.';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>


                    <!-- Analisi correlate per agent type -->
                    <?php
                    $agents = psip_get_agents();
                    $analisi_for_company = psip_get_company_analyses($post_id);

                    // Mappa delle metriche con icone e nomi campi ACF
                    $metrics_map = [
                        ['label' => 'Opportunità',   'icon' => 'dashicons-thumbs-up',    'field' => 'numero_oppurtunita'],
                        ['label' => 'Quick Wins',    'icon' => 'dashicons-star-filled',  'field' => 'quick_wins'],
                        ['label' => 'Punti di forza','icon' => 'dashicons-awards',       'field' => 'punti_di_forza'],
                        ['label' => 'Debolezze',     'icon' => 'dashicons-warning',      'field' => 'debolezze'],
                        ['label' => 'Gap rilevati',  'icon' => 'dashicons-thumbs-down',  'field' => 'gap_rilevati'],
                    ];
                    ?>

                    <?php if (!empty($agents)) : ?>
                        <div id="table_agents">
                            <div class="table-responsive">
                                <table id="companyAgents" class="stripe cell-border hover">
                                    <thead>
                                        <tr>
                                            <th>Agent Type</th>
                                            <th class="th-score">CS</th>
                                            <?php foreach ($metrics_map as $metric) : ?>
                                                <th data-bs-toggle="tooltip" title="<?php echo esc_attr($metric['label']); ?>">
                                                    <span class="dashicons <?php echo esc_attr($metric['icon']); ?>"></span>
                                                </th>
                                            <?php endforeach; ?>
                                            <th>Esecuzione</th>
                                            <th>Azioni</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($agents as $slug => $agent_config) :
                                            $has_analysis = isset($analisi_for_company[$slug]);
                                            $analysis_id = $has_analysis ? $analisi_for_company[$slug] : null;

                                            $score = $has_analysis ? get_field('confidence_score', $analysis_id) : null;
                                            $when = $has_analysis ? get_the_date( 'Y-m-d H:i' , $analysis_id) : null;
                                            $insights = $has_analysis ? get_field('core_promise', $analysis_id) : null;
                                            $bg_color = $score !== null && $score !== '' ? get_heatmap_color($score) : 'transparent';
                                        ?>
                                        <tr>
                                            <td>
                                                <a href="#analysis-tabs" class="analysis-name"><span class="dashicons <?php echo esc_attr($agent_config['icon']); ?>"></span> <?php echo esc_html($agent_config['name']); ?></a>
                                            </td>
                                            <td class="agent-score" style="background-color:<?php echo $bg_color; ?>" data-bs-toggle="tooltip" title="Confidence score"><?php echo ($score !== null && $score !== '') ? esc_html((string) $score) : ''; ?></td>

                                            <?php foreach ($metrics_map as $metric) :
                                                $value = $has_analysis ? get_field($metric['field'], $analysis_id) : null;
                                                $display_value = ($value !== null && $value !== '') ? esc_html((string) $value) : ''; ?>
                                                <td data-bs-toggle="tooltip" title="<?php echo esc_attr($agent_config['name'] . ': ' . $metric['label']); ?>" class="agent-score" style="background-color:<?php echo $bg_color; ?>"><?php echo $display_value; ?></td>
                                            <?php endforeach; ?>

                                            <td><?php echo ($when !== null && $when !== '') ? esc_html((string) $when) : ''; ?></td>
                                            <td>
                                                <?php if ($has_analysis) : ?>
                                                    <a href="javascript:void(0)" class="analysis-link" data-text="✓ Run" onclick="alert('Coming soon!')"></a>
                                                <?php else : ?>
                                                    <a href="javascript:void(0)" class="analysis-link" data-text="Launch" onclick="alert('Coming soon!')"></a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>



                </div>

            </div>

        </div>
    </section>
</main>

<?php get_template_part( 'inc/company-analysis-tabs' ); ?>

<?php
endwhile;
endif;

get_footer();
