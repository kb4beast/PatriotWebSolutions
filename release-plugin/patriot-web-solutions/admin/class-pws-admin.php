<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PWS_Admin
{
    public static function register(): void
    {
        add_action('admin_menu', array(__CLASS__, 'menu'));
        add_action('admin_notices', array(__CLASS__, 'notice'));
        add_action('admin_post_pws_apply_release', array(__CLASS__, 'apply'));
        add_action('admin_post_pws_rollback_release', array(__CLASS__, 'rollback'));
        add_action('admin_post_pws_save_release_settings', array(__CLASS__, 'save_settings'));
    }

    public static function menu(): void
    {
        add_management_page('Patriot site release', 'Patriot site release', 'manage_options', 'pws-release', array(__CLASS__, 'page'));
    }

    public static function notice(): void
    {
        if (get_option('pws_release_needs_setup') !== '1' || !current_user_can('manage_options')) {
            return;
        }
        echo '<div class="notice notice-info"><p><strong>Patriot Web Solutions release installed.</strong> Nothing public changed. <a href="' . esc_url(admin_url('tools.php?page=pws-release')) . '">Run preflight and review the migration</a>.</p></div>';
    }

    public static function page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        $preflight = PWS_Installer::preflight();
        $recovery_pending = PWS_Installer::recovery_pending();
        $result = get_transient('pws_release_admin_result_' . get_current_user_id());
        delete_transient('pws_release_admin_result_' . get_current_user_id());
        ?>
        <div class="wrap"><h1>Patriot Web Solutions site release</h1>
            <p>This installer leaves the current site unchanged until you apply it. Use staging first and take a current files-and-database backup.</p>
            <?php if ($result) : ?><div class="notice notice-<?php echo !empty($result['error']) ? 'error' : 'success'; ?>"><p><?php echo esc_html($result['message']); ?></p></div><?php endif; ?>
            <?php if ($recovery_pending) : ?><div class="notice notice-error inline"><p>An earlier apply did not finish. Run the scoped rollback below to reconcile its durable checkpoint before starting a new apply.</p></div><?php endif; ?>
            <h2>Preflight</h2><table class="widefat striped"><tbody>
            <?php foreach ($preflight['checks'] as $check) : ?><tr><td><?php echo $check['pass'] ? '✓' : '×'; ?></td><th><?php echo esc_html($check['label']); ?></th><td><?php echo esc_html($check['detail']); ?></td></tr><?php endforeach; ?>
            </tbody></table>
            <?php if ($preflight['page_conflicts']) : ?><div class="notice notice-warning inline"><p>Existing pages need an explicit migration decision: <?php echo esc_html(implode(', ', $preflight['page_conflicts'])); ?>. Their current content will be captured for rollback and retained in WordPress revisions.</p></div><?php endif; ?>
            <?php if ($preflight['warning']) : ?><div class="notice notice-warning inline"><p><?php echo esc_html($preflight['warning']); ?></p></div><?php endif; ?>
            <h2>Integration settings</h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><?php wp_nonce_field('pws_save_release_settings'); ?><input type="hidden" name="action" value="pws_save_release_settings">
                <table class="form-table"><tr><th><label for="pws_give_form_id">GiveWP form ID</label></th><td><input class="regular-text" id="pws_give_form_id" name="pws_give_form_id" type="number" min="0" value="<?php echo esc_attr((string) get_option('pws_give_form_id', '')); ?>"><p class="description">Leave blank to use the first published GiveWP form. Save an ID when the site has more than one.</p></td></tr>
                <tr><th><label for="pws_form_recipient">Form recipient</label></th><td><input class="regular-text" id="pws_form_recipient" name="pws_form_recipient" type="email" value="<?php echo esc_attr((string) get_option('pws_form_recipient', get_option('admin_email'))); ?>"><p class="description">Used for learning-interest and general-contact messages. Send a test after saving.</p></td></tr></table>
                <?php submit_button('Save integration settings', 'secondary', 'submit', false); ?>
            </form>
            <h2>Apply on staging</h2><p>Creates missing pages, installs the bundled theme, creates a dedicated menu, sets the homepage, and enables reviewed redirects. GiveWP and WooCommerce data/settings are not changed.</p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><?php wp_nonce_field('pws_apply_release'); ?><input type="hidden" name="action" value="pws_apply_release">
                <?php if ($preflight['page_conflicts']) : ?><p><label><input type="checkbox" name="pws_replace_conflicts" value="1" required> Replace the listed pages with this release on staging. I understand their current content will be captured in the rollback snapshot and WordPress revision history.</label></p><?php endif; ?>
                <?php $apply_attributes = (!empty($preflight['blocking']) || $recovery_pending) ? array('disabled' => 'disabled') : array(); submit_button('Apply release', 'primary', 'submit', false, $apply_attributes); ?>
            </form>
            <h2>Scoped rollback</h2><p>Restores the previous theme, homepage and menu assignments. Replaced pages return to their captured fields when their release title, content, excerpt, status and menu order remain unmodified; pages created by this release move to Trash under the same condition. Donations, orders, accounts and form settings are preserved.</p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><?php wp_nonce_field('pws_rollback_release'); ?><input type="hidden" name="action" value="pws_rollback_release"><?php submit_button('Roll back release', 'secondary', 'submit', false); ?></form>
        </div><?php
    }

    public static function apply(): void
    {
        check_admin_referer('pws_apply_release');
        $replace_conflicts = isset($_POST['pws_replace_conflicts']) && sanitize_text_field(wp_unslash((string) $_POST['pws_replace_conflicts'])) === '1';
        $result = PWS_Installer::apply($replace_conflicts);
        self::finish($result, 'Release applied. Review every route and integration on staging before production.');
    }

    public static function rollback(): void
    {
        check_admin_referer('pws_rollback_release');
        $result = PWS_Installer::rollback();
        self::finish($result, 'Scoped rollback completed. Review any modified pages preserved for safety.');
    }

    public static function save_settings(): void
    {
        check_admin_referer('pws_save_release_settings');
        if (!current_user_can('manage_options')) {
            wp_die('Administrator permission is required.');
        }
        $form_id = absint($_POST['pws_give_form_id'] ?? 0);
        $recipient = sanitize_email((string) ($_POST['pws_form_recipient'] ?? ''));
        if ($recipient === '') {
            self::finish(new WP_Error('pws_invalid_recipient', 'Enter a valid form recipient email address.'), '');
        }
        update_option('pws_give_form_id', $form_id, false);
        update_option('pws_form_recipient', $recipient, false);
        self::finish(array('status' => 'saved'), 'Integration settings saved. Test mail and donations on staging.');
    }

    private static function finish($result, string $success): void
    {
        $payload = is_wp_error($result) ? array('error' => true, 'message' => $result->get_error_message()) : array('error' => false, 'message' => $success);
        set_transient('pws_release_admin_result_' . get_current_user_id(), $payload, MINUTE_IN_SECONDS);
        wp_safe_redirect(admin_url('tools.php?page=pws-release'));
        exit;
    }
}
