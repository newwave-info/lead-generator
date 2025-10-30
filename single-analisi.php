<?php get_header();

$analysis_fields = [
    ['label' => 'Riassunto',             'name' => 'riassunto',                       'type' => 'textarea'],
    ['label' => 'Punti di forza',        'name' => 'punti_di_forza',                  'type' => 'textarea'],
    ['label' => 'Punti di debolezza',    'name' => 'punti_di_debolezza',              'type' => 'textarea'],
    ['label' => 'Opportunità',           'name' => 'opportunita',                     'type' => 'textarea'],
    ['label' => 'Azioni rapide',         'name' => 'azioni_rapide',                   'type' => 'textarea'],
    ['label' => 'Analisi approfondita',  'name' => 'analisy_perplexity_deep_research','type' => 'wysiwyg'],
    ['label' => 'Tot. punti di forza',   'name' => 'numero_punti_di_forza',           'type' => 'number'],
    ['label' => 'Tot. punti di debolezza','name' => 'numero_punti_di_debolezza',      'type' => 'number'],
    ['label' => 'Tot. opportunità',      'name' => 'numero_opportunita',              'type' => 'number'],
    ['label' => 'Tot. azioni rapide',    'name' => 'numero_azioni_rapide',            'type' => 'number'],
    ['label' => 'Voto qualità analisi',  'name' => 'voto_qualita_analisi',            'type' => 'number'],
]; ?>

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

            <?php foreach ($analysis_fields as $field):
                $value = get_field($field['name']);

                if ($value === null || $value === '' || $value === []) {
                    continue;
                }

                switch ($field['type']) {
                    case 'wysiwyg':
                        $output = wp_kses_post($value);
                        break;
                    case 'number':
                        $output = is_numeric($value)
                            ? esc_html(number_format((float) $value, 0, ',', '.'))
                            : esc_html((string) $value);
                        break;
                    case 'textarea':
                    default:
                        $output = wp_kses_post(wpautop((string) $value));
                        break;
                } ?>
                <div class="box">
                    <div class="text">
                        <h2 class="title"><?php echo esc_html($field['label']); ?></h2><?php echo $output; ?>
                    </div>
                </div>
                <hr class="my-4">
            <?php endforeach; ?>

        </div>
    </section>
</main>

<?php get_footer(); ?>
