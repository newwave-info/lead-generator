<?php
get_header();

if (!function_exists('lg_media_url')) {
    /**
     * Restituisce l'URL di un media ACF (ID o URL) con fallback.
     */
    function lg_media_url($value, $size = 'large', $fallback = '') {
        if (is_numeric($value)) {
            $url = wp_get_attachment_image_url((int) $value, $size);
            if ($url) {
                return $url;
            }
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed !== '' && filter_var($trimmed, FILTER_VALIDATE_URL)) {
                return $trimmed;
            }
        }

        if ($fallback !== '') {
            return $fallback;
        }

        return 'https://via.placeholder.com/400x300?text=Media';
    }
}

if (!function_exists('lg_normalize_domain')) {
    /**
     * Normalizza un dominio aggiungendo lo schema mancante.
     */
    function lg_normalize_domain($domain) {
        $domain = trim((string) $domain);
        if ($domain === '') {
            return '';
        }

        if (!preg_match('#^https?://#i', $domain)) {
            $domain = 'https://' . ltrim($domain, '/');
        }

        return esc_url($domain);
    }
}

if (!function_exists('lg_extract_strings')) {
    /**
     * Normalizza valori ACF (stringhe, array di stringhe/label) in un array di stringhe.
     */
    function lg_extract_strings($value) {
        if (is_array($value)) {
            $result = [];
            foreach ($value as $item) {
                if (is_array($item)) {
                    if (isset($item['value']) && is_scalar($item['value'])) {
                        $item = $item['value'];
                    } elseif (isset($item['label']) && is_scalar($item['label'])) {
                        $item = $item['label'];
                    } else {
                        $item = null;
                    }
                }

                if (is_scalar($item)) {
                    $trimmed = trim((string) $item);
                    if ($trimmed !== '') {
                        $result[] = $trimmed;
                    }
                }
            }

            return $result;
        }

        if (is_scalar($value)) {
            $trimmed = trim((string) $value);
            return $trimmed !== '' ? [$trimmed] : [];
        }

        return [];
    }
}

if (!function_exists('lg_normalize_count')) {
    /**
     * Restituisce un conteggio valido partendo da un numero dichiarato o dalla lunghezza dell'array.
     */
    function lg_normalize_count($maybe_number, array $items) {
        if (is_numeric($maybe_number)) {
            return (int) $maybe_number;
        }

        return count($items);
    }
}

if (have_posts()) :
    while (have_posts()) :
        the_post();

        $post_id   = get_the_ID();
        $fields    = get_fields($post_id);
        $fields    = is_array($fields) ? $fields : [];
        $theme_uri = get_template_directory_uri();

        $company_name        = !empty($fields['company_name_full']) ? $fields['company_name_full'] : get_the_title();
        $qualification_status = $fields['qualification_status'] ?? '';
        $budget_tier         = $fields['budget_tier'] ?? '';
        $sector_specific     = $fields['sector_specific'] ?? '';
        $short_bio           = $fields['short_bio'] ?? '';
        $business_type       = $fields['business_type'] ?? '';
        $employee_count      = $fields['employee_count'] ?? '';
        $growth_stage        = $fields['growth_stage'] ?? '';
        $geography_scope     = $fields['geography_scope'] ?? '';
        $annual_revenue      = $fields['annual_revenue'] ?? '';
        $domain_raw          = $fields['domain'] ?? '';
        $city                = $fields['city'] ?? '';
        $province            = $fields['province'] ?? '';
        $address             = $fields['address'] ?? '';
        $partita_iva         = $fields['partita_iva'] ?? '';
        $phone               = $fields['phone'] ?? '';
        $email               = $fields['email'] ?? '';
        $linkedin_url        = $fields['linkedin_url'] ?? '';
        $website_logo        = $fields['website_logo_url'] ?? '';
        $website_screenshot  = $fields['website_screenshot_url'] ?? '';
        $qualification_reason = $fields['qualification_reason'] ?? '';
        $service_fit         = $fields['service_fit'] ?? '';
        $data_enrichment     = $fields['data_ultimo_enrichment'] ?? '';
        $enrichment_status   = $fields['enrichment_last_status_code'] ?? '';
        $enrichment_message  = $fields['enrichment_last_message'] ?? '';
        $digital_score_raw   = $fields['digital_maturity_score'] ?? '';
        $financial_conf_raw  = $fields['financial_confidence'] ?? '';
        $priority_score_raw  = $fields['priority_score'] ?? '';
        $marketing_budget    = $fields['marketing_budget_est'] ?? '';
        $ebitda_margin       = $fields['ebitda_margin_est'] ?? '';

        $logo_url       = lg_media_url($website_logo, 'medium', $theme_uri . '/common/img/logo.svg');
        $screenshot_url = lg_media_url($website_screenshot, 'large', 'https://via.placeholder.com/320x200?text=Anteprima');

        $meta_location = '';
        if ($city !== '' && $province !== '') {
            $meta_location = sprintf('%s (%s)', $city, $province);
        } elseif ($city !== '') {
            $meta_location = $city;
        } elseif ($province !== '') {
            $meta_location = $province;
        }

        $domain_url     = lg_normalize_domain($domain_raw);
        $domain_display = '';
        if ($domain_raw !== '') {
            $domain_display = trim(preg_replace('#^https?://#i', '', $domain_raw));
            $domain_display = rtrim($domain_display, '/');
        }

        $digital_score    = is_numeric($digital_score_raw) ? max(0, min(100, (int) $digital_score_raw)) : null;
        $financial_conf   = is_numeric($financial_conf_raw) ? max(0, min(100, (int) $financial_conf_raw)) : null;
        $priority_score   = is_numeric($priority_score_raw) ? max(0, min(100, (int) $priority_score_raw)) : null;
        $ebitda_display   = $ebitda_margin !== '' ? sprintf('%s%%', trim((string) $ebitda_margin)) : '';

        $enrichment_display = '';
        if ($data_enrichment !== '') {
            $timestamp = strtotime($data_enrichment);
            if ($timestamp) {
                $enrichment_display = date_i18n('d/m/Y H:i', $timestamp);
            } else {
                $enrichment_display = $data_enrichment;
            }
        }

        $analisi_query = new WP_Query([
            'post_type'      => 'analisi',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => [
                [
                    'key'     => 'parent_company_id',
                    'value'   => $post_id,
                    'compare' => '=',
                ],
            ],
        ]);

        $analisi_items = [];

        if ($analisi_query->have_posts()) {
            while ($analisi_query->have_posts()) {
                $analisi_query->the_post();

                $analysis_id     = get_the_ID();
                $analysis_fields = get_fields($analysis_id);
                $analysis_fields = is_array($analysis_fields) ? $analysis_fields : [];

                $strengths         = lg_extract_strings($analysis_fields['punti_di_forza'] ?? []);
                $weaknesses        = lg_extract_strings($analysis_fields['punti_di_debolezza'] ?? []);
                $opportunities     = lg_extract_strings($analysis_fields['opportunita'] ?? []);
                $quick_wins        = lg_extract_strings($analysis_fields['azioni_rapide'] ?? []);
                $questions         = lg_extract_strings($analysis_fields['domande_prospect'] ?? []);
                $value_ideas       = lg_extract_strings($analysis_fields['idee_di_valore_perspect'] ?? []);
                $riassunto         = $analysis_fields['riassunto'] ?? '';
                $deep_research     = $analysis_fields['analisy_perplexity_deep_research'] ?? '';
                $review            = $analysis_fields['revisione_analisi_completa'] ?? '';
                $quality_score     = isset($analysis_fields['voto_qualita_analisi']) && is_numeric($analysis_fields['voto_qualita_analisi'])
                    ? max(0, min(100, (int) $analysis_fields['voto_qualita_analisi']))
                    : null;
                $data_quality      = isset($analysis_fields['qualita_dati']) && is_numeric($analysis_fields['qualita_dati'])
                    ? max(0, min(100, (int) $analysis_fields['qualita_dati']))
                    : null;
                $numero_rischi     = $analysis_fields['numero_rischi'] ?? null;

                $risks = [];
                if (isset($analysis_fields['rischi']) && is_array($analysis_fields['rischi'])) {
                    foreach ($analysis_fields['rischi'] as $risk_row) {
                        if (!is_array($risk_row)) {
                            continue;
                        }

                        $risk_text       = isset($risk_row['rischio']) ? trim((string) $risk_row['rischio']) : '';
                        $mitigation_text = isset($risk_row['mitigazione']) ? trim((string) $risk_row['mitigazione']) : '';

                        if ($risk_text === '' && $mitigation_text === '') {
                            continue;
                        }

                        $risks[] = [
                            'rischio'     => $risk_text,
                            'mitigazione' => $mitigation_text,
                        ];
                    }
                }

                $priorita_raw = $analysis_fields['priorita_temporali'] ?? [];
                $priorita     = [
                    'entro_30_giorni' => [],
                    'entro_90_giorni' => [],
                    'entro_12_mesi'   => [],
                ];

                if (is_array($priorita_raw)) {
                    foreach ($priorita as $key => $default) {
                        if (isset($priorita_raw[$key])) {
                            $priorita[$key] = lg_extract_strings($priorita_raw[$key]);
                        }
                    }
                }

                $analisi_items[] = [
                    'post_id'        => $analysis_id,
                    'title'          => get_the_title(),
                    'riassunto'      => $riassunto,
                    'strengths'      => $strengths,
                    'weaknesses'     => $weaknesses,
                    'opportunities'  => $opportunities,
                    'quick_wins'     => $quick_wins,
                    'deep_research'  => $deep_research,
                    'review'         => $review,
                    'risks'          => $risks,
                    'priorita'       => $priorita,
                    'questions'      => $questions,
                    'value_ideas'    => $value_ideas,
                    'quality_score'  => $quality_score,
                    'data_quality'   => $data_quality,
                    'counts'         => [
                        'strengths'     => lg_normalize_count($analysis_fields['numero_punti_di_forza'] ?? null, $strengths),
                        'weaknesses'    => lg_normalize_count($analysis_fields['numero_punti_di_debolezza'] ?? null, $weaknesses),
                        'opportunities' => lg_normalize_count($analysis_fields['numero_opportunita'] ?? null, $opportunities),
                        'quick_wins'    => lg_normalize_count($analysis_fields['numero_azioni_rapide'] ?? null, $quick_wins),
                        'risks'         => lg_normalize_count($numero_rischi, $risks),
                        'questions'     => lg_normalize_count($analysis_fields['numero_domande'] ?? null, $questions),
                        'value_ideas'   => lg_normalize_count($analysis_fields['numero_idee_di_valore'] ?? null, $value_ideas),
                    ],
                ];
            }
            wp_reset_postdata();
        }

        $has_analisi = !empty($analisi_items);
        ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class('lg-azienda'); ?>>
            <section class="lg-azienda__section lg-azienda__section--hero">
                <div class="lg-azienda__inner">
                    <div class="lg-azienda__hero">
                        <div class="lg-azienda__hero-id">
                            <div class="lg-azienda__logo">
                                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(sprintf('Logo di %s', $company_name)); ?>" loading="lazy" />
                            </div>
                            <div>
                                <h1 class="lg-azienda__title"><?php echo esc_html($company_name); ?></h1>
                                <div class="lg-azienda__meta">
                                    <?php if ($qualification_status !== '') : ?>
                                        <span class="lg-azienda__pill"><?php echo esc_html($qualification_status); ?></span>
                                    <?php endif; ?>
                                    <?php if ($budget_tier !== '') : ?>
                                        <span class="lg-azienda__pill"><?php echo esc_html(sprintf('Budget %s', $budget_tier)); ?></span>
                                    <?php endif; ?>
                                    <?php if ($sector_specific !== '') : ?>
                                        <span class="lg-azienda__pill"><?php echo esc_html($sector_specific); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($meta_location !== '' || $domain_url !== '') : ?>
                                    <p class="lg-azienda__lead">
                                        <?php if ($meta_location !== '') : ?>
                                            <?php echo esc_html($meta_location); ?>
                                        <?php endif; ?>
                                        <?php if ($meta_location !== '' && $domain_url !== '') : ?>
                                            <span aria-hidden="true"> · </span>
                                        <?php endif; ?>
                                        <?php if ($domain_url !== '') : ?>
                                            <a href="<?php echo $domain_url; ?>" target="_blank" rel="nofollow noopener">
                                                <?php echo esc_html($domain_display); ?>
                                            </a>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="lg-azienda__quick" aria-hidden="true">
                            <div>
                                <div class="lg-azienda__mini-title">Website preview</div>
                                <div class="lg-azienda__thumb">
                                    <img src="<?php echo esc_url($screenshot_url); ?>" alt="<?php echo esc_attr(sprintf('Anteprima sito %s', $company_name)); ?>" loading="lazy" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="lg-azienda__section">
                <div class="lg-azienda__inner lg-azienda__body">
                    <nav class="lg-azienda__toc" id="lg-azienda-toc" aria-label="<?php esc_attr_e('Indice contenuti', 'lead-generator'); ?>">
                        <h2 class="lg-azienda__toc-title"><?php esc_html_e('Indice', 'lead-generator'); ?></h2>
                        <a class="lg-azienda__toc-link active" data-lg-toc-link href="#overview">Overview</a>
                        <a class="lg-azienda__toc-link" data-lg-toc-link href="#metriche">Metriche</a>
                        <a class="lg-azienda__toc-link" data-lg-toc-link href="#analisi">Analisi</a>
                        <a class="lg-azienda__toc-link" data-lg-toc-link href="#contatti">Contatti</a>
                    </nav>

                    <div class="lg-azienda__content">
                        <section id="overview" class="lg-azienda__box">
                            <h2 class="lg-azienda__section-title">Overview</h2>
                            <?php if ($short_bio !== '') : ?>
                                <div class="lg-azienda__muted lg-azienda__muted--lead">
                                    <?php echo wp_kses_post(wpautop($short_bio)); ?>
                                </div>
                            <?php endif; ?>

                            <div class="lg-azienda__columns">
                                <div>
                                    <h3 class="lg-azienda__subtitle">Dati profilo</h3>
                                    <ul class="lg-azienda__list">
                                        <li><strong>Business:</strong> <?php echo $business_type !== '' ? esc_html($business_type) : '—'; ?></li>
                                        <li><strong>Settore:</strong> <?php echo $sector_specific !== '' ? esc_html($sector_specific) : '—'; ?></li>
                                        <li><strong>Dipendenti:</strong> <?php echo $employee_count !== '' ? esc_html($employee_count) : '—'; ?></li>
                                    </ul>
                                </div>
                                <div>
                                    <h3 class="lg-azienda__subtitle">Stadio &amp; mercato</h3>
                                    <ul class="lg-azienda__list">
                                        <li><strong>Stage:</strong> <?php echo $growth_stage !== '' ? esc_html($growth_stage) : '—'; ?></li>
                                        <li><strong>Scope:</strong> <?php echo $geography_scope !== '' ? esc_html($geography_scope) : '—'; ?></li>
                                        <li><strong>Ricavi:</strong> <?php echo $annual_revenue !== '' ? esc_html($annual_revenue) : '—'; ?></li>
                                    </ul>
                                </div>
                            </div>

                            <?php if ($qualification_reason !== '' || $service_fit !== '') : ?>
                                <div class="lg-azienda__columns lg-azienda__columns--tight">
                                    <?php if ($qualification_reason !== '') : ?>
                                        <div>
                                            <h3 class="lg-azienda__subtitle">Qualification note</h3>
                                            <div class="lg-azienda__muted"><?php echo wp_kses_post(wpautop($qualification_reason)); ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($service_fit !== '') : ?>
                                        <div>
                                            <h3 class="lg-azienda__subtitle">Service fit</h3>
                                            <div class="lg-azienda__muted"><?php echo wp_kses_post(wpautop($service_fit)); ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </section>

                        <section id="metriche" class="lg-azienda__box">
                            <h2 class="lg-azienda__section-title">Metriche principali</h2>
                            <div class="lg-azienda__metrics">
                                <div class="lg-azienda__kpi">
                                    <span class="lg-azienda__kpi-label">Maturità digitale</span>
                                    <span class="lg-azienda__kpi-value"><?php echo $digital_score !== null ? esc_html($digital_score) : '—'; ?></span>
                                    <small class="lg-azienda__kpi-meta">digital_maturity_score</small>
                                </div>
                                <div class="lg-azienda__kpi">
                                    <span class="lg-azienda__kpi-label">Confidenza finanziaria</span>
                                    <span class="lg-azienda__kpi-value"><?php echo $financial_conf !== null ? esc_html($financial_conf) : '—'; ?></span>
                                    <small class="lg-azienda__kpi-meta">financial_confidence</small>
                                </div>
                                <div class="lg-azienda__kpi">
                                    <span class="lg-azienda__kpi-label">Priorità commerciale</span>
                                    <span class="lg-azienda__kpi-value"><?php echo $priority_score !== null ? esc_html($priority_score) : '—'; ?></span>
                                    <small class="lg-azienda__kpi-meta">priority_score</small>
                                </div>
                                <div class="lg-azienda__kpi">
                                    <span class="lg-azienda__kpi-label">Budget tier</span>
                                    <span class="lg-azienda__kpi-value"><?php echo $budget_tier !== '' ? esc_html($budget_tier) : '—'; ?></span>
                                    <small class="lg-azienda__kpi-meta">marketing_budget_est <?php echo $marketing_budget !== '' ? esc_html(sprintf('(%s)', $marketing_budget)) : ''; ?></small>
                                </div>
                            </div>

                            <div class="lg-azienda__metrics lg-azienda__metrics--secondary">
                                <div class="lg-azienda__kpi">
                                    <span class="lg-azienda__kpi-label">EBITDA margin</span>
                                    <span class="lg-azienda__kpi-value lg-azienda__kpi-value--smaller"><?php echo $ebitda_display !== '' ? esc_html($ebitda_display) : '—'; ?></span>
                                </div>
                                <div class="lg-azienda__kpi">
                                    <span class="lg-azienda__kpi-label">Budget marketing</span>
                                    <span class="lg-azienda__kpi-value lg-azienda__kpi-value--smaller"><?php echo $marketing_budget !== '' ? esc_html($marketing_budget) : '—'; ?></span>
                                </div>
                            </div>
                        </section>

                        <section id="analisi" class="lg-azienda__box">
                            <h2 class="lg-azienda__section-title">Analisi collegate</h2>
                            <?php if (!$has_analisi) : ?>
                                <div class="lg-azienda__empty">
                                    <p><?php esc_html_e('Nessuna analisi disponibile al momento.', 'lead-generator'); ?></p>
                                    <a class="lg-azienda__cta" href="#"><?php esc_html_e('Richiedi un’analisi personalizzata', 'lead-generator'); ?></a>
                                </div>
                            <?php else : ?>
                                <div class="lg-azienda__tabs" data-lg-tabs>
                                    <div class="lg-azienda__tablist" role="tablist" aria-label="<?php esc_attr_e('Analisi collegate', 'lead-generator'); ?>">
                                        <?php foreach ($analisi_items as $index => $item) :
                                            $tab_id   = sprintf('analysis-tab-%d', $item['post_id']);
                                            $panel_id = sprintf('analysis-panel-%d', $item['post_id']);
                                            $is_active = $index === 0;
                                            ?>
                                            <button
                                                class="lg-azienda__tab<?php echo $is_active ? ' is-active' : ''; ?>"
                                                id="<?php echo esc_attr($tab_id); ?>"
                                                type="button"
                                                role="tab"
                                                aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
                                                aria-controls="<?php echo esc_attr($panel_id); ?>"
                                                <?php echo $is_active ? '' : 'tabindex="-1"'; ?>
                                                data-lg-tab
                                            >
                                                <?php echo esc_html($item['title']); ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="lg-azienda__panels">
                                        <?php foreach ($analisi_items as $index => $item) :
                                            $panel_id = sprintf('analysis-panel-%d', $item['post_id']);
                                            $tab_id   = sprintf('analysis-tab-%d', $item['post_id']);
                                            $is_active = $index === 0;
                                            ?>
                                            <article
                                                class="lg-azienda__analysis-panel<?php echo $is_active ? ' is-active' : ''; ?>"
                                                id="<?php echo esc_attr($panel_id); ?>"
                                                role="tabpanel"
                                                aria-labelledby="<?php echo esc_attr($tab_id); ?>"
                                                <?php echo $is_active ? '' : 'hidden'; ?>
                                                data-lg-panel
                                            >
                                                <header class="lg-azienda__analysis-header">
                                                    <h3 class="lg-azienda__analysis-title"><?php echo esc_html($item['title']); ?></h3>
                                                    <div class="lg-azienda__indicators">
                                                        <span class="lg-azienda__indicator">
                                                            <strong><?php esc_html_e('Qualità analisi:', 'lead-generator'); ?></strong>
                                                            <?php echo $item['quality_score'] !== null ? esc_html($item['quality_score']) : '—'; ?>/100
                                                        </span>
                                                        <span class="lg-azienda__indicator">
                                                            <strong><?php esc_html_e('Qualità dati:', 'lead-generator'); ?></strong>
                                                            <?php echo $item['data_quality'] !== null ? esc_html($item['data_quality']) : '—'; ?>/100
                                                        </span>
                                                    </div>
                                                </header>

                                                <?php if (!empty($item['riassunto']) || !empty($item['strengths']) || !empty($item['weaknesses']) || !empty($item['opportunities']) || !empty($item['quick_wins'])) : ?>
                                                    <details class="lg-azienda__accordion" <?php echo $is_active ? 'open' : ''; ?>>
                                                        <summary class="lg-azienda__accordion-summary">
                                                            <?php esc_html_e('Sintesi e punti chiave', 'lead-generator'); ?>
                                                        </summary>
                                                        <div class="lg-azienda__accordion-content lg-azienda__scroll">
                                                            <?php if (!empty($item['riassunto'])) : ?>
                                                                <div class="lg-azienda__muted"><?php echo wp_kses_post(wpautop($item['riassunto'])); ?></div>
                                                            <?php endif; ?>

                                                            <div class="lg-azienda__columns">
                                                                <div>
                                                                    <h4 class="lg-azienda__subtitle"><?php esc_html_e('Punti di forza', 'lead-generator'); ?> (<?php echo esc_html($item['counts']['strengths']); ?>)</h4>
                                                                    <?php if (!empty($item['strengths'])) : ?>
                                                                        <ul class="lg-azienda__list">
                                                                            <?php foreach ($item['strengths'] as $entry) : ?>
                                                                                <li><?php echo esc_html($entry); ?></li>
                                                                            <?php endforeach; ?>
                                                                        </ul>
                                                                    <?php else : ?>
                                                                        <p class="lg-azienda__muted"><?php esc_html_e('Nessun dato disponibile.', 'lead-generator'); ?></p>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div>
                                                                    <h4 class="lg-azienda__subtitle"><?php esc_html_e('Punti di debolezza', 'lead-generator'); ?> (<?php echo esc_html($item['counts']['weaknesses']); ?>)</h4>
                                                                    <?php if (!empty($item['weaknesses'])) : ?>
                                                                        <ul class="lg-azienda__list">
                                                                            <?php foreach ($item['weaknesses'] as $entry) : ?>
                                                                                <li><?php echo esc_html($entry); ?></li>
                                                                            <?php endforeach; ?>
                                                                        </ul>
                                                                    <?php else : ?>
                                                                        <p class="lg-azienda__muted"><?php esc_html_e('Nessun dato disponibile.', 'lead-generator'); ?></p>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>

                                                            <div class="lg-azienda__columns">
                                                                <div>
                                                                    <h4 class="lg-azienda__subtitle"><?php esc_html_e('Opportunità', 'lead-generator'); ?> (<?php echo esc_html($item['counts']['opportunities']); ?>)</h4>
                                                                    <?php if (!empty($item['opportunities'])) : ?>
                                                                        <ul class="lg-azienda__list">
                                                                            <?php foreach ($item['opportunities'] as $entry) : ?>
                                                                                <li><?php echo esc_html($entry); ?></li>
                                                                            <?php endforeach; ?>
                                                                        </ul>
                                                                    <?php else : ?>
                                                                        <p class="lg-azienda__muted"><?php esc_html_e('Nessuna opportunità registrata.', 'lead-generator'); ?></p>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div>
                                                                    <h4 class="lg-azienda__subtitle"><?php esc_html_e('Azioni rapide', 'lead-generator'); ?> (<?php echo esc_html($item['counts']['quick_wins']); ?>)</h4>
                                                                    <?php if (!empty($item['quick_wins'])) : ?>
                                                                        <ol class="lg-azienda__list lg-azienda__list--ordered">
                                                                            <?php foreach ($item['quick_wins'] as $entry) : ?>
                                                                                <li><?php echo esc_html($entry); ?></li>
                                                                            <?php endforeach; ?>
                                                                        </ol>
                                                                    <?php else : ?>
                                                                        <p class="lg-azienda__muted"><?php esc_html_e('Nessuna azione rapida.', 'lead-generator'); ?></p>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </details>
                                                <?php endif; ?>

                                                <?php if ($item['deep_research'] !== '' || $item['review'] !== '') : ?>
                                                    <details class="lg-azienda__accordion">
                                                        <summary class="lg-azienda__accordion-summary">
                                                            <?php esc_html_e('Approfondimenti', 'lead-generator'); ?>
                                                        </summary>
                                                        <div class="lg-azienda__accordion-content lg-azienda__scroll">
                                                            <?php if ($item['deep_research'] !== '') : ?>
                                                                <section class="lg-azienda__accordion-block">
                                                                    <h4 class="lg-azienda__subtitle"><?php esc_html_e('Dettaglio ricerca', 'lead-generator'); ?></h4>
                                                                    <div class="lg-azienda__muted"><?php echo wp_kses_post($item['deep_research']); ?></div>
                                                                </section>
                                                            <?php endif; ?>
                                                            <?php if ($item['review'] !== '') : ?>
                                                                <section class="lg-azienda__accordion-block">
                                                                    <h4 class="lg-azienda__subtitle"><?php esc_html_e('Revisione analisi', 'lead-generator'); ?></h4>
                                                                    <div class="lg-azienda__muted"><?php echo wp_kses_post(wpautop($item['review'])); ?></div>
                                                                </section>
                                                            <?php endif; ?>
                                                        </div>
                                                    </details>
                                                <?php endif; ?>

                                                <details class="lg-azienda__accordion">
                                                    <summary class="lg-azienda__accordion-summary">
                                                        <?php esc_html_e('Rischi &amp; mitigazioni', 'lead-generator'); ?> (<?php echo esc_html($item['counts']['risks']); ?>)
                                                    </summary>
                                                    <div class="lg-azienda__accordion-content lg-azienda__scroll">
                                                        <?php if (!empty($item['risks'])) : ?>
                                                            <div class="lg-azienda__table-wrapper">
                                                                <table class="lg-azienda__table">
                                                                    <thead>
                                                                        <tr>
                                                                            <th scope="col"><?php esc_html_e('Rischio', 'lead-generator'); ?></th>
                                                                            <th scope="col"><?php esc_html_e('Mitigazione', 'lead-generator'); ?></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($item['risks'] as $risk) : ?>
                                                                            <tr>
                                                                                <td><?php echo $risk['rischio'] !== '' ? esc_html($risk['rischio']) : '—'; ?></td>
                                                                                <td><?php echo $risk['mitigazione'] !== '' ? esc_html($risk['mitigazione']) : '—'; ?></td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        <?php else : ?>
                                                            <p class="lg-azienda__muted"><?php esc_html_e('Nessun rischio segnalato.', 'lead-generator'); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </details>

                                                <details class="lg-azienda__accordion">
                                                    <summary class="lg-azienda__accordion-summary">
                                                        <?php esc_html_e('Priorità temporali', 'lead-generator'); ?>
                                                    </summary>
                                                    <div class="lg-azienda__accordion-content lg-azienda__scroll">
                                                        <div class="lg-azienda__columns lg-azienda__columns--three">
                                                            <div>
                                                                <h5 class="lg-azienda__subheading"><?php esc_html_e('Entro 30 giorni', 'lead-generator'); ?></h5>
                                                                <?php if (!empty($item['priorita']['entro_30_giorni'])) : ?>
                                                                    <ul class="lg-azienda__list">
                                                                        <?php foreach ($item['priorita']['entro_30_giorni'] as $entry) : ?>
                                                                            <li><?php echo esc_html($entry); ?></li>
                                                                        <?php endforeach; ?>
                                                                    </ul>
                                                                <?php else : ?>
                                                                    <p class="lg-azienda__muted"><?php esc_html_e('Nessuna attività programmata.', 'lead-generator'); ?></p>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div>
                                                                <h5 class="lg-azienda__subheading"><?php esc_html_e('Entro 90 giorni', 'lead-generator'); ?></h5>
                                                                <?php if (!empty($item['priorita']['entro_90_giorni'])) : ?>
                                                                    <ul class="lg-azienda__list">
                                                                        <?php foreach ($item['priorita']['entro_90_giorni'] as $entry) : ?>
                                                                            <li><?php echo esc_html($entry); ?></li>
                                                                        <?php endforeach; ?>
                                                                    </ul>
                                                                <?php else : ?>
                                                                    <p class="lg-azienda__muted"><?php esc_html_e('Nessuna attività programmata.', 'lead-generator'); ?></p>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div>
                                                                <h5 class="lg-azienda__subheading"><?php esc_html_e('Entro 12 mesi', 'lead-generator'); ?></h5>
                                                                <?php if (!empty($item['priorita']['entro_12_mesi'])) : ?>
                                                                    <ul class="lg-azienda__list">
                                                                        <?php foreach ($item['priorita']['entro_12_mesi'] as $entry) : ?>
                                                                            <li><?php echo esc_html($entry); ?></li>
                                                                        <?php endforeach; ?>
                                                                    </ul>
                                                                <?php else : ?>
                                                                    <p class="lg-azienda__muted"><?php esc_html_e('Nessuna attività programmata.', 'lead-generator'); ?></p>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </details>

                                                <details class="lg-azienda__accordion">
                                                    <summary class="lg-azienda__accordion-summary">
                                                        <?php esc_html_e('Domande prospect', 'lead-generator'); ?> (<?php echo esc_html($item['counts']['questions']); ?>)
                                                    </summary>
                                                    <div class="lg-azienda__accordion-content lg-azienda__scroll">
                                                        <?php if (!empty($item['questions'])) : ?>
                                                            <ul class="lg-azienda__list">
                                                                <?php foreach ($item['questions'] as $entry) : ?>
                                                                    <li><?php echo esc_html($entry); ?></li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php else : ?>
                                                            <p class="lg-azienda__muted"><?php esc_html_e('Nessuna domanda registrata.', 'lead-generator'); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </details>

                                                <details class="lg-azienda__accordion">
                                                    <summary class="lg-azienda__accordion-summary">
                                                        <?php esc_html_e('Idee di valore', 'lead-generator'); ?> (<?php echo esc_html($item['counts']['value_ideas']); ?>)
                                                    </summary>
                                                    <div class="lg-azienda__accordion-content lg-azienda__scroll">
                                                        <?php if (!empty($item['value_ideas'])) : ?>
                                                            <ul class="lg-azienda__list">
                                                                <?php foreach ($item['value_ideas'] as $entry) : ?>
                                                                    <li><?php echo esc_html($entry); ?></li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php else : ?>
                                                            <p class="lg-azienda__muted"><?php esc_html_e('Nessuna idea registrata.', 'lead-generator'); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </details>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </section>

                        <section id="contatti" class="lg-azienda__box">
                            <h2 class="lg-azienda__section-title">Contatti &amp; processi</h2>
                            <div class="lg-azienda__contacts">
                                <div>
                                    <h3 class="lg-azienda__subtitle">Indirizzo</h3>
                                    <p><?php echo $address !== '' ? esc_html($address) : '—'; ?></p>
                                </div>
                                <div>
                                    <h3 class="lg-azienda__subtitle">Partita IVA</h3>
                                    <p><?php echo $partita_iva !== '' ? esc_html($partita_iva) : '—'; ?></p>
                                </div>
                                <div>
                                    <h3 class="lg-azienda__subtitle">Telefono</h3>
                                    <?php if ($phone !== '') : ?>
                                        <?php $phone_href = preg_replace('/[^0-9+]/', '', $phone); ?>
                                        <p><a href="<?php echo esc_url('tel:' . $phone_href); ?>"><?php echo esc_html($phone); ?></a></p>
                                    <?php else : ?>
                                        <p>—</p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h3 class="lg-azienda__subtitle">Email</h3>
                                    <?php if ($email !== '' && is_email($email)) : ?>
                                        <p><a href="<?php echo esc_url('mailto:' . sanitize_email($email)); ?>"><?php echo esc_html($email); ?></a></p>
                                    <?php else : ?>
                                        <p>—</p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h3 class="lg-azienda__subtitle">LinkedIn</h3>
                                    <?php if ($linkedin_url !== '') : ?>
                                        <p><a href="<?php echo lg_normalize_domain($linkedin_url); ?>" target="_blank" rel="nofollow noopener">Profilo LinkedIn</a></p>
                                    <?php else : ?>
                                        <p>—</p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h3 class="lg-azienda__subtitle">Website</h3>
                                    <?php if ($domain_url !== '') : ?>
                                        <p><a href="<?php echo $domain_url; ?>" target="_blank" rel="nofollow noopener"><?php echo esc_html($domain_display !== '' ? $domain_display : $domain_url); ?></a></p>
                                    <?php else : ?>
                                        <p>—</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="lg-azienda__log">
                                <h3 class="lg-azienda__subtitle">Log enrichment</h3>
                                <ul class="lg-azienda__log-list">
                                    <li><strong>Ultimo enrichment:</strong> <?php echo $enrichment_display !== '' ? esc_html($enrichment_display) : '—'; ?></li>
                                    <li><strong>Status code:</strong> <?php echo $enrichment_status !== '' ? esc_html($enrichment_status) : '—'; ?></li>
                                    <li><strong>Messaggio:</strong> <?php echo $enrichment_message !== '' ? esc_html($enrichment_message) : '—'; ?></li>
                                </ul>
                            </div>
                        </section>
                    </div>
                </div>
            </section>
        </article>

        <?php
    endwhile;
endif;

get_footer();
