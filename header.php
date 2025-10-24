<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, minimum-scale=1, viewport-fit=cover">
    <meta name="format-detection" content="telephone=no">
    <title><?php echo is_front_page() ? 'Home' : wp_title(''); ?> · <?php bloginfo('name'); ?></title>

    <?php wp_head(); ?>

    <meta name="author" content="Perspect - perspect.it">
    <meta name="Copyright" content="Copyright <?php echo date("Y"); ?>">

    <link rel="shortcut icon" href="<?php echo get_template_directory_uri() ?>/common/img/favicon.ico">
    <link rel="apple-touch-icon" href="<?php echo get_template_directory_uri() ?>/common/img/apple-touch-icon.png">

    <link rel="stylesheet" href="<?php echo get_template_directory_uri() ?>/common/css/main.css">

</head>

<body>


    <div id="loader" role="presentation" aria-hidden="true"> <div class="spinner"> <div class="rect1"></div> <div class="rect2"></div> <div class="rect3"></div> <div class="rect4"></div> <div class="rect5"></div> </div> </div><!--loader-->


    <header id="header" role="banner">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col me-auto">
                    <a id="brand" href="<?php echo get_home_url(); ?>" aria-label="<?php bloginfo('name'); ?>"><?php get_template_part( 'inc/brand' ); ?></a>
                </div><!--col-->

                <?php if ( has_nav_menu('primary') ) : ?>
                    <div class="col-auto d-none d-md-block pe-0">
                        <?php wp_nav_menu( array(
                            'theme_location'  => 'primary',
                            'depth'           => 1,
                            'container'       => 'nav',
                            'container_class' => '',
                            'container_id'    => 'header_nav',
                            'menu_class'      => '',
                        ) ); ?>
                    </div><!--col-->
                    <div class="col-auto d-md-none">
                        <button id="hamburger" class="hamburger" aria-label="Menu"><svg width="32" height="32" fill="none" viewBox="0 0 32 32" aria-hidden="true"><path d="M6 12H26" stroke="currentColor"/><path d="M6 20H26" stroke="currentColor"/></svg></button>
                    </div><!--col-->
                <?php endif; ?>
            </div><!--row-->
        </div><!--container-->
    </header>