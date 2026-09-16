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
            return '<div class="pws-donation-embed">' . do_shortcode('[give_form id="' . $form_id . '"]') . '</div>';
        }
        return '<div class="pws-notice"><h3>No online donations today</h3><p>This page has no live donation form. To give or ask a question now, email <a href="mailto:support@patriotwebsolutions.org">support@patriotwebsolutions.org</a>.</p></div>';
    }

    public static function project_catalog(): string
    {
        $decoded = json_decode((string) file_get_contents(PWS_RELEASE_DIR . 'payload/projects.json'), true);
        $projects = is_array($decoded['projects'] ?? null) ? $decoded['projects'] : array();
        ob_start(); ?>
        <div class="pws-records">
            <?php foreach ($projects as $project) : ?>
                <article class="pws-record" id="<?php echo esc_attr((string) ($project['slug'] ?? '')); ?>">
                    <p class="pws-chip pws-chip--<?php echo esc_attr((string) ($project['state'] ?? 'development')); ?>"><?php echo esc_html((string) ($project['state_label'] ?? '')); ?> — <?php echo esc_html((string) ($project['checked_label'] ?? '')); ?></p>
                    <h3><a href="<?php echo esc_url(home_url('/our-work/' . (string) ($project['slug'] ?? '') . '/')); ?>"><?php echo esc_html((string) ($project['name'] ?? '')); ?></a></h3>
                    <p><?php echo esc_html((string) ($project['summary'] ?? '')); ?></p>
                    <?php if (($project['state'] ?? '') === 'public') : ?>
                        <?php foreach (($project['links'] ?? array()) as $link) : ?>
                            <p class="pws-record__link"><a href="<?php echo esc_url((string) ($link['url'] ?? '')); ?>" rel="external noopener"><?php echo esc_html((string) ($link['label'] ?? '')); ?> (external)</a></p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
        <?php return (string) ob_get_clean();
    }

    public static function hero_image(): string
    {
        $src = get_theme_file_uri('assets/images/ai-learning-workshop.png');
        return '<figure class="pws-hero__media"><img src="' . esc_url($src) . '" width="1536" height="1024" alt="Illustration of adults learning practical AI skills together in a community workshop"><figcaption>Illustrative scene. We use real participant images only with permission.</figcaption></figure>';
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
