<?php
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

/**
 * Recupera dinamicamente gli agenti dalla tassonomia agent_type
 */
function psip_get_agents() {
    static $agents = null;

    if ($agents !== null) {
        return $agents;
    }

    $agents = [];

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
        $agents[$term->slug] = [
            'name' => $term->name,
            'webhook' => get_field('webhook', 'agent_type_' . $term->term_id) ?: '',
            'icon' => get_field('icon', 'agent_type_' . $term->term_id) ?: 'dashicons-admin-generic',
            'color' => get_field('color', 'agent_type_' . $term->term_id) ?: '#000000',
            'description' => $term->description ?: '',
        ];
    }

    return $agents;
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
            .psip-agent-btn {
                width: 100%;
                padding: 12px;
                margin: 8px 0;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                font-size: 13px;
                font-weight: 600;
                color: white;
                display: flex;
                align-items: center;
                justify-content: space-between;
                transition: all 0.3s ease;
                position: relative;
            }

            .psip-agent-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            }

            .psip-agent-btn:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }

            .psip-agent-btn .dashicons {
                font-size: 18px;
                width: 18px;
                height: 18px;
            }

            .psip-agent-status {
                padding: 8px 12px;
                margin: 4px 0;
                border-radius: 4px;
                font-size: 12px;
                background: #f0f0f1;
            }

            .psip-agent-status.completed {
                background: #d4edda;
                color: #155724;
            }

            .psip-agent-status.running {
                background: #fff3cd;
                color: #856404;
            }

            .psip-loading {
                display: none;
                text-align: center;
                padding: 10px;
                color: #666;
            }

            .psip-loading.active {
                display: block;
            }

            .psip-result {
                margin-top: 10px;
                padding: 10px;
                border-radius: 4px;
                font-size: 12px;
            }

            .psip-result.success {
                background: #d4edda;
                color: #155724;
                border-left: 4px solid #28a745;
            }

            .psip-result.error {
                background: #f8d7da;
                color: #721c24;
                border-left: 4px solid #dc3545;
            }

            .psip-info {
                padding: 10px;
                background: #e7f3ff;
                border-left: 4px solid #2196F3;
                margin-bottom: 15px;
                font-size: 12px;
            }
        </style>

        <div class="psip-info">
            <strong>🎯 Company ID:</strong> <?php echo $company_id; ?><br>
            <strong>📊 Analyses:</strong> <?php echo count($existing_analyses); ?> completed
        </div>

        <div id="psip-results"></div>

        <?php
        $agents = psip_get_agents();
        foreach ($agents as $agent_id => $agent_config): ?>
            <?php 
            $has_analysis = isset($existing_analyses[$agent_id]);
            $last_analysis = $has_analysis ? $existing_analyses[$agent_id] : null;
            ?>

            <button 
            class="psip-agent-btn" 
            style="background-color: <?php echo $agent_config['color']; ?>;"
            onclick="psipLaunchAgent('<?php echo $agent_id; ?>', <?php echo $company_id; ?>)"
            id="psip-btn-<?php echo $agent_id; ?>"
            title="<?php echo $agent_config['description']; ?>"
            >
            <span>
                <span class="dashicons <?php echo $agent_config['icon']; ?>"></span>
                <?php echo $agent_config['name']; ?>
            </span>
            <span class="psip-btn-status">
                <?php echo $has_analysis ? '✓ Run' : 'Launch'; ?>
            </span>
        </button>

        <?php if ($has_analysis): ?>
            <div class="psip-agent-status completed">
                ✓ Last run: <?php echo get_post_meta($last_analysis, 'execution_timestamp', true); ?>
                <!-- | Score: <?php //echo number_format(get_post_meta($last_analysis, 'confidence_score', true), 2); ?> -->
            </div>
        <?php endif; ?>

    <?php endforeach; ?>

    <div class="psip-loading" id="psip-loading">
        <span class="spinner is-active" style="float: none;"></span>
        <p>Running AI agent...</p>
    </div>
</div>

<script>
    function psipLaunchAgent(agentId, companyId) {
        // Disable button
        const btn = document.getElementById('psip-btn-' + agentId);
        btn.disabled = true;

        // Show loading
        document.getElementById('psip-loading').classList.add('active');

        // Clear previous results
        document.getElementById('psip-results').innerHTML = '';

        // AJAX call to WordPress
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'psip_launch_agent',
                agent_id: agentId,
                company_id: companyId,
                nonce: '<?php echo wp_create_nonce("psip_launch_agent"); ?>'
            },
            success: function(response) {
                // Hide loading
                document.getElementById('psip-loading').classList.remove('active');

                // Re-enable button
                btn.disabled = false;

                // Show result
                const resultDiv = document.createElement('div');
                resultDiv.className = 'psip-result ' + (response.success ? 'success' : 'error');
                resultDiv.innerHTML = response.data.message;
                document.getElementById('psip-results').appendChild(resultDiv);

                // Reload page after 2 seconds if success
                if (response.success) {
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                }
            },
            error: function(xhr, status, error) {
                document.getElementById('psip-loading').classList.remove('active');
                btn.disabled = false;

                const resultDiv = document.createElement('div');
                resultDiv.className = 'psip-result error';
                resultDiv.innerHTML = '❌ Connection error: ' + error;
                document.getElementById('psip-results').appendChild(resultDiv);
            }
        });
    }
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
            $result[$agent_slug] = $analysis->ID;
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
    
    // Build response con field names corretti ✅
    $results = [
        'company_id' => $company_id,
        'company_name' => $company->post_title,
        'company_meta' => [
            'domain' => get_field('domain', $company_id),
            'sector' => get_field('sector_specific', $company_id),
            'business_type' => get_field('business_type', $company_id),
            'estimated_revenue' => get_field('estimated_annual_revenue', $company_id),
            'estimated_employees' => get_field('employee_count_est', $company_id),
            'growth_stage' => get_field('growth_stage', $company_id),
            'budget_tier' => get_field('estimated_marketing_budget', $company_id)
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
        $analysis_json_raw = get_post_meta($analysis->ID, 'analysis_result_json', true);

        $analysis_json = !empty($analysis_json_raw) ? json_decode($analysis_json_raw, true) : null;
        
        $recommendations_json_raw = get_post_meta($analysis->ID, 'recommendations_json', true);
        $recommendations_json = !empty($recommendations_json_raw) ? json_decode($recommendations_json_raw, true) : null;
        
        $results['analyses'][$agent_type] = [
            'agent_type' => $agent_type,
            'analysis_id' => $analysis->ID,
            'execution_timestamp' => get_post_meta($analysis->ID, 'execution_timestamp', true),
            'confidence_score' => floatval(get_post_meta($analysis->ID, 'confidence_score', true)),
            'quality_score' => floatval(get_post_meta($analysis->ID, 'quality_score', true)),
            'insights_summary' => get_post_meta($analysis->ID, 'insights_summary', true),
            'opportunities_identified' => get_post_meta($analysis->ID, 'opportunities_identified', true),
            'gaps_weaknesses' => get_post_meta($analysis->ID, 'gaps_weaknesses', true),
            'agent_specific_metrics' => json_decode(get_post_meta($analysis->ID, 'agent_specific_metrics', true), true),
            'analysis_data' => $analysis_json,
            'recommendations' => $recommendations_json
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

function psip_render_company_enrichment_metabox($post) {
    // Leggi campi ACF per calcolare completezza
    $partita_iva = get_field('partita_iva', $post->ID);
    $address = get_field('address', $post->ID);
    $phone = get_field('phone', $post->ID);
    $email = get_field('email_azienda', $post->ID);
    $sector_specific = get_field('sector_specific', $post->ID);
    $employee_count = get_field('employee_count_est', $post->ID);
    $growth_stage = get_field('growth_stage', $post->ID);
    $budget_tier = get_field('budget_tier', $post->ID);
    $website = get_field('website', $post->ID);

    $estimated_annual_revenue = get_field('estimated_annual_revenue', $post->ID);

    $estimated_ebitda_percentage = get_field('estimated_ebitda_percentage', $post->ID);
    $estimated_marketing_budget = get_field('estimated_marketing_budget', $post->ID);
    $business_type = get_field('business_type', $post->ID);
    $budget_qualified = get_field('budget_qualified', $post->ID);

    // Calcola completezza
    $fields_to_check = [
        $partita_iva,
        $address,
        $phone,
        $email,
        $sector_specific,
        $employee_count,
        $growth_stage,
        $budget_tier,
        $budget_qualified,
        $business_type,
        $estimated_marketing_budget,
        $estimated_ebitda_percentage,
        $estimated_annual_revenue
    ];
    
    $populated_fields = 0;
    foreach ($fields_to_check as $field_value) {
        if (!empty($field_value) && $field_value !== 'unknown') {
            $populated_fields++;
        }
    }
    $completeness = round(($populated_fields / count($fields_to_check)) * 100);
    
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




