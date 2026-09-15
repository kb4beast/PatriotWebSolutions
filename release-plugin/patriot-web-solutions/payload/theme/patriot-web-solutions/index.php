<?php get_header(); ?>
<main id="main" class="pws-shell pws-page"><div class="pws-posts">
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <article><p class="pws-kicker"><?php echo esc_html(get_the_date()); ?></p><h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1><div class="pws-prose"><?php the_content(); ?></div></article>
<?php endwhile; the_posts_pagination(); else : ?><h1>Nothing here yet.</h1><?php endif; ?>
</div></main>
<?php get_footer(); ?>

