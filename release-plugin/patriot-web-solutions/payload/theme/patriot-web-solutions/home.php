<?php get_header(); ?>
<main id="main" class="pws-shell pws-page">
    <?php $pws_posts_page = get_option('page_for_posts') ? get_post((int) get_option('page_for_posts')) : null; ?>
    <?php if ($pws_posts_page instanceof WP_Post && trim($pws_posts_page->post_content) !== '') : ?>
    <header class="pws-prose"><?php echo apply_filters('the_content', $pws_posts_page->post_content); ?></header>
    <?php else : ?>
    <header class="pws-prose"><p class="pws-kicker">Field notes</p><h1>Dated, reviewed working notes.</h1><p class="pws-lede">Short, dated notes from our classes and builds. Each note passes owner review before it appears in this list.</p></header>
    <?php endif; ?>
    <div class="pws-posts">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <article><p class="pws-kicker"><?php echo esc_html(get_the_date()); ?></p><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 28)); ?></p></article>
    <?php endwhile; the_posts_pagination(); else : ?>
        <aside class="pws-callout"><h2>No notes have passed review for this index</h2><p>Each note is published here after its author, sources, and permissions pass owner review. The drafting status above is current as of the page's stated date.</p></aside>
    <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>
