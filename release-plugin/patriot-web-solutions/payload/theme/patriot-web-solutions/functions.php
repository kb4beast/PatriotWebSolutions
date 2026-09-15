<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('after_setup_theme', static function (): void {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'gallery', 'caption', 'style', 'script'));
    add_theme_support('responsive-embeds');
    register_nav_menus(array('primary' => __('Primary navigation', 'patriot-web-solutions')));
});

add_action('wp_enqueue_scripts', static function (): void {
    $version = wp_get_theme()->get('Version');
    wp_enqueue_style('pws-site', get_theme_file_uri('assets/css/site.css'), array(), $version);
    wp_enqueue_script('pws-site', get_theme_file_uri('assets/js/site.js'), array(), $version, true);
});

add_filter('body_class', static function (array $classes): array {
    $classes[] = 'pws-site';
    return $classes;
});

add_action('pre_get_posts', static function (WP_Query $query): void {
    if (!is_admin() && $query->is_main_query() && $query->is_home()) {
        $query->set('category_name', 'pws-field-notes');
    }
});

add_filter('wp_sitemaps_posts_query_args', static function (array $args, string $post_type): array {
    if ($post_type === 'post') {
        $args['category_name'] = 'pws-field-notes';
    }
    return $args;
}, 10, 2);

add_filter('wp_sitemaps_taxonomies_query_args', static function (array $args, string $taxonomy): array {
    if ($taxonomy !== 'category') {
        return $args;
    }
    $retired = get_terms(array(
        'taxonomy' => 'category',
        'slug' => array('general', 'blog'),
        'hide_empty' => false,
        'fields' => 'ids',
    ));
    if (!is_wp_error($retired) && $retired) {
        $args['exclude'] = array_values(array_unique(array_merge(array_map('intval', (array) ($args['exclude'] ?? array())), array_map('intval', $retired))));
    }
    return $args;
}, 10, 2);

add_filter('wp_sitemaps_add_provider', static function ($provider, string $name) {
    return $name === 'users' ? false : $provider;
}, 10, 2);

add_action('wp_head', static function (): void {
    if (is_admin()) {
        return;
    }
    $descriptions = array(
        'learn' => 'Practical AI learning for military members, veterans, and their families, taught three live hours each week at a mastery-based pace.',
        'join' => 'Join the interest list for patient, practical AI learning with Patriot Web Solutions.',
        'our-work' => 'Explore AI tools and workflows from Patriot Web Solutions with current project status and evidence boundaries.',
        'solutions' => 'Discuss carefully scoped custom AI tools, workflows, agents, skills, and integrations.',
        'impact' => 'See how Patriot Web Solutions will measure instruction, demonstrated skills, projects, and voluntary outcomes.',
        'donate' => 'Support patient, practical AI learning for military-connected people and their families.',
        'about' => 'Learn why Patriot Web Solutions is moving from website skills to practical, responsible AI learning.',
    );
    $slug = is_front_page() ? 'home' : (string) get_post_field('post_name', get_queried_object_id());
    $description = $descriptions[$slug] ?? 'Patriot Web Solutions helps military members, veterans, and their families learn to use, build, and evaluate AI tools.';
    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:site_name" content="Patriot Web Solutions">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    $schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'Patriot Web Solutions',
        'url' => home_url('/'),
        'email' => 'support@patriotwebsolutions.org',
        'telephone' => '+1-254-761-5991',
        'areaServed' => 'United States',
        'description' => 'Practical AI learning for military members, veterans, and their families.',
    );
    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}, 1);

function pws_primary_fallback(): void
{
    $items = array(
        'Learn' => '/learn/',
        'Our work' => '/our-work/',
        'Impact' => '/impact/',
        'About' => '/about/',
        'Get involved' => '/get-involved/',
    );
    echo '<ul class="pws-nav__list">';
    foreach ($items as $label => $path) {
        echo '<li><a href="' . esc_url(home_url($path)) . '">' . esc_html($label) . '</a></li>';
    }
    echo '</ul>';
}
