<?php get_header(); ?>


<section id="hero">
    <section class="container-fluid">
        <section class="row">
            <section class="col">
                <h1 class="hero-title">Perspect Lead Generator</h1>
            </section><!--col-->
        </section><!--row-->
    </section><!--container-fluid-->
</section>

<main id="archive_analisi" role="main">
    <div class="container-fluid">
        <?php if ( have_posts() ) : ?>
            <table id="analysesTable" class="perspect-table hover cell-border compact stripe" data-page-length='100'>
                <thead>
                    <tr>
                        <th>Analisi</th>
                        <th>Tipo di Analisi</th>
                        <th>Esecuzione</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ( have_posts() ) : the_post(); ?>
                        <tr>
                            <td class="company-name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></td>
                            <td>
                                <?php
                                $terms = get_the_terms($post->ID, 'agent_type');
                                echo $terms ? $terms[0]->name : ''; ?>
                            </td>
                            <td><?php echo get_the_date( 'Y-m-d H:i' ) ?></td>
                            <td><a href="javascript:void(0)" class="analysis-link" data-text="✓ Run" onclick="alert('Coming soon!')"></a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div><!--container-->
</main><!--archive_analisi-->

<?php get_footer(); ?>