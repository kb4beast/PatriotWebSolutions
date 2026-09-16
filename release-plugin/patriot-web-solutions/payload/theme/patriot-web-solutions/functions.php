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
        'home' => 'Patriot Web Solutions teaches military members, veterans, and their families in Killeen, Texas practical AI: live human instruction, AI-tailored practice, and a train-to-standard policy where no one gets left behind.',
        'learn' => 'The learning path: Monday, Wednesday, and Friday live hours, a six-week foundations syllabus, and a repeat-until-understood readiness standard.',
        'join' => 'Join the interest list for live, patient AI classes for military members, veterans, and their families.',
        'our-work' => 'Project records with evidence states and checked dates: Hive Mind OS, AI Developer Workbench, and Coupon Hive.',
        'hive-mind-os' => 'Hive Mind OS is our public, MIT-licensed verification framework for AI coding agents. Read the code and check our claims.',
        'ai-developer-workbench' => 'The record of AI Developer Workbench, an earlier custom-GPT project, with its evidence state stated plainly.',
        'coupon-hive' => 'The development record for Coupon Hive, a skills-based savings workflow, with its current status dated on the record.',
        'solutions' => 'Carefully scoped custom AI tools, workflows, agents, and skills — every engagement starts with discovery and ships with tests, documentation, and limits.',
        'impact' => 'Our accountability ledger: what we can show today, with sources and dates, and what we will not claim yet.',
        'donate' => 'Support live AI instruction for military members, veterans, and their families. Public records and policies are linked before you give.',
        'about' => 'The Patriot Web Solutions record: how we work, the organization listing, and dated public third-party sources.',
        'get-involved' => 'Partner, volunteer, or hire: how employers, mentors, and community organizations can work with us.',
        'contact' => 'Contact Patriot Web Solutions about classes, donations, volunteering, custom builds, employer partnerships, or accessibility.',
        'stories' => 'Field notes: dated, reviewed working notes from our classes and builds.',
        'privacy' => 'The Patriot Web Solutions privacy notice.',
        'terms' => 'The Patriot Web Solutions terms of use.',
        'accessibility' => 'Our accessibility commitments, current conformance work, and how to request help or report a barrier.',
        'learner-code' => 'The learning community agreement: patience, honest evaluation, and respect for every service relationship.',
    );
    $slug = is_front_page() ? 'home' : (string) get_post_field('post_name', get_queried_object_id());
    $description = $descriptions[$slug] ?? 'Patriot Web Solutions teaches military members, veterans, and their families to use, build, and evaluate AI tools.';
    if (!has_site_icon()) {
        // Theme-level fallback only; never writes the site_icon option, so an owner-configured icon always wins.
        echo '<link rel="icon" href="' . esc_url(get_theme_file_uri('assets/images/logo.png')) . '" type="image/png">' . "\n";
    }
    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:site_name" content="Patriot Web Solutions">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr(wp_get_document_title()) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:image" content="' . esc_url(get_theme_file_uri('assets/images/og-card.png')) . '">' . "\n";
    echo '<meta property="og:image:width" content="1200">' . "\n";
    echo '<meta property="og:image:height" content="630">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    $schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'NonprofitOrganization',
        'name' => 'Patriot Web Solutions',
        'url' => home_url('/'),
        'logo' => get_theme_file_uri('assets/images/logo.png'),
        'email' => 'support@patriotwebsolutions.org',
        'address' => array('@type' => 'PostalAddress', 'addressLocality' => 'Killeen', 'addressRegion' => 'TX', 'addressCountry' => 'US'),
        'areaServed' => 'United States',
        'sameAs' => array(
            'https://github.com/kb4beast/hive-mind-os',
            'https://projects.propublica.org/nonprofits/organizations/991238039',
            'https://app.candid.org/profile/15321808/patriot-web-solutions-99-1238039',
        ),
        'description' => 'Practical AI learning for military members, veterans, and their families.',
        'founder' => array('@type' => 'Person', 'name' => 'Brian Espinosa', 'jobTitle' => 'President and Founder'),
    );
    if (class_exists('PWS_Facts')) {
        if (PWS_Facts::has('street_address')) {
            $schema['address']['streetAddress'] = PWS_Facts::get('street_address');
        }
        if (PWS_Facts::get('org_status_confirmed') === 'true') {
            foreach (array('taxID' => 'org_tax_id', 'nonprofitStatus' => 'org_nonprofit_status', 'foundingDate' => 'founding_date') as $property => $fact_key) {
                if (PWS_Facts::has($fact_key)) {
                    $schema[$property] = PWS_Facts::get($fact_key);
                }
            }
        }
    }
    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}, 1);

function pws_primary_fallback(): void
{
    $items = array(
        'Learn' => '/learn/',
        'Work' => '/our-work/',
        'Solutions' => '/solutions/',
        'Impact' => '/impact/',
        'About' => '/about/',
        'Partners' => '/get-involved/',
    );
    echo '<ul class="pws-nav__list">';
    foreach ($items as $label => $path) {
        echo '<li><a href="' . esc_url(home_url($path)) . '">' . esc_html($label) . '</a></li>';
    }
    echo '</ul>';
}

/**
 * Inline Phosphor-style icon (MIT; see LICENSES.md). Returns '' for unknown names so templates never break.
 */
function pws_icon(string $name, string $class = ''): string
{
    static $paths = null;
    if ($paths === null) {
        $stroke = static fn(string $d): string => '<path d="' . $d . '" fill="none" stroke="currentColor" stroke-width="16" stroke-linecap="round" stroke-linejoin="round"/>';
        $paths = array(
            'arrow-right' => '<path d="M221.66,133.66l-72,72a8,8,0,0,1-11.32-11.32L196.69,136H40a8,8,0,0,1,0-16H196.69L138.34,61.66a8,8,0,0,1,11.32-11.32l72,72A8,8,0,0,1,221.66,133.66Z"/>',
            'calendar' => '<path d="M208,32H184V24a8,8,0,0,0-16,0v8H88V24a8,8,0,0,0-16,0v8H48A16,16,0,0,0,32,48V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V48A16,16,0,0,0,208,32ZM72,48v8a8,8,0,0,0,16,0V48h80v8a8,8,0,0,0,16,0V48h24V80H48V48ZM208,208H48V96H208V208Zm-96-88v64a8,8,0,0,1-16,0V132.94l-4.42,2.22a8,8,0,0,1-7.16-14.32l16-8A8,8,0,0,1,112,120Zm59.16,30.45L152,176h16a8,8,0,0,1,0,16H136a8,8,0,0,1-6.4-12.8l28.78-38.37A8,8,0,1,0,145.07,132a8,8,0,1,1-13.85-8A24,24,0,0,1,176,136,23.76,23.76,0,0,1,171.16,150.45Z"/>',
            'check' => $stroke('M216 72 104 184 40 120'),
            'x' => $stroke('M200 56 56 200M56 56l144 144'),
            'list' => $stroke('M40 128h176M40 64h176M40 192h176'),
            'arrow-up-right' => $stroke('M64 192 192 64M88 64h104v104'),
        );
    }
    if (!isset($paths[$name])) {
        return '';
    }
    $classes = trim('pws-icon ' . $class);
    return '<svg class="' . esc_attr($classes) . '" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true" focusable="false">' . $paths[$name] . '</svg>';
}
