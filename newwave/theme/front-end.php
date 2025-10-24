<?php

/**
 * Load Dashicons frontend
 */
function perspect_load_dashicons(){
   wp_enqueue_style('dashicons');
}
add_action('wp_enqueue_scripts', 'perspect_load_dashicons', 999);


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

            $indexed[$parent_id][$agent_type] = [
                'id'                => $post->ID,
                'title'             => get_the_title($post),
                'confidence_score'  => get_field('confidence_score', $post),
                'quality_score'     => get_field('quality_score', $post),
                'when'              => $execution_timestamp,
                'opportunita'       => get_field('numero_oppurtunita', $post),
                'quick_wins'        => get_field('quick_wins', $post),
                'punti_di_forza'    => get_field('punti_di_forza', $post),
                'debolezze'         => get_field('debolezze', $post),
                'gap_rilevati'      => get_field('gap_rilevati', $post),
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
    $confidence_score = esc_html($data['confidence_score']);
    $quality_score = esc_html($data['quality_score']);

    $rows = [
        ['label' => 'Opportunità',   'icon' => '<span class="dashicons dashicons-thumbs-up"></span>',    'val' => $data['opportunita'] ?? 0],
        ['label' => 'Quick Wins',    'icon' => '<span class="dashicons dashicons-star-filled"></span>',          'val' => $data['quick_wins'] ?? 0],
        ['label' => 'Punti di forza','icon' => '<span class="dashicons dashicons-awards"></span>',       'val' => $data['punti_di_forza'] ?? 0],
        ['label' => 'Debolezze',     'icon' => '<span class="dashicons dashicons-warning"></span>',      'val' => $data['debolezze'] ?? 0],
        ['label' => 'Gap rilevati',  'icon' => '<span class="dashicons dashicons-thumbs-down"></span>',  'val' => $data['gap_rilevati'] ?? 0],
    ];

    $rows_html = '';
    foreach ($rows as $r) {
        $val = esc_html((string) (isset($r['val']) ? $r['val'] : 0));
        $rows_html .= '<div class="d-flex align-items-center justify-content-between gap-2">'
                    .    '<span class="me-2">'.$r['icon'].' '.esc_html($r['label']).'</span>'
                    .    '<strong class="ms-2">'.$val.'</strong>'
                    . '</div>';
    }

    return '<div>'
         .    '<div class="fw-semibold">'.$title.'</div>'
         .    '<div class="small">'.$when.'</div>'
         .    '<hr>'
         .    $rows_html
         .    '<hr>'
         .    '<div class="small">Quality score: <span class="badge">'.$quality_score.'</span></div>'
         .    '<div class="small">Confidence score: <span class="badge">'.$confidence_score.'</span></div>'
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

    $bgcolor = get_heatmap_color((int) $data['confidence_score']);

    // Tooltip: per sicurezza uso apici esterni e converto gli apici singoli interni
    $tooltip_html = perspect_build_tooltip_html($data);
    $tooltip_attr = str_replace("'", '&#039;', $tooltip_html); // evita rotture dell'attributo

    // Contenuto visibile nella cella: solo primo e ultimo valore, come richiesto
    $vis_oppo = esc_html((string) ($data['opportunita'] ?? '—'));
    $vis_gap  = esc_html((string) ($data['gap_rilevati'] ?? '—'));

    $html  = '<td class="agent-score"'
          .       ' style="background-color:'.$bgcolor.'"'
          .       ' data-bs-toggle="popover" data-bs-html="true"'
          //.       ' data-bs-title="Titolo"'
          .       ' data-bs-content=\''.$tooltip_attr.'\'>';
    $html .=     '<span class="dashicons dashicons-thumbs-up"></span> '.$vis_oppo;
    $html .=     ' <br>';
    $html .=     '<span class="dashicons dashicons-thumbs-down"></span> '.$vis_gap;
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

