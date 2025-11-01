<?php
get_header();

if (have_posts()):
    while (have_posts()):

        the_post();
        $post_id = get_the_ID();

        // Campi ACF principali (nuovo modello Company Enrichment)
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

        $scalar_fields = [
            'company_name_full',
            'qualification_status',
            'qualification_reason',
            'service_fit',
            'priority_score',
            'financial_confidence',
            'digital_maturity_score',
            'growth_stage',
            'budget_tier',
            'marketing_budget_est',
            'annual_revenue',
            'ebitda_margin_est',
            'employee_count',
            'geography_scope',
            'short_bio',
            'partita_iva',
            'address',
            'city',
            'province',
            'phone',
            'email',
            'domain',
            'business_type',
            'sector_specific',
            'linkedin_url',
            'enrichment_last_status_code',
            'enrichment_last_message',
        ];

        foreach ($scalar_fields as $field_key) {
            $$field_key = psip_theme_normalize_scalar($$field_key);
        }

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

        if ($perplexity_analysis_markup) {
            $perplexity_analysis_markup = preg_replace_callback(
                "/^\\s*#{1,6}\\s*(.*?)\\s*#*\\s*$/m",
                static function ($matches) {
                    $heading_text = trim($matches[1]);
                    if ($heading_text === "") {
                        return $matches[0];
                    }

                    return "<strong>" . esc_html($heading_text) . "</strong>";
                },
                $perplexity_analysis_markup
            );
        }

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
        $enrichment_last_message_display = $enrichment_last_message !== '' ? wp_trim_words($enrichment_last_message, 18, '…') : '';

        $is_verified = strtolower($qualification_status) === 'qualificata';
        $score_hint = __('Score 0–100', 'psip');

        $hero_breadcrumbs = array_filter([
            $business_type !== '' ? $business_type : null,
            $sector_specific !== '' ? $sector_specific : null,
            $geography_scope !== '' ? $geography_scope : null,
        ]);
        if (empty($hero_breadcrumbs)) {
            $hero_breadcrumbs = ['Lead Generator'];
        }
        $hero_breadcrumbs_text = implode(' · ', $hero_breadcrumbs);

        $website_url = '';
        if ($domain !== '') {
            $website_candidate = $domain;
            if (strpos($website_candidate, 'http://') !== 0 && strpos($website_candidate, 'https://') !== 0) {
                $website_candidate = 'https://' . ltrim($website_candidate, '/');
            }
            $website_url = $website_candidate;
        }

        $logo_domain = $domain !== '' ? preg_replace('#^https?://#i', '', $domain) : '';

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

        $deal_widgets = [
            [
                'label' => __('Completezza dati', 'psip'),
                'value' => $enrichment_completeness_percent !== null ? $enrichment_completeness_percent . '%' : '—',
                'context' => __('Campi popolati dall’enrichment', 'psip'),
            ],
            [
                'label' => __('Ultimo enrichment', 'psip'),
                'value' => $enrichment_last_at_display,
                'context' => __('Timestamp workflow', 'psip'),
            ],
            [
                'label' => __('Confidenza dati', 'psip'),
                'value' => $financial_confidence_numeric !== null ? number_format($financial_confidence_numeric, 0) . '/100' : '—',
                'context' => __('Valutazione qualità', 'psip'),
            ],
            [
                'label' => __('Priorità', 'psip'),
                'value' => $priority_score_numeric !== null ? number_format($priority_score_numeric, 0) . '/100' : ($priority_score_display !== '—' ? $priority_score_display : '—'),
                'context' => __('Ranking CRM', 'psip'),
            ],
            [
                'label' => __('Stato workflow', 'psip'),
                'value' => $enrichment_last_status_code !== '' ? $enrichment_last_status_code : '—',
                'context' => __('Esito ultima run', 'psip'),
            ],
        ];

        if ($qualification_reason !== '') {
            $deal_widgets[] = [
                'label' => __('Motivazione qualifica', 'psip'),
                'value' => $qualification_reason,
                'context' => __('Decisione commerciale', 'psip'),
            ];
        }
        if ($service_fit !== '') {
            $deal_widgets[] = [
                'label' => __('Service fit', 'psip'),
                'value' => $service_fit,
                'context' => __('Proposta consigliata', 'psip'),
            ];
        }
        if ($enrichment_last_message_display !== '') {
            $deal_widgets[] = [
                'label' => __('Ultimo messaggio', 'psip'),
                'value' => $enrichment_last_message_display,
                'context' => __('Nota workflow', 'psip'),
            ];
        }

        $profile_widgets = [
            [
                'label' => __('Ragione sociale', 'psip'),
                'value' => $company_name_full !== '' ? $company_name_full : get_the_title(),
            ],
            [
                'label' => __('Settore', 'psip'),
                'value' => $industry_display,
            ],
            [
                'label' => __('Specializzazione', 'psip'),
                'value' => $subindustry_display,
            ],
            [
                'label' => __('Dipendenti', 'psip'),
                'value' => $employee_count !== '' ? $employee_count : '—',
            ],
            [
                'label' => __('Copertura', 'psip'),
                'value' => $geography_display,
            ],
            [
                'label' => __('Stadio di crescita', 'psip'),
                'value' => $growth_stage_display,
            ],
            [
                'label' => __('Headquarters', 'psip'),
                'value' => $headquarters_display,
            ],
        ];

        $hero_summary = $short_bio !== ''
            ? wp_trim_words($short_bio, 48, '…')
            : __('Sintesi non disponibile', 'psip');

        $hero_chip_items = array_values(array_filter([
            [
                'label' => __('Settore', 'psip'),
                'value' => $industry_display,
            ],
            [
                'label' => __('Specializzazione', 'psip'),
                'value' => $subindustry_display,
            ],
            [
                'label' => __('Copertura', 'psip'),
                'value' => $geography_display,
            ],
            [
                'label' => __('Stadio di crescita', 'psip'),
                'value' => $growth_stage_display,
            ],
            [
                'label' => __('Budget tier', 'psip'),
                'value' => $budget_tier_display,
            ],
        ], static function ($item) {
            return isset($item['value']) && $item['value'] !== '—' && $item['value'] !== '';
        }));

        $hero_signal_widgets = array_slice($deal_widgets, 0, 3);

        $contact_widgets = [
            [
                'label' => __('Dominio', 'psip'),
                'value' => $domain !== '' ? $domain : '—',
                'url' => $website_url,
            ],
            [
                'label' => __('Email', 'psip'),
                'value' => $email !== '' ? $email : '—',
                'url' => $email !== '' ? 'mailto:' . $email : '',
            ],
            [
                'label' => __('Telefono', 'psip'),
                'value' => $phone !== '' ? $phone : '—',
                'url' => $phone !== '' ? 'tel:' . preg_replace('/\\s+/', '', $phone) : '',
            ],
            [
                'label' => __('Sede', 'psip'),
                'value' => $address !== '' ? $address : ($headquarters_display !== '—' ? $headquarters_display : '—'),
                'multiline' => $address !== '',
            ],
            [
                'label' => __('Partita IVA', 'psip'),
                'value' => $partita_iva !== '' ? $partita_iva : '—',
            ],
        ];
        ?>
<main id="single_company" role="main">

    <section id="company_hero" class="company-hero">
        <div class="company-hero__gradient"></div>
        <div class="container-fluid">
            <div class="company-hero__grid row gy-5 align-items-start">
                <div class="col-12 col-lg-7">
                    <div class="company-hero__header d-flex align-items-start gap-3">
                        <div class="company-hero__logo">
                            <img class="img-contain" src="https://api.microlink.io/?url=https%3A%2F%2F<?php echo esc_html(
                                $logo_domain
                            ); ?>&palette=true&embed=logo.url" loading="lazy" alt="" />
                        </div>
                        <div class="company-hero__headline">
                            <div class="company-hero__breadcrumb small text-muted">
                                <?php echo esc_html($hero_breadcrumbs_text); ?>
                            </div>
                            <h1 class="company-hero__title"><?php the_title(); ?></h1>
                            <div class="company-hero__status">
                                <span class="company-hero__status-pill <?php echo $is_verified
                                    ? 'is-verified'
                                    : 'is-pending'; ?>"><?php echo esc_html($status_display); ?></span>
                                <?php if ($budget_tier_display !== '—'): ?>
                                    <span class="company-hero__badge"><?php echo esc_html($budget_tier_display); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="company-hero__summary">
                        <p><?php echo esc_html($hero_summary); ?></p>
                    </div>

                    <?php if (!empty($hero_chip_items)): ?>
                        <ul class="company-hero__chips">
                            <?php foreach ($hero_chip_items as $chip): ?>
                                <li>
                                    <span class="company-hero__chip-value"><?php echo esc_html($chip['value']); ?></span>
                                    <span class="company-hero__chip-label"><?php echo esc_html($chip['label']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <div class="col-12 col-lg-5 col-xxl-4 offset-xxl-1">
                    <aside class="deal-summary-card">
                        <header class="deal-summary-card__header">
                            <span class="deal-summary-card__eyebrow"><?php esc_html_e('Deal snapshot', 'psip'); ?></span>
                            <?php if ($is_verified): ?>
                                <span class="deal-summary-card__verified">
                                    <span class="dashicons dashicons-yes-alt"></span>
                                    <?php esc_html_e('Qualificata', 'psip'); ?>
                                </span>
                            <?php endif; ?>
                        </header>
                        <div class="deal-summary-card__score">
                            <span class="deal-summary-card__score-value"><?php echo esc_html($priority_score_display); ?></span>
                            <span class="deal-summary-card__score-hint"><?php echo esc_html($score_hint); ?></span>
                        </div>
                        <?php if (!empty($hero_signal_widgets)): ?>
                            <div class="deal-summary-card__signals">
                                <?php foreach ($hero_signal_widgets as $signal): ?>
                                    <div class="deal-summary-card__signal">
                                        <span class="deal-summary-card__signal-value"><?php echo esc_html($signal['value']); ?></span>
                                        <span class="deal-summary-card__signal-label"><?php echo esc_html($signal['label']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <div class="deal-summary-card__actions">
                            <?php if ($website_url !== ''): ?>
                                <a class="deal-summary-card__button" href="<?php echo esc_url($website_url); ?>" target="_blank" rel="noopener">
                                    <?php esc_html_e('Visita sito', 'psip'); ?>
                                </a>
                            <?php endif; ?>
                            <?php if ($linkedin_url !== ''): ?>
                                <a class="deal-summary-card__button deal-summary-card__button--ghost" href="<?php echo esc_url($linkedin_url); ?>" target="_blank" rel="noopener">
                                    LinkedIn
                                </a>
                            <?php endif; ?>
                            <a class="deal-summary-card__button deal-summary-card__button--ghost" href="#company_content">
                                <?php esc_html_e('Apri dettagli', 'psip'); ?>
                            </a>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </section><!--company_hero-->

    <section id="company_content">
        <div class="container-fluid">

            <div class="row gy-4">

                <!-- Colonna destra -->
                <div class="col-md-6 col-lg-5 col-xxl-4 order-md-last">
                    <aside class="company-sidebar">
                        <?php
                        $screen_home = get_field("screen_home");
                        if ($screen_home):
                            $image_data = wp_get_attachment_image_src(
                                $screen_home,
                                "full"
                            );
                        ?>
                            <div class="company-sidebar__card company-sidebar__card--media">
                                <div class="company-sidebar__card-header">
                                    <h2 class="company-sidebar__title">Website</h2>
                                    <a class="company-sidebar__link" href="<?php echo esc_url($image_data[0]); ?>" data-fancybox>
                                        Apri preview
                                    </a>
                                </div>
                                <div class="company-sidebar__media">
                                    <div class="macos-titlebar">
                                        <div class="macos-buttons">
                                            <span class="macos-btn macos-btn-close"></span>
                                            <span class="macos-btn macos-btn-minimize"></span>
                                            <span class="macos-btn macos-btn-maximize"></span>
                                        </div>
                                    </div>
                                    <figure class="figure figure-scroll" style="padding-top: 56.25%;">
                                        <img class="img-cover" src="<?php echo esc_url($image_data[0]); ?>" loading="lazy" />
                                    </figure>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="company-sidebar__card">
                            <h2 class="company-sidebar__title">Azioni rapide</h2>
                            <div class="company-sidebar__actions">
                                <?php if ($website_url !== ''): ?>
                                    <a class="company-sidebar__button" href="<?php echo esc_url($website_url); ?>" target="_blank" rel="noopener">Visita sito</a>
                                <?php endif; ?>
                                <?php if ($linkedin_url !== ''): ?>
                                    <a class="company-sidebar__button company-sidebar__button--ghost" href="<?php echo esc_url($linkedin_url); ?>" target="_blank" rel="noopener">LinkedIn</a>
                                <?php endif; ?>
                                <?php if ($email !== ''): ?>
                                    <a class="company-sidebar__button company-sidebar__button--ghost" href="mailto:<?php echo esc_attr($email); ?>">Email</a>
                                <?php endif; ?>
                                <a class="company-sidebar__button company-sidebar__button--ghost" href="#table_agents">Tutte le analisi</a>
                            </div>
                        </div>

                    </aside>
                </div>

                <!-- Colonna sinistra: dettaglio -->
                <div class="col-md-6 col-lg-7 col-xxl-8">

                        <section class="company-kpi-board">
                            <header class="company-kpi-board__head">
                                <h2 class="company-kpi-board__title"><?php esc_html_e('Key Commercial KPIs', 'psip'); ?></h2>
                                <span class="company-kpi-board__meta"><?php esc_html_e('Valori stimati dal workflow di enrichment', 'psip'); ?></span>
                            </header>
                            <div class="company-kpi-board__grid">
                                <article class="company-kpi-card">
                                    <span class="company-kpi-card__label"><?php esc_html_e('Fatturato stimato', 'psip'); ?></span>
                                    <span class="company-kpi-card__value"><?php echo $annual_revenue_display !== '—'
                                        ? esc_html($annual_revenue_display)
                                        : '—'; ?></span>
                                    <span class="company-kpi-card__hint"><?php esc_html_e('Annual revenue', 'psip'); ?></span>
                                </article>
                                <article class="company-kpi-card">
                                    <span class="company-kpi-card__label"><?php esc_html_e('Budget marketing', 'psip'); ?></span>
                                    <span class="company-kpi-card__value"><?php echo $marketing_budget_display !== '—'
                                        ? esc_html($marketing_budget_display)
                                        : '—'; ?></span>
                                    <span class="company-kpi-card__hint"><?php esc_html_e('Forecasted spend', 'psip'); ?></span>
                                </article>
                                <article class="company-kpi-card">
                                    <span class="company-kpi-card__label"><?php esc_html_e('Budget tier', 'psip'); ?></span>
                                    <span class="company-kpi-card__value"><?php echo $budget_tier_display !== '—'
                                        ? esc_html($budget_tier_display)
                                        : '—'; ?></span>
                                    <span class="company-kpi-card__hint"><?php esc_html_e('Allocazione stimata', 'psip'); ?></span>
                                </article>
                                <article class="company-kpi-card company-kpi-card--chart">
                                    <span class="company-kpi-card__label"><?php esc_html_e('Digital maturity', 'psip'); ?></span>
                                    <?php if ($digital_maturity_percent !== null): ?>
                                        <div class="company-kpi-card__bar">
                                            <span style="width: <?php echo esc_attr((string) $digital_maturity_percent); ?>%;"></span>
                                        </div>
                                        <span class="company-kpi-card__value company-kpi-card__value--accent">
                                            <?php echo esc_html((string) $digital_maturity_percent); ?>%
                                        </span>
                                    <?php else: ?>
                                        <span class="company-kpi-card__value">—</span>
                                    <?php endif; ?>
                                    <span class="company-kpi-card__hint"><?php esc_html_e('Indice di maturità digitale', 'psip'); ?></span>
                                </article>
                            </div>
                        </section>

                        <div class="company-overview">
                            <section class="company-overview__section company-overview__section--full">
                                <header class="company-overview__section-head">
                                    <h2 class="company-overview__section-title"><?php esc_html_e('Profilo sintetico', 'psip'); ?></h2>
                                </header>
                                <div class="company-overview__section-body">
                                    <p class="company-overview__paragraph">
                                        <?php echo $short_bio !== ''
                                            ? esc_html($short_bio)
                                            : '—'; ?>
                                    </p>
                                </div>
                            </section>

                            <section class="company-overview__section">
                                <header class="company-overview__section-head">
                                    <h3 class="company-overview__section-title"><?php esc_html_e('Deal readiness', 'psip'); ?></h3>
                                    <span class="company-overview__section-meta"><?php esc_html_e('Metriche per pipeline e workflow', 'psip'); ?></span>
                                </header>
                                <div class="company-overview__widget-grid company-overview__widget-grid--cols-3">
                                    <?php foreach ($deal_widgets as $widget): ?>
                                        <div class="company-overview__widget">
                                            <span class="company-overview__widget-label"><?php echo esc_html($widget['label']); ?></span>
                                            <span class="company-overview__widget-value"><?php echo esc_html($widget['value']); ?></span>
                                            <?php if (!empty($widget['context'])): ?>
                                                <span class="company-overview__widget-context"><?php echo esc_html($widget['context']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </section>

                            <section class="company-overview__section">
                                <header class="company-overview__section-head">
                                    <h3 class="company-overview__section-title"><?php esc_html_e('Company snapshot', 'psip'); ?></h3>
                                    <span class="company-overview__section-meta"><?php esc_html_e('Dimensioni, settore e posizionamento', 'psip'); ?></span>
                                </header>
                                <div class="company-overview__widget-grid company-overview__widget-grid--cols-3">
                                    <?php foreach ($profile_widgets as $widget): ?>
                                        <div class="company-overview__widget">
                                            <span class="company-overview__widget-label"><?php echo esc_html($widget['label']); ?></span>
                                            <span class="company-overview__widget-value"><?php echo esc_html($widget['value']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </section>

                            <section class="company-overview__section company-overview__section--full">
                                <header class="company-overview__section-head">
                                    <h3 class="company-overview__section-title"><?php esc_html_e('Contatti & Canali', 'psip'); ?></h3>
                                    <span class="company-overview__section-meta"><?php esc_html_e('Touchpoint per outreach', 'psip'); ?></span>
                                </header>
                                <div class="company-overview__widget-grid company-overview__widget-grid--cols-2">
                                    <?php foreach ($contact_widgets as $widget): ?>
                                        <div class="company-overview__widget">
                                            <span class="company-overview__widget-label"><?php echo esc_html($widget['label']); ?></span>
                                            <span class="company-overview__widget-value">
                                                <?php
                                                if ($widget['value'] === '—') {
                                                    echo '—';
                                                } elseif (!empty($widget['url'])) {
                                                    ?>
                                                    <a href="<?php echo esc_url($widget['url']); ?>" target="_blank" rel="noopener"><?php echo esc_html($widget['value']); ?></a>
                                                    <?php
                                                } elseif (!empty($widget['multiline'])) {
                                                    echo nl2br(esc_html($widget['value']));
                                                } else {
                                                    echo esc_html($widget['value']);
                                                }
                                                ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (!empty($display_social_links)): ?>
                                    <div class="company-overview__socials">
                                        <span class="company-overview__socials-label"><?php esc_html_e('Social & Media', 'psip'); ?></span>
                                        <ul class="company-overview__socials-list">
                                            <?php foreach ($display_social_links as $link): ?>
                                                <?php
                                                $link_href = $link;
                                                if (strpos($link_href, 'http://') !== 0 && strpos($link_href, 'https://') !== 0) {
                                                    $link_href = 'https://' . ltrim($link_href, '/');
                                                }
                                                ?>
                                                <li><a href="<?php echo esc_url($link_href); ?>" target="_blank" rel="noopener"><?php echo esc_html($link); ?></a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </section>
                        </div>
                        
                        
                        <div class="mb-5">
                            <div class="text">
                                <h2 class="mb-2">Analisi azienda completa</h2>
                                <div class="accordion" id="accordionAnalisiPerplexity">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button
                                                class="accordion-button collapsed"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#collapseAnalisiPerplexity"
                                                aria-expanded="false"
                                                aria-controls="collapseAnalisiPerplexity"
                                            >
                                                Apri analisi completa
                                            </button>
                                        </h2>
                                        <div
                                            id="collapseAnalisiPerplexity"
                                            class="accordion-collapse collapse"
                                            data-bs-parent="#accordionAnalisiPerplexity"
                                        >
                                            <div class="accordion-body">
                                                <?php if (
                                                    $perplexity_analysis_markup
                                                ): ?>
                                                    <div class="analysis-perplexity-scroll">
                                                        <?php echo wpautop(
                                                            wp_kses_post(
                                                                $perplexity_analysis_markup
                                                            )
                                                        ); ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div>-</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                    <!-- Analisi correlate per agent type -->
                    <?php
                    $agents = psip_get_agents();
                    $analisi_for_company = psip_get_company_analyses($post_id);

                    $agent_total = count($agents);
                    $analysis_completed = 0;
                    $quality_sum = 0.0;
                    $quality_count = 0;

                    foreach ($analisi_for_company as $analysis_item) {
                        if (!is_array($analysis_item) || empty($analysis_item)) {
                            continue;
                        }

                        $analysis_completed++;
                        $analysis_id = $analysis_item["id"] ?? null;
                        if (!$analysis_id) {
                            continue;
                        }

                        $quality_raw = get_field(
                            "voto_qualita_analisi",
                            $analysis_id
                        );
                        $quality_normalized = psip_theme_normalize_scalar(
                            $quality_raw
                        );
                        if (
                            $quality_normalized !== "" &&
                            is_numeric($quality_normalized)
                        ) {
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

                    // Mappa delle metriche con icone e nomi campi ACF aggiornati
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
                    ?>

                    <?php if (!empty($agents)): ?>
                        <div id="table_agents" class="company-analyses">
                            <div class="company-analyses__header">
                                <div>
                                    <h2 class="company-analyses__title">Panoramica agent</h2>
                                    <p class="company-analyses__subtitle">Monitoraggio centralizzato delle analisi AI e delle priorità suggerite dai diversi agent.</p>
                                </div>
                                <div class="company-analyses__stats">
                                    <div class="company-analyses__stat">
                                        <span class="company-analyses__stat-label">Agent disponibili</span>
                                        <span class="company-analyses__stat-value"><?php echo esc_html((string) $agent_total); ?></span>
                                    </div>
                                    <div class="company-analyses__stat">
                                        <span class="company-analyses__stat-label">Analisi completate</span>
                                        <span class="company-analyses__stat-value"><?php echo esc_html((string) $analysis_completed); ?></span>
                                        <span class="company-analyses__stat-hint"><?php echo esc_html((string) $analysis_completion_rate); ?>%</span>
                                    </div>
                                    <div class="company-analyses__stat">
                                        <span class="company-analyses__stat-label">Qualità media</span>
                                        <span class="company-analyses__stat-value"><?php echo esc_html($average_quality_display); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="company-analyses__table-wrapper">
                                <table id="companyAgents" class="company-analyses-table display nowrap">
                                    <thead>
                                        <tr>
                                            <th>Agent</th>
                                            <th class="th-score">Qualità</th>
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
                                                <td class="company-analyses-table__agent">
                                                    <span class="company-analyses-table__agent-icon"><span class="dashicons <?php echo esc_attr($agent_config['icon']); ?>"></span></span>
                                                    <div class="company-analyses-table__agent-info">
                                                        <a href="#analysis-tabs" class="company-analyses-table__agent-name"><?php echo esc_html($agent_config['name']); ?></a>
                                                        <span class="company-analyses-table__agent-status <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span>
                                                    </div>
                                                </td>
                                                <td class="company-analyses-table__score"<?php echo $score_color ? ' style="--score-color:' . esc_attr($score_color) . ';"' : ''; ?>>
                                                    <?php if ($score_numeric !== null): ?>
                                                        <span class="company-analyses-table__score-badge"><?php echo esc_html(number_format($score_numeric, 1)); ?></span>
                                                    <?php else: ?>
                                                        <span class="company-analyses-table__score-badge company-analyses-table__score-badge--empty">—</span>
                                                    <?php endif; ?>
                                                </td>

                                            <?php foreach ($metrics_map as $metric):
                                                $value = $has_analysis ? psip_theme_normalize_scalar(get_field($metric['field'], $analysis_id)) : '';
                                                $has_metric_value = $value !== '';
                                                $display_value = $has_metric_value ? esc_html($value) : '—';
                                                $metric_class = $has_metric_value ? '' : ' company-analyses-table__metric-pill--empty';
                                            ?>
                                                <td class="company-analyses-table__metric" data-bs-toggle="tooltip" title="<?php echo esc_attr($agent_config['name'] . ': ' . $metric['label']); ?>">
                                                    <span class="company-analyses-table__metric-pill<?php echo $metric_class; ?>"><?php echo esc_html($display_value); ?></span>
                                                </td>
                                                <?php endforeach; ?>

                                                <td class="company-analyses-table__timestamp" title="<?php echo $last_run_relative ? esc_attr($last_run_relative) : ''; ?>">
                                                    <?php echo $last_run_display !== '' ? esc_html($last_run_display) : '—'; ?>
                                                </td>
                                                <td class="company-analyses-table__action">
                                                    <button class="company-analyses-table__action-btn" type="button" onclick="alert('Coming soon!')">
                                                        <?php echo $has_analysis ? 'Apri report' : 'Avvia analisi'; ?>
                                                    </button>
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

<?php get_template_part("inc/company-analysis-tabs"); ?>

<?php
    endwhile;
endif;

get_footer();
