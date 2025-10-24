<?php get_header();

// Array: 'field_key' => ['label' => 'Etichetta', 'name' => 'field_name', 'type' => 'tipo']
$campi_analisi = [
    'field_68f14770ae94f' => [
        'label' => 'Riassunto',
        'name' => 'core_promise',
        'type' => 'textarea'
    ],
    'field_insights_summary' => [
        'label' => 'Sintesi Insights',
        'name' => 'insights_summary',
        'type' => 'textarea'
    ],
    'field_quality_score' => [
        'label' => 'Quality Score',
        'name' => 'quality_score',
        'type' => 'number'
    ],
    'field_confidence_score' => [
        'label' => 'Confidence Score',
        'name' => 'confidence_score',
        'type' => 'number'
    ],
    'field_68f14878ae954' => [
        'label' => 'Core promise',
        'name' => 'riassunto_esecutivo',
        'type' => 'textarea'
    ],
    'field_gaps_weaknesses' => [
        'label' => 'Gap e Debolezze',
        'name' => 'gaps_weaknesses',
        'type' => 'wysiwyg'
    ],
    'field_68f14f5bd883d' => [
        'label' => 'Debolezze',
        'name' => 'debolezze_html',
        'type' => 'wysiwyg'
    ],
    'field_68f14f4ad883c' => [
        'label' => 'Punti di forza',
        'name' => 'punti_di_forza_html',
        'type' => 'wysiwyg'
    ],
    'field_opportunities_identified' => [
        'label' => 'Opportunità Identificate',
        'name' => 'opportunities_identified',
        'type' => 'wysiwyg'
    ],
    'field_68f148bbae955' => [
        'label' => 'Azioni rapide',
        'name' => 'azioni_rapide',
        'type' => 'wysiwyg'
    ],
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

            <?php foreach ($campi_analisi as $field_key => $campo):
                $valore = get_field($campo['name']);

                if (!$valore) continue;

                switch ($campo['type']) {
                    case 'post_object':
                        $output = get_the_title($valore);
                        break;
                    case 'wysiwyg':
                        $output = $valore;
                        break;
                    case 'number':
                        $output = number_format($valore, 0, ',', '.');
                        break;
                    case 'textarea':
                    default:
                        $output = nl2br(esc_html($valore));
                        break;
                }
                ?>
                <div class="box">
                    <div class="text">
                        <h2 class="title"><?php echo esc_html($campo['label']); ?></h2><?php echo $output; ?>
                    </div>
                </div>
                <hr class="my-4">
            <?php endforeach; ?>

        </div>
    </section>
</main>

<?php get_footer(); ?>