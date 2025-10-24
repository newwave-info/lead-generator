<?php get_header(); ?>

<main id="page" role="main">
    <div class="container-fluid">
        <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <div class="row">
                    <div class="col">
                        <h1 class="title"><?php the_title(); ?></h1>
                        <div class="text"><?php the_content(); ?></div>
                    </div><!--col-->
                </div><!--row-->
            <?php endwhile; ?>
        <?php endif; ?>
    </div><!--container-->
</main><!--page-->

<?php get_footer(); ?>