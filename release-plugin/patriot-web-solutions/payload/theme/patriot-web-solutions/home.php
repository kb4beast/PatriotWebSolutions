<?php get_header(); ?>
<main id="main" class="pws-shell pws-page">
    <header class="pws-prose"><p class="pws-kicker">Field notes</p><h1>What we are learning and building.</h1><p class="pws-lede">Dated updates from the classroom, workshop, and project bench.</p></header>
    <div class="pws-posts">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <article><p class="pws-kicker"><?php echo esc_html(get_the_date()); ?></p><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 28)); ?></p></article>
    <?php endwhile; the_posts_pagination(); else : ?>
        <aside class="pws-callout"><h2>Updates are being prepared</h2><p>We will publish the first field note when its author, sources, project status, and permissions are ready.</p></aside>
    <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>

