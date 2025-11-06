<?php
/**
 * Template per single post type: azienda
 */

// Helper functions - must be defined before get_header()
if (!function_exists('lg_media_url')) {
    function lg_media_url($value, $size = 'large', $fallback = '') {
        if (is_numeric($value)) {
            $url = wp_get_attachment_image_url((int) $value, $size);
            if ($url) return $url;
        }
        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed !== '' && filter_var($trimmed, FILTER_VALIDATE_URL)) {
                return $trimmed;
            }
        }
        return $fallback !== '' ? $fallback : 'https://via.placeholder.com/400x300?text=Media';
    }
}

if (!function_exists('lg_normalize_domain')) {
    function lg_normalize_domain($domain) {
        $domain = trim((string) $domain);
        if ($domain === '') return '';
        if (!preg_match('#^https?://#i', $domain)) {
            $domain = 'https://' . ltrim($domain, '/');
        }
        return esc_url($domain);
    }
}

if (!function_exists('lg_extract_strings')) {
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

if (!function_exists('lg_to_string')) {
    function lg_to_string($value) {
        if (is_string($value)) {
            return $value;
        }
        if (is_array($value)) {
            // If it's an array, try to extract text content
            $strings = [];
            foreach ($value as $item) {
                if (is_string($item)) {
                    $strings[] = $item;
                } elseif (is_array($item) && isset($item['value'])) {
                    $strings[] = (string) $item['value'];
                } elseif (is_scalar($item)) {
                    $strings[] = (string) $item;
                }
            }
            return implode("\n", $strings);
        }
        if (is_scalar($value)) {
            return (string) $value;
        }
        return '';
    }
}

get_header();

if (have_posts()) : while (have_posts()) : the_post();

$post_id = get_the_ID();
$fields = function_exists('get_fields') ? get_fields($post_id) : [];
$fields = is_array($fields) ? $fields : [];
$theme_uri = get_template_directory_uri();

// Campi Azienda
$company_name = !empty($fields['company_name_full']) ? $fields['company_name_full'] : get_the_title();
$partita_iva = $fields['partita_iva'] ?? '';
$domain_raw = $fields['domain'] ?? '';
$short_bio = $fields['short_bio'] ?? '';
$address = $fields['address'] ?? '';
$city = $fields['city'] ?? '';
$province = $fields['province'] ?? '';
$phone = $fields['phone'] ?? '';
$email = $fields['email'] ?? '';
$linkedin_url = $fields['linkedin_url'] ?? '';
$business_type = $fields['business_type'] ?? '';
$sector_specific = $fields['sector_specific'] ?? '';
$employee_count = $fields['employee_count'] ?? '';
$growth_stage = $fields['growth_stage'] ?? '';
$geography_scope = $fields['geography_scope'] ?? '';
$annual_revenue = $fields['annual_revenue'] ?? '';
$ebitda_margin = $fields['ebitda_margin_est'] ?? '';
$marketing_budget = $fields['marketing_budget_est'] ?? '';
$budget_tier = $fields['budget_tier'] ?? '';
$financial_conf_raw = $fields['financial_confidence'] ?? '';
$digital_score_raw = $fields['digital_maturity_score'] ?? '';
$qualification_status = $fields['qualification_status'] ?? '';
$qualification_reason = $fields['qualification_reason'] ?? '';
$service_fit = $fields['service_fit'] ?? '';
$priority_score_raw = $fields['priority_score'] ?? '';
$social_links = $fields['social_links'] ?? '';
$data_enrichment = $fields['data_ultimo_enrichment'] ?? '';
$enrichment_status = $fields['enrichment_last_status_code'] ?? '';
$enrichment_message = $fields['enrichment_last_message'] ?? '';
$website_logo = $fields['website_logo_url'] ?? '';
$website_screenshot = $fields['website_screenshot_url'] ?? '';

$logo_url = lg_media_url($website_logo, 'medium', $theme_uri . '/common/img/logo.svg');
$screenshot_url = lg_media_url($website_screenshot, 'large', 'https://via.placeholder.com/320x200?text=Anteprima');

$domain_url = lg_normalize_domain($domain_raw);
$domain_display = '';
if ($domain_raw !== '') {
    $domain_display = trim(preg_replace('#^https?://#i', '', $domain_raw));
    $domain_display = rtrim($domain_display, '/');
}

$meta_location = '';
if ($city !== '' && $province !== '') {
    $meta_location = sprintf('%s, %s', $city, $province);
} elseif ($city !== '') {
    $meta_location = $city;
} elseif ($province !== '') {
    $meta_location = $province;
}

$digital_score = is_numeric($digital_score_raw) ? max(0, min(100, (int) $digital_score_raw)) : null;
$financial_conf = is_numeric($financial_conf_raw) ? max(0, min(100, (int) $financial_conf_raw)) : null;
$priority_score = is_numeric($priority_score_raw) ? max(0, min(100, (int) $priority_score_raw)) : null;

// Query analisi collegate
$analisi_query = new WP_Query([
    'post_type' => 'analisi',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'meta_query' => [
        [
            'key' => 'parent_company_id',
            'value' => $post_id,
            'compare' => '=',
        ],
    ],
]);

$analisi_items = [];
if ($analisi_query->have_posts()) {
    while ($analisi_query->have_posts()) {
        $analisi_query->the_post();
        $analysis_id = get_the_ID();
        $analysis_fields = get_fields($analysis_id);
        $analysis_fields = is_array($analysis_fields) ? $analysis_fields : [];

        $analisi_items[] = [
            'post_id' => $analysis_id,
            'title' => get_the_title(),
            'permalink' => get_permalink(),
            'riassunto' => $analysis_fields['riassunto'] ?? '',
            'strengths' => lg_extract_strings($analysis_fields['punti_di_forza'] ?? []),
            'weaknesses' => lg_extract_strings($analysis_fields['punti_di_debolezza'] ?? []),
            'opportunities' => lg_extract_strings($analysis_fields['opportunita'] ?? []),
            'quick_wins' => lg_extract_strings($analysis_fields['azioni_rapide'] ?? []),
            'deep_research' => $analysis_fields['analisy_perplexity_deep_research'] ?? '',
            'review' => $analysis_fields['revisione_analisi_completa'] ?? '',
            'risks' => lg_extract_strings($analysis_fields['rischi'] ?? []),
            'questions' => lg_extract_strings($analysis_fields['domande_prospect'] ?? []),
            'value_ideas' => lg_extract_strings($analysis_fields['idee_di_valore_perspect'] ?? []),
            'quality_score' => $analysis_fields['voto_qualita_analisi'] ?? null,
            'data_quality' => $analysis_fields['qualita_dati'] ?? null,
            'analysis_status' => $analysis_fields['analysis_last_status_code'] ?? '',
            'analysis_at' => $analysis_fields['analysis_last_at'] ?? '',
            'messaggi_principali' => lg_extract_strings($analysis_fields['messaggi_principali'] ?? []),
            'promessa_di_valore' => $analysis_fields['promessa_di_valore'] ?? '',
            'tono_di_voce' => $analysis_fields['tono_di_voce'] ?? '',
            'coerenza_comunicativa' => $analysis_fields['coerenza_comunicativa'] ?? '',
            'elementi_differenzianti' => lg_extract_strings($analysis_fields['elementi_differenzianti'] ?? []),
            'target_commerciali' => lg_extract_strings($analysis_fields['target_commerciali'] ?? []),
            'priorita_temporali' => $analysis_fields['priorita_temporali'] ?? '',
        ];
    }
    wp_reset_postdata();
}
$has_analisi = !empty($analisi_items);
?>

<!-- Company Header -->
<div class="company-header">
    <div class="company-title-section">
        <div class="company-title-main">
            <h1><?php echo esc_html($company_name); ?></h1>
            <?php if ($sector_specific !== '') : ?>
                <p class="company-subtitle"><?php echo esc_html($sector_specific); ?></p>
            <?php endif; ?>

            <?php if ($logo_url !== '' || $screenshot_url !== '') : ?>
                <div class="company-visual">
                    <?php if ($logo_url !== '') : ?>
                        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($company_name); ?> Logo" class="company-logo" />
                    <?php endif; ?>
                    <?php if ($screenshot_url !== '') : ?>
                        <img src="<?php echo esc_url($screenshot_url); ?>" alt="<?php echo esc_attr($company_name); ?> Website" class="company-screenshot" />
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($priority_score !== null) : ?>
            <div class="priority-badge">
                <div class="priority-score"><?php echo esc_html($priority_score); ?></div>
                <div class="priority-label">Priority Score</div>
            </div>
        <?php endif; ?>
    </div>

    <div class="company-meta">
        <div class="meta-item">
            <div class="meta-item-label">Città</div>
            <div class="meta-item-value"><?php echo $meta_location !== '' ? esc_html($meta_location) : '—'; ?></div>
        </div>
        <div class="meta-item">
            <div class="meta-item-label">Respiro</div>
            <div class="meta-item-value"><?php echo $geography_scope !== '' ? esc_html($geography_scope) : '—'; ?></div>
        </div>
        <div class="meta-item">
            <div class="meta-item-label">Dipendenti</div>
            <div class="meta-item-value"><?php echo $employee_count !== '' ? esc_html($employee_count) : '—'; ?></div>
        </div>
        <div class="meta-item">
            <div class="meta-item-label">Fatturato</div>
            <div class="meta-item-value"><?php echo $annual_revenue !== '' ? esc_html($annual_revenue) : '—'; ?></div>
        </div>
        <div class="meta-item">
            <div class="meta-item-label">Stadio</div>
            <div class="meta-item-value"><?php echo $growth_stage !== '' ? esc_html($growth_stage) : '—'; ?></div>
        </div>
        <div class="meta-item">
            <div class="meta-item-label">Settore</div>
            <div class="meta-item-value"><?php echo $business_type !== '' ? esc_html($business_type) : '—'; ?></div>
        </div>
    </div>
</div>

<!-- Tab Navigation -->
<nav class="tab-navigation">
    <button class="tab-btn active" data-tab="dati-minimi">Dati Minimi</button>
    <button class="tab-btn" data-tab="anagrafica">Anagrafica</button>
    <button class="tab-btn" data-tab="profilo">Profilo</button>
    <button class="tab-btn" data-tab="economics">Economics</button>
    <button class="tab-btn" data-tab="digital">Digital</button>
    <button class="tab-btn" data-tab="qualifica">Qualifica</button>
    <?php if ($has_analisi) : ?>
        <button class="tab-btn" data-tab="analysis">Analisi</button>
    <?php endif; ?>
</nav>

<!-- Tab Contents -->

<!-- TAB 1: DATI MINIMI -->
<div id="dati-minimi" class="tab-container active">
    <div class="grid-2col">
        <div class="form-field">
            <label>Ragione Sociale</label>
            <input type="text" value="<?php echo esc_attr($company_name); ?>" readonly />
        </div>
        <div class="form-field">
            <label>Partita IVA</label>
            <input type="text" value="<?php echo esc_attr($partita_iva); ?>" readonly />
        </div>
    </div>
    <div class="form-field full-width">
        <label>Dominio / Sito Web</label>
        <input type="url" value="<?php echo esc_attr($domain_url); ?>" readonly />
    </div>
</div>

<!-- TAB 2: ANAGRAFICA -->
<div id="anagrafica" class="tab-container">
    <?php if ($short_bio !== '') : ?>
        <div class="form-field full-width">
            <label>Descrizione / Bio</label>
            <textarea readonly style="min-height: 110px;"><?php echo esc_textarea($short_bio); ?></textarea>
        </div>
    <?php endif; ?>

    <div class="grid-3col">
        <div class="form-field">
            <label>Indirizzo</label>
            <p><?php echo $address !== '' ? esc_html($address) : '—'; ?></p>
        </div>
        <div class="form-field">
            <label>Città</label>
            <p><?php echo $city !== '' ? esc_html($city) : '—'; ?></p>
        </div>
        <div class="form-field">
            <label>Provincia</label>
            <p><?php echo $province !== '' ? esc_html($province) : '—'; ?></p>
        </div>
    </div>

    <div class="grid-2col">
        <div class="form-field">
            <label>Telefono</label>
            <p><?php echo $phone !== '' ? esc_html($phone) : '—'; ?></p>
        </div>
        <div class="form-field">
            <label>Email</label>
            <p><?php echo $email !== '' ? esc_html($email) : '—'; ?></p>
        </div>
    </div>

    <?php if ($linkedin_url !== '') : ?>
        <div class="form-field full-width">
            <label>LinkedIn</label>
            <p><a href="<?php echo lg_normalize_domain($linkedin_url); ?>" target="_blank" rel="nofollow noopener"><?php echo esc_html($linkedin_url); ?></a></p>
        </div>
    <?php endif; ?>
</div>

<!-- TAB 3: PROFILO -->
<div id="profilo" class="tab-container">
    <div class="metrics-grid">
        <div class="metric-card">
            <h4>Tipo Business</h4>
            <p class="metric-value"><?php echo $business_type !== '' ? esc_html($business_type) : '—'; ?></p>
        </div>
        <div class="metric-card">
            <h4>Settore Specifico</h4>
            <p class="metric-value"><?php echo $sector_specific !== '' ? esc_html($sector_specific) : '—'; ?></p>
        </div>
        <div class="metric-card">
            <h4>Dipendenti</h4>
            <p class="metric-value"><?php echo $employee_count !== '' ? esc_html($employee_count) : '—'; ?></p>
        </div>
        <div class="metric-card">
            <h4>Stadio Crescita</h4>
            <p class="metric-value"><?php echo $growth_stage !== '' ? esc_html($growth_stage) : '—'; ?></p>
        </div>
        <div class="metric-card">
            <h4>Respiro Geografico</h4>
            <p class="metric-value"><?php echo $geography_scope !== '' ? esc_html($geography_scope) : '—'; ?></p>
        </div>
        <div class="metric-card">
            <h4>Maturità Digitale</h4>
            <p class="metric-value"><?php echo $digital_score !== null ? esc_html($digital_score) : '—'; ?> / 100</p>
            <?php if ($digital_score !== null) : ?>
                <progress value="<?php echo esc_attr($digital_score); ?>" max="100"></progress>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- TAB 4: ECONOMICS -->
<div id="economics" class="tab-container">
    <div class="metrics-grid">
        <div class="metric-card">
            <h4>Fatturato</h4>
            <p class="metric-value"><?php echo $annual_revenue !== '' ? esc_html($annual_revenue) : '—'; ?></p>
        </div>
        <div class="metric-card">
            <h4>EBITDA %</h4>
            <p class="metric-value"><?php echo $ebitda_margin !== '' ? esc_html($ebitda_margin . '%') : '—'; ?></p>
        </div>
        <div class="metric-card">
            <h4>Budget Marketing</h4>
            <p class="metric-value"><?php echo $marketing_budget !== '' ? esc_html($marketing_budget) : '—'; ?></p>
        </div>
    </div>

    <div class="grid-2col">
        <div class="form-field">
            <label>Tier Budget</label>
            <p><strong><?php echo $budget_tier !== '' ? esc_html($budget_tier) : '—'; ?></strong></p>
        </div>
        <div class="form-field">
            <label>Confidenza Dato</label>
            <?php if ($financial_conf !== null) : ?>
                <progress value="<?php echo esc_attr($financial_conf); ?>" max="100"></progress>
                <p><?php echo esc_html($financial_conf); ?>%</p>
            <?php else : ?>
                <p>—</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- TAB 5: DIGITAL -->
<div id="digital" class="tab-container">
    <?php if ($social_links !== '') : ?>
        <div class="form-field full-width">
            <label>Social Links</label>
            <?php echo wp_kses_post(wpautop(lg_to_string($social_links))); ?>
        </div>
    <?php endif; ?>

    <?php if ($digital_score !== null) : ?>
        <div style="margin-top: var(--spacing-xl);">
            <h3 style="font-size: 18px; margin-bottom: var(--spacing-md);">Maturità Digitale: <?php echo esc_html($digital_score); ?> / 100</h3>
            <progress value="<?php echo esc_attr($digital_score); ?>" max="100" style="width: 100%; height: 8px;"></progress>
        </div>
    <?php endif; ?>
</div>

<!-- TAB 6: QUALIFICA -->
<div id="qualifica" class="tab-container">
    <div class="grid-2col">
        <div class="form-field">
            <label>Stato Qualifica</label>
            <p><strong style="font-size: 17px; color: var(--color-primary);"><?php echo $qualification_status !== '' ? esc_html($qualification_status) : '—'; ?></strong></p>
        </div>
        <div class="form-field">
            <label>Priorità Score</label>
            <p style="font-size: 32px; font-weight: 700; color: var(--color-primary);"><?php echo $priority_score !== null ? esc_html($priority_score) . ' / 100' : '—'; ?></p>
        </div>
    </div>

    <?php if ($qualification_reason !== '') : ?>
        <div class="form-field full-width">
            <label>Motivo Qualifica</label>
            <div style="background: var(--color-bg-medium); padding: var(--spacing-lg); border: 1px solid var(--color-border-light); border-left: 3px solid var(--color-accent);">
                <?php echo wp_kses_post(wpautop(lg_to_string($qualification_reason))); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($service_fit !== '') : ?>
        <div class="form-field full-width">
            <label>Servizi Possibili</label>
            <div style="background: var(--color-bg-medium); padding: var(--spacing-lg); border: 1px solid var(--color-border-light);">
                <?php echo wp_kses_post(wpautop(lg_to_string($service_fit))); ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- TAB 7: ANALYSIS -->
<?php if ($has_analisi) : ?>
<div id="analysis" class="tab-container">
    <h2>Analisi Perspect</h2>

    <?php foreach ($analisi_items as $index => $analisi) : ?>
        <!-- Analysis Overview -->
        <div class="analysis-overview" style="margin-top: <?php echo $index > 0 ? 'var(--spacing-xl)' : '0'; ?>;">
            <h3 style="font-size: 28px; margin-bottom: var(--spacing-md); color: var(--color-primary);">
                <?php echo esc_html($analisi['title']); ?>
            </h3>

            <div class="overview-card">
                <?php if (!empty($analisi['riassunto'])) : ?>
                    <p class="summary-text"><?php echo esc_html($analisi['riassunto']); ?></p>
                <?php endif; ?>

                <div class="overview-meta">
                    <div class="meta-badge">
                        <div class="meta-badge-label">Data Analisi</div>
                        <div class="meta-badge-value">
                            <?php
                            if (!empty($analisi['analysis_at'])) {
                                $timestamp = strtotime($analisi['analysis_at']);
                                echo $timestamp ? esc_html(date_i18n('d M Y', $timestamp)) : '—';
                            } else {
                                echo '—';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="meta-badge">
                        <div class="meta-badge-label">Status</div>
                        <div class="meta-badge-value"><?php echo !empty($analisi['analysis_status']) ? esc_html($analisi['analysis_status']) : '—'; ?></div>
                    </div>
                    <div class="meta-badge">
                        <div class="meta-badge-label">Qualità</div>
                        <div class="meta-badge-value"><?php echo isset($analisi['quality_score']) && $analisi['quality_score'] !== null ? esc_html($analisi['quality_score']) . ' / 10' : '—'; ?></div>
                    </div>
                    <div class="meta-badge">
                        <div class="meta-badge-label">Confidenza</div>
                        <div class="meta-badge-value"><?php echo !empty($analisi['data_quality']) ? esc_html($analisi['data_quality']) : '—'; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accordion Sections -->
        <div class="analysis-accordion">

            <!-- ACCORDION 1: BRAND & POSITIONING -->
            <?php if (!empty($analisi['messaggi_principali']) || !empty($analisi['tono_di_voce']) || !empty($analisi['elementi_differenzianti']) || !empty($analisi['target_commerciali'])) : ?>
            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span class="accordion-icon">▶</span>
                    <span class="accordion-title">Brand e Posizionamento</span>
                    <div class="accordion-meta">
                        <span class="badge"><?php echo count($analisi['messaggi_principali'] ?? []); ?> Messaggi</span>
                        <?php if (!empty($analisi['coerenza_comunicativa'])) : ?>
                            <span class="badge"><?php echo esc_html($analisi['coerenza_comunicativa']); ?> Coerenza</span>
                        <?php endif; ?>
                    </div>
                </button>

                <div class="accordion-body">
                    <div class="accordion-content">
                        <div class="grid-2col">
                            <?php if (!empty($analisi['messaggi_principali'])) : ?>
                                <div class="brand-section">
                                    <h4>Messaggi Principali</h4>
                                    <ol class="numbered-list">
                                        <?php foreach ($analisi['messaggi_principali'] as $msg) : ?>
                                            <li><?php echo esc_html($msg); ?></li>
                                        <?php endforeach; ?>
                                    </ol>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($analisi['tono_di_voce'])) : ?>
                                <div class="brand-section">
                                    <h4>Tono di Voce</h4>
                                    <p class="tone-text"><?php echo esc_html($analisi['tono_di_voce']); ?></p>

                                    <?php if (!empty($analisi['coerenza_comunicativa'])) : ?>
                                        <div style="margin-top: var(--spacing-md);">
                                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--color-text-tertiary); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.7px;">Coerenza Comunicativa</label>
                                            <p style="font-size: 14px; font-weight: 700; color: var(--color-primary);"><?php echo esc_html($analisi['coerenza_comunicativa']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($analisi['elementi_differenzianti'])) : ?>
                                <div class="brand-section">
                                    <h4>Elementi Differenzianti</h4>
                                    <ul class="bullet-list">
                                        <?php foreach ($analisi['elementi_differenzianti'] as $elem) : ?>
                                            <li><?php echo esc_html($elem); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($analisi['target_commerciali'])) : ?>
                                <div class="brand-section">
                                    <h4>Target Commerciali</h4>
                                    <ul class="bullet-list">
                                        <?php foreach ($analisi['target_commerciali'] as $target) : ?>
                                            <li><?php echo esc_html($target); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ACCORDION 2: COMMERCIAL ANALYSIS -->
            <?php if (!empty($analisi['promessa_di_valore']) || !empty($analisi['questions']) || !empty($analisi['value_ideas'])) : ?>
            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span class="accordion-icon">▶</span>
                    <span class="accordion-title">Analisi Commerciale</span>
                    <div class="accordion-meta">
                        <span class="badge"><?php echo count($analisi['value_ideas'] ?? []); ?> Idee</span>
                        <span class="badge"><?php echo count($analisi['questions'] ?? []); ?> Domande</span>
                    </div>
                </button>

                <div class="accordion-body">
                    <div class="accordion-content">
                        <?php if (!empty($analisi['promessa_di_valore'])) : ?>
                            <div class="value-promise">
                                <h4>Promessa di Valore</h4>
                                <blockquote><?php echo esc_html($analisi['promessa_di_valore']); ?></blockquote>
                            </div>
                        <?php endif; ?>

                        <div class="grid-2col">
                            <?php if (!empty($analisi['questions'])) : ?>
                                <div class="commercial-section">
                                    <h4>Domande Prospect Chiave</h4>
                                    <ul class="bullet-list">
                                        <?php foreach ($analisi['questions'] as $question) : ?>
                                            <li><?php echo esc_html($question); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($analisi['value_ideas'])) : ?>
                                <div class="commercial-section">
                                    <h4>Idee di Valore Perspect</h4>
                                    <ol class="numbered-list">
                                        <?php foreach ($analisi['value_ideas'] as $idea) : ?>
                                            <li><?php echo esc_html($idea); ?></li>
                                        <?php endforeach; ?>
                                    </ol>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ACCORDION 3: SWOT -->
            <?php if (!empty($analisi['strengths']) || !empty($analisi['weaknesses']) || !empty($analisi['opportunities']) || !empty($analisi['quick_wins'])) : ?>
            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span class="accordion-icon">▶</span>
                    <span class="accordion-title">Punti di Forza e Debolezza</span>
                    <div class="accordion-meta">
                        <span class="badge"><?php echo count($analisi['strengths'] ?? []); ?> Punti Forza</span>
                        <span class="badge"><?php echo count($analisi['weaknesses'] ?? []); ?> Debolezze</span>
                    </div>
                </button>

                <div class="accordion-body">
                    <div class="accordion-content">
                        <div class="grid-2x2">
                            <?php if (!empty($analisi['strengths'])) : ?>
                                <div class="swot-card">
                                    <h4>Punti di Forza</h4>
                                    <ul class="bullet-list">
                                        <?php foreach ($analisi['strengths'] as $strength) : ?>
                                            <li><?php echo esc_html($strength); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($analisi['weaknesses'])) : ?>
                                <div class="swot-card">
                                    <h4>Punti di Debolezza</h4>
                                    <ul class="bullet-list">
                                        <?php foreach ($analisi['weaknesses'] as $weakness) : ?>
                                            <li><?php echo esc_html($weakness); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($analisi['opportunities'])) : ?>
                                <div class="swot-card">
                                    <h4>Opportunità</h4>
                                    <ul class="bullet-list">
                                        <?php foreach ($analisi['opportunities'] as $opportunity) : ?>
                                            <li><?php echo esc_html($opportunity); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($analisi['quick_wins'])) : ?>
                                <div class="swot-card">
                                    <h4>Azioni Rapide</h4>
                                    <ol class="numbered-list">
                                        <?php foreach ($analisi['quick_wins'] as $action) : ?>
                                            <li><?php echo esc_html($action); ?></li>
                                        <?php endforeach; ?>
                                    </ol>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ACCORDION 4: RISKS -->
            <?php if (!empty($analisi['risks'])) : ?>
            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span class="accordion-icon">▶</span>
                    <span class="accordion-title">Rischi e Mitigazione</span>
                    <div class="accordion-meta">
                        <span class="badge"><?php echo count($analisi['risks'] ?? []); ?> Rischi</span>
                    </div>
                </button>

                <div class="accordion-body">
                    <div class="accordion-content">
                        <ul class="bullet-list">
                            <?php foreach ($analisi['risks'] as $risk) : ?>
                                <li><?php echo esc_html($risk); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ACCORDION 5: DEEP RESEARCH -->
            <?php if (!empty($analisi['deep_research']) || !empty($analisi['review'])) : ?>
            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span class="accordion-icon">▶</span>
                    <span class="accordion-title">Analisi Approfondita</span>
                </button>

                <div class="accordion-body">
                    <div class="accordion-content">
                        <?php if (!empty($analisi['deep_research'])) : ?>
                            <div style="margin-bottom: var(--spacing-lg);">
                                <h4>Analisi Iniziale</h4>
                                <div style="margin-top: var(--spacing-md);">
                                    <?php echo wp_kses_post(wpautop(lg_to_string($analisi['deep_research']))); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($analisi['review'])) : ?>
                            <div>
                                <h4>Revisione Analisi</h4>
                                <div style="margin-top: var(--spacing-md);">
                                    <?php echo wp_kses_post(wpautop(lg_to_string($analisi['review']))); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ACCORDION 6: PRIORITA TEMPORALI -->
            <?php if (!empty($analisi['priorita_temporali'])) : ?>
            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span class="accordion-icon">▶</span>
                    <span class="accordion-title">Priorità Temporali</span>
                </button>

                <div class="accordion-body">
                    <div class="accordion-content">
                        <?php echo wp_kses_post(wpautop(lg_to_string($analisi['priorita_temporali']))); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

    <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const tabId = this.dataset.tab;
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-container').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    });
});

function toggleAccordion(header) {
    const body = header.nextElementSibling;
    const isOpen = body.classList.contains('open');

    // Close all accordions
    document.querySelectorAll('.accordion-body').forEach(b => b.classList.remove('open'));
    document.querySelectorAll('.accordion-header').forEach(h => h.classList.remove('active'));

    // Open clicked accordion if it was closed
    if (!isOpen) {
        body.classList.add('open');
        header.classList.add('active');
    }
}
</script>

<?php
endwhile;
endif;

get_footer();
