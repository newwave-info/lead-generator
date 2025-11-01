<?php

/**
 * Load Dashicons frontend
 */
function perspect_load_dashicons(){
   wp_enqueue_style('dashicons');
}
add_action('wp_enqueue_scripts', 'perspect_load_dashicons', 999);

/**
 * Load Lead Dashboard CSS for single-azienda pages
 */
function perspect_load_lead_dashboard_css() {
    // Carica solo nelle pagine single del post type 'azienda'
    if (is_singular('azienda')) {
        wp_enqueue_style(
            'lead-dashboard',
            get_template_directory_uri() . '/common/css/lead-dashboard.css',
            array(), // dependencies
            '1.3.0', // version - Minimalist redesign
            'all' // media
        );
    }
}
add_action('wp_enqueue_scripts', 'perspect_load_lead_dashboard_css', 999);


/**
 * Ottieni heatmap per le celle
 */
function get_heatmap_color($value) {
    $value = max(0, min(100, $value));
    $hue = $value * 1.20;
    return "hsl($hue, 45%, 80%)";
}

/**
 * Pre-carica tutte le analisi per tutte le aziende
 * Ritorna array strutturato: azienda_id => [agent_type => analisi_data]
 */
function perspect_preload_analisi_by_aziende(array $azienda_ids = []): array {
    if (empty($azienda_ids)) {
        return [];
    }

    $args = [
        'post_type' => 'analisi',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'no_found_rows' => true,
        'orderby' => 'date',
        'order' => 'DESC',
        'meta_query' => [
            [
                'key' => 'parent_company_id',
                'value' => $azienda_ids,
                'compare' => 'IN',
                'type' => 'NUMERIC',
            ]
        ],
    ];

    $q = new WP_Query($args);

    if (!$q->have_posts()) {
        return [];
    }

    $indexed = [];

    foreach ($q->posts as $post) {
        $parent_id = (int) get_field('parent_company_id', $post->ID);

        // Recupera agent_type dalla tassonomia
        $terms = get_the_terms($post->ID, 'agent_type');

        if (!$terms || is_wp_error($terms)) {
            continue;
        }

        $agent_type = $terms[0]->slug;

        if (!isset($indexed[$parent_id])) {
            $indexed[$parent_id] = [];
        }

        // Salva solo l'analisi più recente per tipo (già ordinata DESC)
        if (!isset($indexed[$parent_id][$agent_type])) {
            $execution_timestamp = get_the_date('Y-m-d H:i', $post->ID);

            $quality_score = get_field('voto_qualita_analisi', $post->ID);
            $strengths     = get_field('numero_punti_di_forza', $post->ID);
            $weaknesses    = get_field('numero_punti_di_debolezza', $post->ID);
            $opportunities = get_field('numero_opportunita', $post->ID);
            $quick_actions = get_field('numero_azioni_rapide', $post->ID);
            $summary_raw   = get_field('riassunto', $post->ID);
            $summary       = function_exists('psip_theme_normalize_scalar') ? psip_theme_normalize_scalar($summary_raw) : $summary_raw;

            $indexed[$parent_id][$agent_type] = [
                'id'              => $post->ID,
                'title'           => get_the_title($post),
                'quality_score'   => is_numeric($quality_score) ? (float) $quality_score : null,
                'summary'         => $summary,
                'when'            => $execution_timestamp,
                'strengths'       => is_numeric($strengths) ? (int) $strengths : null,
                'weaknesses'      => is_numeric($weaknesses) ? (int) $weaknesses : null,
                'opportunities'   => is_numeric($opportunities) ? (int) $opportunities : null,
                'quick_actions'   => is_numeric($quick_actions) ? (int) $quick_actions : null,
            ];
        }

    }

    wp_reset_postdata();
    return $indexed;
}

/**
 * Helper: costruisce l'HTML del tooltip (con tutti i 5 valori)
 */
function perspect_build_tooltip_html(array $data): string {
    $title = esc_html($data['title']);
    $when = esc_html($data['when']);
    $quality_score = isset($data['quality_score']) && $data['quality_score'] !== null
        ? esc_html((string) $data['quality_score'])
        : '—';
    $summary = isset($data['summary']) && $data['summary'] !== ''
        ? esc_html((string) $data['summary'])
        : '';

    $rows = [
        ['label' => 'Opportunità',        'icon' => '<span class="dashicons dashicons-thumbs-up"></span>',   'val' => $data['opportunities'] ?? null],
        ['label' => 'Punti di debolezza', 'icon' => '<span class="dashicons dashicons-warning"></span>',     'val' => $data['weaknesses'] ?? null],
    ];

    $rows_html = '';
    foreach ($rows as $r) {
        $val_raw = isset($r['val']) && $r['val'] !== null ? $r['val'] : '—';
        $val = esc_html((string) $val_raw);
        $rows_html .= '<div class="d-flex align-items-center justify-content-between gap-2">'
                    .    '<span class="me-2">'.$r['icon'].' '.esc_html($r['label']).'</span>'
                    .    '<strong class="ms-2">'.$val.'</strong>'
                    . '</div>';
    }

    $summary_html = $summary !== ''
        ? '<hr><div class="small">'.$summary.'</div>'
        : '';

    return '<div>'
         .    '<div class="fw-semibold">'.$title.'</div>'
         .    '<div class="small">'.$when.'</div>'
         .    '<hr>'
         .    $rows_html
         .    $summary_html
         .    '<hr>'
         .    '<div class="small">Quality score: <span class="badge">'.$quality_score.'</span></div>'
         . '</div>';
}


/**
 * Cell renderer con tooltip HTML + heatmap
 */
function perspect_get_score_cell(array $analisi_map, int $azienda_id, string $agent_type): string {
    if (!isset($analisi_map[$azienda_id][$agent_type])) {
        return '<td class="agent-score"></td>';
    }

    $data = $analisi_map[$azienda_id][$agent_type];

    $score_for_heatmap = isset($data['quality_score']) && $data['quality_score'] !== null
        ? (float) $data['quality_score']
        : 0;
    $bgcolor = get_heatmap_color($score_for_heatmap);

    // Tooltip: per sicurezza uso apici esterni e converto gli apici singoli interni
    $tooltip_html = perspect_build_tooltip_html($data);
    $tooltip_attr = str_replace("'", '&#039;', $tooltip_html); // evita rotture dell'attributo

    // Contenuto visibile nella cella: solo primo e ultimo valore, come richiesto
    $vis_opportunities = esc_html((string) ($data['opportunities'] ?? '—'));
    $vis_weaknesses  = esc_html((string) ($data['weaknesses'] ?? '—'));

    $html  = '<td class="agent-score"'
          .       ' style="background-color:'.$bgcolor.'"'
          .       ' data-bs-toggle="popover" data-bs-html="true"'
          //.       ' data-bs-title="Titolo"'
          .       ' data-bs-content=\''.$tooltip_attr.'\'>';
    $html .=     '<span class="dashicons dashicons-thumbs-up"></span> '.$vis_opportunities;
    $html .=     ' <br>';
    $html .=     '<span class="dashicons dashicons-warning"></span> '.$vis_weaknesses;
    $html .= '</td>';

    return $html;
}




/**
 * Truncate text
 */
// function theme_truncate( $string, $length = 60, $append = '&hellip;' ) {
//     $string = trim( $string );

//     if ( strlen( $string ) > $length ) {
//         $string = wordwrap( $string, $length );
//         $string = explode( "\n", $string, 2 );
//         $string = $string[0] . $append;
//     }

//     return $string;
// }



/**
 * Excerpt Length
 */
// function theme_excerpt_length( $length ) {
//     return 20;
// }
// add_filter( 'excerpt_length', 'theme_excerpt_length', 999 );


/**
 * Excerpt Ellisis
 */
// function theme_excerpt_more($more) {
//     return '...';
// }
// add_filter('excerpt_more', 'theme_excerpt_more');


/**
 * Remove admin bar
 */
add_filter( 'show_admin_bar', '__return_false' );


/**
 * Remove Yoast searchbox
 */
//add_filter( 'disable_wpseo_json_ld_search', '__return_true' );


/**
 * Edit query pre_get_posts
 */
function theme_modify_query( $query ) {
    if ( ! is_admin() && $query->is_main_query() && is_post_type_archive('analisi') ) {
        $query->set( 'posts_per_page', '-1' );
    }
}
add_action('pre_get_posts', 'theme_modify_query');


/**
 * Custom pagination
 */
// function pagination( $pages = '', $range = 4 ) {
//     $showitems = ( $range * 2 ) + 1;

//     global $paged;
//     if ( empty( $paged ) ) $paged = 1;

//     if( $pages == '' ) {
//         global $wp_query;
//         $pages = $wp_query->max_num_pages;
//         if( !$pages ) {
//             $pages = 1;
//         }
//     }

//     if( 1 != $pages ) {
//         echo '<section id="pagination"><section class="row align-items-center">';
//         echo '<section class="col-auto"><span>Pagina '.$paged.' di '.$pages.'</span></section>';
//         echo '<section class="col"><nav><ul>';
//         if($paged > 2 && $paged > $range+1 && $showitems < $pages) echo '<li><a href="'.get_pagenum_link(1).'">&laquo;</a></li>';
//         if($paged > 1 && $showitems < $pages) echo '<li><a href="'.get_pagenum_link($paged - 1).'">&lsaquo;</a></li>';

//         for ($i=1; $i <= $pages; $i++) {
//             if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems )) {
//                 echo ($paged == $i)? '<li><a href="javascript:void(0);" class="current">'.$i.'</a></li>':'<li><a href="'.get_pagenum_link($i).'" class="inactive">'.$i.'</a></li>';
//             }
//         }

//         if ($paged < $pages && $showitems < $pages) echo '<li><a href="'.get_pagenum_link($paged + 1).'">&rsaquo;</a></li>';
//         if ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) echo '<li><a href="'.get_pagenum_link($pages).'">&raquo;</a></li>';
//         echo '</ul></nav></section></section></section>';
//     }
// }
