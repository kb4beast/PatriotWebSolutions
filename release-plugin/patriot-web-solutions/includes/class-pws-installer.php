<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PWS_Installer
{
    private const APPLIED_OPTION = 'pws_release_applied';
    private const ROLLBACK_OPTION = 'pws_release_rollback_v1';
    private const MENU_SLUG = 'patriot-web-solutions-primary';
    private const FIELD_NOTES_SLUG = 'pws-field-notes';

    public static function activate(): void
    {
        update_option('pws_release_needs_setup', '1', false);
    }

    public static function is_applied(): bool
    {
        $applied = get_option(self::APPLIED_OPTION);
        return is_array($applied) && ($applied['version'] ?? '') === PWS_RELEASE_VERSION;
    }

    public static function is_any_applied(): bool
    {
        $applied = get_option(self::APPLIED_OPTION);
        return is_array($applied) && !empty($applied['version']);
    }

    public static function applied_version(): string
    {
        $applied = get_option(self::APPLIED_OPTION);
        return is_array($applied) ? (string) ($applied['version'] ?? '') : '';
    }

    public static function recovery_pending(): bool
    {
        return !self::is_applied() && is_array(get_option(self::ROLLBACK_OPTION));
    }

    public static function content_manifest(): array
    {
        $path = PWS_RELEASE_DIR . 'payload/content.json';
        $decoded = json_decode((string) file_get_contents($path), true);
        return is_array($decoded) ? $decoded : array();
    }

    public static function preflight(): array
    {
        $manifest = self::content_manifest();
        $theme_target = WP_CONTENT_DIR . '/themes/patriot-web-solutions';
        $give_form_id = PWS_Public::find_give_form_id();
        $checks = array(
            array('label' => 'WordPress 6.5 or newer', 'pass' => version_compare(get_bloginfo('version'), '6.5', '>='), 'detail' => get_bloginfo('version')),
            array('label' => 'PHP 8.1 or newer', 'pass' => version_compare(PHP_VERSION, '8.1', '>='), 'detail' => PHP_VERSION),
            array('label' => 'Release content manifest', 'pass' => !empty($manifest['pages']) && ($manifest['version'] ?? '') === PWS_RELEASE_VERSION, 'detail' => $manifest['version'] ?? 'missing'),
            array('label' => 'Theme destination writable', 'pass' => is_dir($theme_target) ? is_writable($theme_target) : is_writable(WP_CONTENT_DIR . '/themes'), 'detail' => $theme_target),
            array('label' => 'Site contact recipient configured', 'pass' => (bool) is_email((string) get_option('pws_form_recipient', get_option('admin_email'))), 'detail' => (string) get_option('pws_form_recipient', get_option('admin_email'))),
            array('label' => 'Active GiveWP donation form detected', 'pass' => $give_form_id > 0, 'detail' => $give_form_id > 0 ? 'Published give_forms ID ' . $give_form_id : 'GiveWP and a published donation form were not both detected; the donation page will show a configuration notice'),
            array('label' => 'Permalink structure supports pages', 'pass' => (string) get_option('permalink_structure') !== '', 'detail' => (string) get_option('permalink_structure') ?: 'Plain permalinks'),
            array('label' => 'No earlier release version still applied', 'pass' => !self::is_any_applied() || self::is_applied(), 'detail' => self::is_any_applied() && !self::is_applied() ? 'Release ' . self::applied_version() . ' is applied. Roll it back first (Scoped rollback below); this ' . PWS_RELEASE_VERSION . ' apply then treats its pages as reviewed conflicts.' : 'Clear'),
            array('label' => 'Bundled theme directory free or upgradeable', 'pass' => self::theme_directory_state() !== 'conflict', 'detail' => self::theme_directory_detail()),
        );

        $page_conflicts = array();
        foreach (($manifest['pages'] ?? array()) as $page) {
            $existing = get_page_by_path(self::page_path($page), OBJECT, 'page');
            if ($existing && get_post_meta($existing->ID, '_pws_release_managed', true) !== PWS_RELEASE_VERSION) {
                $page_conflicts[] = '/' . self::page_path($page) . '/';
            }
        }

        return array(
            'checks' => $checks,
            'page_conflicts' => $page_conflicts,
            'blocking' => array_values(array_filter($checks, static fn(array $check): bool => !$check['pass'] && $check['label'] !== 'Active GiveWP donation form detected')),
            'warning' => $give_form_id === 0 ? 'GiveWP must be active with a published give_forms record before the donation route is production-ready.' : '',
        );
    }

    public static function apply(bool $replace_conflicts = false): array|WP_Error
    {
        if (!current_user_can('manage_options')) {
            return new WP_Error('pws_forbidden', 'Administrator permission is required.');
        }

        $already = get_option(self::APPLIED_OPTION);
        if (is_array($already) && ($already['version'] ?? '') === PWS_RELEASE_VERSION) {
            return array('status' => 'already-applied', 'pages' => $already['page_ids'] ?? array());
        }
        if (is_array(get_option(self::ROLLBACK_OPTION))) {
            return new WP_Error('pws_recovery_required', 'A prior apply did not complete or has not been rolled back. Run scoped rollback before applying again.');
        }

        $preflight = self::preflight();
        if (!empty($preflight['blocking'])) {
            return new WP_Error('pws_preflight_failed', 'Resolve the blocking preflight failures before applying the release.');
        }
        if (!empty($preflight['page_conflicts']) && !$replace_conflicts) {
            return new WP_Error('pws_page_conflicts', 'Review the existing page conflicts and explicitly authorize their replacement.');
        }

        $manifest = self::content_manifest();
        $snapshot = array(
            'version' => PWS_RELEASE_VERSION,
            'status' => 'applying',
            'created_at' => current_time('mysql', true),
            'previous_stylesheet' => get_stylesheet(),
            'previous_template' => get_template(),
            'show_on_front' => get_option('show_on_front'),
            'page_on_front' => (int) get_option('page_on_front'),
            'page_for_posts' => (int) get_option('page_for_posts'),
            'nav_menu_locations' => get_theme_mod('nav_menu_locations', array()),
            'created_page_ids' => array(),
            'created_page_states' => array(),
            'updated_pages' => array(),
            'pending_page' => array(),
            'created_note_ids' => array(),
            'created_note_states' => array(),
            'pending_note' => array(),
            'changed_blogname' => false,
            'previous_blogname' => '',
            'changed_blogdescription' => false,
            'previous_blogdescription' => '',
            'created_menu_id' => 0,
            'pending_menu' => false,
            'created_term_id' => 0,
            'pending_term' => false,
            'retired_theme_dir' => '',
        );
        self::checkpoint($snapshot);

        $theme_result = self::install_theme_payload($snapshot);
        if (is_wp_error($theme_result)) {
            if (empty($snapshot['retired_theme_dir'])) {
                delete_option(self::ROLLBACK_OPTION);
            }
            return $theme_result;
        }

        $field_notes = get_term_by('slug', self::FIELD_NOTES_SLUG, 'category');
        if (!$field_notes) {
            $snapshot['pending_term'] = true;
            self::checkpoint($snapshot);
            $term = wp_insert_term('Field Notes — reviewed', 'category', array('slug' => self::FIELD_NOTES_SLUG, 'description' => 'Posts approved for the public Patriot Web Solutions Field Notes archive.'));
            if (is_wp_error($term)) {
                self::rollback_snapshot($snapshot);
                return $term;
            }
            $snapshot['created_term_id'] = (int) $term['term_id'];
            $snapshot['pending_term'] = false;
            self::checkpoint($snapshot);
        }

        $page_ids = array();
        foreach (($manifest['pages'] ?? array()) as $page) {
            $content_path = PWS_RELEASE_DIR . 'payload/' . ltrim((string) ($page['source'] ?? ''), '/');
            if (!is_file($content_path)) {
                self::rollback_snapshot($snapshot);
                return new WP_Error('pws_content_missing', 'A required page content file is missing: ' . ($page['source'] ?? 'unknown'));
            }
            $parent_slug = (string) ($page['parent'] ?? '');
            if ($parent_slug !== '' && empty($page_ids[$parent_slug])) {
                self::rollback_snapshot($snapshot);
                return new WP_Error('pws_parent_missing', 'A child page is listed before its parent in the release manifest: /' . self::page_path($page) . '/');
            }
            $page_content = (string) file_get_contents($content_path);
            $planned = array(
                'post_title' => sanitize_text_field((string) $page['title']),
                'post_content' => wp_kses_post($page_content),
                'post_excerpt' => '',
                'post_status' => 'publish',
                'post_parent' => $parent_slug !== '' ? (int) $page_ids[$parent_slug] : 0,
                'menu_order' => (int) ($page['menu_order'] ?? 0),
            );
            $planned_hash = self::fingerprint_fields($planned);
            $existing = get_page_by_path(self::page_path($page), OBJECT, 'page');
            if ($existing) {
                $managed = get_post_meta($existing->ID, '_pws_release_managed', true) === PWS_RELEASE_VERSION;
                if (!$managed) {
                    if (!$replace_conflicts) {
                        self::rollback_snapshot($snapshot);
                        return new WP_Error('pws_page_conflict', 'Existing page requires explicit replacement approval: /' . $page['slug'] . '/');
                    }
                    $before = array(
                        'ID' => (int) $existing->ID,
                        'post_title' => (string) $existing->post_title,
                        'post_content' => (string) $existing->post_content,
                        'post_excerpt' => (string) $existing->post_excerpt,
                        'post_status' => (string) $existing->post_status,
                        'post_parent' => (int) $existing->post_parent,
                        'menu_order' => (int) $existing->menu_order,
                        'before_state_hash' => self::page_fingerprint($existing),
                        'release_state_hash' => $planned_hash,
                        'managed_meta' => (string) get_post_meta($existing->ID, '_pws_release_managed', true),
                        'hash_meta' => (string) get_post_meta($existing->ID, '_pws_release_content_hash', true),
                        'state_hash_meta' => (string) get_post_meta($existing->ID, '_pws_release_state_hash', true),
                    );
                    $snapshot['updated_pages'][] = $before;
                    self::checkpoint($snapshot);
                    wp_save_post_revision((int) $existing->ID);
                    $updated = wp_update_post(array_merge(array('ID' => (int) $existing->ID), $planned), true);
                    if (is_wp_error($updated)) {
                        self::rollback_snapshot($snapshot);
                        return $updated;
                    }
                    self::set_release_meta((int) $existing->ID, $page_content);
                } elseif ($existing->post_status !== 'publish') {
                    $snapshot['created_page_ids'][] = (int) $existing->ID;
                    $snapshot['created_page_states'][(string) $existing->ID] = $planned_hash;
                    self::checkpoint($snapshot);
                    wp_update_post(array_merge(array('ID' => (int) $existing->ID), $planned));
                    self::set_release_meta((int) $existing->ID, $page_content);
                }
                $page_ids[$page['slug']] = (int) $existing->ID;
                continue;
            }

            $snapshot['pending_page'] = array('slug' => (string) $page['slug'], 'path' => self::page_path($page), 'state_hash' => $planned_hash);
            self::checkpoint($snapshot);
            $page_id = wp_insert_post(array_merge(array('post_type' => 'page', 'post_name' => sanitize_title((string) $page['slug'])), $planned), true);
            if (is_wp_error($page_id)) {
                self::rollback_snapshot($snapshot);
                return $page_id;
            }
            $snapshot['created_page_ids'][] = (int) $page_id;
            $snapshot['created_page_states'][(string) $page_id] = $planned_hash;
            $snapshot['pending_page'] = array();
            self::checkpoint($snapshot);
            self::set_release_meta((int) $page_id, $page_content);
            $page_ids[$page['slug']] = (int) $page_id;
        }

        $field_notes = get_term_by('slug', self::FIELD_NOTES_SLUG, 'category');
        $field_notes_term_id = $field_notes ? (int) $field_notes->term_id : (int) $snapshot['created_term_id'];
        foreach (($manifest['notes'] ?? array()) as $note) {
            $note_path = PWS_RELEASE_DIR . 'payload/' . ltrim((string) ($note['source'] ?? ''), '/');
            if (!is_file($note_path)) {
                self::rollback_snapshot($snapshot);
                return new WP_Error('pws_content_missing', 'A required field-note file is missing: ' . ($note['source'] ?? 'unknown'));
            }
            $existing_note = get_posts(array('name' => sanitize_title((string) $note['slug']), 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => 1));
            if ($existing_note) {
                continue;
            }
            $note_content = (string) file_get_contents($note_path);
            $planned_note = array(
                'post_title' => sanitize_text_field((string) $note['title']),
                'post_content' => wp_kses_post($note_content),
                'post_excerpt' => '',
                'post_status' => 'draft',
                'post_parent' => 0,
                'menu_order' => 0,
            );
            $note_hash = self::fingerprint_fields($planned_note);
            $snapshot['pending_note'] = array('slug' => (string) $note['slug'], 'state_hash' => $note_hash);
            self::checkpoint($snapshot);
            $note_id = wp_insert_post(array_merge(array('post_type' => 'post', 'post_name' => sanitize_title((string) $note['slug']), 'post_category' => $field_notes_term_id > 0 ? array($field_notes_term_id) : array()), $planned_note), true);
            if (is_wp_error($note_id)) {
                self::rollback_snapshot($snapshot);
                return $note_id;
            }
            $snapshot['created_note_ids'][] = (int) $note_id;
            $snapshot['created_note_states'][(string) $note_id] = $note_hash;
            $snapshot['pending_note'] = array();
            self::checkpoint($snapshot);
        }

        $menu_id = wp_get_nav_menu_object(self::MENU_SLUG);
        if (!$menu_id) {
            $snapshot['pending_menu'] = true;
            self::checkpoint($snapshot);
            $menu_id = wp_create_nav_menu('Patriot Web Solutions Primary');
            if (is_wp_error($menu_id)) {
                self::rollback_snapshot($snapshot);
                return $menu_id;
            }
            $snapshot['created_menu_id'] = (int) $menu_id;
            $snapshot['pending_menu'] = false;
            self::checkpoint($snapshot);
            foreach (($manifest['primary_navigation'] ?? array()) as $slug) {
                if (isset($page_ids[$slug])) {
                    wp_update_nav_menu_item((int) $menu_id, 0, array(
                        'menu-item-object-id' => $page_ids[$slug],
                        'menu-item-object' => 'page',
                        'menu-item-type' => 'post_type',
                        'menu-item-status' => 'publish',
                    ));
                }
            }
        } else {
            $menu_id = (int) $menu_id->term_id;
        }

        switch_theme('patriot-web-solutions');
        set_theme_mod('nav_menu_locations', array('primary' => (int) $menu_id));
        update_option('show_on_front', 'page');
        update_option('page_on_front', $page_ids['home'] ?? 0);
        update_option('page_for_posts', $page_ids['stories'] ?? 0);
        self::maybe_brand_option('blogname', 'Patriot Web Solutions', array('My WordPress Website', 'My WordPress Site', ''), $snapshot);
        self::maybe_brand_option('blogdescription', 'Practical AI learning for military members, veterans, and their families', array('Just another WordPress site', ''), $snapshot);
        update_option('pws_release_redirects_enabled', '1', false);
        $snapshot['status'] = 'applied';
        self::checkpoint($snapshot);
        update_option(self::APPLIED_OPTION, array(
            'version' => PWS_RELEASE_VERSION,
            'applied_at' => current_time('mysql', true),
            'page_ids' => $page_ids,
            'theme' => 'patriot-web-solutions',
        ), false);
        delete_option('pws_release_needs_setup');
        flush_rewrite_rules(false);

        return array('status' => 'applied', 'pages' => $page_ids, 'give_form_id' => PWS_Public::find_give_form_id());
    }

    /**
     * 'absent' | 'current' (same version) | 'upgradeable' (an inactive earlier release of this theme) | 'conflict'.
     */
    private static function theme_directory_state(): string
    {
        $target = WP_CONTENT_DIR . '/themes/patriot-web-solutions';
        if (!is_dir($target)) {
            return 'absent';
        }
        $style = $target . '/style.css';
        $contents = is_file($style) ? (string) file_get_contents($style) : '';
        if (strpos($contents, 'Version: ' . PWS_RELEASE_VERSION) !== false) {
            return 'current';
        }
        $ours = strpos($contents, 'Text Domain: patriot-web-solutions') !== false && strpos($contents, 'Theme Name: Patriot Web Solutions') !== false;
        $inactive = get_stylesheet() !== 'patriot-web-solutions' && get_template() !== 'patriot-web-solutions';
        return ($ours && $inactive) ? 'upgradeable' : 'conflict';
    }

    private static function theme_directory_detail(): string
    {
        switch (self::theme_directory_state()) {
            case 'absent':
                return 'Will be installed at ' . WP_CONTENT_DIR . '/themes/patriot-web-solutions';
            case 'current':
                return 'Release theme ' . PWS_RELEASE_VERSION . ' already present';
            case 'upgradeable':
                return 'An earlier inactive release of this theme is present; apply renames it to a dated -retired- directory and installs ' . PWS_RELEASE_VERSION;
            default:
                return 'A different or active patriot-web-solutions theme directory exists. Roll back the earlier release or rename the directory before applying.';
        }
    }

    private static function install_theme_payload(array &$snapshot)
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        if (!WP_Filesystem()) {
            return new WP_Error('pws_filesystem_unavailable', 'WordPress could not initialize filesystem access for the bundled theme.');
        }
        global $wp_filesystem;
        $source = PWS_RELEASE_DIR . 'payload/theme/patriot-web-solutions';
        $target = WP_CONTENT_DIR . '/themes/patriot-web-solutions';
        $state = self::theme_directory_state();
        if ($state === 'current') {
            return true;
        }
        if ($state === 'conflict') {
            return new WP_Error('pws_theme_conflict', 'A different or active patriot-web-solutions theme directory already exists. Roll back the earlier release or rename the directory before applying.');
        }
        if ($state === 'upgradeable') {
            $retired = $target . '-retired-' . gmdate('Ymd-His');
            if (!$wp_filesystem->move($target, $retired)) {
                return new WP_Error('pws_theme_retire_failed', 'The earlier release theme directory could not be renamed. Rename it manually and apply again.');
            }
            $snapshot['retired_theme_dir'] = $retired;
            self::checkpoint($snapshot);
        }
        $result = copy_dir($source, $target);
        return is_wp_error($result) ? $result : true;
    }

    public static function rollback(): array|WP_Error
    {
        if (!current_user_can('manage_options')) {
            return new WP_Error('pws_forbidden', 'Administrator permission is required.');
        }
        $snapshot = get_option(self::ROLLBACK_OPTION);
        if (!is_array($snapshot)) {
            return new WP_Error('pws_no_snapshot', 'No release rollback snapshot exists.');
        }

        if (!empty($snapshot['previous_stylesheet'])) {
            switch_theme((string) $snapshot['previous_stylesheet']);
        }
        update_option('show_on_front', $snapshot['show_on_front'] ?? 'posts');
        update_option('page_on_front', (int) ($snapshot['page_on_front'] ?? 0));
        update_option('page_for_posts', (int) ($snapshot['page_for_posts'] ?? 0));
        set_theme_mod('nav_menu_locations', $snapshot['nav_menu_locations'] ?? array());
        foreach (array('blogname', 'blogdescription') as $option) {
            if (!empty($snapshot['changed_' . $option])) {
                update_option($option, (string) ($snapshot['previous_' . $option] ?? ''));
            }
        }

        $preserved = array();
        foreach (array_reverse(array_map('intval', $snapshot['created_page_ids'] ?? array())) as $page_id) {
            $page = get_post((int) $page_id);
            if (!$page) {
                continue;
            }
            $expected = (string) get_post_meta($page->ID, '_pws_release_state_hash', true);
            if ($expected === '') {
                $expected = (string) ($snapshot['created_page_states'][(string) $page->ID] ?? '');
            }
            if ($expected === '' || !hash_equals($expected, self::page_fingerprint($page))) {
                $preserved[] = (int) $page->ID;
                continue;
            }
            wp_trash_post((int) $page->ID);
        }
        self::recover_pending_page($snapshot, false, $preserved);

        foreach (array_reverse(array_map('intval', $snapshot['created_note_ids'] ?? array())) as $note_id) {
            $note = get_post($note_id);
            if (!$note) {
                continue;
            }
            $expected = (string) ($snapshot['created_note_states'][(string) $note_id] ?? '');
            if ($expected === '' || !hash_equals($expected, self::page_fingerprint($note))) {
                $preserved[] = $note_id;
                continue;
            }
            wp_trash_post($note_id);
        }
        self::recover_pending_note($snapshot, false, $preserved);

        foreach (($snapshot['updated_pages'] ?? array()) as $before) {
            $page = get_post((int) ($before['ID'] ?? 0));
            if (!$page) {
                continue;
            }
            $actual = self::page_fingerprint($page);
            $release = (string) ($before['release_state_hash'] ?? '');
            $original = (string) ($before['before_state_hash'] ?? '');
            if ($release !== '' && hash_equals($release, $actual)) {
                self::restore_page($before);
            } elseif ($original !== '' && hash_equals($original, $actual)) {
                self::restore_release_meta((int) $page->ID, $before);
            } else {
                $preserved[] = (int) $page->ID;
            }
        }
        self::remove_created_menu($snapshot);
        self::remove_created_term($snapshot);
        delete_option('pws_release_redirects_enabled');
        delete_option(self::APPLIED_OPTION);
        delete_option(self::ROLLBACK_OPTION);
        flush_rewrite_rules(false);
        return array('status' => 'rolled-back', 'preserved_modified_page_ids' => array_values(array_unique($preserved)), 'retired_theme_dir' => (string) ($snapshot['retired_theme_dir'] ?? ''));
    }

    private static function rollback_snapshot(array $snapshot): void
    {
        foreach (array_reverse(array_map('intval', $snapshot['created_page_ids'] ?? array())) as $page_id) {
            wp_delete_post((int) $page_id, true);
        }
        foreach (array_reverse(array_map('intval', $snapshot['created_note_ids'] ?? array())) as $note_id) {
            wp_delete_post($note_id, true);
        }
        $preserved = array();
        self::recover_pending_page($snapshot, true, $preserved);
        self::recover_pending_note($snapshot, true, $preserved);
        foreach (($snapshot['updated_pages'] ?? array()) as $before) {
            self::restore_page($before);
        }
        self::remove_created_menu($snapshot);
        self::remove_created_term($snapshot);
        delete_option(self::ROLLBACK_OPTION);
    }

    private static function recover_pending_page(array $snapshot, bool $force_delete, array &$preserved): void
    {
        $pending = $snapshot['pending_page'] ?? array();
        if (empty($pending['slug'])) {
            return;
        }
        $page = get_page_by_path((string) ($pending['path'] ?? $pending['slug']), OBJECT, 'page');
        if (!$page || in_array((int) $page->ID, array_map('intval', $snapshot['created_page_ids'] ?? array()), true)) {
            return;
        }
        $expected = (string) ($pending['state_hash'] ?? '');
        if (!$force_delete && ($expected === '' || !hash_equals($expected, self::page_fingerprint($page)))) {
            $preserved[] = (int) $page->ID;
            return;
        }
        $force_delete ? wp_delete_post((int) $page->ID, true) : wp_trash_post((int) $page->ID);
    }

    private static function recover_pending_note(array $snapshot, bool $force_delete, array &$preserved): void
    {
        $pending = $snapshot['pending_note'] ?? array();
        if (empty($pending['slug'])) {
            return;
        }
        $found = get_posts(array('name' => sanitize_title((string) $pending['slug']), 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => 1));
        if (!$found || in_array((int) $found[0]->ID, array_map('intval', $snapshot['created_note_ids'] ?? array()), true)) {
            return;
        }
        $note = $found[0];
        $expected = (string) ($pending['state_hash'] ?? '');
        if (!$force_delete && ($expected === '' || !hash_equals($expected, self::page_fingerprint($note)))) {
            $preserved[] = (int) $note->ID;
            return;
        }
        $force_delete ? wp_delete_post((int) $note->ID, true) : wp_trash_post((int) $note->ID);
    }

    private static function remove_created_menu(array $snapshot): void
    {
        $menu_id = (int) ($snapshot['created_menu_id'] ?? 0);
        if ($menu_id > 0) {
            wp_delete_nav_menu($menu_id);
        } elseif (!empty($snapshot['pending_menu'])) {
            $menu = wp_get_nav_menu_object(self::MENU_SLUG);
            if ($menu) {
                wp_delete_nav_menu((int) $menu->term_id);
            }
        }
    }

    private static function remove_created_term(array $snapshot): void
    {
        $term_id = (int) ($snapshot['created_term_id'] ?? 0);
        if ($term_id <= 0 && !empty($snapshot['pending_term'])) {
            $term = get_term_by('slug', self::FIELD_NOTES_SLUG, 'category');
            $term_id = $term ? (int) $term->term_id : 0;
        }
        if ($term_id > 0) {
            $term = get_term($term_id, 'category');
            if ($term && !is_wp_error($term) && (int) $term->count === 0) {
                wp_delete_term($term_id, 'category');
            }
        }
    }

    private static function set_release_meta(int $page_id, string $page_content): void
    {
        update_post_meta($page_id, '_pws_release_managed', PWS_RELEASE_VERSION);
        update_post_meta($page_id, '_pws_release_content_hash', hash('sha256', $page_content));
        update_post_meta($page_id, '_pws_release_state_hash', self::page_fingerprint(get_post($page_id)));
    }

    private static function restore_page(array $before): void
    {
        $page_id = (int) ($before['ID'] ?? 0);
        if ($page_id <= 0) {
            return;
        }
        wp_update_post(array(
            'ID' => $page_id,
            'post_title' => (string) ($before['post_title'] ?? ''),
            'post_content' => (string) ($before['post_content'] ?? ''),
            'post_excerpt' => (string) ($before['post_excerpt'] ?? ''),
            'post_status' => (string) ($before['post_status'] ?? 'draft'),
            'post_parent' => (int) ($before['post_parent'] ?? 0),
            'menu_order' => (int) ($before['menu_order'] ?? 0),
        ));
        self::restore_release_meta($page_id, $before);
    }

    private static function restore_release_meta(int $page_id, array $before): void
    {
        foreach (array('_pws_release_managed' => 'managed_meta', '_pws_release_content_hash' => 'hash_meta', '_pws_release_state_hash' => 'state_hash_meta') as $meta_key => $snapshot_key) {
            $value = (string) ($before[$snapshot_key] ?? '');
            $value === '' ? delete_post_meta($page_id, $meta_key) : update_post_meta($page_id, $meta_key, $value);
        }
    }

    private static function checkpoint(array $snapshot): void
    {
        update_option(self::ROLLBACK_OPTION, $snapshot, false);
    }

    private static function page_path(array $page): string
    {
        $parent = (string) ($page['parent'] ?? '');
        return ($parent !== '' ? $parent . '/' : '') . (string) ($page['slug'] ?? '');
    }

    private static function maybe_brand_option(string $option, string $value, array $defaults, array &$snapshot): void
    {
        $current = (string) get_option($option, '');
        if (!in_array(trim($current), $defaults, true)) {
            return;
        }
        $snapshot['previous_' . $option] = $current;
        $snapshot['changed_' . $option] = true;
        update_option($option, $value);
    }

    private static function page_fingerprint(?WP_Post $page): string
    {
        if (!$page) {
            return '';
        }
        return self::fingerprint_fields(array(
            'post_title' => (string) $page->post_title,
            'post_content' => (string) $page->post_content,
            'post_excerpt' => (string) $page->post_excerpt,
            'post_status' => (string) $page->post_status,
            'post_parent' => (int) $page->post_parent,
            'menu_order' => (int) $page->menu_order,
        ));
    }

    private static function fingerprint_fields(array $fields): string
    {
        return hash('sha256', (string) wp_json_encode(array(
            'post_title' => (string) ($fields['post_title'] ?? ''),
            'post_content' => (string) ($fields['post_content'] ?? ''),
            'post_excerpt' => (string) ($fields['post_excerpt'] ?? ''),
            'post_status' => (string) ($fields['post_status'] ?? ''),
            'post_parent' => (int) ($fields['post_parent'] ?? 0),
            'menu_order' => (int) ($fields['menu_order'] ?? 0),
        ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
