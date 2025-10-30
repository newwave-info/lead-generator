<?php
if (!function_exists('psip_theme_normalize_scalar')) {
    /**
     * Converte valori ACF (array, oggetti, bool) in stringhe pronte per il template.
     */
    function psip_theme_normalize_scalar($value) {
        if (is_array($value)) {
            if (isset($value['label']) && is_scalar($value['label'])) {
                return trim((string) $value['label']);
            }
            if (isset($value['value']) && is_scalar($value['value'])) {
                return trim((string) $value['value']);
            }

            $flattened = [];
            foreach ($value as $item) {
                if (is_scalar($item)) {
                    $flattened[] = trim((string) $item);
                } elseif (is_array($item)) {
                    if (isset($item['label']) && is_scalar($item['label'])) {
                        $flattened[] = trim((string) $item['label']);
                    } elseif (isset($item['value']) && is_scalar($item['value'])) {
                        $flattened[] = trim((string) $item['value']);
                    }
                }
            }

            return implode(', ', array_filter($flattened, static function ($entry) {
                return $entry !== '';
            }));
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_object($value)) {
            if ($value instanceof WP_Post) {
                return $value->post_title;
            }

            if ($value instanceof WP_Term) {
                return $value->name;
            }

            if ($value instanceof DateTimeInterface) {
                return $value->format('Y-m-d H:i:s');
            }

            if (method_exists($value, '__toString')) {
                return trim((string) $value);
            }

            return '';
        }

        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }
}

//custom theme option
include_once(get_template_directory() .'/newwave/theme/front-end.php');
include_once(get_template_directory() .'/newwave/theme/back-end.php');
include_once(get_template_directory() .'/newwave/theme/no-comments.php');


//custom post type & tax
//include_once(get_template_directory().'/newwave/custom/taxonomies.php');
//include_once(get_template_directory().'/newwave/custom/posts.php');


//custom bootstrap navwalker
//require_once(get_template_directory().'/newwave/class-wp-bootstrap-navwalker.php');

//custom images size
//include_once(get_template_directory().'/newwave/theme/images.php');


// Protegge tutto il front-end: i non loggati vengono reindirizzati al login.
// Esclude: wp-login.php, wp-register.php, wp-admin, admin-ajax, REST API, cron, risorse statiche, sitemap, feed.
add_action('template_redirect', function () {
    if ( is_user_logged_in() ) {
        return;
    }

    // Bypass se richiesta è login/registrazione/attivazione
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    $is_login_page = ( false !== stripos($request_uri, 'wp-login.php') ) || ( false !== stripos($request_uri, 'wp-register.php') );

    // Bypass backend e ajax
    if ( is_admin() || $is_login_page || ( defined('DOING_AJAX') && DOING_AJAX ) ) {
        return;
    }

    // Bypass REST API e cron
    if ( defined('REST_REQUEST') && REST_REQUEST ) {
        return;
    }
    if ( defined('DOING_CRON') && DOING_CRON ) {
        return;
    }

    // Bypass per risorse e endpoint pubblici comuni
    $public_paths = [
        '/wp-json/',      // REST base
        '/wp-admin/',     // lascia che wp-admin gestisca i redirect
        '/sitemap',       // sitemap vari
        '/feed',          // feed
        '.xml', '.xsl',   // sitemap formati
        '.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.webp', '.svg', '.ico', '.txt',
    ];
    foreach ( $public_paths as $path ) {
        if ( stripos($request_uri, $path) !== false ) {
            return;
        }
    }

    // Reindirizza a wp-login con redirect_to = URL richiesto
    $login_url = wp_login_url( home_url( $request_uri ) );
    wp_safe_redirect( $login_url, 302 );
    exit;
});










// === Perspect PSIP REST API Routing ===

// 1. ENDPOINT DI TEST
add_action('rest_api_init', function() {
    register_rest_route('perspect/v1', '/test', array(
        'methods' => 'GET',
        'callback' => function() {
            return array(
                'status' => 'ok',
                'timestamp' => current_time('mysql'),
            );
        },
        'permission_callback' => '__return_true',
    ));
});

// 2. ENDPOINT UPSERT AZIENDA
add_action('rest_api_init', function() {
    register_rest_route('perspect/v1', '/companies/upsert', array(
        'methods' => 'POST',
        'callback' => 'perspect_upsert_company',
        'permission_callback' => '__return_true',
        'args' => array(
            'discovery_id' => array(
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'company_name' => array(
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'domain' => array(
                'required' => false,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
        )
    ));
});

// 3. CALLBACK UPSERT
function perspect_upsert_company($request) {
    $params = $request->get_json_params();

    if (empty($params['discovery_id']) || empty($params['company_name'])) {
        return new WP_Error('missing_params', 'Parametro discovery_id o company_name mancante', array('status' => 400));
    }

    $discovery_id = sanitize_text_field($params['discovery_id']);
    $company_name = sanitize_text_field($params['company_name']);
    $domain = sanitize_text_field($params['domain'] ?? '');

    $meta_query = array(
        'relation' => 'OR',
        array(
            'key' => 'discovery_id',
            'value' => $discovery_id,
            'compare' => '='
        )
    );
    if ($domain) {
        $meta_query[] = array(
            'key' => 'domain',
            'value' => $domain,
            'compare' => '='
        );
    }
    
    $args = array(
        'post_type'      => 'azienda',
        'posts_per_page' => 1,
        'post_status'    => array('publish','private','pending','future','inherit'),
        'meta_query'     => $meta_query,
    );

    $existing_query = new WP_Query($args);

    error_log('PSIP: Posts trovati per discovery_id ' . $discovery_id . ': ' . $existing_query->found_posts);
    foreach ($existing_query->posts as $post) {
        error_log('PSIP: Found post ID: ' . $post->ID . ' - status: ' . $post->post_status);
    }

    if ($existing_query->have_posts()) {
        $existing_post = $existing_query->posts[0];
        $post_id = $existing_post->ID;

        $existing_confidence = (int)get_post_meta($post_id, 'confidence', true);
        $new_confidence = isset($params['confidence']) ? (int)$params['confidence'] : 0;

        if ($new_confidence >= $existing_confidence) {
            wp_update_post(array(
                'ID'           => $post_id,
                'post_title'   => $company_name,
                'post_modified'=> current_time('mysql')
            ));
            foreach ($params as $key => $value) {
                if (!in_array($key, ['post_title', 'post_status', 'ID'])) {
                    update_post_meta($post_id, $key, $value);
                }
            }
            $update_count = (int)get_post_meta($post_id, 'update_count', true);
            update_post_meta($post_id, 'update_count', $update_count + 1);
            update_post_meta($post_id, 'last_update_date', current_time('mysql'));
            $operation = 'updated';
            error_log("PSIP: Updated post ID $post_id");
        } else {
            $operation = 'skipped';
            error_log("PSIP: Skipped post ID $post_id - confidence too low");
        }
    } else {
        $post_id = wp_insert_post(array(
            'post_title'   => $company_name,
            'post_type'    => 'azienda',
            'post_status'  => 'publish',
            'post_date'    => current_time('mysql')
        ));
        
        if (is_wp_error($post_id)) {
            error_log('PSIP: Error creating post - ' . $post_id->get_error_message());
            return $post_id;
        }
        
        foreach ($params as $key => $value) {
            if (!in_array($key, ['post_title', 'post_status'])) {
                add_post_meta($post_id, $key, $value, true);
            }
        }
        add_post_meta($post_id, 'creation_date', current_time('mysql'), true);
        add_post_meta($post_id, 'update_count', 0, true);
        $operation = 'created';
        error_log("PSIP: Created new post ID $post_id for company $company_name");
    }

    wp_reset_postdata();

    return array(
        'success'      => true,
        'operation'    => $operation,
        'post_id'      => $post_id,
        'company_name' => $company_name,
        'discovery_id' => $discovery_id,
        'timestamp'    => current_time('mysql')
    );
}





//ENDPOINT UPSERT ANALISI
add_action('rest_api_init', function() {
    register_rest_field('analisi', 'acf', [
        'get_callback' => function($post) {
            return get_fields($post['id']);
        },
        'update_callback' => function($value, $post) {
            foreach ($value as $field_name => $field_value) {
                update_field($field_name, $field_value, $post->ID);
            }
        }
    ]);
});








/**
 * PSIP - Agent Launcher Buttons
 * Perspect Sales Intelligence Platform - Multi-Agent WordPress System
 * 
 * Aggiunge bottoni nel CPT "company" per lanciare agenti AI on-demand
 * Integrazione diretta con workflow N8N via webhook
 * 
 * @package Perspect PSIP
 * @version 1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// CONFIGURAZIONE
// ============================================================================

// URL base della tua istanza N8N (MODIFICA QUI)
define('PSIP_N8N_BASE_URL', 'https://automation.perspect.it');

if (!function_exists('psip_get_default_agent_map')) {
    /**
     * Configurazione di fallback per agenti registrati via codice.
     *
     * @return array<string,array<string,string>>
     */
    function psip_get_default_agent_map() {
        return [
            'eaa-accessibility' => [
                'name'        => 'EAA Accessibility',
                'description' => 'European Accessibility Act & WCAG 2.1 Level AA compliance audit',
                'webhook'     => '/webhook/eaa-accessibility-agent',
                'icon'        => 'dashicons-universal-access',
                'color'       => '#574ae2',
            ],
        ];
    }
}

add_action('init', 'psip_ensure_default_agents');

/**
 * Registra (se mancanti) gli agenti di default, inclusi metadati ACF.
 */
function psip_ensure_default_agents() {
    if (!taxonomy_exists('agent_type')) {
        return;
    }

    $defaults = psip_get_default_agent_map();

    foreach ($defaults as $slug => $config) {
        $existing = get_term_by('slug', $slug, 'agent_type');
        if ($existing instanceof WP_Term) {
            $term_id = $existing->term_id;
            // Aggiorna descrizione se vuota.
            if (isset($config['description']) && $existing->description === '') {
                wp_update_term($term_id, 'agent_type', [
                    'description' => $config['description'],
                ]);
            }
        } else {
            $insert = wp_insert_term(
                $config['name'] ?? $slug,
                'agent_type',
                [
                    'description' => $config['description'] ?? '',
                    'slug'        => $slug,
                ]
            );

            if (is_wp_error($insert) || empty($insert['term_id'])) {
                continue;
            }

            $term_id = (int) $insert['term_id'];
        }

        if (empty($term_id)) {
            continue;
        }

        $meta_updates = [
            'webhook' => $config['webhook'] ?? '',
            'icon'    => $config['icon'] ?? '',
            'color'   => $config['color'] ?? '',
        ];

        foreach ($meta_updates as $meta_key => $value) {
            if ($value === '') {
                continue;
            }

            $current = get_term_meta($term_id, $meta_key, true);
            if ($current === '' || $current === null) {
                update_term_meta($term_id, $meta_key, $value);
            }
        }
    }
}

/**
 * Recupera dinamicamente gli agenti dalla tassonomia agent_type
 */
function psip_get_agents() {
    static $agents = null;

    if ($agents !== null) {
        return $agents;
    }

    $agents = [];

    $defaults = psip_get_default_agent_map();

    $terms = get_terms([
        'taxonomy'      => 'agent_type',
        'hide_empty'    => false,
        'orderby'       => 'term_order',
        'order'         => 'ASC'
    ]);

    if (empty($terms) || is_wp_error($terms)) {
        return $agents;
    }

    foreach ($terms as $term) {
        $default = $defaults[$term->slug] ?? [];
        $agents[$term->slug] = [
            'name'        => $term->name ?: ($default['name'] ?? $term->slug),
            'webhook'     => get_field('webhook', 'agent_type_' . $term->term_id) ?: ($default['webhook'] ?? ''),
            'icon'        => get_field('icon', 'agent_type_' . $term->term_id) ?: ($default['icon'] ?? 'dashicons-admin-generic'),
            'color'       => get_field('color', 'agent_type_' . $term->term_id) ?: ($default['color'] ?? '#000000'),
            'description' => $term->description ?: ($default['description'] ?? ''),
        ];
    }

    return $agents;
}

if (!function_exists('psip_parse_datetime_value')) {
    /**
     * Converte un valore generico (stringa, timestamp, DateTime) in timestamp Unix.
     *
     * @param mixed $value
     * @return int|null
     */
    function psip_parse_datetime_value($value) {
        if ($value instanceof DateTimeInterface) {
            return $value->getTimestamp();
        }

        if (is_numeric($value)) {
            $numeric = (int) $value;
            if ($numeric > 0) {
                return $numeric;
            }
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return null;
            }

            $timestamp = strtotime($trimmed);
            if ($timestamp !== false) {
                return $timestamp;
            }
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                $timestamp = psip_parse_datetime_value($item);
                if ($timestamp !== null) {
                    return $timestamp;
                }
            }
        }

        return null;
    }
}

if (!function_exists('psip_get_analysis_last_run')) {
    /**
     * Restituisce informazioni normalizzate sull'ultima esecuzione di un'analisi.
     *
     * @param int $analysis_id
     * @return array{timestamp:int|null, display:string, relative:string, source:string}
     */
    function psip_get_analysis_last_run($analysis_id) {
        $candidate_keys = [
            'analysis_date',
            'execution_timestamp',
            'analysis_timestamp',
            'enrichment_timestamp',
            '_analysis_last_run',
        ];

        $timestamp = null;
        $source = '';

        foreach ($candidate_keys as $meta_key) {
            $raw_value = get_post_meta($analysis_id, $meta_key, true);
            if ($raw_value === '' || $raw_value === null) {
                continue;
            }

            $timestamp = psip_parse_datetime_value($raw_value);
            if ($timestamp !== null) {
                $source = $meta_key;
                break;
            }
        }

        if ($timestamp === null) {
            $timestamp = get_post_meta($analysis_id, '_last_run_timestamp', true);
            if ($timestamp) {
                $parsed = psip_parse_datetime_value($timestamp);
                if ($parsed !== null) {
                    $timestamp = $parsed;
                    $source = '_last_run_timestamp';
                } else {
                    $timestamp = null;
                }
            } else {
                $timestamp = null;
            }
        }

        if ($timestamp === null) {
            $modified = get_post_modified_time('U', true, $analysis_id);
            if ($modified) {
                $timestamp = (int) $modified;
                $source = 'post_modified';
            }
        }

        if ($timestamp === null) {
            $created = get_post_time('U', true, $analysis_id);
            if ($created) {
                $timestamp = (int) $created;
                $source = 'post_date';
            }
        }

        $display = $timestamp ? wp_date('d/m/Y H:i', $timestamp) : '';
        $relative = '';
        if ($timestamp) {
            $diff = human_time_diff($timestamp, current_time('timestamp'));
            if (!empty($diff)) {
                $relative = sprintf(__('circa %s fa', 'psip'), $diff);
            }
        }

        return [
            'timestamp' => $timestamp,
            'display'   => $display,
            'relative'  => $relative,
            'source'    => $source,
        ];
    }
}



// ============================================================================
// META BOX: AGENT LAUNCHER
// ============================================================================

/**
 * Registra meta box per lancio agenti
 */
add_action('add_meta_boxes', 'psip_register_agent_launcher_metabox');

function psip_register_agent_launcher_metabox() {
    add_meta_box(
        'psip_agent_launcher',
        'Agent Launcher',
        'psip_agent_launcher_metabox_html',
        'azienda', // CPT slug
        'side',    // Sidebar destra
        'high'     // Alta priorità
    );
}

/**
 * HTML del meta box con bottoni agenti
 */
function psip_agent_launcher_metabox_html($post) {
    $company_id = $post->ID;
    $company_name = get_the_title($company_id);

    // Verifica se ci sono già analisi per questa azienda
    $existing_analyses = psip_get_company_analyses($company_id);

    ?>
    <div id="psip-agent-launcher">
        <style>
            #psip-agent-launcher {
                font-size: 13px;
                color: #1d2327;
            }

            #psip-agent-launcher .psip-agent-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 12px;
                padding: 10px;
                border-radius: 8px;
                background: #f6f7f7;
                margin-bottom: 10px;
            }

            .psip-agent-header__title {
                font-weight: 600;
                font-size: 14px;
                display: block;
                margin-bottom: 2px;
            }

            .psip-agent-header__subtitle {
                color: #697380;
                display: block;
            }

            .psip-agent-header__meta {
                text-align: right;
                font-size: 12px;
                color: #697380;
            }

            .psip-agent-header__badge {
                display: inline-block;
                background: #2271b1;
                color: #fff;
                padding: 3px 8px;
                border-radius: 999px;
                font-weight: 600;
                font-size: 11px;
                margin-bottom: 4px;
            }

            .psip-agent-grid {
                display: grid;
                gap: 8px;
            }

            .psip-agent-card {
                background: #fff;
                border: 1px solid #dcdcde;
                border-radius: 8px;
                padding: 10px;
                box-shadow: 0 1px 2px rgba(0,0,0,0.04);
                display: flex;
                flex-direction: column;
                gap: 8px;
                position: relative;
                overflow: hidden;
            }

            .psip-agent-card::before {
                content: "";
                position: absolute;
                inset: 0;
                border-radius: inherit;
                pointer-events: none;
                opacity: 0;
                transition: opacity 0.2s ease;
            }

            .psip-agent-card:hover::before {
                opacity: 0.08;
                background: currentColor;
            }

            .psip-agent-card__head {
                display: flex;
                align-items: flex-start;
                gap: 8px;
            }

            .psip-agent-card__icon {
                width: 30px;
                height: 30px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 6px;
                background: #2271b1;
                color: #fff;
                font-size: 16px;
            }

            .psip-agent-card__titles {
                flex: 1;
                min-width: 0;
            }

            .psip-agent-card__name {
                font-weight: 600;
                display: block;
            }

            .psip-agent-card__description {
                font-size: 11px;
                line-height: 1.2;
                color: #50575e;
                display: block;
                margin-top: 1px;
            }

            .psip-agent-card__badge {
                font-size: 11px;
                font-weight: 600;
                padding: 3px 8px;
                border-radius: 999px;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }

            .psip-agent-card__badge.is-success {
                background: #e7f7ef;
                color: #087443;
            }

            .psip-agent-card__badge.is-pending {
                background: #fff4ce;
                color: #8a6700;
            }

            .psip-agent-card__meta {
                display: flex;
                flex-wrap: wrap;
                gap: 6px 12px;
            }

            .psip-agent-card__meta-item {
                padding: 0;
            }

            .psip-agent-card__meta-item .label {
                display: block;
                font-size: 10px;
                text-transform: uppercase;
                color: #6c7781;
                letter-spacing: 0.05em;
                margin-bottom: 2px;
            }

            .psip-agent-card__meta-item .value {
                font-weight: 600;
                font-size: 12px;
                display: inline-flex;
                word-break: break-word;
            }

            .psip-agent-card__meta-item .hint {
                display: block;
                font-size: 10px;
                color: #6c7781;
                margin-top: 1px;
            }

            .psip-agent-card__actions {
                display: flex;
                gap: 6px;
                flex-wrap: wrap;
                margin-top: 2px;
            }

            .psip-agent-card__actions .button {
                font-size: 12px;
                padding: 4px 12px;
                line-height: 1.4;
                height: auto;
            }

            .psip-agent-card__button.is-busy {
                opacity: 0.85;
                position: relative;
                pointer-events: none;
            }

            .psip-agent-card__view {
                background: #f6f7f7;
                border-color: #dcdcde;
                color: #1d2327;
            }

            .psip-agent-card__view:hover {
                background: #e6e7e8;
                color: #1d2327;
            }

            .psip-agent-feedback {
                margin-bottom: 12px;
            }

            .psip-agent-feedback__message {
                padding: 10px 12px;
                border-radius: 6px;
                font-size: 12px;
                border-left: 4px solid;
            }

            .psip-agent-feedback__message.is-success {
                background: #e7f7ef;
                color: #0a4b2e;
                border-color: #33a36f;
            }

            .psip-agent-feedback__message.is-error {
                background: #fdeaea;
                color: #8a1f1f;
                border-color: #d63638;
            }

            .psip-agent-loading {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-top: 8px;
                font-size: 12px;
                color: #697380;
            }
        </style>

        <div class="psip-agent-header">
            <div>
                <span class="psip-agent-header__title"><?php esc_html_e('Analisi verticali AI', 'psip'); ?></span>
                <span class="psip-agent-header__subtitle"><?php esc_html_e('Lancia o aggiorna gli agenti di analisi per questa azienda.', 'psip'); ?></span>
            </div>
            <div class="psip-agent-header__meta">
                <span class="psip-agent-header__badge"><?php echo esc_html(count($existing_analyses)); ?> <?php esc_html_e('analisi', 'psip'); ?></span><br>
                <span class="psip-agent-header__company">ID #<?php echo esc_html($company_id); ?></span>
            </div>
        </div>

        <div id="psip-results" class="psip-agent-feedback" aria-live="polite"></div>

        <?php $agents = psip_get_agents(); ?>

        <div class="psip-agent-grid">
            <?php foreach ($agents as $agent_id => $agent_config): ?>
                <?php
                $analysis_info = $existing_analyses[$agent_id] ?? null;
                $has_analysis = $analysis_info !== null;
                $last_run_display = $analysis_info['last_run']['display'] ?? '';
                $last_run_relative = $analysis_info['last_run']['relative'] ?? '';
                $quality_score = $analysis_info['quality_score'] ?? '';
                $badge_class = $has_analysis ? 'is-success' : 'is-pending';
                $badge_label = $has_analysis ? __('Completata', 'psip') : __('Da avviare', 'psip');
                ?>
                <div class="psip-agent-card<?php echo $has_analysis ? ' has-analysis' : ' no-analysis'; ?>">
                    <div class="psip-agent-card__head">
                        <div class="psip-agent-card__icon" style="background: <?php echo esc_attr($agent_config['color']); ?>;">
                            <span class="dashicons <?php echo esc_attr($agent_config['icon']); ?>"></span>
                        </div>
                        <div class="psip-agent-card__titles">
                            <span class="psip-agent-card__name"><?php echo esc_html($agent_config['name']); ?></span>
                            <?php if (!empty($agent_config['description'])): ?>
                                <span class="psip-agent-card__description"><?php echo esc_html($agent_config['description']); ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="psip-agent-card__badge <?php echo esc_attr($badge_class); ?>">
                            <?php echo esc_html($badge_label); ?>
                        </span>
                    </div>

                    <div class="psip-agent-card__meta">
                        <div class="psip-agent-card__meta-item">
                            <span class="label"><?php esc_html_e('Ultima esecuzione', 'psip'); ?></span>
                            <span class="value">
                                <?php echo ($has_analysis && $last_run_display) ? esc_html($last_run_display) : esc_html__('Mai eseguita', 'psip'); ?>
                            </span>
                            <?php if ($has_analysis && $last_run_relative): ?>
                                <span class="hint"><?php echo esc_html($last_run_relative); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="psip-agent-card__meta-item">
                            <span class="label"><?php esc_html_e('Indice qualità', 'psip'); ?></span>
                            <span class="value"><?php echo ($has_analysis && $quality_score !== '') ? esc_html($quality_score) : '—'; ?></span>
                        </div>
                    </div>

                    <div class="psip-agent-card__actions">
                        <button
                            type="button"
                            class="button button-primary psip-agent-card__button"
                            data-agent="<?php echo esc_attr($agent_id); ?>"
                            data-company="<?php echo esc_attr($company_id); ?>"
                        >
                            <?php echo $has_analysis ? esc_html__('Riesegui analisi', 'psip') : esc_html__('Avvia analisi', 'psip'); ?>
                        </button>

                        <?php if ($has_analysis && !empty($analysis_info['view_link'])): ?>
                            <a
                                class="button psip-agent-card__view"
                                href="<?php echo esc_url($analysis_info['view_link']); ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <?php esc_html_e('Apri report', 'psip'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="psip-agent-loading" id="psip-agent-loading" hidden>
            <span class="spinner is-active" style="float: none;"></span>
            <span><?php esc_html_e('Analisi in avvio...', 'psip'); ?></span>
        </div>
    </div>

    <script>
        (function($) {
            const $launcher = $('#psip-agent-launcher');
            const $feedback = $('#psip-results');
            const $loading = $('#psip-agent-loading');
            const nonce = '<?php echo wp_create_nonce("psip_launch_agent"); ?>';

            $launcher.on('click', '.psip-agent-card__button', function() {
                const $button = $(this);

                if ($button.prop('disabled')) {
                    return;
                }

                const agentId = $button.data('agent');
                const companyId = $button.data('company');
                const originalLabel = $button.text();

                $button.prop('disabled', true).addClass('is-busy').text('⏳ <?php echo esc_js(__('Avvio in corso…', 'psip')); ?>');
                $loading.prop('hidden', false);
                $feedback.empty();

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'psip_launch_agent',
                        agent_id: agentId,
                        company_id: companyId,
                        nonce: nonce
                    }
                }).done(function(response) {
                    const isSuccess = response && response.success;
                    const message = response && response.data && response.data.message
                        ? response.data.message
                        : '<?php echo esc_js(__('Risposta sconosciuta dal server.', 'psip')); ?>';

                    const $message = $('<div/>', {
                        'class': 'psip-agent-feedback__message ' + (isSuccess ? 'is-success' : 'is-error'),
                        'text': message
                    });

                    $feedback.empty().append($message);

                    if (isSuccess) {
                        setTimeout(function() {
                            window.location.reload();
                        }, 1800);
                    }
                }).fail(function(xhr) {
                    let message = '<?php echo esc_js(__('Connessione non riuscita. Riprova.', 'psip')); ?>';

                    if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        message = xhr.responseJSON.data.message;
                    }

                    const $message = $('<div/>', {
                        'class': 'psip-agent-feedback__message is-error',
                        'text': message
                    });

                    $feedback.empty().append($message);
                }).always(function() {
                    $button.prop('disabled', false).removeClass('is-busy').text(originalLabel);
                    $loading.prop('hidden', true);
                });
            });
        })(jQuery);
    </script>
    <?php
}

// ============================================================================
// AJAX HANDLER: Launch Agent
// ============================================================================

add_action('wp_ajax_psip_launch_agent', 'psip_ajax_launch_agent');

function psip_ajax_launch_agent() {
    // Security check
    check_ajax_referer('psip_launch_agent', 'nonce');

    // Get parameters
    $agent_id = sanitize_text_field($_POST['agent_id']);
    $company_id = intval($_POST['company_id']);
    $agents = psip_get_agents();

    // Validate
    if (!$company_id || !isset($agents[$agent_id])) {
        wp_send_json_error([
            'message' => '❌ Invalid parameters'
        ]);
    }

    // Get agent config
    $agent = $agents[$agent_id];
    $webhook_url = PSIP_N8N_BASE_URL . $agent['webhook'];

    // Call N8N webhook
    $response = wp_remote_post($webhook_url, [
        'headers' => [
            'Content-Type' => 'application/json'
        ],
        'body' => json_encode([
            'company_id' => $company_id
        ]),
        'timeout' => 60
    ]);

    // Check response
    if (is_wp_error($response)) {
        wp_send_json_error([
            'message' => '❌ N8N connection failed: ' . $response->get_error_message()
        ]);
    }

    $status_code = wp_remote_retrieve_response_code($response);

    if ($status_code >= 200 && $status_code < 300) {
        wp_send_json_success([
            'message' => '✅ ' . $agent['name'] . ' Agent launched successfully! Analysis in progress...'
        ]);
    } else {
        wp_send_json_error([
            'message' => '❌ N8N returned error code: ' . $status_code
        ]);
    }
}

// ============================================================================
// UTILITY: Get Company Analyses
// ============================================================================
function psip_get_company_analyses($company_id) {
    $analyses = get_posts([
        'post_type' => 'analisi',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => 'parent_company_id',
                'value' => $company_id,
                'compare' => '='
            ]
        ]
    ]);

    $result = [];
    foreach ($analyses as $analysis) {
        $terms = get_the_terms($analysis->ID, 'agent_type');

        if ($terms && !is_wp_error($terms)) {
            $agent_slug = $terms[0]->slug;
            $quality_raw = function_exists('get_field') ? get_field('voto_qualita_analisi', $analysis->ID) : '';
            $quality_score = psip_theme_normalize_scalar($quality_raw);

            $result[$agent_slug] = [
                'id' => $analysis->ID,
                'title' => get_the_title($analysis->ID),
                'status' => get_post_status($analysis->ID),
                'last_run' => psip_get_analysis_last_run($analysis->ID),
                'quality_score' => $quality_score,
                'view_link' => get_permalink($analysis->ID),
                'edit_link' => get_edit_post_link($analysis->ID, ''),
            ];
        }
    }

    return $result;
}


// ============================================================================
// ADMIN COLUMN: Show Analyses Count
// ============================================================================

// add_filter('manage_company_posts_columns', 'psip_add_analyses_column');
// add_action('manage_company_posts_custom_column', 'psip_show_analyses_column', 10, 2);

// function psip_add_analyses_column($columns) {
//     $columns['psip_analyses'] = '🤖 AI Analyses';
//     return $columns;
// }

// function psip_show_analyses_column($column, $post_id) {
//     if ($column === 'psip_analyses') {
//         $analyses = psip_get_company_analyses($post_id);
//         $count = count($analyses);

//         if ($count > 0) {
//             echo '<span style="background: #4CAF50; color: white; padding: 4px 8px; border-radius: 3px; font-weight: bold;">';
//             echo $count . ' completed';
//             echo '</span>';
//         } else {
//             echo '<span style="color: #999;">No analyses</span>';
//         }
//     }
// }












// Fix definitivo: salva parent_company_id con ACF internal format
add_action('rest_after_insert_analisi', 'psip_force_save_company_relation', 99, 3);

function psip_force_save_company_relation($post, $request, $creating) {
    $params = $request->get_params();
    $json_params = $request->get_json_params();
    
    $company_id = null;
    
    // Cerca company_id
    $possible_keys = ['parent_company_id', 'field_parent_company_id'];
    
    if (isset($json_params['acf'])) {
        foreach ($possible_keys as $key) {
            if (isset($json_params['acf'][$key])) {
                $company_id = intval($json_params['acf'][$key]);
                break;
            }
        }
    }
    
    if ($company_id > 0) {
        // Verifica che l'azienda esista
        $company = get_post($company_id);
        if (!$company || $company->post_type !== 'azienda') {
            error_log("PSIP ERROR: Invalid company_id={$company_id}");
            return;
        }
        
        // Metodo 1: Salva come post_meta standard
        update_post_meta($post->ID, 'parent_company_id', $company_id);
        
        // Metodo 2: Salva con ACF internal format (necessario per post_object)
        $field_object = get_field_object('parent_company_id', $post->ID);
        
        if ($field_object && isset($field_object['key'])) {
            $field_key = $field_object['key'];
            
            // Salva il field key come riferimento interno ACF
            update_post_meta($post->ID, '_parent_company_id', $field_key);
            
            // Salva il valore dell'ID azienda
            update_post_meta($post->ID, 'parent_company_id', $company_id);
            
            error_log("PSIP SUCCESS: Saved company via ACF format - ID={$company_id}, Field Key={$field_key}");
        } else {
            error_log("PSIP WARNING: Field object not found, saved as simple meta");
        }
        
        // Verifica finale
        $saved_value = get_post_meta($post->ID, 'parent_company_id', true);
        $acf_value = get_field('parent_company_id', $post->ID);
        
        error_log("PSIP VERIFY: Meta value={$saved_value}, ACF value={$acf_value}");
    }
}










// UPSERT automatico: aggiorna analisi esistente invece di creare duplicato
add_action('rest_pre_insert_analisi', 'psip_auto_upsert_analysis', 10, 2);

function psip_auto_upsert_analysis($prepared_post, $request) {
    // Se è già un update, skip
    if (!empty($prepared_post->ID)) {
        error_log("PSIP UPSERT: Already updating post ID={$prepared_post->ID}, skipping upsert logic");
        return $prepared_post;
    }

    $params = $request->get_json_params();
    
    // Debug completo parametri ricevuti
    error_log("PSIP UPSERT DEBUG: Full params = " . json_encode($params));

    // Estrai agent_type (N8N invia array di term_id)
    $agent_type_term_id = null;
    if (isset($params['agent_type'])) {
        if (is_array($params['agent_type']) && !empty($params['agent_type'])) {
            $agent_type_term_id = intval($params['agent_type'][0]);
        } elseif (is_numeric($params['agent_type'])) {
            $agent_type_term_id = intval($params['agent_type']);
        }
    }

    // Estrai company_id da ACF params
    $company_id = null;
    if (isset($params['acf']['parent_company_id'])) {
        $company_id = intval($params['acf']['parent_company_id']);
    }
    
    // Se non trovato in acf, prova nel root
    if (!$company_id && isset($params['parent_company_id'])) {
        $company_id = intval($params['parent_company_id']);
    }

    // Validazione parametri minimi
    if (!$agent_type_term_id || !$company_id) {
        error_log("PSIP UPSERT SKIP: Missing required params - agent_type_term_id=$agent_type_term_id, company_id=$company_id");
        return $prepared_post;
    }

    // Valida che il term esista nella tassonomia agent_type
    $term = get_term($agent_type_term_id, 'agent_type');
    if (is_wp_error($term) || !$term) {
        error_log("PSIP UPSERT ERROR: Invalid agent_type term_id=$agent_type_term_id in taxonomy 'agent_type'");
        return $prepared_post;
    }

    $agent_slug = $term->slug;
    $agent_name = $term->name;

    error_log("PSIP UPSERT: Searching for existing analysis - company_id=$company_id, agent_type=$agent_slug (term_id=$agent_type_term_id)");

    // Cerca analisi esistente usando meta_query
    $existing_query = new WP_Query([
        'post_type' => 'analisi',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'post_status' => ['publish', 'draft', 'pending', 'private'],
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => 'parent_company_id',
                'value' => $company_id,
                'compare' => '='
            ],
            [
                'key' => '_psip_agent_type_term_id',
                'value' => $agent_type_term_id,
                'compare' => '='
            ]
        ]
    ]);

    if ($existing_query->have_posts()) {
        $existing_id = $existing_query->posts[0];
        $prepared_post->ID = $existing_id;
        
        $company_name = get_the_title($company_id);
        error_log("PSIP UPSERT SUCCESS: Found existing analysis ID=$existing_id for company='$company_name' (ID=$company_id), agent='$agent_name' ($agent_slug) - UPDATING");
    } else {
        error_log("PSIP UPSERT: No existing analysis found for company_id=$company_id, agent='$agent_name' ($agent_slug) - CREATING NEW");
    }

    wp_reset_postdata();
    return $prepared_post;
}




add_action('rest_after_insert_analisi', 'psip_assign_agent_type_taxonomy', 10, 3);

function psip_assign_agent_type_taxonomy($post, $request, $creating) {
    $params = $request->get_json_params();
    
    // Estrai term_id da array (N8N invia agent_type: [5])
    $agent_type_term_id = null;
    if (isset($params['agent_type'])) {
        if (is_array($params['agent_type']) && !empty($params['agent_type'])) {
            $agent_type_term_id = intval($params['agent_type'][0]);
        } elseif (is_numeric($params['agent_type'])) {
            $agent_type_term_id = intval($params['agent_type']);
        }
    }

    if (!$agent_type_term_id) {
        error_log("PSIP TAX: No agent_type found in request for post_id={$post->ID}");
        return;
    }

    // Valida term
    $term = get_term($agent_type_term_id, 'agent_type');
    if (is_wp_error($term) || !$term) {
        error_log("PSIP TAX ERROR: Invalid term_id=$agent_type_term_id for taxonomy 'agent_type' on post_id={$post->ID}");
        return;
    }

    // Assegna taxonomy (sostituisce eventuali precedenti)
    $result = wp_set_object_terms($post->ID, (int) $agent_type_term_id, 'agent_type', false);

    if (is_wp_error($result)) {
        error_log("PSIP TAX ERROR: Failed to assign term_id=$agent_type_term_id to post_id={$post->ID} - " . $result->get_error_message());
    } else {
        error_log("PSIP TAX SUCCESS: Assigned term '{$term->name}' (term_id=$agent_type_term_id) to analysis post_id={$post->ID}");
    }

    // IMPORTANTE: Salva term_id come meta per query veloci nell'upsert
    update_post_meta($post->ID, '_psip_agent_type_term_id', $agent_type_term_id);
    error_log("PSIP TAX: Saved meta '_psip_agent_type_term_id'=$agent_type_term_id for post_id={$post->ID}");
}









// ================================================================
// CUSTOM REST API - Fetch All Analyses for Sales Strategy Agent
// ================================================================

add_action('rest_api_init', function () {
    register_rest_route('psip/v1', '/company/(?P<id>\d+)/all-analyses', [
        'methods' => 'GET',
        'callback' => 'psip_get_all_company_analyses',
        'permission_callback' => '__return_true', // TODO: Add auth in production
        'args' => [
            'id' => [
                'required' => true,
                'validate_callback' => function($param) {
                    return is_numeric($param);
                }
            ]
        ]
    ]);
});



function psip_get_all_company_analyses($request) {
    $company_id = intval($request['id']);
    
    $company = get_post($company_id);
    if (!$company || $company->post_type !== 'azienda') {
        return new WP_Error('not_found', 'Company not found', ['status' => 404]);
    }
    
    // Metodo 1: Query meta_query
    $analyses_method1 = get_posts([
        'post_type' => 'analisi',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => 'parent_company_id',
                'value' => $company_id,
                'compare' => '='
            ]
        ]
    ]);
    
    // Metodo 2: ACF get_field() filtering
    $all_analyses = get_posts([
        'post_type' => 'analisi',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ]);
    
    $analyses_method2 = [];
    foreach ($all_analyses as $analysis) {
        $linked_company = get_field('parent_company_id', $analysis->ID);
        
        $linked_company_id = 0;
        if (is_numeric($linked_company)) {
            $linked_company_id = intval($linked_company);
        } elseif (is_object($linked_company) && isset($linked_company->ID)) {
            $linked_company_id = $linked_company->ID;
        } elseif (is_array($linked_company) && isset($linked_company['ID'])) {
            $linked_company_id = $linked_company['ID'];
        }
        
        if ($linked_company_id === $company_id) {
            $analyses_method2[] = $analysis;
        }
    }
    
    // Merge
    $all_found = [];
    foreach ($analyses_method1 as $a) {
        $all_found[$a->ID] = $a;
    }
    foreach ($analyses_method2 as $a) {
        $all_found[$a->ID] = $a;
    }
    
    $analyses = array_values($all_found);
    
    // Build response utilizzando esclusivamente i campi ACF attivi
    $results = [
        'company_id' => $company_id,
        'company_name' => $company->post_title,
        'company_meta' => [
            'domain' => psip_theme_normalize_scalar(get_field('domain', $company_id)),
            'sector' => psip_theme_normalize_scalar(get_field('sector_specific', $company_id)),
            'business_type' => psip_theme_normalize_scalar(get_field('business_type', $company_id)),
            'estimated_revenue' => psip_theme_normalize_scalar(get_field('estimated_annual_revenue', $company_id)),
            'estimated_employees' => psip_theme_normalize_scalar(get_field('employee_count_est', $company_id)),
            'growth_stage' => psip_theme_normalize_scalar(get_field('growth_stage', $company_id)),
            'budget_tier' => psip_theme_normalize_scalar(get_field('budget_tier', $company_id)),
            'estimated_marketing_budget' => psip_theme_normalize_scalar(get_field('estimated_marketing_budget', $company_id)),
        ],
        'analyses' => [],
        'analyses_count' => 0
    ];
    
    // Parse analyses
    foreach ($analyses as $analysis) {
        $terms = get_the_terms($analysis->ID, 'agent_type');

        if (!$terms || is_wp_error($terms)) {
            continue;
        }
        $agent_type = $terms[0]->slug;

        $quality_score_raw = get_field('voto_qualita_analisi', $analysis->ID);
        $quality_score = ($quality_score_raw !== null && $quality_score_raw !== '' && is_numeric($quality_score_raw))
            ? (int) $quality_score_raw
            : null;

        $summary              = psip_theme_normalize_scalar(get_field('riassunto', $analysis->ID));
        $strengths            = psip_theme_normalize_scalar(get_field('punti_di_forza', $analysis->ID));
        $weaknesses           = psip_theme_normalize_scalar(get_field('punti_di_debolezza', $analysis->ID));
        $opportunities_text   = psip_theme_normalize_scalar(get_field('opportunita', $analysis->ID));
        $quick_actions_text   = psip_theme_normalize_scalar(get_field('azioni_rapide', $analysis->ID));
        $deep_research_raw    = get_field('analisy_perplexity_deep_research', $analysis->ID);
        $deep_research        = $deep_research_raw ? wp_kses_post($deep_research_raw) : '';

        $count_strengths = get_field('numero_punti_di_forza', $analysis->ID);
        $count_weaknesses = get_field('numero_punti_di_debolezza', $analysis->ID);
        $count_opportunities = get_field('numero_opportunita', $analysis->ID);
        $count_quick_actions = get_field('numero_azioni_rapide', $analysis->ID);

        $last_run = psip_get_analysis_last_run($analysis->ID);
        $execution_timestamp = $last_run['display'];

        $results['analyses'][$agent_type] = [
            'agent_type' => $agent_type,
            'analysis_id' => $analysis->ID,
            'execution_timestamp' => $execution_timestamp,
            'quality_score' => $quality_score,
            'summary' => $summary,
            'strengths' => $strengths,
            'weaknesses' => $weaknesses,
            'opportunities' => $opportunities_text,
            'quick_actions' => $quick_actions_text,
            'deep_research' => $deep_research,
            'counts' => [
                'strengths' => ($count_strengths !== null && $count_strengths !== '' && is_numeric($count_strengths)) ? (int) $count_strengths : null,
                'weaknesses' => ($count_weaknesses !== null && $count_weaknesses !== '' && is_numeric($count_weaknesses)) ? (int) $count_weaknesses : null,
                'opportunities' => ($count_opportunities !== null && $count_opportunities !== '' && is_numeric($count_opportunities)) ? (int) $count_opportunities : null,
                'quick_actions' => ($count_quick_actions !== null && $count_quick_actions !== '' && is_numeric($count_quick_actions)) ? (int) $count_quick_actions : null,
            ],
        ];
    }
    
    $results['analyses_count'] = count($results['analyses']);
    
    return rest_ensure_response($results);
}







// ============================================================================
// BUTTON 1: Launch Campaign (CPT Campagne)
// ============================================================================

add_action('add_meta_boxes', 'psip_add_campaign_launcher_metabox');

function psip_add_campaign_launcher_metabox() {
    add_meta_box(
        'psip_campaign_launcher',
        '🚀 Campaign Lead Generator',
        'psip_render_campaign_launcher_metabox',
        'campagna',
        'side',
        'high'
    );
}

function psip_render_campaign_launcher_metabox($post) {
    $search_query = get_post_meta($post->ID, 'search_query', true);
    $max_results = get_post_meta($post->ID, 'max_results', true) ?: 20;
    $campaign_status = get_post_meta($post->ID, '_campaign_status', true);
    $last_run = get_post_meta($post->ID, '_last_run_timestamp', true);
    $companies_found = get_post_meta($post->ID, '_companies_found', true) ?: 0;
    
    ?>
    <div class="psip-campaign-launcher">
        <div style="margin-bottom: 15px; padding: 10px; background: #f0f0f1; border-radius: 4px;">
            <p style="margin: 0 0 5px 0;"><strong>Query:</strong> <?php echo esc_html($search_query ?: 'Non impostata'); ?></p>
            <p style="margin: 0 0 5px 0;"><strong>Max Results:</strong> <?php echo esc_html($max_results); ?></p>
            <?php if ($last_run): ?>
                <p style="margin: 0 0 5px 0;"><strong>Last Run:</strong> <?php echo date('d/m/Y H:i', strtotime($last_run)); ?></p>
                <p style="margin: 0;"><strong>Companies Found:</strong> <?php echo esc_html($companies_found); ?></p>
            <?php endif; ?>
        </div>
        
        <?php if ($campaign_status === 'running'): ?>
            <div style="padding: 8px; background: #f0b849; color: #fff; text-align: center; border-radius: 4px; margin-bottom: 10px;">
                ⏳ <strong>Campagna in esecuzione...</strong>
            </div>
        <?php elseif ($campaign_status === 'completed'): ?>
            <div style="padding: 8px; background: #46b450; color: #fff; text-align: center; border-radius: 4px; margin-bottom: 10px;">
                ✅ <strong>Completata</strong>
            </div>
        <?php endif; ?>
        
        <button 
            type="button" 
            class="button button-primary button-large psip-launch-campaign" 
            data-campaign-id="<?php echo $post->ID; ?>"
            data-search-query="<?php echo esc_attr($search_query); ?>"
            data-max-results="<?php echo esc_attr($max_results); ?>"
            style="width: 100%; height: 50px; font-size: 16px;">
            🚀 Avvia Campagna
        </button>
        
        <p style="font-size: 11px; color: #666; margin-top: 10px; text-align: center;">
            Google Maps Discovery + Economic Analysis automatico
        </p>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('.psip-launch-campaign').on('click', function() {
            const btn = $(this);
            const campaignId = btn.data('campaign-id');
            const searchQuery = btn.data('search-query');
            const maxResults = btn.data('max-results');
            
            if (!searchQuery) {
                alert('⚠️ Imposta una Search Query prima di avviare la campagna!');
                return;
            }
            
            if (!confirm(`Avviare campagna?\n\nQuery: ${searchQuery}\nMax Results: ${maxResults}\n\nQuesto potrebbe richiedere diversi minuti.`)) {
                return;
            }
            
            btn.prop('disabled', true).html('⏳ Avvio in corso...');
            
            $.ajax({
                url: '<?php echo PSIP_N8N_BASE_URL; ?>/webhook/lead-generator-campaign',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    campaign_id: campaignId,
                    search_query: searchQuery,
                    max_results: maxResults
                }),
                success: function(response) {
                    alert('✅ Campagna avviata con successo!\n\nRiceverai una notifica al completamento.');
                    btn.prop('disabled', false).html('🚀 Campagna Avviata - Riesegui');
                    location.reload();
                },
                error: function(xhr) {
                    alert('❌ Errore durante l\'avvio:\n\n' + (xhr.responseJSON?.message || 'Errore sconosciuto'));
                    btn.prop('disabled', false).html('🚀 Avvia Campagna Lead Generator');
                }
            });
        });
    });
    </script>
    <?php
}



/// ============================================================================
// BUTTON 2: Company Enrichment (CPT Azienda) - Versione Semplificata
// ============================================================================

add_action('add_meta_boxes', 'psip_add_company_enrichment_metabox');

function psip_add_company_enrichment_metabox() {
    add_meta_box(
        'psip_company_enrichment',
        'Company Enrichment',
        'psip_render_company_enrichment_metabox',
        'azienda',
        'side',
        'high'
    );
}

if (!function_exists('psip_company_enrichment_value_is_populated')) {
    /**
     * Determina se un valore va conteggiato come presente per la completezza dati.
     */
    function psip_company_enrichment_value_is_populated($value, $allow_zero = false, $count_false = false) {
        if (is_bool($value)) {
            return $value || ($count_false && $value === false);
        }

        $normalized = trim(psip_theme_normalize_scalar($value));

        if ($normalized === '') {
            return false;
        }

        $normalized_lower = strtolower($normalized);
        if (in_array($normalized_lower, ['unknown', 'non disponibile'], true)) {
            return false;
        }

        if ($normalized_lower === 'false') {
            return $count_false;
        }

        if ($normalized === '0') {
            return $allow_zero || $count_false;
        }

        return true;
    }
}

if (!function_exists('psip_company_enrichment_social_json_has_value')) {
    /**
     * Controlla se il JSON dei social contiene almeno un link valido.
     *
     * @param mixed $raw_values
     */
    function psip_company_enrichment_social_json_has_value($raw_values) {
        foreach ((array) $raw_values as $value) {
            if (empty($value)) {
                continue;
            }

            $json_string = is_array($value) ? wp_json_encode($value) : $value;
            if (!is_string($json_string) || trim($json_string) === '') {
                continue;
            }

            $decoded = json_decode($json_string, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                foreach ($decoded as $link) {
                    if (psip_company_enrichment_value_is_populated($link)) {
                        return true;
                    }
                }
            } elseif (psip_company_enrichment_value_is_populated($json_string)) {
                return true;
            }
        }

        return false;
    }
}

function psip_render_company_enrichment_metabox($post) {
    $post_id = $post->ID;

    // Mappa dei campi gestiti dal workflow Company Enrichment di n8n.
    $field_groups = [
        'partita_iva' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'partita_iva'],
            ],
        ],
        'address' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'address'],
            ],
        ],
        'provincia' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'provincia'],
            ],
        ],
        'phone' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'phone'],
            ],
        ],
        'email' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'email_azienda'],
            ],
        ],
        'website' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'website'],
            ],
        ],
        'domain' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'domain'],
            ],
        ],
        'sector_specific' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'sector_specific'],
            ],
        ],
        'business_type' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'business_type'],
            ],
        ],
        'employee_count_est' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'employee_count_est'],
            ],
        ],
        'growth_stage' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'growth_stage'],
            ],
        ],
        'estimated_annual_revenue' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'estimated_annual_revenue'],
            ],
        ],
        'estimated_ebitda_percentage' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'estimated_ebitda_percentage'],
            ],
        ],
        'estimated_marketing_budget' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'estimated_marketing_budget'],
            ],
        ],
        'budget_tier' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'budget_tier'],
            ],
            'allow_zero' => true,
        ],
        'budget_qualified' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'budget_qualified'],
            ],
            'count_false' => true,
        ],
        'linkedin_url' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'linkedin_url'],
            ],
        ],
        'facebook_url' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'facebook_url'],
            ],
        ],
        'instagram_url' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'instagram_url'],
            ],
        ],
        'x_url' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'x_url'],
            ],
        ],
        'youtube_url' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'youtube_url'],
            ],
        ],
        'tiktok_url' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'tiktok_url'],
            ],
        ],
        'social_links_json' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'social_json'],
            ],
            'custom' => 'social_json',
        ],
        'screen_home' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'screen_home'],
            ],
        ],
        'analysis_date' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'analysis_date'],
                ['type' => 'meta', 'key' => 'enrichment_timestamp'],
            ],
        ],
        'confidence' => [
            'sources' => [
                ['type' => 'acf', 'key' => 'confidence'],
                ['type' => 'meta', 'key' => 'enrichment_confidence'],
            ],
        ],
        'enrichment_sources' => [
            'sources' => [
                ['type' => 'meta', 'key' => 'enrichment_sources'],
            ],
        ],
        'enrichment_notes' => [
            'sources' => [
                ['type' => 'meta', 'key' => 'enrichment_notes'],
            ],
        ],
        'enrichment_citations' => [
            'sources' => [
                ['type' => 'meta', 'key' => 'enrichment_citations'],
            ],
        ],
    ];

    $fields_to_check = [];

    foreach ($field_groups as $group_key => $config) {
        $group_values = [];
        $group_considered = false;

        foreach ($config['sources'] as $source) {
            if ($source['type'] === 'acf') {
                $acf_key = $source['key'];
                $field_exists = true;

                if (function_exists('get_field_object')) {
                    $field_object = get_field_object($acf_key, $post_id, false, false);
                    $field_exists = $field_object !== false;
                }

                if (!$field_exists && !metadata_exists('post', $post_id, $acf_key)) {
                    continue;
                }

                $group_considered = true;
                $group_values[] = get_field($acf_key, $post_id);
            } elseif ($source['type'] === 'meta') {
                $group_considered = true;
                $group_values[] = get_post_meta($post_id, $source['key'], true);
            }
        }

        if (!$group_considered) {
            continue;
        }

        $fields_to_check[$group_key] = [
            'values' => $group_values,
            'allow_zero' => !empty($config['allow_zero']),
            'count_false' => !empty($config['count_false']),
            'custom' => $config['custom'] ?? '',
        ];
    }

    $total_fields = count($fields_to_check);
    $populated_fields = 0;

    foreach ($fields_to_check as $group_key => $data) {
        $has_value = false;

        if ($data['custom'] === 'social_json') {
            $has_value = psip_company_enrichment_social_json_has_value($data['values']);
        } else {
            foreach ($data['values'] as $group_value) {
                if (psip_company_enrichment_value_is_populated($group_value, $data['allow_zero'], $data['count_false'])) {
                    $has_value = true;
                    break;
                }
            }
        }

        if ($has_value) {
            $populated_fields++;
        }
    }

    $completeness = $total_fields > 0 ? round(($populated_fields / $total_fields) * 100) : 0;
    $website = get_field('website', $post->ID);
    
    ?>
    <div class="psip-enrichment-section">
        <div style="margin-bottom: 15px;">
            <div style="font-size: 12px; margin-bottom: 5px;">
                <strong>Completezza Dati: <?php echo $completeness; ?>%</strong>
            </div>
            <div style="background: #ddd; height: 10px; border-radius: 5px; overflow: hidden;">
                <div style="background: <?php echo $completeness > 70 ? '#46b450' : ($completeness > 40 ? '#f0b849' : '#dc3232'); ?>; width: <?php echo $completeness; ?>%; height: 100%; transition: width 0.3s;"></div>
            </div>
        </div>
        
        <button 
            type="button" 
            class="button button-primary button-large psip-trigger-enrichment" 
            data-company-id="<?php echo $post->ID; ?>"
            data-company-name="<?php echo esc_attr($post->post_title); ?>"
            data-website="<?php echo esc_attr($website); ?>"
            style="width: 100%; height: 45px; font-size: 15px;">
            🔍 Arricchisci Dati Azienda
        </button>
        
        <p style="font-size: 11px; color: #666; margin-top: 8px; text-align: center;">
            Popola automaticamente: P.IVA, indirizzo, telefono, settore, fatturato, dipendenti + Economic Analysis
        </p>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('.psip-trigger-enrichment').on('click', function() {
            const btn = $(this);
            const companyId = btn.data('company-id');
            const companyName = btn.data('company-name');
            const website = btn.data('website');
            
            if (!confirm(`Arricchire dati per "${companyName}"?`)) {
                return;
            }
            
            btn.prop('disabled', true).html('⏳ Elaborazione...');
            
            $.ajax({
                url: '<?php echo PSIP_N8N_BASE_URL; ?>/webhook/company-enrichment',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    company_id: companyId,
                    company_name: companyName,
                    website: website || '',
                    skip_google_maps: true
                }),
                success: function(response) {
                    alert('✅ Arricchimento completato!');
                    location.reload();
                },
                error: function(xhr) {
                    alert('❌ Errore: ' + (xhr.responseJSON?.message || 'Errore sconosciuto'));
                    btn.prop('disabled', false).html('🔍 Arricchisci Dati Azienda');
                }
            });
        });
    });
    </script>
    <?php
}
