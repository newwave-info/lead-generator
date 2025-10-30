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
                        $status                     = psip_theme_normalize_scalar( get_field('status') );
                        $growth_stage               = psip_theme_normalize_scalar( get_field('growth_stage') );
                        $estimated_marketing_budget = psip_theme_normalize_scalar( get_field('estimated_marketing_budget') );
                        $confidence                 = psip_theme_normalize_scalar( get_field('confidence') );
                        $analysis_date_raw          = get_field('analysis_date');
                        $analysis_date              = psip_theme_normalize_scalar( $analysis_date_raw );
                        $campaign_name              = psip_theme_normalize_scalar( get_field('campaign_name') );
                        $query_used                 = psip_theme_normalize_scalar( get_field('query_used') );
                        $business_type              = psip_theme_normalize_scalar( get_field('sector_specific') );
                        $province                   = psip_theme_normalize_scalar( get_field('provincia') );
                        $website                    = psip_theme_normalize_scalar( get_field('website') );

                        $budget_tier_field = get_field_object('budget_tier');
                        $budget_tier_value = 0;
                        $budget_tier_label = '';
                        if ($budget_tier_field) {
                            $raw_value = $budget_tier_field['value'];
                            if (is_array($raw_value)) {
                                $raw_value = reset($raw_value);
                            }
                            $normalized_value = psip_theme_normalize_scalar($raw_value);
                            if ($normalized_value !== '' && is_numeric($normalized_value)) {
                                $budget_tier_value = (int) $normalized_value;
                            }

                            if (isset($budget_tier_field['choices'][$normalized_value])) {
                                $budget_tier_label = $budget_tier_field['choices'][$normalized_value];
                            } elseif (isset($budget_tier_field['choices'][(string) $budget_tier_value])) {
                                $budget_tier_label = $budget_tier_field['choices'][(string) $budget_tier_value];
                            }
                        }

                        $analysis_date_display = '';
                        if ($analysis_date !== '') {
                            $dt = DateTime::createFromFormat('d/m/Y g:i a', $analysis_date);
                            $analysis_date_display = $dt ? $dt->format('Y-m-d H:i') : $analysis_date;
                        }

                        $status_display = $status !== '' ? $status : '-';
                        $confidence_display = $confidence !== '' ? $confidence : '—';
                        $status_tooltip = sprintf(
                            '%s | Confidence score: %s',
                            $status_display,
                            $confidence_display
                        ); ?>
                        <tr>
                            <td>
                                <a href="<?php the_permalink(); ?>" class="company-name"><?php the_title(); ?></a>
                                <?php if ($website !== '') : ?>
                                    <a href="<?php echo esc_url( $website ); ?>" class="no-dt company-web" target="_blank" rel="noopener"></a>
                                <?php endif; ?>
                            </td>
                            <td><?php if ( $business_type !== '' ) : ?><div class="company-type"><?php echo esc_html( $business_type ); ?></div><?php endif; ?></td>
                            <td><?php echo $province !== '' ? esc_html($province) : ''; ?></td>
                            <td>
                                <?php if ($analysis_date_display !== '') : ?>
                                    <div class="company-analysis-date"><?php echo esc_html($analysis_date_display); ?></div>
                                <?php endif; ?>
                                <a href="javascript:void(0)" class="no-dt company-enrichment" onclick="alert('Coming soon!')"></a>
                            </td>
                            <td><?php echo $estimated_marketing_budget !== '' ? esc_html($estimated_marketing_budget) : ''; ?></td>
                            <td data-bs-toggle="tooltip" title="<?php echo esc_attr($budget_tier_label); ?>" class="text-center">
                                <?php echo $budget_tier_value > 0 ? str_repeat('<span class="money">$</span>', $budget_tier_value) : ''; ?>
                            </td>
                            <td
                                data-bs-toggle="tooltip"
                                title="<?php echo esc_attr($status_tooltip); ?>"
                                class="text-center">
                                <?php
                                if ($status_display !== '-') {
                                    echo $status_display === 'QUALIFIED'
                                        ? '<span class="dashicons dashicons-yes-alt"></span>'
                                        : '<span class="dashicons dashicons-no"></span>';
                                }
                                ?>
                            </td>
                            <?php foreach ($agent_types as $slug => $agent_config) : ?>
                                <?php echo perspect_get_score_cell($analisi_map, $post_id, $slug); ?>
                            <?php endforeach; ?>
                            <td><?php echo $campaign_name !== '' ? esc_html($campaign_name) : ''; ?></td>
                            <td><?php echo $query_used !== '' ? esc_html($query_used) : ''; ?></td>
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
