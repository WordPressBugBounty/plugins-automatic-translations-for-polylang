<?php

if(!defined('ABSPATH')){
    exit;
}

if(!current_user_can('manage_options')){
    wp_die(__('You do not have sufficient permissions to access this page.', 'atfp'));
}

    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atfp_optin_nonce'])) {

            check_admin_referer( 'atfp_save_optin_settings', 'atfp_optin_nonce' );

            // Handle feedback checkbox
            if (get_option('cpfm_opt_in_choice_cool_translations')) {
                $feedback_opt_in = isset($_POST['atfp-dashboard-feedback-checkbox']) ? 'yes' : 'no';
                update_option('atfp_feedback_opt_in', $feedback_opt_in);
            }

        // If user opted out, remove the cron job
        if ($feedback_opt_in === 'no' && wp_next_scheduled('atfp_extra_data_update') ){
                
            wp_clear_scheduled_hook('atfp_extra_data_update');
        
        }

        if ($feedback_opt_in === 'yes' && !wp_next_scheduled('atfp_extra_data_update')) {

                wp_schedule_event(time(), 'every_30_days', 'atfp_extra_data_update');   

                if (class_exists('ATFP_cronjob')) {

                    ATFP_cronjob::atfp_send_data();
                } 
        }
        
    }
?>
<div class="atfp-dashboard-settings">
    <div class="atfp-dashboard-settings-container">
    <div class="header">
        <h1><?php echo esc_html__('Polylang Addon Settings', $text_domain); ?></h1>
        <div class="atfp-dashboard-status">
            <span><?php echo esc_html__('Inactive', $text_domain); ?></span>
            <a href="https://coolplugins.net/product/autopoly-ai-translation-for-polylang/?utm_source=atfp_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=settings" class='atfp-dashboard-btn' target="_blank">
                <img src="<?php echo esc_url(ATFP_URL . 'admin/atfp-dashboard/images/upgrade-now.svg'); ?>" alt="<?php esc_attr_e('Upgrade Now', esc_html($text_domain)); ?>">
                <?php echo esc_html__('Upgrade Now', $text_domain); ?>
            </a>
        </div>
    </div>
    
    <!-- Add the description here -->
    <p class="description">
        <?php echo esc_html__('Configure your settings for the Polylang Addon to optimize your translation experience. Enter your API keys and manage your preferences for seamless integration.', esc_html($text_domain)); ?>
    </p>

    <div class="atfp-dashboard-api-settings-container">
        <div class="atfp-dashboard-api-settings">
            <form method="post">
                <?php wp_nonce_field('atfp_save_optin_settings', 'atfp_optin_nonce'); ?>
                <div class="atfp-dashboard-api-settings-form">
            <?php
            // Define all API-related settings in a single configuration array
            $api_settings = [
                'gemini' => [
                    'name' => 'Gemini',
                    'doc_url' => 'https://docs.coolplugins.net/doc/generate-gemini-api-key/?utm_source=atfp_plugin&utm_medium=inside&utm_campaign=docs&utm_content=settings_gemini',
                    'placeholder' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'
                ],
                'openai' => [
                    'name' => 'OpenAI',
                    'doc_url' => 'https://docs.coolplugins.net/doc/generate-open-ai-api-key/?utm_source=atfp_plugin&utm_medium=inside&utm_campaign=docs&utm_content=settings_openai',
                    'placeholder' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'
                ]
            ];

            foreach ($api_settings as $key => $settings): ?>
                <label for="<?php echo esc_attr($key); ?>-api">
                    <?php printf(esc_html__('Add %s API key', $text_domain), esc_html($settings['name'])); ?>
                </label>
                <div class="input-group">
                <input 
                    type="text" 
                    id="<?php echo esc_attr($key); ?>-api" 
                    placeholder="<?php echo esc_attr($settings['placeholder']); ?>" 
                    disabled
                >
                </div>
                <?php
                printf(
                    esc_html__('%s to See How to Generate %s API Key', $text_domain),
                    '<a href="' . esc_url($settings['doc_url']) . '" target="_blank">' . esc_html__('Click Here', $text_domain) . '</a>',
                    esc_html($settings['name'])
                );
            endforeach; ?>

            <!-- Add Context Aware textarea -->
            <label for="context-aware">
                <?php echo esc_html__('Context Aware', $text_domain); ?>
            </label>
            <textarea 
                id="context-aware"
                placeholder="<?php esc_attr_e('Provide optional context about WordPress page or post to enhance translation accuracy (e.g. content purpose, target audience, SEO focus, tone)...', esc_html($text_domain)); ?>"
                disabled
            ></textarea>
            <p class="api-settings-note" style="margin-block: 5px;">
                <?php echo esc_html__('This setting only works with Gemini AI and OpenAI.', $text_domain); ?>
            </p>
                                
            <!-- Add bulk translate post status -->
            <label for="bulk-translate-post-status">
                <?php echo esc_html__('Bulk Translate Post Status', $text_domain); ?>
            </label>
            <div class="atfp-bulk-translation-post-status-options">
                <input type="radio" name="publish" id="publish" value="publish" disabled>
                <label for="publish"><?php echo esc_html__('Publish', $text_domain); ?></label>
                <input type="radio" name="draft" id="draft" value="draft" checked disabled>
                <label for="draft"><?php echo esc_html__('Draft', $text_domain); ?></label>
            </div>
            </div>

            <?php if (get_option('cpfm_opt_in_choice_cool_translations')) : ?>
                <div class="atfp-dashboard-feedback-container">
                    <div class="atfp-dashboard-feedback-row">
                        <input type="checkbox" 
                            id="atfp-dashboard-feedback-checkbox" 
                            name="atfp-dashboard-feedback-checkbox"
                            <?php checked(get_option('atfp_feedback_opt_in'), 'yes'); ?>>
                        <p><?php echo esc_html__('Help us make this plugin more compatible with your site by sharing non-sensitive site data.', $text_domain); ?></p>
                        <a href="#" class="atfp-see-terms">[See terms]</a>
                    </div>
                    <div id="termsBox" style="display: none;padding-left: 20px; margin-top: 10px; font-size: 12px; color: #999;">
                            <p><?php echo esc_html__("Opt in to receive email updates about security improvements, new features, helpful tutorials, and occasional special offers. We'll collect:", $text_domain); ?> <a href="https://my.coolplugins.net/terms/usage-tracking/?utm_source=atfp_plugin&utm_medium=inside&utm_campaign=terms&utm_content=dashboard" target="_blank"><?php echo esc_html__('Click Here', $text_domain); ?></a></p>
                            <ul style="list-style-type:auto;">
                                <li><?php esc_html_e('Your website home URL and WordPress admin email.', $text_domain); ?></li>
                                <li><?php esc_html_e('To check plugin compatibility, we will collect the following: list of active plugins and themes, server type, MySQL version, WordPress version, memory limit, site language and database prefix.', $text_domain); ?></li>
                            </ul>
                    </div>
                </div>
            <?php endif; ?>
            <div class="atfp-dashboard-save-btn-container">
                <button <?php echo get_option('cpfm_opt_in_choice_cool_translations') ? '' : 'disabled'; ?> class="button button-primary"><?php echo esc_html__('Save', $text_domain); ?></button>
            </div>
            </form>
        </div>
    </div>
    </div>
</div>
