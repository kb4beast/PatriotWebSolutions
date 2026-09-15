<?php get_header(); ?>
<main id="main" class="pws-shell pws-page">
<?php while (have_posts()) : the_post(); ?>
    <article class="pws-prose"><?php the_content(); ?></article>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>

