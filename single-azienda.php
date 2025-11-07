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

if (!function_exists('lg_format_display')) {
    /**
     * Restituisce una stringa pronta per il template con fallback.
     */
    function lg_format_display($value, $fallback = '—') {
        if (is_array($value)) {
            $value = array_filter(array_map('trim', array_map('strval', $value)));
            $value = implode(', ', $value);
        } elseif (is_scalar($value)) {
            $value = trim((string) $value);
        } else {
            $value = '';
        }

        return $value !== '' ? $value : $fallback;
    }
}

if (!function_exists('lg_parse_list')) {
    /**
     * Parser per liste separate da newline o pipe.
     */
    function lg_parse_list($text) {
        if (empty($text) || !is_string($text)) {
            return [];
        }
        // Prova a splittare per newline o pipe
        $items = preg_split('/[\n\r\|]+/', $text);
        $items = array_map('trim', $items);
        $items = array_filter($items);
        return array_values($items);
    }
}

if (have_posts()) :
    while (have_posts()) :
        the_post();

        $post_id   = get_the_ID();
        $fields    = get_fields($post_id);
        $fields    = is_array($fields) ? $fields : [];
        $theme_uri = get_template_directory_uri();

        $company_name         = !empty($fields['company_name_full']) ? $fields['company_name_full'] : get_the_title();
        $qualification_status = $fields['qualification_status'] ?? '';
        $budget_tier          = $fields['budget_tier'] ?? '';
        $sector_specific      = $fields['sector_specific'] ?? '';
        $short_bio            = $fields['short_bio'] ?? '';
        $business_type        = $fields['business_type'] ?? '';
        $employee_count       = $fields['employee_count'] ?? '';
        $growth_stage         = $fields['growth_stage'] ?? '';
        $geography_scope      = $fields['geography_scope'] ?? '';
        $annual_revenue       = $fields['annual_revenue'] ?? '';
        $domain_raw           = $fields['domain'] ?? '';
        $city                 = $fields['city'] ?? '';
        $province             = $fields['province'] ?? '';
        $address              = $fields['address'] ?? '';
        $partita_iva          = $fields['partita_iva'] ?? '';
        $phone                = $fields['phone'] ?? '';
        $email                = $fields['email'] ?? '';
        $linkedin_url         = $fields['linkedin_url'] ?? '';
        $qualification_reason = $fields['qualification_reason'] ?? '';
        $service_fit          = $fields['service_fit'] ?? '';
        $data_enrichment      = $fields['data_ultimo_enrichment'] ?? '';
        $enrichment_status    = $fields['enrichment_last_status_code'] ?? '';
        $enrichment_message   = $fields['enrichment_last_message'] ?? '';
        $digital_score_raw    = $fields['digital_maturity_score'] ?? '';
        $financial_conf_raw   = $fields['financial_confidence'] ?? '';
        $priority_score_raw   = $fields['priority_score'] ?? '';
        $marketing_budget     = $fields['marketing_budget_est'] ?? '';
        $ebitda_margin        = $fields['ebitda_margin_est'] ?? '';
        $website_logo         = $fields['website_logo_url'] ?? '';
        $social_links         = $fields['social_links'] ?? '';

        $logo_url = lg_media_url($website_logo, 'medium', $theme_uri . '/common/img/logo.svg');

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

        $digital_score  = is_numeric($digital_score_raw) ? max(0, min(100, (int) $digital_score_raw)) : null;
        $financial_conf = is_numeric($financial_conf_raw) ? max(0, min(100, (int) $financial_conf_raw)) : null;
        $priority_score = is_numeric($priority_score_raw) ? max(0, min(100, (int) $priority_score_raw)) : null;
        $ebitda_display = $ebitda_margin !== '' ? sprintf('%s%%', trim((string) $ebitda_margin)) : '';

        $enrichment_display = '';
        if ($data_enrichment !== '') {
            $timestamp = strtotime($data_enrichment);
            if ($timestamp) {
                $enrichment_display = date_i18n('d/m/Y H:i', $timestamp);
            } else {
                $enrichment_display = $data_enrichment;
            }
        }

        $company_subtitle = $business_type !== '' ? $business_type : $sector_specific;

        $company_meta = [
            [
                'label' => __('Settore', 'lead-generator'),
                'value' => $sector_specific !== '' ? $sector_specific : $business_type,
            ],
            [
                'label' => __('Città', 'lead-generator'),
                'value' => $meta_location,
            ],
            [
                'label' => __('Respiro', 'lead-generator'),
                'value' => $geography_scope,
            ],
            [
                'label' => __('Dipendenti', 'lead-generator'),
                'value' => $employee_count,
            ],
            [
                'label' => __('Fatturato', 'lead-generator'),
                'value' => $annual_revenue,
            ],
            [
                'label' => __('Stadio', 'lead-generator'),
                'value' => $growth_stage,
            ],
        ];

        $profile_metrics = [
            [
                'label' => __('Tipo Business', 'lead-generator'),
                'value' => $business_type,
            ],
            [
                'label' => __('Settore Specifico', 'lead-generator'),
                'value' => $sector_specific,
            ],
            [
                'label' => __('Dipendenti', 'lead-generator'),
                'value' => $employee_count,
            ],
            [
                'label' => __('Stadio Crescita', 'lead-generator'),
                'value' => $growth_stage,
            ],
            [
                'label' => __('Respiro Geografico', 'lead-generator'),
                'value' => $geography_scope,
            ],
            [
                'label' => __('Maturità Digitale', 'lead-generator'),
                'value' => $digital_score !== null ? sprintf('%d / 100', $digital_score) : '',
                'progress' => $digital_score !== null ? $digital_score : null,
            ],
        ];

        $economics_cards = [
            [
                'label' => __('Fatturato', 'lead-generator'),
                'value' => $annual_revenue,
                'meta'  => __('ultimo anno stimato', 'lead-generator'),
            ],
            [
                'label' => __('EBITDA %', 'lead-generator'),
                'value' => $ebitda_display,
                'meta'  => __('stima', 'lead-generator'),
            ],
            [
                'label' => __('Budget Marketing', 'lead-generator'),
                'value' => $marketing_budget,
                'meta'  => __('annuale', 'lead-generator'),
            ],
        ];

        $digital_highlights = array_values(array_filter([
            $domain_display !== '' ? sprintf(__('Dominio verificato: %s', 'lead-generator'), $domain_display) : '',
            $linkedin_url !== '' ? __('Profilo LinkedIn collegato', 'lead-generator') : '',
            $enrichment_status !== '' ? sprintf(__('Ultimo enrichment: %s', 'lead-generator'), $enrichment_status) : '',
            $enrichment_display !== '' ? sprintf(__('Aggiornato il %s', 'lead-generator'), $enrichment_display) : '',
            $enrichment_message !== '' ? $enrichment_message : '',
        ]));

        $analisi_count = 0;
        $analisi_id = null;
        $analisi_fields = [];
        $analisi_query = new WP_Query([
            'post_type'      => 'analisi',
            'posts_per_page' => 1,
            'post_status'    => 'publish',
            'meta_query'     => [
                [
                    'key'     => 'parent_company_id',
                    'value'   => $post_id,
                    'compare' => '=',
                ],
            ],
        ]);

        if ($analisi_query->have_posts()) {
            $analisi_count = (int) $analisi_query->found_posts;
            $analisi_id = $analisi_query->posts[0]->ID;
            $analisi_fields = get_fields($analisi_id);
            $analisi_fields = is_array($analisi_fields) ? $analisi_fields : [];
        }
        wp_reset_postdata();

        // Estrai campi analisi
        $analisi_riassunto = $analisi_fields['riassunto'] ?? '';
        $analisi_data = $analisi_fields['analysis_last_at'] ?? '';
        $analisi_status = $analisi_fields['analysis_last_status_code'] ?? '';
        $analisi_qualita = $analisi_fields['voto_qualita_analisi'] ?? '';
        $analisi_qualita_dati = $analisi_fields['qualita_dati'] ?? '';
        $analisi_messaggi = $analisi_fields['messaggi_principali'] ?? '';
        $analisi_num_messaggi = $analisi_fields['numero_messaggi_principali'] ?? 0;
        $analisi_tono = $analisi_fields['tono_di_voce'] ?? '';
        $analisi_coerenza = $analisi_fields['coerenza_comunicativa'] ?? '';
        $analisi_differenzianti = $analisi_fields['elementi_differenzianti'] ?? '';
        $analisi_num_differenzianti = $analisi_fields['numero_elementi_differenzianti'] ?? 0;
        $analisi_target = $analisi_fields['target_commerciali'] ?? '';
        $analisi_num_target = $analisi_fields['numero_target_commerciali'] ?? 0;
        $analisi_promessa = $analisi_fields['promessa_di_valore'] ?? '';
        $analisi_domande = $analisi_fields['domande_prospect'] ?? '';
        $analisi_num_domande = $analisi_fields['numero_domande'] ?? 0;
        $analisi_idee = $analisi_fields['idee_di_valore_perspect'] ?? '';
        $analisi_num_idee = $analisi_fields['numero_idee_di_valore'] ?? 0;
        $analisi_forza = $analisi_fields['punti_di_forza'] ?? '';
        $analisi_num_forza = $analisi_fields['numero_punti_di_forza'] ?? 0;
        $analisi_debolezza = $analisi_fields['punti_di_debolezza'] ?? '';
        $analisi_num_debolezza = $analisi_fields['numero_punti_di_debolezza'] ?? 0;
        $analisi_opportunita = $analisi_fields['opportunita'] ?? '';
        $analisi_num_opportunita = $analisi_fields['numero_opportunita'] ?? 0;
        $analisi_azioni = $analisi_fields['azioni_rapide'] ?? '';
        $analisi_num_azioni = $analisi_fields['numero_azioni_rapide'] ?? 0;
        $analisi_rischi = $analisi_fields['rischi'] ?? '';
        $analisi_num_rischi = $analisi_fields['numero_rischi'] ?? 0;
        $analisi_priorita = $analisi_fields['priorita_temporali'] ?? '';

        // Formatta data analisi
        $analisi_data_display = '';
        if ($analisi_data !== '') {
            $timestamp = strtotime($analisi_data);
            if ($timestamp) {
                $analisi_data_display = date_i18n('d M Y', $timestamp);
            } else {
                $analisi_data_display = $analisi_data;
            }
        }
        ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class('azienda-profile'); ?>>
            <div class="ap-shell">
                <header class="ap-main-header">
                    <div class="ap-header-brand">
                        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php esc_attr_e('Logo azienda', 'lead-generator'); ?>">
                        <span><?php echo esc_html(get_bloginfo('name')); ?></span>
                    </div>
                    <nav class="ap-header-nav">
                        <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Aziende', 'lead-generator'); ?></a>
                        <a href="#"><?php esc_html_e('Impostazioni', 'lead-generator'); ?></a>
                        <a href="#"><?php esc_html_e('Aiuto', 'lead-generator'); ?></a>
                    </nav>
                </header>

                <section class="ap-company-header">
                    <div class="ap-company-title">
                        <div class="ap-company-main">
                            <div class="ap-company-logo">
                                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($company_name); ?>">
                            </div>
                            <div class="ap-company-info">
                                <div class="ap-company-name-row">
                                    <h1><?php echo esc_html($company_name); ?></h1>
                                    <?php if ($qualification_status !== '') : ?>
                                        <span class="ap-qualification-badge <?php echo esc_attr('status-' . sanitize_title($qualification_status)); ?>">
                                            <?php echo esc_html(ucfirst($qualification_status)); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($company_subtitle !== '') : ?>
                                    <p class="ap-company-subtitle"><?php echo esc_html($company_subtitle); ?></p>
                                <?php endif; ?>
                                <?php if ($domain_display !== '') : ?>
                                    <p class="ap-company-link">
                                        <a href="<?php echo esc_url($domain_url); ?>" target="_blank" rel="noreferrer noopener">
                                            <?php echo esc_html($domain_display); ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="ap-priority">
                            <div class="ap-priority-score">
                                <?php echo $priority_score !== null ? esc_html($priority_score) : '—'; ?>
                                <?php if ($priority_score !== null) : ?>
                                    <span class="ap-priority-max">/100</span>
                                <?php endif; ?>
                            </div>
                            <div class="ap-priority-label"><?php esc_html_e('Priority score', 'lead-generator'); ?></div>
                        </div>
                    </div>

                    <div class="ap-company-meta">
                        <?php foreach ($company_meta as $meta) : ?>
                            <div class="ap-meta-item">
                                <div class="ap-meta-label"><?php echo esc_html($meta['label']); ?></div>
                                <div class="ap-meta-value"><?php echo esc_html(lg_format_display($meta['value'])); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <nav class="ap-tab-nav" data-ap-tabs role="tablist">
                    <?php
                    $tabs = [
                        'anagrafica'  => __('Anagrafica', 'lead-generator'),
                        'economics'   => __('Economics', 'lead-generator'),
                        'digital'     => __('Digital', 'lead-generator'),
                        'qualifica'   => __('Qualifica', 'lead-generator'),
                        'analisi'     => __('Analisi Perspect', 'lead-generator'),
                    ];
                    $tab_index = 0;
                    foreach ($tabs as $slug => $label) :
                        $is_active = $tab_index === 0;
                        ?>
                        <button
                            type="button"
                            class="ap-tab-btn<?php echo $is_active ? ' is-active' : ''; ?>"
                            data-ap-tab="<?php echo esc_attr($slug); ?>"
                            role="tab"
                            aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
                            aria-controls="tab-<?php echo esc_attr($slug); ?>"
                        >
                            <?php echo esc_html($label); ?>
                        </button>
                        <?php
                        $tab_index++;
                    endforeach;
                    ?>
                </nav>

                <div class="ap-tabs-wrapper">
                    <section id="tab-anagrafica" class="ap-tab-panel is-active" data-ap-panel role="tabpanel">
                        <div class="form-field full-width">
                            <label><?php esc_html_e('Descrizione / Bio', 'lead-generator'); ?></label>
                            <p class="field-value"><?php echo esc_html(lg_format_display($short_bio)); ?></p>
                        </div>

                        <div class="grid-2col">
                            <div class="form-field">
                                <label><?php esc_html_e('Ragione sociale', 'lead-generator'); ?></label>
                                <p class="field-value"><?php echo esc_html(lg_format_display($company_name)); ?></p>
                            </div>
                            <div class="form-field">
                                <label><?php esc_html_e('Partita IVA', 'lead-generator'); ?></label>
                                <p class="field-value"><?php echo esc_html(lg_format_display($partita_iva)); ?></p>
                            </div>
                        </div>

                        <div class="grid-3col">
                            <div class="form-field">
                                <label><?php esc_html_e('Indirizzo', 'lead-generator'); ?></label>
                                <p class="field-value"><?php echo esc_html(lg_format_display($address)); ?></p>
                            </div>
                            <div class="form-field">
                                <label><?php esc_html_e('Città', 'lead-generator'); ?></label>
                                <p class="field-value"><?php echo esc_html(lg_format_display($city)); ?></p>
                            </div>
                            <div class="form-field">
                                <label><?php esc_html_e('Provincia', 'lead-generator'); ?></label>
                                <p class="field-value"><?php echo esc_html(lg_format_display($province)); ?></p>
                            </div>
                        </div>

                        <div class="grid-2col">
                            <div class="form-field">
                                <label><?php esc_html_e('Telefono', 'lead-generator'); ?></label>
                                <p class="field-value"><?php echo esc_html(lg_format_display($phone)); ?></p>
                            </div>
                            <div class="form-field">
                                <label><?php esc_html_e('Email', 'lead-generator'); ?></label>
                                <?php if ($email !== '') : ?>
                                    <a class="field-value-link" href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                                <?php else : ?>
                                    <p class="field-value"><?php esc_html_e('Non disponibile', 'lead-generator'); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-field full-width">
                            <label><?php esc_html_e('LinkedIn', 'lead-generator'); ?></label>
                            <?php if ($linkedin_url !== '') : ?>
                                <a class="field-value-link" href="<?php echo esc_url($linkedin_url); ?>" target="_blank" rel="noreferrer noopener">
                                    <?php echo esc_html($linkedin_url); ?>
                                </a>
                            <?php else : ?>
                                <p class="field-value"><?php esc_html_e('Non disponibile', 'lead-generator'); ?></p>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section id="tab-economics" class="ap-tab-panel" data-ap-panel role="tabpanel" hidden>
                        <div class="metrics-grid">
                            <?php foreach ($economics_cards as $card) : ?>
                                <div class="metric-card">
                                    <h4><?php echo esc_html($card['label']); ?></h4>
                                    <p class="metric-value"><?php echo esc_html(lg_format_display($card['value'])); ?></p>
                                    <span class="sub-text"><?php echo esc_html($card['meta']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="grid-2col">
                            <div class="form-field">
                                <label><?php esc_html_e('Tier budget', 'lead-generator'); ?></label>
                                <p><strong><?php echo esc_html(lg_format_display($budget_tier)); ?></strong></p>
                            </div>
                            <div class="form-field">
                                <label><?php esc_html_e('Confidenza dato', 'lead-generator'); ?></label>
                                <div class="coherence-meter">
                                    <div class="progress-container">
                                        <progress value="<?php echo esc_attr($financial_conf ?? 0); ?>" max="100"></progress>
                                        <span class="progress-value">
                                            <?php echo $financial_conf !== null ? esc_html(sprintf('%d%%', $financial_conf)) : '—'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="tab-digital" class="ap-tab-panel" data-ap-panel role="tabpanel" hidden>
                        <?php if ($social_links !== '') : ?>
                        <div class="form-field full-width">
                            <label><?php esc_html_e('Social Links', 'lead-generator'); ?></label>
                            <ul class="bullet-list" style="margin-top: 10px;">
                                <?php
                                $social_list = lg_parse_list($social_links);
                                foreach ($social_list as $social_item) :
                                ?>
                                    <li><?php echo esc_html($social_item); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <div style="margin-top: var(--spacing-xl);">
                            <h3 style="font-size: 18px; margin-bottom: var(--spacing-md);">
                                <?php
                                if ($digital_score !== null) {
                                    printf(esc_html__('Maturità Digitale: %d / 100', 'lead-generator'), $digital_score);
                                } else {
                                    esc_html_e('Maturità Digitale', 'lead-generator');
                                }
                                ?>
                            </h3>
                            <ul class="bullet-list">
                                <?php if ($domain_display !== '') : ?>
                                    <li><?php printf(esc_html__('Sito: %s', 'lead-generator'), $domain_display); ?></li>
                                <?php endif; ?>
                                <?php if ($linkedin_url !== '') : ?>
                                    <li><?php esc_html_e('Profilo LinkedIn attivo', 'lead-generator'); ?></li>
                                <?php endif; ?>
                                <?php if (!empty($digital_highlights)) : ?>
                                    <?php foreach ($digital_highlights as $highlight) : ?>
                                        <li><?php echo esc_html($highlight); ?></li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </section>

                    <section id="tab-qualifica" class="ap-tab-panel" data-ap-panel role="tabpanel" hidden>
                        <div class="grid-2col">
                            <div class="form-field">
                                <label><?php esc_html_e('Stato qualifica', 'lead-generator'); ?></label>
                                <p class="ap-qualifica-state"><?php echo esc_html(lg_format_display($qualification_status)); ?></p>
                            </div>
                            <div class="form-field">
                                <label><?php esc_html_e('Priority score', 'lead-generator'); ?></label>
                                <p class="ap-qualifica-priority">
                                    <?php echo $priority_score !== null ? esc_html(sprintf('%d / 100', $priority_score)) : '—'; ?>
                                </p>
                            </div>
                        </div>

                        <div class="grid-2col">
                            <div class="reason-box">
                                <h4><?php esc_html_e('Motivo della qualifica', 'lead-generator'); ?></h4>
                                <p><?php echo esc_html(lg_format_display($qualification_reason)); ?></p>
                            </div>
                            <div class="reason-box">
                                <h4><?php esc_html_e('Service fit', 'lead-generator'); ?></h4>
                                <p><?php echo esc_html(lg_format_display($service_fit)); ?></p>
                            </div>
                        </div>

                        <div class="message-box">
                            <h4><?php esc_html_e('Ultimo enrichment', 'lead-generator'); ?></h4>
                            <p>
                                <?php
                                if ($enrichment_display !== '') {
                                    printf(
                                        esc_html__('%s (%s)', 'lead-generator'),
                                        $enrichment_display,
                                        lg_format_display($enrichment_status)
                                    );
                                } else {
                                    esc_html_e('Nessun enrichment registrato.', 'lead-generator');
                                }
                                ?>
                            </p>
                        </div>
                    </section>

                    <section id="tab-analisi" class="ap-tab-panel" data-ap-panel role="tabpanel" hidden>
                        <?php if ($analisi_id !== null) : ?>
                            <!-- OVERVIEW -->
                            <div class="analysis-overview">
                                <h2><?php esc_html_e('Riassunto Esecutivo', 'lead-generator'); ?></h2>
                                <div class="overview-card">
                                    <p class="summary-text"><?php echo esc_html(lg_format_display($analisi_riassunto, 'Analisi in corso...')); ?></p>

                                    <div class="overview-meta">
                                        <div class="meta-badge">
                                            <div class="meta-badge-label"><?php esc_html_e('Data Analisi', 'lead-generator'); ?></div>
                                            <div class="meta-badge-value"><?php echo esc_html(lg_format_display($analisi_data_display)); ?></div>
                                        </div>
                                        <div class="meta-badge">
                                            <div class="meta-badge-label"><?php esc_html_e('Status', 'lead-generator'); ?></div>
                                            <div class="meta-badge-value"><?php echo esc_html(lg_format_display($analisi_status, '—')); ?></div>
                                        </div>
                                        <div class="meta-badge">
                                            <div class="meta-badge-label"><?php esc_html_e('Qualità', 'lead-generator'); ?></div>
                                            <div class="meta-badge-value"><?php echo esc_html(lg_format_display($analisi_qualita ? $analisi_qualita . ' / 10' : '')); ?></div>
                                        </div>
                                        <div class="meta-badge">
                                            <div class="meta-badge-label"><?php esc_html_e('Confidenza', 'lead-generator'); ?></div>
                                            <div class="meta-badge-value"><?php echo esc_html(lg_format_display($analisi_qualita_dati)); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ACCORDION SECTIONS -->
                            <div class="analysis-accordion">

                                <!-- ACCORDION 1: BRAND & POSITIONING -->
                                <div class="accordion-item">
                                    <button class="accordion-header" onclick="toggleAccordion(this)">
                                        <span class="accordion-icon">▶</span>
                                        <span class="accordion-title"><?php esc_html_e('Brand e Posizionamento', 'lead-generator'); ?></span>
                                        <div class="accordion-meta">
                                            <span class="badge"><?php echo esc_html($analisi_num_messaggi . ' ' . __('Messaggi', 'lead-generator')); ?></span>
                                            <span class="badge"><?php echo esc_html(lg_format_display($analisi_coerenza, '—') . ' ' . __('Coerenza', 'lead-generator')); ?></span>
                                        </div>
                                    </button>

                                    <div class="accordion-body">
                                        <div class="accordion-content">
                                            <div class="grid-2col">
                                                <div class="brand-section">
                                                    <h4><?php esc_html_e('Messaggi Principali', 'lead-generator'); ?></h4>
                                                    <?php
                                                    $messaggi_list = lg_parse_list($analisi_messaggi);
                                                    if (!empty($messaggi_list)) :
                                                    ?>
                                                        <ol class="numbered-list">
                                                            <?php foreach ($messaggi_list as $msg) : ?>
                                                                <li><?php echo esc_html($msg); ?></li>
                                                            <?php endforeach; ?>
                                                        </ol>
                                                    <?php else : ?>
                                                        <p class="tone-text"><?php esc_html_e('Nessun messaggio disponibile', 'lead-generator'); ?></p>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="brand-section">
                                                    <h4><?php esc_html_e('Tono di Voce', 'lead-generator'); ?></h4>
                                                    <p class="tone-text"><?php echo esc_html(lg_format_display($analisi_tono)); ?></p>

                                                    <?php if ($analisi_coerenza !== '') : ?>
                                                    <div class="coherence-meter">
                                                        <label><?php esc_html_e('Coerenza Comunicativa', 'lead-generator'); ?></label>
                                                        <div class="progress-container">
                                                            <?php
                                                            $coerenza_val = is_numeric($analisi_coerenza) ? max(0, min(100, (int) $analisi_coerenza)) : 0;
                                                            ?>
                                                            <progress value="<?php echo esc_attr($coerenza_val); ?>" max="100"></progress>
                                                            <span class="progress-value"><?php echo esc_html($coerenza_val . '%'); ?></span>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="brand-section">
                                                    <h4><?php esc_html_e('Elementi Differenzianti', 'lead-generator'); ?></h4>
                                                    <?php
                                                    $diff_list = lg_parse_list($analisi_differenzianti);
                                                    if (!empty($diff_list)) :
                                                    ?>
                                                        <ul class="bullet-list">
                                                            <?php foreach ($diff_list as $diff) : ?>
                                                                <li><?php echo esc_html($diff); ?></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php else : ?>
                                                        <p class="tone-text"><?php esc_html_e('Nessun elemento disponibile', 'lead-generator'); ?></p>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="brand-section">
                                                    <h4><?php esc_html_e('Target Commerciali', 'lead-generator'); ?></h4>
                                                    <?php
                                                    $target_list = lg_parse_list($analisi_target);
                                                    if (!empty($target_list)) :
                                                    ?>
                                                        <ul class="bullet-list">
                                                            <?php foreach ($target_list as $target) : ?>
                                                                <li><?php echo esc_html($target); ?></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php else : ?>
                                                        <p class="tone-text"><?php esc_html_e('Nessun target identificato', 'lead-generator'); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ACCORDION 2: COMMERCIAL ANALYSIS -->
                                <div class="accordion-item">
                                    <button class="accordion-header" onclick="toggleAccordion(this)">
                                        <span class="accordion-icon">▶</span>
                                        <span class="accordion-title"><?php esc_html_e('Analisi Commerciale', 'lead-generator'); ?></span>
                                        <div class="accordion-meta">
                                            <span class="badge"><?php echo esc_html($analisi_num_idee . ' ' . __('Idee', 'lead-generator')); ?></span>
                                            <span class="badge"><?php echo esc_html($analisi_num_domande . ' ' . __('Domande', 'lead-generator')); ?></span>
                                        </div>
                                    </button>

                                    <div class="accordion-body">
                                        <div class="accordion-content">
                                            <?php if ($analisi_promessa !== '') : ?>
                                            <div class="value-promise">
                                                <h4><?php esc_html_e('Promessa di Valore', 'lead-generator'); ?></h4>
                                                <blockquote><?php echo esc_html($analisi_promessa); ?></blockquote>
                                            </div>
                                            <?php endif; ?>

                                            <div class="grid-2col">
                                                <div class="commercial-section">
                                                    <h4><?php esc_html_e('Domande Prospect Chiave', 'lead-generator'); ?></h4>
                                                    <?php
                                                    $domande_list = lg_parse_list($analisi_domande);
                                                    if (!empty($domande_list)) :
                                                    ?>
                                                        <ul class="bullet-list">
                                                            <?php foreach ($domande_list as $domanda) : ?>
                                                                <li><?php echo esc_html($domanda); ?></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php else : ?>
                                                        <p class="tone-text"><?php esc_html_e('Nessuna domanda identificata', 'lead-generator'); ?></p>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="commercial-section">
                                                    <h4><?php esc_html_e('Idee di Valore Perspect', 'lead-generator'); ?></h4>
                                                    <?php
                                                    $idee_list = lg_parse_list($analisi_idee);
                                                    if (!empty($idee_list)) :
                                                    ?>
                                                        <ol class="numbered-list">
                                                            <?php foreach ($idee_list as $idea) : ?>
                                                                <li><?php echo esc_html($idea); ?></li>
                                                            <?php endforeach; ?>
                                                        </ol>
                                                    <?php else : ?>
                                                        <p class="tone-text"><?php esc_html_e('Nessuna idea disponibile', 'lead-generator'); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ACCORDION 3: SWOT -->
                                <div class="accordion-item">
                                    <button class="accordion-header" onclick="toggleAccordion(this)">
                                        <span class="accordion-icon">▶</span>
                                        <span class="accordion-title"><?php esc_html_e('Punti di Forza e Debolezza', 'lead-generator'); ?></span>
                                        <div class="accordion-meta">
                                            <span class="badge"><?php echo esc_html($analisi_num_forza . ' ' . __('Forza', 'lead-generator')); ?></span>
                                            <span class="badge"><?php echo esc_html($analisi_num_debolezza . ' ' . __('Debolezze', 'lead-generator')); ?></span>
                                        </div>
                                    </button>

                                    <div class="accordion-body">
                                        <div class="accordion-content">
                                            <div class="grid-2x2">
                                                <div class="swot-card">
                                                    <h4><?php esc_html_e('Punti di Forza', 'lead-generator'); ?></h4>
                                                    <?php
                                                    $forza_list = lg_parse_list($analisi_forza);
                                                    if (!empty($forza_list)) :
                                                    ?>
                                                        <ul class="bullet-list">
                                                            <?php foreach ($forza_list as $item) : ?>
                                                                <li><?php echo esc_html($item); ?></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php else : ?>
                                                        <p class="tone-text"><?php esc_html_e('Nessun dato disponibile', 'lead-generator'); ?></p>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="swot-card">
                                                    <h4><?php esc_html_e('Punti di Debolezza', 'lead-generator'); ?></h4>
                                                    <?php
                                                    $debolezza_list = lg_parse_list($analisi_debolezza);
                                                    if (!empty($debolezza_list)) :
                                                    ?>
                                                        <ul class="bullet-list">
                                                            <?php foreach ($debolezza_list as $item) : ?>
                                                                <li><?php echo esc_html($item); ?></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php else : ?>
                                                        <p class="tone-text"><?php esc_html_e('Nessun dato disponibile', 'lead-generator'); ?></p>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="swot-card">
                                                    <h4><?php esc_html_e('Opportunità', 'lead-generator'); ?></h4>
                                                    <?php
                                                    $opportunita_list = lg_parse_list($analisi_opportunita);
                                                    if (!empty($opportunita_list)) :
                                                    ?>
                                                        <ul class="bullet-list">
                                                            <?php foreach ($opportunita_list as $item) : ?>
                                                                <li><?php echo esc_html($item); ?></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php else : ?>
                                                        <p class="tone-text"><?php esc_html_e('Nessun dato disponibile', 'lead-generator'); ?></p>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="swot-card">
                                                    <h4><?php esc_html_e('Azioni Rapide', 'lead-generator'); ?></h4>
                                                    <?php
                                                    $azioni_list = lg_parse_list($analisi_azioni);
                                                    if (!empty($azioni_list)) :
                                                    ?>
                                                        <ol class="numbered-list">
                                                            <?php foreach ($azioni_list as $item) : ?>
                                                                <li><?php echo esc_html($item); ?></li>
                                                            <?php endforeach; ?>
                                                        </ol>
                                                    <?php else : ?>
                                                        <p class="tone-text"><?php esc_html_e('Nessun dato disponibile', 'lead-generator'); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ACCORDION 4: RISKS -->
                                <div class="accordion-item">
                                    <button class="accordion-header" onclick="toggleAccordion(this)">
                                        <span class="accordion-icon">▶</span>
                                        <span class="accordion-title"><?php esc_html_e('Rischi e Mitigazione', 'lead-generator'); ?></span>
                                        <div class="accordion-meta">
                                            <span class="badge"><?php echo esc_html($analisi_num_rischi . ' ' . __('Rischi', 'lead-generator')); ?></span>
                                        </div>
                                    </button>

                                    <div class="accordion-body">
                                        <div class="accordion-content">
                                            <?php
                                            $rischi_list = lg_parse_list($analisi_rischi);
                                            if (!empty($rischi_list)) :
                                            ?>
                                                <table class="risks-table">
                                                    <thead>
                                                        <tr>
                                                            <th><?php esc_html_e('Rischio', 'lead-generator'); ?></th>
                                                            <th><?php esc_html_e('Mitigazione', 'lead-generator'); ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($rischi_list as $rischio) : ?>
                                                            <?php
                                                            // Prova a splittare rischio|mitigazione
                                                            $parts = explode('|', $rischio, 2);
                                                            $r = isset($parts[0]) ? trim($parts[0]) : $rischio;
                                                            $m = isset($parts[1]) ? trim($parts[1]) : '—';
                                                            ?>
                                                            <tr>
                                                                <td><?php echo esc_html($r); ?></td>
                                                                <td><?php echo esc_html($m); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            <?php else : ?>
                                                <p class="tone-text"><?php esc_html_e('Nessun rischio identificato', 'lead-generator'); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- ACCORDION 5: TIMELINE -->
                                <div class="accordion-item">
                                    <button class="accordion-header" onclick="toggleAccordion(this)">
                                        <span class="accordion-icon">▶</span>
                                        <span class="accordion-title"><?php esc_html_e('Timing e Priorità', 'lead-generator'); ?></span>
                                        <div class="accordion-meta">
                                            <span class="badge"><?php echo esc_html(lg_format_display($analisi_priorita, '—')); ?></span>
                                        </div>
                                    </button>

                                    <div class="accordion-body">
                                        <div class="accordion-content">
                                            <div class="timeline-info">
                                                <div class="timeline-section">
                                                    <h4><?php esc_html_e('Timing Consigliato', 'lead-generator'); ?></h4>
                                                    <p class="timeline-value"><?php echo esc_html(lg_format_display($analisi_priorita)); ?></p>
                                                </div>
                                                <div class="timeline-section">
                                                    <h4><?php esc_html_e('Canale Prioritario', 'lead-generator'); ?></h4>
                                                    <p class="timeline-value"><?php esc_html_e('LinkedIn → Email → Call', 'lead-generator'); ?></p>
                                                </div>
                                                <div class="timeline-section full-width">
                                                    <h4><?php esc_html_e('Best Contact', 'lead-generator'); ?></h4>
                                                    <div class="contact-box">
                                                        <?php if ($email !== '' || $phone !== '' || $linkedin_url !== '') : ?>
                                                            <?php if ($email !== '') : ?>
                                                                <p><strong><?php esc_html_e('Email:', 'lead-generator'); ?></strong> <?php echo esc_html($email); ?></p>
                                                            <?php endif; ?>
                                                            <?php if ($phone !== '') : ?>
                                                                <p><strong><?php esc_html_e('Telefono:', 'lead-generator'); ?></strong> <?php echo esc_html($phone); ?></p>
                                                            <?php endif; ?>
                                                            <?php if ($linkedin_url !== '') : ?>
                                                                <p><strong><?php esc_html_e('LinkedIn:', 'lead-generator'); ?></strong> <?php echo esc_html($linkedin_url); ?></p>
                                                            <?php endif; ?>
                                                        <?php else : ?>
                                                            <p><?php esc_html_e('Contatti non disponibili', 'lead-generator'); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        <?php else : ?>
                            <div class="ap-placeholder-box">
                                <h4><?php esc_html_e('Nessuna analisi disponibile', 'lead-generator'); ?></h4>
                                <p><?php esc_html_e('Non è ancora stata generata un\'analisi per questa azienda.', 'lead-generator'); ?></p>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>

                <div class="quick-actions-bar">
                    <button type="button" class="btn-primary"><?php esc_html_e('Genera outreach email', 'lead-generator'); ?></button>
                    <button type="button" class="btn-secondary"><?php esc_html_e('Prepara LinkedIn', 'lead-generator'); ?></button>
                    <button type="button" class="btn-secondary"><?php esc_html_e('Full report PDF', 'lead-generator'); ?></button>
                    <button type="button" class="btn-secondary"><?php esc_html_e('Riavvia analisi', 'lead-generator'); ?></button>
                </div>
            </div>
        </article>

        <?php
    endwhile;
endif;

get_footer();
