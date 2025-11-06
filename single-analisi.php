<?php
/**
 * Template per single post type: analisi
 */

get_header();

// Helper functions
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

// Campi Analisi
$parent_company_id = $fields['parent_company_id'] ?? null;
$parent_company_title = '';
$parent_company_url = '';
if ($parent_company_id) {
    $parent_company_title = get_the_title($parent_company_id);
    $parent_company_url = get_permalink($parent_company_id);
}

$riassunto = $fields['riassunto'] ?? '';
$report_discorsivo = $fields['report_discorsivo'] ?? '';
$deep_research = $fields['analisy_perplexity_deep_research'] ?? '';
$review = $fields['revisione_analisi_completa'] ?? '';
$strengths = lg_extract_strings($fields['punti_di_forza'] ?? []);
$weaknesses = lg_extract_strings($fields['punti_di_debolezza'] ?? []);
$opportunities = lg_extract_strings($fields['opportunita'] ?? []);
$quick_wins = lg_extract_strings($fields['azioni_rapide'] ?? []);
$quality_score = $fields['voto_qualita_analisi'] ?? null;
$data_quality = $fields['qualita_dati'] ?? null;
$analysis_status = $fields['analysis_last_status_code'] ?? '';
$analysis_message = $fields['analysis_last_message'] ?? '';
$analysis_at = $fields['analysis_last_at'] ?? '';

// Brand e Posizionamento
$messaggi_principali = lg_extract_strings($fields['messaggi_principali'] ?? []);
$numero_messaggi = $fields['numero_messaggi_principali'] ?? count($messaggi_principali);
$promessa_di_valore = $fields['promessa_di_valore'] ?? '';
$tono_di_voce = $fields['tono_di_voce'] ?? '';
$elementi_differenzianti = lg_extract_strings($fields['elementi_differenzianti'] ?? []);
$coerenza_comunicativa = $fields['coerenza_comunicativa'] ?? '';
$target_commerciali = lg_extract_strings($fields['target_commerciali'] ?? []);

// Analisi Commerciale
$domande_prospect = lg_extract_strings($fields['domande_prospect'] ?? []);
$numero_domande = $fields['numero_domande'] ?? count($domande_prospect);
$idee_di_valore = lg_extract_strings($fields['idee_di_valore_perspect'] ?? []);
$numero_idee = $fields['numero_idee_di_valore'] ?? count($idee_di_valore);

// Rischi
$rischi = lg_extract_strings($fields['rischi'] ?? []);
$numero_rischi = $fields['numero_rischi'] ?? count($rischi);

// Priorità temporali
$priorita_temporali = $fields['priorita_temporali'] ?? '';

// Conteggi
$numero_forza = $fields['numero_punti_di_forza'] ?? count($strengths);
$numero_debolezza = $fields['numero_punti_di_debolezza'] ?? count($weaknesses);
$numero_opportunita = $fields['numero_opportunita'] ?? count($opportunities);
$numero_azioni = $fields['numero_azioni_rapide'] ?? count($quick_wins);
?>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --color-primary: #2c3e50;
    --color-primary-dark: #1a252f;
    --color-primary-light: #556270;
    --color-bg-light: #ffffff;
    --color-bg-medium: #f7f9fc;
    --color-bg-soft: #eef2f7;
    --color-text-primary: #1e272e;
    --color-text-secondary: #4a5568;
    --color-text-tertiary: #6c7a89;
    --color-border: #d4dce8;
    --color-border-light: #e8eef5;
    --color-accent: #556270;
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

/* Analysis Header */
.analysis-header {
    padding: var(--spacing-xl);
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: #ffffff;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.analysis-title-section {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: var(--spacing-xl);
    margin-bottom: var(--spacing-xl);
    align-items: flex-start;
}

.analysis-title-section h1 {
    color: #ffffff;
    font-size: 58px;
}

.analysis-subtitle {
    font-size: 17px;
    color: rgba(255, 255, 255, 0.85);
    font-weight: 400;
    letter-spacing: 0.2px;
    margin-top: 10px;
}

.quality-badge {
    text-align: right;
    padding-top: 8px;
}

.quality-score {
    font-size: 64px;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 6px;
    color: #ffffff;
}

.quality-label {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.75);
    text-transform: uppercase;
    letter-spacing: 0.8px;
    font-weight: 600;
}

.analysis-meta {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
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

/* Analysis Overview */
.analysis-overview {
    margin-bottom: var(--spacing-xl);
    padding: var(--spacing-xl);
}

.overview-card {
    background: var(--color-bg-medium);
    padding: var(--spacing-lg);
    margin-bottom: var(--spacing-md);
    border: 1px solid var(--color-border-light);
    border-left: 4px solid var(--color-accent);
}

.summary-text {
    font-size: 16px;
    line-height: 1.75;
    color: var(--color-text-primary);
    margin-bottom: var(--spacing-lg);
    font-weight: 500;
}

.overview-meta {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--spacing-lg);
}

.meta-badge {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.meta-badge-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: var(--color-text-tertiary);
    font-weight: 700;
}

.meta-badge-value {
    font-size: 17px;
    color: var(--color-primary);
    font-weight: 700;
}

/* Accordion */
.analysis-accordion {
    margin: var(--spacing-xl);
    border: 1px solid var(--color-border-light);
    background: var(--color-bg-light);
    overflow: hidden;
}

.accordion-item {
    border-bottom: 1px solid var(--color-border-light);
}

.accordion-item:last-child {
    border-bottom: none;
}

.accordion-item:nth-child(odd) .accordion-header {
    background: var(--color-bg-light);
}

.accordion-item:nth-child(even) .accordion-header {
    background: var(--color-bg-medium);
}

.accordion-header {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 18px var(--spacing-lg);
    border: none;
    cursor: pointer;
    font-size: 15px;
    font-weight: 700;
    color: var(--color-text-primary);
    transition: all 0.2s ease;
    text-align: left;
    letter-spacing: 0.2px;
}

.accordion-header:hover {
    background: var(--color-bg-soft);
}

.accordion-header.active {
    background: var(--color-bg-soft);
}

.accordion-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    font-size: 10px;
    color: var(--color-primary);
    font-weight: 700;
    transition: transform 0.3s ease;
    flex-shrink: 0;
}

.accordion-header.active .accordion-icon {
    transform: rotate(90deg);
}

.accordion-title {
    flex: 1;
}

.accordion-meta {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-left: auto;
}

.badge {
    display: inline-block;
    background: transparent;
    color: var(--color-text-tertiary);
    font-size: 11px;
    font-weight: 700;
    padding: 3px 9px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
    border: 1px solid var(--color-border);
}

.accordion-body {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.accordion-body.open {
    max-height: 5000px;
}

.accordion-content {
    padding: var(--spacing-lg);
    background: var(--color-bg-light);
    border-top: 1px solid var(--color-border-light);
}

/* Grid Layouts */
.grid-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-lg);
}

.grid-2x2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-lg);
}

/* Sections within Accordion */
.brand-section,
.commercial-section,
.swot-card {
    padding: var(--spacing-lg);
    background: var(--color-bg-medium);
    border: 1px solid var(--color-border-light);
}

.swot-card {
    border-left: 3px solid var(--color-accent);
}

/* Lists */
.numbered-list,
.bullet-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.numbered-list {
    counter-reset: item;
}

.numbered-list li,
.bullet-list li {
    padding: 9px 0 9px 26px;
    position: relative;
    font-size: 15px;
    line-height: 1.7;
    color: var(--color-text-secondary);
}

.numbered-list li:before {
    content: counter(item);
    counter-increment: item;
    position: absolute;
    left: 0;
    width: 18px;
    height: 18px;
    background: var(--color-accent);
    color: white;
    font-size: 10px;
    font-weight: 700;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

.bullet-list li:before {
    content: "—";
    position: absolute;
    left: 0;
    color: var(--color-text-tertiary);
    font-weight: 700;
    font-size: 16px;
}

/* Value Promise Box */
.value-promise {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: var(--spacing-lg);
    margin-bottom: var(--spacing-lg);
}

.value-promise h4 {
    color: rgba(255, 255, 255, 0.85);
}

.value-promise blockquote {
    margin: 0;
    font-size: 16px;
    font-style: italic;
    color: rgba(255, 255, 255, 0.95);
    line-height: 1.75;
    padding-left: 0;
    font-weight: 500;
}

/* Tone Text */
.tone-text {
    font-size: 15px;
    color: var(--color-text-secondary);
    line-height: 1.75;
}

/* Tables */
.risks-table {
    width: 100%;
    border-collapse: collapse;
    background: var(--color-bg-light);
}

.risks-table th {
    background: var(--color-bg-medium);
    padding: 12px var(--spacing-lg);
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: var(--color-text-tertiary);
    border-bottom: 1px solid var(--color-border);
}

.risks-table td {
    padding: 14px var(--spacing-lg);
    font-size: 15px;
    line-height: 1.6;
    color: var(--color-text-secondary);
    border-bottom: 1px solid var(--color-border-light);
}

.risks-table tr:nth-child(even) {
    background: var(--color-bg-soft);
}

/* Responsive */
@media (max-width: 1024px) {
    .grid-2col,
    .grid-2x2,
    .analysis-meta {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    h1 { font-size: 48px; }
    h2 { font-size: 32px; }
    h3 { font-size: 22px; }
    p, li { font-size: 15px; }

    .analysis-header,
    .analysis-overview {
        padding-left: var(--spacing-lg);
        padding-right: var(--spacing-lg);
    }

    .analysis-title-section {
        grid-template-columns: 1fr;
        gap: var(--spacing-lg);
    }

    .analysis-title-section h1 {
        font-size: 48px;
    }

    .analysis-meta,
    .overview-meta {
        grid-template-columns: 1fr;
        gap: var(--spacing-md);
    }

    .accordion-meta {
        display: none;
    }
}
</style>

<!-- Analysis Header -->
<div class="analysis-header">
    <div class="analysis-title-section">
        <div class="analysis-title-main">
            <h1><?php echo esc_html(get_the_title()); ?></h1>
            <?php if ($parent_company_title !== '') : ?>
                <p class="analysis-subtitle">
                    Analisi per:
                    <?php if ($parent_company_url) : ?>
                        <a href="<?php echo esc_url($parent_company_url); ?>" style="color: rgba(255, 255, 255, 0.95); text-decoration: underline;">
                            <?php echo esc_html($parent_company_title); ?>
                        </a>
                    <?php else : ?>
                        <?php echo esc_html($parent_company_title); ?>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
        <?php if ($quality_score !== null) : ?>
            <div class="quality-badge">
                <div class="quality-score"><?php echo esc_html($quality_score); ?></div>
                <div class="quality-label">Quality Score</div>
            </div>
        <?php endif; ?>
    </div>

    <div class="analysis-meta">
        <div class="meta-item">
            <div class="meta-item-label">Data Analisi</div>
            <div class="meta-item-value"><?php echo $analysis_at !== '' ? esc_html(date_i18n('d M Y', strtotime($analysis_at))) : '—'; ?></div>
        </div>
        <div class="meta-item">
            <div class="meta-item-label">Status</div>
            <div class="meta-item-value"><?php echo $analysis_status !== '' ? esc_html($analysis_status) : '—'; ?></div>
        </div>
        <div class="meta-item">
            <div class="meta-item-label">Qualità</div>
            <div class="meta-item-value"><?php echo $quality_score !== null ? esc_html($quality_score) . ' / 10' : '—'; ?></div>
        </div>
        <div class="meta-item">
            <div class="meta-item-label">Confidenza</div>
            <div class="meta-item-value"><?php echo $data_quality !== '' ? esc_html($data_quality) : '—'; ?></div>
        </div>
    </div>
</div>

<!-- Analysis Overview -->
<div class="analysis-overview">
    <h2>Riassunto Esecutivo</h2>
    <div class="overview-card">
        <?php if ($riassunto !== '') : ?>
            <p class="summary-text"><?php echo esc_html($riassunto); ?></p>
        <?php endif; ?>

        <div class="overview-meta">
            <div class="meta-badge">
                <div class="meta-badge-label">Data Analisi</div>
                <div class="meta-badge-value"><?php echo $analysis_at !== '' ? esc_html(date_i18n('d M Y', strtotime($analysis_at))) : '—'; ?></div>
            </div>
            <div class="meta-badge">
                <div class="meta-badge-label">Status</div>
                <div class="meta-badge-value"><?php echo $analysis_status !== '' ? esc_html($analysis_status) : '—'; ?></div>
            </div>
            <div class="meta-badge">
                <div class="meta-badge-label">Qualità</div>
                <div class="meta-badge-value"><?php echo $quality_score !== null ? esc_html($quality_score) . ' / 10' : '—'; ?></div>
            </div>
            <div class="meta-badge">
                <div class="meta-badge-label">Confidenza</div>
                <div class="meta-badge-value"><?php echo $data_quality !== '' ? esc_html($data_quality) : '—'; ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Accordion Sections -->
<div class="analysis-accordion">

    <!-- ACCORDION 1: BRAND & POSITIONING -->
    <?php if (!empty($messaggi_principali) || $tono_di_voce !== '' || !empty($elementi_differenzianti) || !empty($target_commerciali)) : ?>
    <div class="accordion-item">
        <button class="accordion-header" onclick="toggleAccordion(this)">
            <span class="accordion-icon">▶</span>
            <span class="accordion-title">Brand e Posizionamento</span>
            <div class="accordion-meta">
                <span class="badge"><?php echo esc_html($numero_messaggi); ?> Messaggi</span>
                <?php if ($coerenza_comunicativa !== '') : ?>
                    <span class="badge"><?php echo esc_html($coerenza_comunicativa); ?> Coerenza</span>
                <?php endif; ?>
            </div>
        </button>

        <div class="accordion-body">
            <div class="accordion-content">
                <div class="grid-2col">
                    <?php if (!empty($messaggi_principali)) : ?>
                        <div class="brand-section">
                            <h4>Messaggi Principali</h4>
                            <ol class="numbered-list">
                                <?php foreach ($messaggi_principali as $msg) : ?>
                                    <li><?php echo esc_html($msg); ?></li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                    <?php endif; ?>

                    <?php if ($tono_di_voce !== '') : ?>
                        <div class="brand-section">
                            <h4>Tono di Voce</h4>
                            <p class="tone-text"><?php echo esc_html($tono_di_voce); ?></p>

                            <?php if ($coerenza_comunicativa !== '') : ?>
                                <div style="margin-top: var(--spacing-md);">
                                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--color-text-tertiary); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.7px;">Coerenza Comunicativa</label>
                                    <p style="font-size: 14px; font-weight: 700; color: var(--color-primary);"><?php echo esc_html($coerenza_comunicativa); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($elementi_differenzianti)) : ?>
                        <div class="brand-section">
                            <h4>Elementi Differenzianti</h4>
                            <ul class="bullet-list">
                                <?php foreach ($elementi_differenzianti as $elem) : ?>
                                    <li><?php echo esc_html($elem); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($target_commerciali)) : ?>
                        <div class="brand-section">
                            <h4>Target Commerciali</h4>
                            <ul class="bullet-list">
                                <?php foreach ($target_commerciali as $target) : ?>
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
    <?php if ($promessa_di_valore !== '' || !empty($domande_prospect) || !empty($idee_di_valore)) : ?>
    <div class="accordion-item">
        <button class="accordion-header" onclick="toggleAccordion(this)">
            <span class="accordion-icon">▶</span>
            <span class="accordion-title">Analisi Commerciale</span>
            <div class="accordion-meta">
                <span class="badge"><?php echo esc_html($numero_idee); ?> Idee</span>
                <span class="badge"><?php echo esc_html($numero_domande); ?> Domande</span>
            </div>
        </button>

        <div class="accordion-body">
            <div class="accordion-content">
                <?php if ($promessa_di_valore !== '') : ?>
                    <div class="value-promise">
                        <h4>Promessa di Valore</h4>
                        <blockquote><?php echo esc_html($promessa_di_valore); ?></blockquote>
                    </div>
                <?php endif; ?>

                <div class="grid-2col">
                    <?php if (!empty($domande_prospect)) : ?>
                        <div class="commercial-section">
                            <h4>Domande Prospect Chiave</h4>
                            <ul class="bullet-list">
                                <?php foreach ($domande_prospect as $domanda) : ?>
                                    <li><?php echo esc_html($domanda); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($idee_di_valore)) : ?>
                        <div class="commercial-section">
                            <h4>Idee di Valore Perspect</h4>
                            <ol class="numbered-list">
                                <?php foreach ($idee_di_valore as $idea) : ?>
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
    <?php if (!empty($strengths) || !empty($weaknesses) || !empty($opportunities) || !empty($quick_wins)) : ?>
    <div class="accordion-item">
        <button class="accordion-header" onclick="toggleAccordion(this)">
            <span class="accordion-icon">▶</span>
            <span class="accordion-title">Punti di Forza e Debolezza</span>
            <div class="accordion-meta">
                <span class="badge"><?php echo esc_html($numero_forza); ?> Punti Forza</span>
                <span class="badge"><?php echo esc_html($numero_debolezza); ?> Debolezze</span>
            </div>
        </button>

        <div class="accordion-body">
            <div class="accordion-content">
                <div class="grid-2x2">
                    <?php if (!empty($strengths)) : ?>
                        <div class="swot-card">
                            <h4>Punti di Forza</h4>
                            <ul class="bullet-list">
                                <?php foreach ($strengths as $strength) : ?>
                                    <li><?php echo esc_html($strength); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($weaknesses)) : ?>
                        <div class="swot-card">
                            <h4>Punti di Debolezza</h4>
                            <ul class="bullet-list">
                                <?php foreach ($weaknesses as $weakness) : ?>
                                    <li><?php echo esc_html($weakness); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($opportunities)) : ?>
                        <div class="swot-card">
                            <h4>Opportunità</h4>
                            <ul class="bullet-list">
                                <?php foreach ($opportunities as $opportunity) : ?>
                                    <li><?php echo esc_html($opportunity); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($quick_wins)) : ?>
                        <div class="swot-card">
                            <h4>Azioni Rapide</h4>
                            <ol class="numbered-list">
                                <?php foreach ($quick_wins as $action) : ?>
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
    <?php if (!empty($rischi)) : ?>
    <div class="accordion-item">
        <button class="accordion-header" onclick="toggleAccordion(this)">
            <span class="accordion-icon">▶</span>
            <span class="accordion-title">Rischi e Mitigazione</span>
            <div class="accordion-meta">
                <span class="badge"><?php echo esc_html($numero_rischi); ?> Rischi</span>
            </div>
        </button>

        <div class="accordion-body">
            <div class="accordion-content">
                <ul class="bullet-list">
                    <?php foreach ($rischi as $rischio) : ?>
                        <li><?php echo esc_html($rischio); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ACCORDION 5: DEEP RESEARCH -->
    <?php if ($deep_research !== '' || $review !== '') : ?>
    <div class="accordion-item">
        <button class="accordion-header" onclick="toggleAccordion(this)">
            <span class="accordion-icon">▶</span>
            <span class="accordion-title">Analisi Approfondita</span>
        </button>

        <div class="accordion-body">
            <div class="accordion-content">
                <?php if ($deep_research !== '') : ?>
                    <div style="margin-bottom: var(--spacing-lg);">
                        <h4>Analisi Iniziale</h4>
                        <div style="margin-top: var(--spacing-md);">
                            <?php echo wp_kses_post(wpautop($deep_research)); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($review !== '') : ?>
                    <div>
                        <h4>Revisione Analisi</h4>
                        <div style="margin-top: var(--spacing-md);">
                            <?php echo wp_kses_post(wpautop($review)); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ACCORDION 6: PRIORITA TEMPORALI -->
    <?php if ($priorita_temporali !== '') : ?>
    <div class="accordion-item">
        <button class="accordion-header" onclick="toggleAccordion(this)">
            <span class="accordion-icon">▶</span>
            <span class="accordion-title">Priorità Temporali</span>
        </button>

        <div class="accordion-body">
            <div class="accordion-content">
                <?php echo wp_kses_post(wpautop($priorita_temporali)); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
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
