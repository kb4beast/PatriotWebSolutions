<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PWS_Public
{
    public static function register(): void
    {
        add_shortcode('pws_donation', array(__CLASS__, 'donation'));
        add_shortcode('pws_project_catalog', array(__CLASS__, 'project_catalog'));
        add_shortcode('pws_hero_image', array(__CLASS__, 'hero_image'));
        add_shortcode('pws_icon', array(__CLASS__, 'icon'));
        add_shortcode('pws_fact', array(__CLASS__, 'fact'));
        add_shortcode('pws_founder_note', array(__CLASS__, 'founder_note'));
        add_action('template_redirect', array(__CLASS__, 'redirect_legacy_routes'), 1);
        add_action('send_headers', array(__CLASS__, 'security_headers'));
        add_filter('document_title_separator', static fn(): string => '—');
    }

    public static function find_give_form_id(): int
    {
        if (!post_type_exists('give_forms') || !shortcode_exists('give_form')) {
            return 0;
        }
        $configured = (int) get_option('pws_give_form_id');
        if ($configured > 0 && get_post_type($configured) === 'give_forms' && get_post_status($configured) === 'publish') {
            return $configured;
        }
        $forms = get_posts(array('post_type' => 'give_forms', 'post_status' => 'publish', 'numberposts' => 1, 'fields' => 'ids'));
        return isset($forms[0]) ? (int) $forms[0] : 0;
    }

    public static function donation(): string
    {
        $form_id = self::find_give_form_id();
        if ($form_id > 0 && shortcode_exists('give_form')) {
            return '<div class="card pws-donation-embed">' . do_shortcode('[give_form id="' . $form_id . '"]') . '</div>';
        }
        return '<aside class="card pws-notice" aria-label="Donation status"><span class="tag tag-neutral">No online donations today</span><h3>This page has no live donation form.</h3><p>To give or ask a question now, email <a href="mailto:support@patriotwebsolutions.org">support@patriotwebsolutions.org</a>. The payment processor and receipt language are under review.</p></aside>';
    }

    /** [pws_icon name="check"] — inline Phosphor-style SVG from the theme's icon set. */
    public static function icon($atts): string
    {
        $atts = shortcode_atts(array('name' => '', 'class' => ''), (array) $atts, 'pws_icon');
        return function_exists('pws_icon') ? pws_icon(sanitize_key((string) $atts['name']), sanitize_html_class((string) $atts['class'])) : '';
    }

    /**
     * [pws_fact key="class_time_ct" prefix="" suffix="" fallback=""] — renders a confirmed owner fact from payload/facts.json,
     * or the fallback sentence when the fact is absent. Both branches render complete visitor copy.
     */
    public static function fact($atts): string
    {
        $atts = shortcode_atts(array('key' => '', 'prefix' => '', 'suffix' => '', 'fallback' => ''), (array) $atts, 'pws_fact');
        $key = sanitize_key((string) $atts['key']);
        if ($key !== '' && PWS_Facts::has($key)) {
            return esc_html((string) $atts['prefix'] . PWS_Facts::get($key) . (string) $atts['suffix']);
        }
        return esc_html((string) $atts['fallback']);
    }

    /** [pws_founder_note] — a signed note, rendered only once founder_note (and optionally founder_name) are confirmed facts. */
    public static function founder_note(): string
    {
        if (!PWS_Facts::has('founder_note')) {
            return '';
        }
        $by = PWS_Facts::has('founder_name') ? '<figcaption>— ' . esc_html((string) PWS_Facts::get('founder_name')) . '</figcaption>' : '';
        return '<section class="pws-wrap pws-section"><p class="pws-kicker">A note from the founder</p><figure class="pws-quote"><blockquote>“' . esc_html((string) PWS_Facts::get('founder_note')) . '”</blockquote>' . $by . '</figure></section>';
    }

    public static function project_catalog(): string
    {
        $decoded = json_decode((string) file_get_contents(PWS_RELEASE_DIR . 'payload/projects.json'), true);
        $projects = is_array($decoded['projects'] ?? null) ? $decoded['projects'] : array();
        $tags = array('public' => 'tag-accent', 'historical' => 'tag-neutral', 'development' => 'tag-outline');
        ob_start(); ?>
        <ul class="pws-records">
            <?php foreach ($projects as $project) : $state = (string) ($project['state'] ?? 'development'); ?>
                <li class="pws-record" id="<?php echo esc_attr((string) ($project['slug'] ?? '')); ?>">
                    <div><span class="tag <?php echo esc_attr($tags[$state] ?? 'tag-outline'); ?>"><?php echo esc_html((string) ($project['state_label'] ?? '')); ?></span><h2><a href="<?php echo esc_url(home_url('/our-work/' . (string) ($project['slug'] ?? '') . '/')); ?>"><?php echo esc_html((string) ($project['name'] ?? '')); ?></a></h2><p class="pws-record__checked"><?php echo esc_html(ucfirst((string) ($project['checked_label'] ?? ''))); ?></p></div>
                    <div class="pws-record__body"><p><?php echo esc_html((string) ($project['summary'] ?? '')); ?></p>
                    <?php if ($state === 'public') : foreach (($project['links'] ?? array()) as $link) : ?>
                        <p><a href="<?php echo esc_url((string) ($link['url'] ?? '')); ?>" rel="external noopener"><?php echo esc_html((string) ($link['label'] ?? '')); ?> <?php echo function_exists('pws_icon') ? pws_icon('arrow-up-right') : ''; ?><span class="screen-reader-text"> (external)</span></a></p>
                    <?php endforeach; endif; ?>
                    <p><a href="<?php echo esc_url(home_url('/our-work/' . (string) ($project['slug'] ?? '') . '/')); ?>">Read the record <?php echo function_exists('pws_icon') ? pws_icon('arrow-right') : ''; ?></a></p></div>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php return (string) ob_get_clean();
    }

    /** The hero shows a real class photograph only when the owner has confirmed consent (fact hero_photo = image URL). Otherwise nothing renders. */
    public static function hero_image(): string
    {
        if (!PWS_Facts::has('hero_photo')) {
            return '';
        }
        $src = esc_url((string) PWS_Facts::get('hero_photo'));
        if ($src === '') {
            return '';
        }
        $alt = PWS_Facts::has('hero_photo_alt') ? (string) PWS_Facts::get('hero_photo_alt') : 'Learners in a Patriot Web Solutions class';
        return '<figure class="pws-hero__photo lighten"><img src="' . $src . '" alt="' . esc_attr($alt) . '" loading="eager"><figcaption>Photograph published with the written consent of everyone shown.</figcaption></figure>';
    }

    public static function redirect_legacy_routes(): void
    {
        if (is_admin() || wp_doing_ajax() || get_option('pws_release_redirects_enabled') !== '1') {
            return;
        }
        $path = trailingslashit((string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
        $map = json_decode((string) file_get_contents(PWS_RELEASE_DIR . 'payload/redirects.json'), true);
        $redirects = is_array($map['redirects'] ?? null) ? $map['redirects'] : array();
        if (isset($redirects[$path])) {
            wp_safe_redirect(home_url($redirects[$path]), 301);
            exit;
        }
        $gone = is_array($map['gone'] ?? null) ? $map['gone'] : array();
        if (in_array($path, $gone, true)) {
            status_header(410);
            nocache_headers();
            include get_query_template('404');
            exit;
        }
    }

    public static function security_headers(): void
    {
        if (headers_sent()) {
            return;
        }
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header('X-Frame-Options: SAMEORIGIN');
    }
}
