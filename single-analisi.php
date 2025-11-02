<?php
get_header();

$helpers_path = get_theme_file_path('inc/company-analysis-helpers.php');
if (file_exists($helpers_path)) {
    require_once $helpers_path;
}

$placeholder_text = __('Dato non disponibile.', 'psip');
$placeholder_html = wp_kses_post(wpautop($placeholder_text));

$json_maybe_decode = static function ($value) {
    if (is_string($value)) {
        $trimmed = trim($value);
        $first_char = $trimmed !== '' ? $trimmed[0] : '';
        $last_char = $trimmed !== '' ? substr($trimmed, -1) : '';
        if ($trimmed !== '' && (
            ($first_char === '[' && $last_char === ']') ||
            ($first_char === '{' && $last_char === '}')
        )) {
            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        return $trimmed;
    }
    return $value;
};

$listify_value = static function ($value) use (&$listify_value, $json_maybe_decode) {
    $value = $json_maybe_decode($value);
    $items = [];

    if (is_array($value)) {
        if (function_exists('psip_theme_is_assoc_array') && psip_theme_is_assoc_array($value)) {
            // Specific structures (es. rischi, priorità temporali)
            if (isset($value['rischio']) || isset($value['mitigazione'])) {
                $risk = trim((string)($value['rischio'] ?? ''));
                $mitigation = trim((string)($value['mitigazione'] ?? ''));
                $parts = [];
                if ($risk !== '') {
                    $parts[] = $risk;
                }
                if ($mitigation !== '') {
                    $parts[] = sprintf(__('Mitigazione: %s', 'psip'), $mitigation);
                }
                if ($parts) {
                    $items[] = implode(' — ', $parts);
                }
            } else {
                foreach ($value as $key => $sub_value) {
                    $sub_items = $listify_value($sub_value);
                    if (empty($sub_items)) {
                        continue;
                    }
                    $label = function_exists('psip_theme_pretty_label')
                        ? psip_theme_pretty_label($key)
                        : ucfirst(str_replace('_', ' ', (string) $key));

                    if (count($sub_items) === 1) {
                        $items[] = $label . ': ' . $sub_items[0];
                    } else {
                        $items[] = $label . ': ' . implode(', ', $sub_items);
                    }
                }
            }
        } else {
            foreach ($value as $entry) {
                $entry_items = $listify_value($entry);
                if (!empty($entry_items)) {
                    foreach ($entry_items as $item) {
                        if ($item !== '') {
                            $items[] = $item;
                        }
                    }
                }
            }
        }
    } else {
        $value = trim((string) $value);
        if ($value === '') {
            return [];
        }
        if (strpos($value, "\n") !== false) {
            foreach (preg_split('/\r\n|\r|\n/', $value) as $line) {
                $line = trim($line, " \t\n\r\0\x0B•-");
                if ($line !== '') {
                    $items[] = $line;
                }
            }
        } else {
            $items[] = $value;
        }
    }

    $unique = [];
    foreach ($items as $item) {
        $item = trim($item);
        if ($item === '') {
            continue;
        }
        if (!in_array($item, $unique, true)) {
            $unique[] = $item;
        }
    }

    return $unique;
};

$render_list_html = static function ($value) use ($listify_value, $placeholder_html) {
    $items = $listify_value($value);
    if (empty($items)) {
        return $placeholder_html;
    }

    $html = '<ul class="analysis-suite__list">';
    foreach ($items as $item) {
        $html .= '<li>' . esc_html($item) . '</li>';
    }
    $html .= '</ul>';

    return $html;
};

$parent_company_id = get_field('parent_company_id');
$parent_company_title = '';
$parent_company_url = '';
if ($parent_company_id) {
    $parent_company_title = get_the_title($parent_company_id);
    $parent_company_url = get_permalink($parent_company_id);
}

$summary = psip_theme_normalize_scalar(get_field('riassunto'));
$summary_html = $summary !== '' ? wp_kses_post(wpautop($summary)) : '';

$deep_research_raw = get_field('analisy_perplexity_deep_research');
$deep_research_html = psip_theme_format_markdown_bold($deep_research_raw);

$analysis_review_raw = get_field('revisione_analisi_completa');
$analysis_review_html = psip_theme_format_markdown_bold($analysis_review_raw);

$punti_di_forza_raw = get_field('punti_di_forza', false, false);
$punti_di_debolezza_raw = get_field('punti_di_debolezza', false, false);
$opportunita_raw = get_field('opportunita', false, false);
$azioni_rapide_raw = get_field('azioni_rapide', false, false);

$count_strengths = get_field('numero_punti_di_forza');
$count_weaknesses = get_field('numero_punti_di_debolezza');
$count_opportunities = get_field('numero_opportunita');
$count_quick_actions = get_field('numero_azioni_rapide');

$format_count = static function ($value) {
    return ($value !== null && $value !== '' && is_numeric($value)) ? (string) (int) $value : '—';
};

$count_strengths_display = $format_count($count_strengths);
$count_weaknesses_display = $format_count($count_weaknesses);
$count_opportunities_display = $format_count($count_opportunities);
$count_quick_actions_display = $format_count($count_quick_actions);

$strengths_output = $render_list_html($punti_di_forza_raw);
$weaknesses_output = $render_list_html($punti_di_debolezza_raw);
$opportunities_output = $render_list_html($opportunita_raw);
$quick_actions_output = $render_list_html($azioni_rapide_raw);

$quality_score_raw = get_field('voto_qualita_analisi');
$quality_score_normalized = psip_theme_normalize_scalar($quality_score_raw);
$quality_score_display = ($quality_score_normalized !== '') ? $quality_score_normalized : '—';

$analysis_last_status_code = psip_theme_normalize_scalar(get_field('analysis_last_status_code'));
$analysis_last_message = psip_theme_normalize_scalar(get_field('analysis_last_message'));
$analysis_last_at_raw = psip_theme_normalize_scalar(get_field('analysis_last_at'));

$analysis_last_at_display = '—';
if ($analysis_last_at_raw !== '') {
    $analysis_last_timestamp = strtotime($analysis_last_at_raw);
    if ($analysis_last_timestamp) {
        $analysis_last_at_display = date_i18n('d/m/Y H:i', $analysis_last_timestamp);
    } else {
        $analysis_last_at_display = $analysis_last_at_raw;
    }
}

$analysis_updated_label = $analysis_last_at_display !== '—'
    ? sprintf(__('Ultima esecuzione %s', 'psip'), $analysis_last_at_display)
    : null;
if ($analysis_updated_label === null) {
    $analysis_updated_fallback = get_the_modified_date(get_option('date_format'));
    if ($analysis_updated_fallback) {
        $analysis_updated_label = sprintf(__('Aggiornato il %s', 'psip'), $analysis_updated_fallback);
    }
}

$qualita_dati = psip_theme_normalize_scalar(get_field('qualita_dati'));

$messaggi_principali_raw = get_field('messaggi_principali', false, false);
$count_messaggi_principali = get_field('numero_messaggi_principali');
$count_messaggi_principali_display = $format_count($count_messaggi_principali);
$messaggi_principali_output = $render_list_html($messaggi_principali_raw);

$promessa_di_valore = psip_theme_normalize_scalar(get_field('promessa_di_valore'));
$tono_di_voce = psip_theme_normalize_scalar(get_field('tono_di_voce'));
$coerenza_comunicativa = psip_theme_normalize_scalar(get_field('coerenza_comunicativa'));

$promessa_di_valore_output = $promessa_di_valore !== '' ? wpautop(esc_html($promessa_di_valore)) : $placeholder_html;
$tono_di_voce_output = $tono_di_voce !== '' ? wpautop(esc_html($tono_di_voce)) : $placeholder_html;
$coerenza_comunicativa_output = $coerenza_comunicativa !== '' ? wpautop(esc_html($coerenza_comunicativa)) : $placeholder_html;

$elementi_differenzianti_raw = get_field('elementi_differenzianti', false, false);
$count_elementi_differenzianti = get_field('numero_elementi_differenzianti');
$count_elementi_differenzianti_display = $format_count($count_elementi_differenzianti);
$elementi_differenzianti_output = $render_list_html($elementi_differenzianti_raw);

$target_commerciali_raw = get_field('target_commerciali', false, false);
$count_target_commerciali = get_field('numero_target_commerciali');
$count_target_commerciali_display = $format_count($count_target_commerciali);
$target_commerciali_output = $render_list_html($target_commerciali_raw);

$idee_di_valore_raw = get_field('idee_di_valore_perspect', false, false);
$count_idee_di_valore = get_field('numero_idee_di_valore');
$count_idee_di_valore_display = $format_count($count_idee_di_valore);
$idee_di_valore_output = $render_list_html($idee_di_valore_raw);

$domande_prospect_raw = get_field('domande_prospect', false, false);
$count_domande = get_field('numero_domande');
$count_domande_display = $format_count($count_domande);
$domande_prospect_output = $render_list_html($domande_prospect_raw);

$rischi_raw = get_field('rischi', false, false);
$count_rischi = get_field('numero_rischi');
$count_rischi_display = $format_count($count_rischi);
$rischi_output = $render_list_html($rischi_raw);

$priorita_temporali_raw = get_field('priorita_temporali', false, false);
$priorita_temporali_output = $render_list_html($priorita_temporali_raw);

$summary_output = $summary_html !== '' ? $summary_html : $placeholder_html;
$deep_research_output = $deep_research_html !== '' ? $deep_research_html : $placeholder_html;
$analysis_review_output = $analysis_review_html !== '' ? $analysis_review_html : $placeholder_html;

$analysis_last_message_output = $analysis_last_message !== '' ? wpautop(esc_html($analysis_last_message)) : $placeholder_html;
$qualita_dati_output = $qualita_dati !== '' ? $qualita_dati : '—';

$analysis_meta = [
    [
        'label' => __('Ultima esecuzione', 'psip'),
        'value' => $analysis_last_at_display,
        'is_html' => false,
    ],
    [
        'label' => __('Status code', 'psip'),
        'value' => $analysis_last_status_code !== '' ? $analysis_last_status_code : '—',
        'is_html' => false,
    ],
    [
        'label' => __('Messaggio ultimo run', 'psip'),
        'value' => $analysis_last_message_output,
        'is_html' => true,
    ],
    [
        'label' => __('Qualità dati', 'psip'),
        'value' => $qualita_dati_output,
        'is_html' => false,
    ],
];

$list_cards = [
    [
        'title' => __('Punti di forza', 'psip'),
        'html' => $strengths_output,
        'badge' => $count_strengths_display,
    ],
    [
        'title' => __('Punti di debolezza', 'psip'),
        'html' => $weaknesses_output,
        'badge' => $count_weaknesses_display,
    ],
    [
        'title' => __('Opportunità', 'psip'),
        'html' => $opportunities_output,
        'badge' => $count_opportunities_display,
    ],
    [
        'title' => __('Azioni rapide', 'psip'),
        'html' => $quick_actions_output,
        'badge' => $count_quick_actions_display,
    ],
    [
        'title' => __('Messaggi principali', 'psip'),
        'html' => $messaggi_principali_output,
        'badge' => $count_messaggi_principali_display,
    ],
    [
        'title' => __('Elementi differenzianti', 'psip'),
        'html' => $elementi_differenzianti_output,
        'badge' => $count_elementi_differenzianti_display,
    ],
    [
        'title' => __('Target commerciali', 'psip'),
        'html' => $target_commerciali_output,
        'badge' => $count_target_commerciali_display,
    ],
    [
        'title' => __('Idee di valore', 'psip'),
        'html' => $idee_di_valore_output,
        'badge' => $count_idee_di_valore_display,
    ],
    [
        'title' => __('Domande prospect', 'psip'),
        'html' => $domande_prospect_output,
        'badge' => $count_domande_display,
    ],
    [
        'title' => __('Rischi', 'psip'),
        'html' => $rischi_output,
        'badge' => $count_rischi_display,
    ],
    [
        'title' => __('Priorità temporali', 'psip'),
        'html' => $priorita_temporali_output,
        'badge' => null,
    ],
];

$parent_company_display = '—';
if ($parent_company_title !== '') {
    $parent_company_display = $parent_company_url
        ? sprintf('<a href="%s">%s</a>', esc_url($parent_company_url), esc_html($parent_company_title))
        : esc_html($parent_company_title);
}

$acf_detail_rows = [
    [
        'label' => __('Azienda collegata', 'psip'),
        'value' => $parent_company_display,
        'is_html' => true,
    ],
    [
        'label' => __('Riassunto', 'psip'),
        'value' => $summary_output,
        'is_html' => true,
    ],
    [
        'label' => __('Analisi iniziale', 'psip'),
        'value' => $deep_research_output,
        'is_html' => true,
    ],
    [
        'label' => __('Revisione analisi', 'psip'),
        'value' => $analysis_review_output,
        'is_html' => true,
    ],
    [
        'label' => __('Punti di forza', 'psip'),
        'value' => $strengths_output,
        'is_html' => true,
    ],
    [
        'label' => __('Numero punti di forza', 'psip'),
        'value' => $count_strengths_display,
        'is_html' => false,
    ],
    [
        'label' => __('Punti di debolezza', 'psip'),
        'value' => $weaknesses_output,
        'is_html' => true,
    ],
    [
        'label' => __('Numero punti di debolezza', 'psip'),
        'value' => $count_weaknesses_display,
        'is_html' => false,
    ],
    [
        'label' => __('Opportunità', 'psip'),
        'value' => $opportunities_output,
        'is_html' => true,
    ],
    [
        'label' => __('Numero opportunità', 'psip'),
        'value' => $count_opportunities_display,
        'is_html' => false,
    ],
    [
        'label' => __('Azioni rapide', 'psip'),
        'value' => $quick_actions_output,
        'is_html' => true,
    ],
    [
        'label' => __('Numero azioni rapide', 'psip'),
        'value' => $count_quick_actions_display,
        'is_html' => false,
    ],
    [
        'label' => __('Voto qualità analisi', 'psip'),
        'value' => $quality_score_display,
        'is_html' => false,
    ],
    [
        'label' => __('Status code', 'psip'),
        'value' => $analysis_last_status_code !== '' ? $analysis_last_status_code : '—',
        'is_html' => false,
    ],
    [
        'label' => __('Messaggio ultimo run', 'psip'),
        'value' => $analysis_last_message_output,
        'is_html' => true,
    ],
    [
        'label' => __('Data ultima analisi', 'psip'),
        'value' => $analysis_last_at_display,
        'is_html' => false,
    ],
    [
        'label' => __('Qualità dati', 'psip'),
        'value' => $qualita_dati_output,
        'is_html' => false,
    ],
    [
        'label' => __('Messaggi principali', 'psip'),
        'value' => $messaggi_principali_output,
        'is_html' => true,
    ],
    [
        'label' => __('Numero messaggi principali', 'psip'),
        'value' => $count_messaggi_principali_display,
        'is_html' => false,
    ],
    [
        'label' => __('Promessa di valore', 'psip'),
        'value' => $promessa_di_valore_output,
        'is_html' => true,
    ],
    [
        'label' => __('Tono di voce', 'psip'),
        'value' => $tono_di_voce_output,
        'is_html' => true,
    ],
    [
        'label' => __('Elementi differenzianti', 'psip'),
        'value' => $elementi_differenzianti_output,
        'is_html' => true,
    ],
    [
        'label' => __('Numero elementi differenzianti', 'psip'),
        'value' => $count_elementi_differenzianti_display,
        'is_html' => false,
    ],
    [
        'label' => __('Coerenza comunicativa', 'psip'),
        'value' => $coerenza_comunicativa_output,
        'is_html' => true,
    ],
    [
        'label' => __('Target commerciali', 'psip'),
        'value' => $target_commerciali_output,
        'is_html' => true,
    ],
    [
        'label' => __('Numero target commerciali', 'psip'),
        'value' => $count_target_commerciali_display,
        'is_html' => false,
    ],
    [
        'label' => __('Idee di valore', 'psip'),
        'value' => $idee_di_valore_output,
        'is_html' => true,
    ],
    [
        'label' => __('Numero idee di valore', 'psip'),
        'value' => $count_idee_di_valore_display,
        'is_html' => false,
    ],
    [
        'label' => __('Domande prospect', 'psip'),
        'value' => $domande_prospect_output,
        'is_html' => true,
    ],
    [
        'label' => __('Numero domande', 'psip'),
        'value' => $count_domande_display,
        'is_html' => false,
    ],
    [
        'label' => __('Rischi', 'psip'),
        'value' => $rischi_output,
        'is_html' => true,
    ],
    [
        'label' => __('Numero rischi', 'psip'),
        'value' => $count_rischi_display,
        'is_html' => false,
    ],
    [
        'label' => __('Priorità temporali', 'psip'),
        'value' => $priorita_temporali_output,
        'is_html' => true,
    ],
];

$kpi_cards = [
    [
        'label' => __('Quality score', 'psip'),
        'value' => $quality_score_display,
        'hint' => $analysis_updated_label,
        'modifier' => 'is-accent',
    ],
    [
        'label' => __('Punti di forza', 'psip'),
        'value' => $count_strengths_display,
        'hint' => null,
        'modifier' => '',
    ],
    [
        'label' => __('Punti di debolezza', 'psip'),
        'value' => $count_weaknesses_display,
        'hint' => null,
        'modifier' => '',
    ],
    [
        'label' => __('Opportunità', 'psip'),
        'value' => $count_opportunities_display,
        'hint' => null,
        'modifier' => '',
    ],
    [
        'label' => __('Azioni rapide', 'psip'),
        'value' => $count_quick_actions_display,
        'hint' => null,
        'modifier' => '',
    ],
];
?>

<main id="single_company" role="main">

    <section id="company_hero">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-12 col-lg-7">
                    <h1 class="company-title"><?php the_title(); ?></h1>
                </div>
            </div>
        </div>
    </section><!--company_hero-->

    <section id="company_content">
        <div class="container-fluid">
            <div class="analysis-suite__panel analysis-suite__panel--single">
                <header class="analysis-suite__panel-header">
                    <div class="analysis-suite__panel-heading">
                        <span class="analysis-suite__panel-tag"><?php esc_html_e('Analisi verticale', 'psip'); ?></span>
                        <h3 class="analysis-suite__panel-title"><?php the_title(); ?></h3>
                        <?php if ($parent_company_title !== ''): ?>
                            <p class="analysis-suite__panel-subtitle">
                                <?php esc_html_e('Azienda:', 'psip'); ?>
                                <?php if ($parent_company_url): ?>
                                    <a href="<?php echo esc_url($parent_company_url); ?>">
                                        <?php echo esc_html($parent_company_title); ?>
                                    </a>
                                <?php else: ?>
                                    <?php echo esc_html($parent_company_title); ?>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <?php if ($analysis_updated_label || $analysis_last_status_code !== ''): ?>
                        <div class="analysis-suite__panel-meta">
                            <?php if ($analysis_updated_label): ?>
                                <span class="analysis-suite__status-chip is-completed">
                                    <?php echo esc_html($analysis_updated_label); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($analysis_last_status_code !== ''): ?>
                                <span class="analysis-suite__status-chip is-neutral">
                                    <?php printf(__('Status %s', 'psip'), esc_html($analysis_last_status_code)); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </header>

                <div class="analysis-suite__kpi-section">
                    <div class="analysis-suite__kpi-grid">
                        <?php foreach ($kpi_cards as $card): ?>
                            <div class="analysis-suite__kpi-card <?php echo esc_attr($card['modifier']); ?>">
                                <span class="analysis-suite__kpi-label"><?php echo esc_html($card['label']); ?></span>
                                <span class="analysis-suite__kpi-value"><?php echo esc_html((string) $card['value']); ?></span>
                                <?php if (!empty($card['hint'])): ?>
                                    <span class="analysis-suite__kpi-hint"><?php echo esc_html($card['hint']); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>

                <div class="analysis-suite__panel-body">
                    <div class="analysis-suite__column analysis-suite__column--narrative">
                        <div class="analysis-suite__card">
                            <h4 class="analysis-suite__card-title"><?php esc_html_e('Riassunto', 'psip'); ?></h4>
                            <div class="analysis-suite__card-content">
                                <?php echo wp_kses_post($summary_output); ?>
                            </div>
                        </div>
                        <div class="analysis-suite__card">
                            <h4 class="analysis-suite__card-title"><?php esc_html_e('Analisi approfondita', 'psip'); ?></h4>
                            <div class="analysis-suite__card-content">
                                <?php echo wp_kses_post($deep_research_output); ?>
                            </div>
                        </div>
                        <div class="analysis-suite__card">
                            <h4 class="analysis-suite__card-title"><?php esc_html_e("Revisione dell'analisi", 'psip'); ?></h4>
                            <div class="analysis-suite__card-content">
                                <?php echo wp_kses_post($analysis_review_output); ?>
                            </div>
                        </div>
                        <div class="analysis-suite__card">
                            <h4 class="analysis-suite__card-title"><?php esc_html_e('Promessa di valore', 'psip'); ?></h4>
                            <div class="analysis-suite__card-content">
                                <?php echo wp_kses_post($promessa_di_valore_output); ?>
                            </div>
                        </div>
                        <div class="analysis-suite__card">
                            <h4 class="analysis-suite__card-title"><?php esc_html_e('Tono di voce', 'psip'); ?></h4>
                            <div class="analysis-suite__card-content">
                                <?php echo wp_kses_post($tono_di_voce_output); ?>
                            </div>
                        </div>
                        <div class="analysis-suite__card">
                            <h4 class="analysis-suite__card-title"><?php esc_html_e('Coerenza comunicativa', 'psip'); ?></h4>
                            <div class="analysis-suite__card-content">
                                <?php echo wp_kses_post($coerenza_comunicativa_output); ?>
                            </div>
                        </div>
                    </div>
                    <div class="analysis-suite__column analysis-suite__column--stack">
                        <div class="analysis-suite__card">
                            <div class="analysis-suite__card-header">
                                <h4 class="analysis-suite__card-title"><?php esc_html_e('Stato analisi', 'psip'); ?></h4>
                            </div>
                            <div class="analysis-suite__card-content">
                                <dl class="analysis-suite__meta-list">
                                    <?php foreach ($analysis_meta as $meta_item): ?>
                                        <dt><?php echo esc_html($meta_item['label']); ?></dt>
                                        <dd>
                                            <?php
                                            if (!empty($meta_item['is_html'])) {
                                                echo wp_kses_post($meta_item['value']);
                                            } else {
                                                echo esc_html((string) $meta_item['value']);
                                            }
                                            ?>
                                        </dd>
                                    <?php endforeach; ?>
                                </dl>
                            </div>
                        </div>
                        <?php foreach ($list_cards as $card): ?>
                            <div class="analysis-suite__card analysis-suite__card--list">
                                <div class="analysis-suite__card-header">
                                    <h4 class="analysis-suite__card-title"><?php echo esc_html($card['title']); ?></h4>
                                    <?php if ($card['badge'] !== null): ?>
                                        <span class="analysis-suite__card-badge"><?php echo esc_html($card['badge']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="analysis-suite__card-content">
                                    <?php echo wp_kses_post($card['html']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="analysis-suite__card analysis-suite__card--details">
                    <div class="analysis-suite__card-header">
                        <h4 class="analysis-suite__card-title"><?php esc_html_e('Dettaglio campi ACF', 'psip'); ?></h4>
                    </div>
                    <div class="analysis-suite__card-content">
                        <table class="analysis-suite__detail-table">
                            <tbody>
                                <?php foreach ($acf_detail_rows as $row): ?>
                                    <tr>
                                        <th scope="row"><?php echo esc_html($row['label']); ?></th>
                                        <td>
                                            <?php
                                            if (!empty($row['is_html'])) {
                                                echo wp_kses_post($row['value']);
                                            } else {
                                                echo esc_html((string) $row['value']);
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
