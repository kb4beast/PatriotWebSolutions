<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PWS_Forms
{
    public static function register(): void
    {
        add_shortcode('pws_interest_form', array(__CLASS__, 'interest_form'));
        add_shortcode('pws_contact_form', array(__CLASS__, 'contact_form'));
        add_action('admin_post_nopriv_pws_submit_form', array(__CLASS__, 'handle'));
        add_action('admin_post_pws_submit_form', array(__CLASS__, 'handle'));
    }

    public static function interest_form(): string
    {
        return self::render('interest');
    }

    public static function contact_form(): string
    {
        return self::render('contact');
    }

    private static function render(string $kind): string
    {
        $status = sanitize_key((string) ($_GET['form_status'] ?? ''));
        $message = '';
        if ($status === 'sent') {
            $message = '<div class="pws-notice pws-notice--success" role="status">Thank you. Your message was sent, and our team will follow up using the contact information you provided.</div>';
        } elseif ($status === 'error') {
            $message = '<div class="pws-notice pws-notice--error" role="alert">We could not send your message. Please email <a href="mailto:support@patriotwebsolutions.org">support@patriotwebsolutions.org</a> or call <a href="tel:+12547615991">(254) 761-5991</a>.</div>';
        }
        $is_interest = $kind === 'interest';
        ob_start();
        echo wp_kses_post($message);
        ?>
        <form class="pws-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <input type="hidden" name="action" value="pws_submit_form">
            <input type="hidden" name="pws_kind" value="<?php echo esc_attr($kind); ?>">
            <?php wp_nonce_field('pws_submit_' . $kind, 'pws_nonce'); ?>
            <div class="pws-honeypot" aria-hidden="true"><label>Leave this empty <input name="website" tabindex="-1" autocomplete="off"></label></div>
            <div class="pws-form__grid">
                <label>First name <input type="text" name="first_name" autocomplete="given-name" required maxlength="80"></label>
                <label>Last name <input type="text" name="last_name" autocomplete="family-name" required maxlength="80"></label>
                <label>Email <input type="email" name="email" autocomplete="email" required maxlength="190"></label>
                <label>Phone <span class="pws-optional">optional</span><input type="tel" name="phone" autocomplete="tel" maxlength="40"></label>
            </div>
            <?php if ($is_interest) : ?>
                <fieldset>
                    <legend>I am interested in</legend>
                    <label class="pws-check"><input type="checkbox" name="interest[]" value="AI foundations"> AI foundations</label>
                    <label class="pws-check"><input type="checkbox" name="interest[]" value="Skills and plugins"> Skills and plugins</label>
                    <label class="pws-check"><input type="checkbox" name="interest[]" value="APIs and workflows"> APIs and workflows</label>
                    <label class="pws-check"><input type="checkbox" name="interest[]" value="Agents and evaluation"> Agents and evaluation</label>
                </fieldset>
                <label>Military or family connection <span class="pws-optional">optional</span>
                    <select name="connection"><option value="">Prefer not to say</option><option>Service member</option><option>Veteran</option><option>Military family member</option><option>Supporter or community partner</option></select>
                </label>
            <?php else : ?>
                <label>What can we help with?
                    <select name="topic" required><option value="">Choose one</option><option>Learning cohorts</option><option>Donations</option><option>Custom AI solutions</option><option>Accessibility</option><option>Media or partnership</option><option>Something else</option></select>
                </label>
            <?php endif; ?>
            <label>Message <span class="pws-optional">optional</span><textarea name="message" rows="5" maxlength="2000"></textarea></label>
            <label class="pws-check"><input type="checkbox" name="consent" value="1" required> You may use these details to respond to this request. See our <a href="<?php echo esc_url(home_url('/privacy/')); ?>">privacy notice</a>.</label>
            <button class="pws-button" type="submit"><?php echo $is_interest ? 'Join the interest list' : 'Send message'; ?></button>
            <p class="pws-form__note">Do not send Social Security numbers, military IDs, medical information, passwords, or payment-card details.</p>
        </form>
        <?php
        return (string) ob_get_clean();
    }

    public static function handle(): void
    {
        $kind = sanitize_key((string) ($_POST['pws_kind'] ?? 'contact'));
        if (!in_array($kind, array('interest', 'contact'), true)) {
            self::redirect('error', '/contact/');
        }
        if (!isset($_POST['pws_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pws_nonce'])), 'pws_submit_' . $kind)) {
            self::redirect('error', $kind === 'interest' ? '/join/' : '/contact/');
        }
        if (!empty($_POST['website'])) {
            self::redirect('sent', $kind === 'interest' ? '/join/' : '/contact/');
        }
        $ip = sanitize_text_field((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        $rate_key = 'pws_form_' . hash('sha256', $ip . '|' . $kind);
        if ((int) get_transient($rate_key) >= 5) {
            self::redirect('error', $kind === 'interest' ? '/join/' : '/contact/');
        }
        set_transient($rate_key, (int) get_transient($rate_key) + 1, HOUR_IN_SECONDS);

        $first = sanitize_text_field((string) ($_POST['first_name'] ?? ''));
        $last = sanitize_text_field((string) ($_POST['last_name'] ?? ''));
        $email = sanitize_email((string) ($_POST['email'] ?? ''));
        $consent = !empty($_POST['consent']);
        if ($first === '' || $last === '' || !is_email($email) || !$consent) {
            self::redirect('error', $kind === 'interest' ? '/join/' : '/contact/');
        }

        $raw_interest = array_filter((array) ($_POST['interest'] ?? array()), 'is_scalar');
        $clean_interest = array_map(static fn($value): string => sanitize_text_field(wp_unslash((string) $value)), $raw_interest);
        $lines = array(
            'Request type: ' . $kind,
            'Name: ' . $first . ' ' . $last,
            'Email: ' . $email,
            'Phone: ' . sanitize_text_field((string) ($_POST['phone'] ?? '')),
            'Connection: ' . sanitize_text_field((string) ($_POST['connection'] ?? '')),
            'Topic: ' . sanitize_text_field((string) ($_POST['topic'] ?? '')),
            'Interest: ' . implode(', ', $clean_interest),
            'Message: ' . sanitize_textarea_field((string) ($_POST['message'] ?? '')),
            'Consent: yes',
            'Submitted: ' . current_time('mysql', true) . ' UTC',
        );
        $recipient = (string) apply_filters('pws_form_recipient', get_option('pws_form_recipient', get_option('admin_email')), $kind);
        $sent = wp_mail($recipient, $kind === 'interest' ? 'New cohort interest' : 'New website inquiry', implode("\n", $lines), array('Reply-To: ' . $first . ' ' . $last . ' <' . $email . '>'));
        self::redirect($sent ? 'sent' : 'error', $kind === 'interest' ? '/join/' : '/contact/');
    }

    private static function redirect(string $status, string $path): void
    {
        wp_safe_redirect(add_query_arg('form_status', $status, home_url($path)));
        exit;
    }
}
