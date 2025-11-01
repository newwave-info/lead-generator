<?php
get_header();

if (have_posts()):
    while (have_posts()):

        the_post();
        $post_id = get_the_ID();

        // Campi ACF principali
        $company_name_full = get_field('company_name_full', $post_id);
        $qualification_status = get_field('qualification_status', $post_id);
        $qualification_reason = get_field('qualification_reason', $post_id);
        $service_fit = get_field('service_fit', $post_id);
        $priority_score = get_field('priority_score', $post_id);
        $financial_confidence = get_field('financial_confidence', $post_id);
        $digital_maturity_score = get_field('digital_maturity_score', $post_id);
        $growth_stage = get_field('growth_stage', $post_id);
        $budget_tier = get_field('budget_tier', $post_id);
        $marketing_budget_est = get_field('marketing_budget_est', $post_id);
        $annual_revenue = get_field('annual_revenue', $post_id);
        $ebitda_margin_est = get_field('ebitda_margin_est', $post_id);
        $employee_count = get_field('employee_count', $post_id);
        $geography_scope = get_field('geography_scope', $post_id);
        $short_bio = get_field('short_bio', $post_id);
        $partita_iva = get_field('partita_iva', $post_id);
        $address = get_field('address', $post_id);
        $city = get_field('city', $post_id);
        $province = get_field('province', $post_id);
        $phone = get_field('phone', $post_id);
        $email = get_field('email', $post_id);
        $domain = get_field('domain', $post_id);
        $business_type = get_field('business_type', $post_id);
        $sector_specific = get_field('sector_specific', $post_id);
        $linkedin_url = get_field('linkedin_url', $post_id);
        $social_links = get_field('social_links', $post_id);
        $analisy_perplexity_deep_research = get_field('analisy_perplexity_deep_research', $post_id);
        $perplexity_analysis_markup = $analisy_perplexity_deep_research;

        $enrichment_last_status_code = get_field('enrichment_last_status_code', $post_id);
        $enrichment_last_message = get_field('enrichment_last_message', $post_id);
        $enrichment_last_at = get_field('data_ultimo_enrichment', $post_id);
        if ($enrichment_last_at === '') {
            $enrichment_last_at = get_post_meta($post_id, 'enrichment_last_at', true);
        }

        // Normalizzazione campi scalari
        $scalar_fields = [
            'company_name_full', 'qualification_status', 'qualification_reason', 'service_fit',
            'priority_score', 'financial_confidence', 'digital_maturity_score', 'growth_stage',
            'budget_tier', 'marketing_budget_est', 'annual_revenue', 'ebitda_margin_est',
            'employee_count', 'geography_scope', 'short_bio', 'partita_iva', 'address',
            'city', 'province', 'phone', 'email', 'domain', 'business_type', 'sector_specific',
            'linkedin_url', 'enrichment_last_status_code', 'enrichment_last_message',
        ];

        foreach ($scalar_fields as $field_key) {
            $$field_key = psip_theme_normalize_scalar($$field_key);
        }

        // Social links
        $social_links_list = [];
        if (is_string($social_links) && $social_links !== '') {
            $social_links_list = array_filter(array_map(
                static function ($entry) {
                    $entry = trim($entry);
                    return $entry !== '' ? $entry : null;
                },
                preg_split('/\r\n|\r|\n/', $social_links)
            ));
        }

        // Perplexity markdown processing
        if ($perplexity_analysis_markup) {
            $perplexity_analysis_markup = preg_replace_callback(
                "/^\s*#{1,6}\s*(.*?)\s*#*\s*$/m",
                static function ($matches) {
                    $heading_text = trim($matches[1]);
                    if ($heading_text === "") return $matches[0];
                    return "<strong>" . esc_html($heading_text) . "</strong>";
                },
                $perplexity_analysis_markup
            );
        }

        // Display variables
        $industry_display = $business_type !== '' ? $business_type : '—';
        $subindustry_display = $sector_specific !== '' ? $sector_specific : '—';
        $headquarters_display = '—';
        if ($city !== '' || $province !== '') {
            $headquarters_parts = array_filter([$city !== '' ? $city : null, $province !== '' ? $province : null]);
            $headquarters_display = implode(' · ', $headquarters_parts);
        } elseif ($address !== '') {
            $headquarters_display = $address;
        }

        $status_display = $qualification_status !== '' ? ucwords($qualification_status) : '—';
        $growth_stage_display = $growth_stage !== '' ? $growth_stage : '—';
        $geography_display = $geography_scope !== '' ? $geography_scope : '—';
        $budget_tier_display = $budget_tier !== '' ? $budget_tier : '—';
        $marketing_budget_display = $marketing_budget_est !== '' ? $marketing_budget_est : '—';
        $annual_revenue_display = $annual_revenue !== '' ? $annual_revenue : '—';
        $ebitda_margin_display = $ebitda_margin_est !== '' ? $ebitda_margin_est . '%' : '—';

        // Numeric values
        $priority_score_numeric = null;
        if ($priority_score !== '' && is_numeric($priority_score)) {
            $priority_score_numeric = (float) $priority_score;
        }
        $priority_score_display = $priority_score_numeric !== null
            ? number_format($priority_score_numeric, 0)
            : ($priority_score !== '' ? $priority_score : '—');

        $financial_confidence_numeric = null;
        if ($financial_confidence !== '' && is_numeric($financial_confidence)) {
            $financial_confidence_numeric = max(0, min(100, (float) $financial_confidence));
        }

        $digital_maturity_numeric = null;
        if ($digital_maturity_score !== '' && is_numeric($digital_maturity_score)) {
            $digital_maturity_numeric = max(0, min(100, (float) $digital_maturity_score));
        }
        $digital_maturity_percent = $digital_maturity_numeric !== null ? (int) round($digital_maturity_numeric) : null;

        $enrichment_completeness_raw = get_post_meta($post_id, 'enrichment_completeness', true);
        $enrichment_completeness_percent = null;
        if ($enrichment_completeness_raw !== '') {
            $enrichment_completeness_percent = (int) round((float) $enrichment_completeness_raw);
        }

        $enrichment_last_at_display = '—';
        if ($enrichment_last_at !== '') {
            $timestamp = strtotime($enrichment_last_at);
            if ($timestamp) {
                $enrichment_last_at_display = date_i18n('d/m/Y H:i', $timestamp);
            } else {
                $enrichment_last_at_display = $enrichment_last_at;
            }
        }

        $is_verified = strtolower($qualification_status) === 'qualificata';

        // Website URL
        $website_url = '';
        if ($domain !== '') {
            $website_candidate = $domain;
            if (strpos($website_candidate, 'http://') !== 0 && strpos($website_candidate, 'https://') !== 0) {
                $website_candidate = 'https://' . ltrim($website_candidate, '/');
            }
            $website_url = $website_candidate;
        }

        $logo_domain = $domain !== '' ? preg_replace('#^https?://#i', '', $domain) : '';

        // Display social links
        $display_social_links = $social_links_list;
        if ($linkedin_url !== '') {
            $has_linkedin = false;
            foreach ($display_social_links as $link_item) {
                if (stripos($link_item, 'linkedin.com') !== false) {
                    $has_linkedin = true;
                    break;
                }
            }
            if (!$has_linkedin) {
                $display_social_links[] = $linkedin_url;
            }
        }

        // Agent analyses
        $agents = psip_get_agents();
        $analisi_for_company = psip_get_company_analyses($post_id);

        $agent_total = count($agents);
        $analysis_completed = 0;
        $quality_sum = 0.0;
        $quality_count = 0;

        foreach ($analisi_for_company as $analysis_item) {
            if (!is_array($analysis_item) || empty($analysis_item)) continue;
            $analysis_completed++;
            $analysis_id = $analysis_item["id"] ?? null;
            if (!$analysis_id) continue;

            $quality_raw = get_field("voto_qualita_analisi", $analysis_id);
            $quality_normalized = psip_theme_normalize_scalar($quality_raw);
            if ($quality_normalized !== "" && is_numeric($quality_normalized)) {
                $quality_sum += (float) $quality_normalized;
                $quality_count++;
            }
        }

        $analysis_completion_rate = $agent_total > 0
            ? round(($analysis_completed / $agent_total) * 100)
            : 0;
        $average_quality_display = $quality_count > 0
            ? number_format($quality_sum / $quality_count, 1)
            : "—";

        $metrics_map = [
            [
                "label" => "Punti di forza",
                "icon" => "dashicons-awards",
                "field" => "numero_punti_di_forza",
            ],
            [
                "label" => "Punti di debolezza",
                "icon" => "dashicons-warning",
                "field" => "numero_punti_di_debolezza",
            ],
            [
                "label" => "Opportunità",
                "icon" => "dashicons-thumbs-up",
                "field" => "numero_opportunita",
            ],
            [
                "label" => "Azioni rapide",
                "icon" => "dashicons-star-filled",
                "field" => "numero_azioni_rapide",
            ],
        ];

        $screen_home = get_field("screen_home");
        ?>

<main id="single_company" role="main" class="lead-company-dashboard">

    <!-- Hero Header -->
    <section class="lead-hero">
        <div class="container-fluid">
            <div class="lead-hero__wrapper">
                <div class="lead-hero__identity">
                    <div class="lead-hero__logo-container">
                        <?php if ($logo_domain): ?>
                            <img class="lead-hero__logo"
                                 src="https://api.microlink.io/?url=https%3A%2F%2F<?php echo esc_attr($logo_domain); ?>&palette=true&embed=logo.url"
                                 loading="lazy"
                                 alt="<?php echo esc_attr(get_the_title()); ?>" />
                        <?php endif; ?>
                    </div>
                    <div class="lead-hero__info">
                        <div class="lead-hero__breadcrumb">
                            <?php echo esc_html($industry_display); ?>
                            <?php if ($geography_display !== '—'): ?>
                                · <?php echo esc_html($geography_display); ?>
                            <?php endif; ?>
                        </div>
                        <h1 class="lead-hero__title"><?php the_title(); ?></h1>
                        <div class="lead-hero__meta">
                            <span class="lead-badge lead-badge--<?php echo $is_verified ? 'success' : 'pending'; ?>">
                                <?php if ($is_verified): ?>
                                    <span class="dashicons dashicons-yes-alt"></span>
                                <?php endif; ?>
                                <?php echo esc_html($status_display); ?>
                            </span>
                            <?php if ($budget_tier_display !== '—'): ?>
                                <span class="lead-badge lead-badge--tier"><?php echo esc_html($budget_tier_display); ?></span>
                            <?php endif; ?>
                            <?php if ($growth_stage_display !== '—'): ?>
                                <span class="lead-badge lead-badge--neutral"><?php echo esc_html($growth_stage_display); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="lead-hero__score-card">
                    <div class="lead-score">
                        <div class="lead-score__label">Priority Score</div>
                        <div class="lead-score__value"><?php echo esc_html($priority_score_display); ?></div>
                        <div class="lead-score__max">/100</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Stats Bar -->
    <section class="lead-stats-bar">
        <div class="container-fluid">
            <div class="lead-stats-bar__grid">
                <div class="lead-stat">
                    <span class="lead-stat__label">Fatturato</span>
                    <span class="lead-stat__value"><?php echo esc_html($annual_revenue_display); ?></span>
                </div>
                <div class="lead-stat">
                    <span class="lead-stat__label">EBITDA Margin</span>
                    <span class="lead-stat__value"><?php echo esc_html($ebitda_margin_display); ?></span>
                </div>
                <div class="lead-stat">
                    <span class="lead-stat__label">Budget Marketing</span>
                    <span class="lead-stat__value"><?php echo esc_html($marketing_budget_display); ?></span>
                </div>
                <div class="lead-stat">
                    <span class="lead-stat__label">Digital Maturity</span>
                    <span class="lead-stat__value">
                        <?php echo $digital_maturity_percent !== null ? esc_html($digital_maturity_percent) . '%' : '—'; ?>
                    </span>
                </div>
                <div class="lead-stat">
                    <span class="lead-stat__label">Dipendenti</span>
                    <span class="lead-stat__value"><?php echo $employee_count !== '' ? esc_html($employee_count) : '—'; ?></span>
                </div>
                <div class="lead-stat">
                    <span class="lead-stat__label">Analisi completate</span>
                    <span class="lead-stat__value"><?php echo esc_html((string) $analysis_completed); ?>/<?php echo esc_html((string) $agent_total); ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="lead-content">
        <div class="container-fluid">
            <div class="lead-layout">

                <!-- Main Column -->
                <div class="lead-layout__main">

                    <!-- Commercial Intelligence -->
                    <?php if ($qualification_reason !== '' || $service_fit !== ''): ?>
                    <div class="lead-card lead-card--highlight">
                        <div class="lead-card__header">
                            <h2 class="lead-card__title">
                                <span class="dashicons dashicons-lightbulb"></span>
                                Commercial Intelligence
                            </h2>
                            <p class="lead-card__subtitle">Opportunità commerciali e fit servizi</p>
                        </div>
                        <div class="lead-card__body">
                            <?php if ($qualification_reason !== ''): ?>
                            <div class="lead-insight">
                                <h3 class="lead-insight__title">Perché questa azienda è interessante</h3>
                                <p class="lead-insight__text"><?php echo esc_html($qualification_reason); ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if ($service_fit !== ''): ?>
                            <div class="lead-insight">
                                <h3 class="lead-insight__title">Servizi consigliati</h3>
                                <p class="lead-insight__text"><?php echo esc_html($service_fit); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Company Overview -->
                    <div class="lead-card">
                        <div class="lead-card__header">
                            <h2 class="lead-card__title">
                                <span class="dashicons dashicons-building"></span>
                                Company Overview
                            </h2>
                        </div>
                        <div class="lead-card__body">
                            <?php if ($short_bio !== ''): ?>
                            <div class="lead-description">
                                <p><?php echo esc_html($short_bio); ?></p>
                            </div>
                            <?php endif; ?>

                            <div class="lead-info-grid">
                                <div class="lead-info-item">
                                    <span class="lead-info-item__label">Ragione sociale</span>
                                    <span class="lead-info-item__value"><?php echo $company_name_full !== '' ? esc_html($company_name_full) : esc_html(get_the_title()); ?></span>
                                </div>
                                <div class="lead-info-item">
                                    <span class="lead-info-item__label">Settore</span>
                                    <span class="lead-info-item__value"><?php echo esc_html($industry_display); ?></span>
                                </div>
                                <div class="lead-info-item">
                                    <span class="lead-info-item__label">Specializzazione</span>
                                    <span class="lead-info-item__value"><?php echo esc_html($subindustry_display); ?></span>
                                </div>
                                <div class="lead-info-item">
                                    <span class="lead-info-item__label">Dipendenti</span>
                                    <span class="lead-info-item__value"><?php echo $employee_count !== '' ? esc_html($employee_count) : '—'; ?></span>
                                </div>
                                <div class="lead-info-item">
                                    <span class="lead-info-item__label">Stadio di crescita</span>
                                    <span class="lead-info-item__value"><?php echo esc_html($growth_stage_display); ?></span>
                                </div>
                                <div class="lead-info-item">
                                    <span class="lead-info-item__label">Copertura geografica</span>
                                    <span class="lead-info-item__value"><?php echo esc_html($geography_display); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Deep Research -->
                    <?php if ($perplexity_analysis_markup): ?>
                    <div class="lead-card">
                        <div class="lead-card__header">
                            <h2 class="lead-card__title">
                                <span class="dashicons dashicons-search"></span>
                                Deep Research
                            </h2>
                            <p class="lead-card__subtitle">Analisi approfondita generata da AI</p>
                        </div>
                        <div class="lead-card__body">
                            <div class="lead-research-content">
                                <?php echo wpautop(wp_kses_post($perplexity_analysis_markup)); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Agent Analysis Table -->
                    <?php if (!empty($agents)): ?>
                    <div class="lead-card">
                        <div class="lead-card__header">
                            <h2 class="lead-card__title">
                                <span class="dashicons dashicons-chart-area"></span>
                                Analisi AI Agent
                            </h2>
                            <div class="lead-card__header-stats">
                                <span class="lead-badge lead-badge--info"><?php echo esc_html((string) $analysis_completed); ?> completate</span>
                                <span class="lead-badge lead-badge--info">Qualità media: <?php echo esc_html($average_quality_display); ?></span>
                            </div>
                        </div>
                        <div class="lead-card__body lead-card__body--table">
                            <div class="lead-table-wrapper">
                                <table id="companyAgents" class="lead-table display nowrap">
                                    <thead>
                                        <tr>
                                            <th>Agent</th>
                                            <th>Qualità</th>
                                            <?php foreach ($metrics_map as $metric): ?>
                                                <th data-bs-toggle="tooltip" title="<?php echo esc_attr($metric['label']); ?>">
                                                    <span class="dashicons <?php echo esc_attr($metric['icon']); ?>"></span>
                                                </th>
                                            <?php endforeach; ?>
                                            <th>Ultima esecuzione</th>
                                            <th>Azione</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($agents as $slug => $agent_config):
                                            $analysis_info = $analisi_for_company[$slug] ?? null;
                                            $has_analysis = $analysis_info !== null;
                                            $analysis_id = $analysis_info['id'] ?? null;
                                            $last_run_display = $analysis_info['last_run']['display'] ?? '';
                                            $last_run_relative = $analysis_info['last_run']['relative'] ?? '';

                                            $score_value = $has_analysis ? psip_theme_normalize_scalar(get_field('voto_qualita_analisi', $analysis_id)) : '';
                                            $score_numeric = ($score_value !== '' && is_numeric($score_value)) ? (float) $score_value : null;
                                            $score_color = $score_numeric !== null ? get_heatmap_color($score_numeric) : null;

                                            $status_label = $has_analysis ? 'Completata' : 'In attesa';
                                            $status_class = $has_analysis ? 'is-completed' : 'is-pending';
                                            ?>
                                            <tr>
                                                <td class="lead-table__agent">
                                                    <span class="lead-table__agent-icon">
                                                        <span class="dashicons <?php echo esc_attr($agent_config['icon']); ?>"></span>
                                                    </span>
                                                    <div class="lead-table__agent-info">
                                                        <a href="#analysis-tabs" class="lead-table__agent-name">
                                                            <?php echo esc_html($agent_config['name']); ?>
                                                        </a>
                                                        <span class="lead-table__agent-status <?php echo esc_attr($status_class); ?>">
                                                            <?php echo esc_html($status_label); ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="lead-table__score"<?php echo $score_color ? ' style="--score-color:' . esc_attr($score_color) . ';"' : ''; ?>>
                                                    <?php if ($score_numeric !== null): ?>
                                                        <span class="lead-table__score-badge"><?php echo esc_html(number_format($score_numeric, 1)); ?></span>
                                                    <?php else: ?>
                                                        <span class="lead-table__score-badge lead-table__score-badge--empty">—</span>
                                                    <?php endif; ?>
                                                </td>

                                                <?php foreach ($metrics_map as $metric):
                                                    $value = $has_analysis ? psip_theme_normalize_scalar(get_field($metric['field'], $analysis_id)) : '';
                                                    $has_metric_value = $value !== '';
                                                    $display_value = $has_metric_value ? esc_html($value) : '—';
                                                    $metric_class = $has_metric_value ? '' : ' lead-table__metric-pill--empty';
                                                ?>
                                                    <td class="lead-table__metric" data-bs-toggle="tooltip" title="<?php echo esc_attr($agent_config['name'] . ': ' . $metric['label']); ?>">
                                                        <span class="lead-table__metric-pill<?php echo $metric_class; ?>"><?php echo esc_html($display_value); ?></span>
                                                    </td>
                                                <?php endforeach; ?>

                                                <td class="lead-table__timestamp" title="<?php echo $last_run_relative ? esc_attr($last_run_relative) : ''; ?>">
                                                    <?php echo $last_run_display !== '' ? esc_html($last_run_display) : '—'; ?>
                                                </td>
                                                <td class="lead-table__action">
                                                    <button class="lead-table__action-btn" type="button" onclick="alert('Coming soon!')">
                                                        <?php echo $has_analysis ? 'Apri' : 'Avvia'; ?>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>

                <!-- Sidebar -->
                <aside class="lead-layout__sidebar">

                    <!-- Quick Actions -->
                    <div class="lead-card lead-card--sidebar">
                        <div class="lead-card__header">
                            <h3 class="lead-card__title">Azioni rapide</h3>
                        </div>
                        <div class="lead-card__body">
                            <div class="lead-actions">
                                <?php if ($website_url !== ''): ?>
                                    <a class="lead-action-btn lead-action-btn--primary" href="<?php echo esc_url($website_url); ?>" target="_blank" rel="noopener">
                                        <span class="dashicons dashicons-admin-site"></span>
                                        <span>Visita sito</span>
                                    </a>
                                <?php endif; ?>
                                <?php if ($linkedin_url !== ''): ?>
                                    <a class="lead-action-btn" href="<?php echo esc_url($linkedin_url); ?>" target="_blank" rel="noopener">
                                        <span class="dashicons dashicons-linkedin"></span>
                                        <span>LinkedIn</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Riferimenti Aziendali -->
                    <div class="lead-card lead-card--sidebar">
                        <div class="lead-card__header">
                            <h3 class="lead-card__title">
                                <span class="dashicons dashicons-id"></span>
                                Riferimenti aziendali
                            </h3>
                        </div>
                        <div class="lead-card__body">
                            <div class="lead-contact-list">
                                <?php if ($address !== ''): ?>
                                <div class="lead-contact-item">
                                    <span class="lead-contact-item__label">
                                        <span class="dashicons dashicons-location"></span>
                                        Indirizzo
                                    </span>
                                    <span class="lead-contact-item__value"><?php echo esc_html($address); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if ($city !== '' || $province !== ''): ?>
                                <div class="lead-contact-item">
                                    <span class="lead-contact-item__label">
                                        <span class="dashicons dashicons-admin-home"></span>
                                        Città
                                    </span>
                                    <span class="lead-contact-item__value"><?php echo esc_html($headquarters_display); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if ($partita_iva !== ''): ?>
                                <div class="lead-contact-item">
                                    <span class="lead-contact-item__label">
                                        <span class="dashicons dashicons-portfolio"></span>
                                        Partita IVA
                                    </span>
                                    <span class="lead-contact-item__value"><?php echo esc_html($partita_iva); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if ($domain !== ''): ?>
                                <div class="lead-contact-item">
                                    <span class="lead-contact-item__label">
                                        <span class="dashicons dashicons-admin-site"></span>
                                        Dominio
                                    </span>
                                    <span class="lead-contact-item__value">
                                        <?php if ($website_url !== ''): ?>
                                            <a href="<?php echo esc_url($website_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($domain); ?></a>
                                        <?php else: ?>
                                            <?php echo esc_html($domain); ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                                <?php if ($email !== ''): ?>
                                <div class="lead-contact-item">
                                    <span class="lead-contact-item__label">
                                        <span class="dashicons dashicons-email"></span>
                                        Email
                                    </span>
                                    <span class="lead-contact-item__value">
                                        <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                                    </span>
                                </div>
                                <?php endif; ?>
                                <?php if ($phone !== ''): ?>
                                <div class="lead-contact-item">
                                    <span class="lead-contact-item__label">
                                        <span class="dashicons dashicons-phone"></span>
                                        Telefono
                                    </span>
                                    <span class="lead-contact-item__value">
                                        <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a>
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Website Screenshot -->
                    <?php if ($screen_home):
                        $image_data = wp_get_attachment_image_src($screen_home, "full");
                    ?>
                    <div class="lead-card lead-card--sidebar">
                        <div class="lead-card__header">
                            <h3 class="lead-card__title">Website Preview</h3>
                            <a class="lead-card__link" href="<?php echo esc_url($image_data[0]); ?>" data-fancybox>
                                Espandi
                            </a>
                        </div>
                        <div class="lead-card__body lead-card__body--media">
                            <div class="lead-screenshot">
                                <div class="lead-screenshot__titlebar">
                                    <span class="lead-screenshot__dot"></span>
                                    <span class="lead-screenshot__dot"></span>
                                    <span class="lead-screenshot__dot"></span>
                                </div>
                                <div class="lead-screenshot__image">
                                    <img src="<?php echo esc_url($image_data[0]); ?>" loading="lazy" alt="Website screenshot" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Social Links -->
                    <?php if (!empty($display_social_links)): ?>
                    <div class="lead-card lead-card--sidebar">
                        <div class="lead-card__header">
                            <h3 class="lead-card__title">Social & Media</h3>
                        </div>
                        <div class="lead-card__body">
                            <ul class="lead-social-list">
                                <?php foreach ($display_social_links as $link):
                                    $link_href = $link;
                                    if (strpos($link_href, 'http://') !== 0 && strpos($link_href, 'https://') !== 0) {
                                        $link_href = 'https://' . ltrim($link_href, '/');
                                    }
                                    // Detect platform from URL
                                    $platform = '';
                                    if (stripos($link, 'linkedin.com') !== false) $platform = 'linkedin';
                                    elseif (stripos($link, 'facebook.com') !== false) $platform = 'facebook';
                                    elseif (stripos($link, 'instagram.com') !== false) $platform = 'instagram';
                                    elseif (stripos($link, 'twitter.com') !== false || stripos($link, 'x.com') !== false) $platform = 'twitter';
                                    elseif (stripos($link, 'youtube.com') !== false) $platform = 'youtube';
                                    ?>
                                    <li class="lead-social-item <?php echo $platform ? 'lead-social-item--' . esc_attr($platform) : ''; ?>">
                                        <a href="<?php echo esc_url($link_href); ?>" target="_blank" rel="noopener">
                                            <?php echo esc_html($link); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Debug Info (Collapsible) -->
                    <details class="lead-card lead-card--sidebar lead-card--debug">
                        <summary class="lead-card__header lead-card__header--toggle">
                            <h3 class="lead-card__title">
                                <span class="dashicons dashicons-admin-tools"></span>
                                Dati tecnici
                            </h3>
                        </summary>
                        <div class="lead-card__body">
                            <div class="lead-debug-grid">
                                <div class="lead-debug-item">
                                    <span class="lead-debug-item__label">Completezza</span>
                                    <span class="lead-debug-item__value">
                                        <?php echo $enrichment_completeness_percent !== null ? esc_html($enrichment_completeness_percent) . '%' : '—'; ?>
                                    </span>
                                </div>
                                <div class="lead-debug-item">
                                    <span class="lead-debug-item__label">Confidenza</span>
                                    <span class="lead-debug-item__value">
                                        <?php echo $financial_confidence_numeric !== null ? esc_html(number_format($financial_confidence_numeric, 0)) . '/100' : '—'; ?>
                                    </span>
                                </div>
                                <div class="lead-debug-item">
                                    <span class="lead-debug-item__label">Ultimo enrichment</span>
                                    <span class="lead-debug-item__value"><?php echo esc_html($enrichment_last_at_display); ?></span>
                                </div>
                                <div class="lead-debug-item">
                                    <span class="lead-debug-item__label">Status code</span>
                                    <span class="lead-debug-item__value">
                                        <?php echo $enrichment_last_status_code !== '' ? esc_html($enrichment_last_status_code) : '—'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </details>

                </aside>

            </div>
        </div>
    </section>

</main>

<?php get_template_part("inc/company-analysis-tabs"); ?>

<?php
    endwhile;
endif;

get_footer();
