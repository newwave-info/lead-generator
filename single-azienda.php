<?php
/**
 * Template per single post type: azienda
 */

get_header();

// Helper functions
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

if (have_posts()) : while (have_posts()) : the_post();

$post_id = get_the_ID();
$fields = get_fields($post_id);
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

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --color-primary: #5b7fa6;
    --color-primary-dark: #3d4f6f;
    --color-primary-light: #8ba3c4;
    --color-bg-light: #ffffff;
    --color-bg-medium: #f7f9fc;
    --color-bg-soft: #eef2f7;
    --color-text-primary: #2a3f5f;
    --color-text-secondary: #5a6b7e;
    --color-text-tertiary: #8a95a8;
    --color-border: #d4dce8;
    --color-border-light: #e8eef5;
    --color-accent: #6b8ec4;
    --spacing-xs: 8px;
    --spacing-sm: 16px;
    --spacing-md: 24px;
    --spacing-lg: 40px;
    --spacing-xl: 60px;
}

html, body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    background: var(--color-bg-light);
    color: var(--color-text-primary);
    line-height: 1.6;
}

/* Typography */
h1 {
    font-size: 60px;
    font-weight: 700;
    letter-spacing: -1.2px;
    line-height: 1.15;
    margin: 0;
    color: var(--color-text-primary);
}

h2 {
    font-size: 38px;
    font-weight: 700;
    letter-spacing: -0.6px;
    line-height: 1.2;
    margin: 0 0 var(--spacing-md) 0;
    color: var(--color-text-primary);
}

h3 {
    font-size: 26px;
    font-weight: 700;
    letter-spacing: -0.3px;
    line-height: 1.25;
    margin: 0 0 var(--spacing-md) 0;
    color: var(--color-text-primary);
}

h4 {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    line-height: 1.4;
    color: var(--color-text-tertiary);
    margin: 0 0 var(--spacing-sm) 0;
}

p, li {
    font-size: 16px;
    font-weight: 400;
    line-height: 1.7;
    color: var(--color-text-primary);
}

/* Company Header */
.company-header {
    padding: var(--spacing-xl);
    background: linear-gradient(135deg, #5b7fa6 0%, #6b8ec4 100%);
    color: #ffffff;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.company-title-section {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: var(--spacing-xl);
    margin-bottom: var(--spacing-xl);
    align-items: flex-start;
}

.company-title-section h1 {
    color: #ffffff;
    font-size: 58px;
}

.company-subtitle {
    font-size: 17px;
    color: rgba(255, 255, 255, 0.85);
    font-weight: 400;
    letter-spacing: 0.2px;
    margin-top: 10px;
}

.priority-badge {
    text-align: right;
    padding-top: 8px;
}

.priority-score {
    font-size: 64px;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 6px;
    color: #ffffff;
}

.priority-label {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.75);
    text-transform: uppercase;
    letter-spacing: 0.8px;
    font-weight: 600;
}

.company-meta {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: var(--spacing-lg);
    padding-top: var(--spacing-lg);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.meta-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.meta-item-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: rgba(255, 255, 255, 0.7);
}

.meta-item-value {
    font-size: 16px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.98);
}

/* Tab Navigation */
.tab-navigation {
    display: flex;
    border-bottom: 1px solid var(--color-border-light);
    background: var(--color-bg-light);
    padding: 0 var(--spacing-xl);
    gap: 0;
}

.tab-btn {
    flex: 0 0 auto;
    padding: 16px 14px;
    font-size: 14px;
    font-weight: 600;
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--color-text-secondary);
    border-bottom: 3px solid transparent;
    transition: all 0.2s;
    white-space: nowrap;
    letter-spacing: 0.2px;
    text-transform: capitalize;
}

.tab-btn:hover {
    color: var(--color-primary);
    background: var(--color-bg-medium);
}

.tab-btn.active {
    color: var(--color-primary);
    border-bottom-color: var(--color-primary);
    background: var(--color-bg-light);
    font-weight: 700;
}

/* Tab Content */
.tab-container {
    display: none;
    padding: var(--spacing-xl);
    animation: fadeIn 0.3s ease-in;
    background: var(--color-bg-light);
}

.tab-container.active {
    display: block;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(2px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Grid Layouts */
.grid-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-lg);
}

.grid-3col {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-lg);
}

.grid-2x2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-lg);
}

/* Form Fields */
.form-grid {
    display: grid;
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-lg);
}

.form-field {
    display: flex;
    flex-direction: column;
}

.form-field label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: var(--color-text-tertiary);
    margin-bottom: 8px;
}

.form-field input,
.form-field textarea,
.form-field p {
    font-size: 15px;
    color: var(--color-text-primary);
    line-height: 1.6;
}

.form-field input,
.form-field textarea {
    padding: 12px var(--spacing-sm);
    border: 1px solid var(--color-border);
    background: var(--color-bg-medium);
    font-family: inherit;
}

.form-field a {
    color: var(--color-primary);
    text-decoration: none;
    font-weight: 600;
    border-bottom: 1px solid var(--color-border);
}

.form-field a:hover {
    border-bottom-color: var(--color-primary);
}

/* Metric Cards */
.metrics-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-lg);
}

.metric-card {
    padding: var(--spacing-lg);
    background: var(--color-bg-medium);
    border: 1px solid var(--color-border-light);
}

.metric-card .metric-value {
    font-size: 26px;
    font-weight: 700;
    color: var(--color-primary);
    margin: 10px 0;
}

.metric-card progress {
    width: 100%;
    height: 5px;
    border-radius: 0;
    border: none;
    background: var(--color-border);
    margin-top: 10px;
}

.metric-card progress::-webkit-progress-bar {
    background: var(--color-border);
}

.metric-card progress::-webkit-progress-value {
    background: var(--color-accent);
}

.metric-card progress::-moz-progress-bar {
    background: var(--color-accent);
}

/* Lists */
.bullet-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.bullet-list li {
    padding: 9px 0 9px 26px;
    position: relative;
    font-size: 15px;
    line-height: 1.7;
    color: var(--color-text-secondary);
}

.bullet-list li:before {
    content: "—";
    position: absolute;
    left: 0;
    color: var(--color-text-tertiary);
    font-weight: 700;
    font-size: 16px;
}

/* Accordion for Analysis */
.analysis-section {
    margin: var(--spacing-xl) 0;
}

.analysis-card {
    background: var(--color-bg-light);
    border: 1px solid var(--color-border-light);
    margin-bottom: var(--spacing-md);
    padding: var(--spacing-lg);
}

.analysis-card h3 {
    font-size: 22px;
    margin-bottom: var(--spacing-md);
    color: var(--color-primary);
}

.analysis-link {
    display: inline-block;
    margin-top: var(--spacing-md);
    color: var(--color-primary);
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
}

.analysis-link:hover {
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 1024px) {
    .grid-2col,
    .grid-2x2,
    .company-meta {
        grid-template-columns: 1fr;
    }
    .grid-3col {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 768px) {
    h1 { font-size: 48px; }
    h2 { font-size: 32px; }
    h3 { font-size: 22px; }
    p, li { font-size: 15px; }

    .company-header,
    .tab-navigation,
    .tab-container {
        padding-left: var(--spacing-lg);
        padding-right: var(--spacing-lg);
    }

    .company-title-section {
        grid-template-columns: 1fr;
        gap: var(--spacing-lg);
    }

    .company-title-main h1 {
        font-size: 48px;
    }

    .company-meta {
        grid-template-columns: 1fr;
        gap: var(--spacing-md);
    }

    .metrics-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Company Header -->
<div class="company-header">
    <div class="company-title-section">
        <div class="company-title-main">
            <h1><?php echo esc_html($company_name); ?></h1>
            <?php if ($sector_specific !== '') : ?>
                <p class="company-subtitle"><?php echo esc_html($sector_specific); ?></p>
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
            <?php echo wp_kses_post(wpautop($social_links)); ?>
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
                <?php echo wp_kses_post(wpautop($qualification_reason)); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($service_fit !== '') : ?>
        <div class="form-field full-width">
            <label>Servizi Possibili</label>
            <div style="background: var(--color-bg-medium); padding: var(--spacing-lg); border: 1px solid var(--color-border-light);">
                <?php echo wp_kses_post(wpautop($service_fit)); ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- TAB 7: ANALYSIS -->
<?php if ($has_analisi) : ?>
<div id="analysis" class="tab-container">
    <h2>Analisi Collegate</h2>

    <?php foreach ($analisi_items as $analisi) : ?>
        <div class="analysis-card">
            <h3><?php echo esc_html($analisi['title']); ?></h3>

            <?php if ($analisi['riassunto'] !== '') : ?>
                <div style="margin-bottom: var(--spacing-md);">
                    <?php echo wp_kses_post(wpautop($analisi['riassunto'])); ?>
                </div>
            <?php endif; ?>

            <a href="<?php echo esc_url($analisi['permalink']); ?>" class="analysis-link">Visualizza analisi completa →</a>
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
</script>

<?php
endwhile;
endif;

get_footer();
