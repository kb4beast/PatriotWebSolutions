<?php get_header(); ?>
<main id="main" class="pws-wrap pws-page">
<?php while (have_posts()) : the_post(); ?>
    <?php if (function_exists('is_account_page') && is_account_page()) : ?><h1 class="pws-page__title"><?php the_title(); ?></h1><?php endif; ?>
    <article class="pws-prose"><?php the_content(); ?></article>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>
