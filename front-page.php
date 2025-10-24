<?php get_header(); ?>

<section id="hero">
    <section class="container-fluid">
        <section class="row">
            <section class="col">
                <h1 class="hero-title">Perspect Lead Generator</h1>
            </section><!--col-->
        </section><!--row-->
    </section><!--container-fluid-->
</section>

<main id="front_page" role="main">
    <div class="container-fluid">

        <?php
        $args = [
            'post_type' => ['azienda'],
            'post_status' => ['publish'],
            'posts_per_page' => -1,
            'order' => 'ASC',
            'orderby' => 'title',
        ];

        $query = new WP_Query( $args );

        if ( $query->have_posts() ) :

            // Pre-carica tutte le analisi per performance
            $azienda_ids = wp_list_pluck( $query->posts, 'ID' );
            $analisi_map = perspect_preload_analisi_by_aziende( $azienda_ids );

            // Ottieni tipi agent disponibili
            $agent_types = psip_get_agents(); ?>

            <table id="companiesTable" class="perspect-table hover cell-border compact stripe" data-page-length='100'>
                <thead>
                    <tr>
                        <th width="200px">Azienda</th>
                        <th>Tipo di Business</th>
                        <th>Provincia</th>
                        <th>Data enrichment</th>
                        <th data-bs-toggle="tooltip" title="Budget Marketing">Bgt Mkt</th>
                        <th data-bs-toggle="tooltip" title="Tier Budget">Tier Bgt</th>
                        <th>Status</th>
                        <?php foreach ($agent_types as $slug => $agent_config) : ?>
                            <th data-bs-toggle="tooltip" title="<?php echo esc_attr($agent_config['name']); ?>" class="th-score"><span class="dashicons <?php echo $agent_config['icon'] ?>"></span></th>
                        <?php endforeach; ?>
                        <th>Campagna</th>
                        <th>Query</th>
                    </tr>
                </thead>
                <tbody>

                    <?php while ( $query->have_posts() ) : $query->the_post();
                        $post_id                    = get_the_ID();
                        $status                     = get_field('status');
                        $growth_stage               = get_field('growth_stage');
                        $estimated_marketing_budget = get_field('estimated_marketing_budget');
                        $confidence                 = get_field('confidence');
                        $budget_tier                = get_field_object('budget_tier');
                        $analysis_date              = get_field('analysis_date');
                        $campaign_name              = get_field('campaign_name');
                        $query_used                 = get_field('query_used');
                        $business_type              = get_field('sector_specific');
                        $province                   = get_field('provincia'); ?>
                        <tr>
                            <td>
                                <a href="<?php the_permalink(); ?>" class="company-name"><?php the_title(); ?></a>
                                <a href="<?php echo esc_url( get_field('website') ); ?>" class="no-dt company-web" target="_blank"></a>
                            </td>
                            <td><?php if ( $business_type ) : ?><div class="company-type"><?php echo esc_html( $business_type ); ?></div><?php endif; ?></td>
                            <td><?php echo $province ?: '' ; ?></td>
                            <td>
                                <?php if ($analysis_date) :
                                $dt = DateTime::createFromFormat('d/m/Y g:i a', $analysis_date);
                                $analysis_date_ymd = $dt ? esc_html($dt->format('Y-m-d H:i')) : esc_html($analysis_date);
                                ?>
                                    <div class="company-analysis-date"><?php echo $analysis_date_ymd; ?></div>
                                <?php endif; ?>
                                <a href="javascript:void(0)" class="no-dt company-enrichment" onclick="alert('Coming soon!')"></a>
                            </td>
                            <td><?php echo $estimated_marketing_budget ?: ''; ?></td>
                            <?php
                            $budget_tier_value = (int)$budget_tier['value'];
                            $budget_tier_label = $budget_tier['choices'][ $budget_tier_value ]; ?>
                            <td data-bs-toggle="tooltip" title="<?php echo $budget_tier_label; ?>" class="text-center"><?php echo str_repeat('<span class="money">$</span>', $budget_tier_value); ?></td>
                            <td data-bs-toggle="tooltip" data-bs-html="true" title="<?php echo $status ?: '-'; ?><div class='small'><?php echo 'Confidence score: <span class=\'badge\'>'.$confidence.'</span>'; ?></div>" class="text-center"><?php echo $status ? ( $status==='QUALIFIED' ? '<span class=\'dashicons dashicons-yes-alt\'></span>' : '<span class=\'dashicons dashicons-no\'></span>' ) : ''; ?></td>
                            <?php foreach ($agent_types as $slug => $agent_config) : ?>
                                <?php echo perspect_get_score_cell($analisi_map, $post_id, $slug); ?>
                            <?php endforeach; ?>
                            <td><?php echo $campaign_name ?: ''; ?></td>
                            <td><?php echo $query_used ?: ''; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php
            wp_reset_postdata();
        endif; ?>

    </div>
</main>

<?php get_footer(); ?>
