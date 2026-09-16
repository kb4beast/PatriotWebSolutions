<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="pws-skip" href="#main">Skip to main content</a>
<header class="pws-header">
    <div class="pws-wrap pws-header__inner">
        <a class="pws-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Patriot Web Solutions home"><span class="pws-brand__mark" aria-hidden="true">PW</span><span class="pws-brand__words">Patriot <span>Web Solutions</span></span></a>
        <button class="btn btn-secondary btn-icon pws-menu-toggle" type="button" aria-expanded="false" aria-controls="primary-menu"><?php echo pws_icon('list', 'pws-icon--list') . pws_icon('x', 'pws-icon--x'); ?><span class="screen-reader-text">Menu</span></button>
        <nav class="pws-nav" id="primary-menu" aria-label="Primary navigation">
            <?php wp_nav_menu(array('theme_location' => 'primary', 'container' => false, 'menu_class' => 'pws-nav__list', 'fallback_cb' => 'pws_primary_fallback', 'depth' => 1)); ?>
            <div class="pws-nav__actions"><a class="btn btn-secondary" href="<?php echo esc_url(home_url('/join/')); ?>">Join the list</a><a class="btn btn-primary" href="<?php echo esc_url(home_url('/donate/')); ?>">Donate</a></div>
        </nav>
    </div>
</header>
