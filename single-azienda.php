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
            [
                'label' => __('Settore', 'lead-generator'),
                'value' => $sector_specific !== '' ? $sector_specific : $business_type,
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
        $analisi_query = new WP_Query([
            'post_type'      => 'analisi',
            'posts_per_page' => 1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
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
        }
        wp_reset_postdata();
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
                        <div>
                            <h1><?php echo esc_html($company_name); ?></h1>
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
                        <div class="ap-priority">
                            <div class="ap-priority-score"><?php echo $priority_score !== null ? esc_html($priority_score) : '—'; ?></div>
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
                        'dati-minimi' => __('Dati Minimi', 'lead-generator'),
                        'anagrafica'  => __('Anagrafica', 'lead-generator'),
                        'profilo'     => __('Profilo', 'lead-generator'),
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
                    <section id="tab-dati-minimi" class="ap-tab-panel is-active" data-ap-panel role="tabpanel">
                        <div class="grid-2col">
                            <div class="form-field">
                                <label><?php esc_html_e('Ragione sociale', 'lead-generator'); ?></label>
                                <input type="text" readonly value="<?php echo esc_attr($company_name); ?>">
                            </div>
                            <div class="form-field">
                                <label><?php esc_html_e('Partita IVA', 'lead-generator'); ?></label>
                                <input type="text" readonly value="<?php echo esc_attr($partita_iva); ?>">
                            </div>
                        </div>
                        <div class="form-field full-width">
                            <label><?php esc_html_e('Dominio / sito web', 'lead-generator'); ?></label>
                            <?php if ($domain_display !== '') : ?>
                                <a class="ap-link-plain" href="<?php echo esc_url($domain_url); ?>" target="_blank" rel="noreferrer noopener">
                                    <?php echo esc_html($domain_display); ?>
                                </a>
                            <?php else : ?>
                                <span class="ap-placeholder"><?php esc_html_e('Non disponibile', 'lead-generator'); ?></span>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section id="tab-anagrafica" class="ap-tab-panel" data-ap-panel role="tabpanel" hidden>
                        <div class="form-field full-width">
                            <label><?php esc_html_e('Descrizione / Bio', 'lead-generator'); ?></label>
                            <textarea readonly><?php echo esc_textarea(lg_format_display($short_bio, '')); ?></textarea>
                        </div>

                        <div class="grid-3col">
                            <div class="form-field">
                                <label><?php esc_html_e('Indirizzo', 'lead-generator'); ?></label>
                                <p><?php echo esc_html(lg_format_display($address)); ?></p>
                            </div>
                            <div class="form-field">
                                <label><?php esc_html_e('Città', 'lead-generator'); ?></label>
                                <p><?php echo esc_html(lg_format_display($city)); ?></p>
                            </div>
                            <div class="form-field">
                                <label><?php esc_html_e('Provincia', 'lead-generator'); ?></label>
                                <p><?php echo esc_html(lg_format_display($province)); ?></p>
                            </div>
                        </div>

                        <div class="grid-2col">
                            <div class="form-field">
                                <label><?php esc_html_e('Telefono', 'lead-generator'); ?></label>
                                <p><?php echo esc_html(lg_format_display($phone)); ?></p>
                            </div>
                            <div class="form-field">
                                <label><?php esc_html_e('Email', 'lead-generator'); ?></label>
                                <?php if ($email !== '') : ?>
                                    <a class="ap-link-plain" href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                                <?php else : ?>
                                    <p><?php esc_html_e('Non disponibile', 'lead-generator'); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-field full-width">
                            <label><?php esc_html_e('LinkedIn', 'lead-generator'); ?></label>
                            <?php if ($linkedin_url !== '') : ?>
                                <a class="ap-link-plain" href="<?php echo esc_url($linkedin_url); ?>" target="_blank" rel="noreferrer noopener">
                                    <?php echo esc_html($linkedin_url); ?>
                                </a>
                            <?php else : ?>
                                <span class="ap-placeholder"><?php esc_html_e('Non disponibile', 'lead-generator'); ?></span>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section id="tab-profilo" class="ap-tab-panel" data-ap-panel role="tabpanel" hidden>
                        <div class="metrics-grid">
                            <?php foreach ($profile_metrics as $metric) : ?>
                                <div class="metric-card">
                                    <h4><?php echo esc_html($metric['label']); ?></h4>
                                    <p class="metric-value"><?php echo esc_html(lg_format_display($metric['value'])); ?></p>
                                    <?php if (array_key_exists('progress', $metric) && $metric['progress'] !== null) : ?>
                                        <progress max="100" value="<?php echo esc_attr($metric['progress']); ?>"></progress>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
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
                        <div class="form-field full-width">
                            <label><?php esc_html_e('Social & contatti digitali', 'lead-generator'); ?></label>
                            <ul class="bullet-list">
                                <?php if ($linkedin_url !== '') : ?>
                                    <li><?php esc_html_e('LinkedIn collegato', 'lead-generator'); ?>: <?php echo esc_html($linkedin_url); ?></li>
                                <?php endif; ?>
                                <?php if ($domain_display !== '') : ?>
                                    <li><?php esc_html_e('Dominio verificato', 'lead-generator'); ?>: <?php echo esc_html($domain_display); ?></li>
                                <?php endif; ?>
                                <?php if ($email !== '') : ?>
                                    <li><?php esc_html_e('Email primaria', 'lead-generator'); ?>: <?php echo esc_html($email); ?></li>
                                <?php endif; ?>
                                <?php if ($phone !== '') : ?>
                                    <li><?php esc_html_e('Telefono', 'lead-generator'); ?>: <?php echo esc_html($phone); ?></li>
                                <?php endif; ?>
                                <?php if ($linkedin_url === '' && $domain_display === '' && $email === '' && $phone === '') : ?>
                                    <li><?php esc_html_e('Nessun contatto digitale disponibile.', 'lead-generator'); ?></li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <div class="digital-summary">
                            <h3><?php esc_html_e('Sintesi maturità digitale', 'lead-generator'); ?></h3>
                            <p class="tone-text">
                                <?php
                                if ($digital_score !== null) {
                                    printf(esc_html__('Punteggio attuale: %d/100', 'lead-generator'), $digital_score);
                                } else {
                                    esc_html_e('Punteggio non disponibile', 'lead-generator');
                                }
                                ?>
                            </p>
                            <ul class="bullet-list">
                                <?php if (!empty($digital_highlights)) : ?>
                                    <?php foreach ($digital_highlights as $highlight) : ?>
                                        <li><?php echo esc_html($highlight); ?></li>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <li><?php esc_html_e('In attesa di aggiornare la diagnostica digitale.', 'lead-generator'); ?></li>
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
                        <div class="ap-placeholder-box">
                            <h4><?php esc_html_e('Schede analisi', 'lead-generator'); ?></h4>
                            <p>
                                <?php esc_html_e('Stiamo lavorando al nuovo layout delle analisi. Saranno disponibili in un passaggio successivo.', 'lead-generator'); ?>
                            </p>
                            <?php if ($analisi_count > 0) : ?>
                                <p class="ap-placeholder-count">
                                    <?php
                                    printf(
                                        esc_html(_n('Hai già %d analisi collegate a questa azienda.', 'Hai già %d analisi collegate a questa azienda.', $analisi_count, 'lead-generator')),
                                        $analisi_count
                                    );
                                    ?>
                                </p>
                            <?php endif; ?>
                        </div>
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
