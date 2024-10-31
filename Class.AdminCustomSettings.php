<?php

class CustomPaymentSettings {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct() {
        /* add_action('admin_menu', array($this, 'add_plugin_page'));
          add_action('admin_init', array($this, 'page_init')); */
    }

    public function CreateCustomSettings() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
    }

    /**
     * Add options page
     */
    public function add_plugin_page() {
        // This page will be under "Settings"
        add_options_page(
                'Settings Admin', 'E-Giving Settings', 'manage_options', 'custom-setting-admin', array($this, 'create_admin_page')
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page() {
        // Set class property
        $this->options = get_option('custom_payment_name');
        ?>
        <div class="wrap">
        <?php screen_icon(); ?>
            <!--            <h2>Custom Payment Settings</h2>           -->
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields('my_option_group');
                do_settings_sections('custom-setting-admin');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init() {
        register_setting(
                'my_option_group', // Option group
                'custom_payment_name', // Option name
                array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
                'setting_section_id', // ID
                'E-Giving', // Title
                array($this, 'print_section_info'), // Callback
                'custom-setting-admin' // Page
        );

        add_settings_field(
                'wshost', 'E-Giving URL', array($this, 'wshost_callback'), 'custom-setting-admin', 'setting_section_id'
        );

        add_settings_field(
                'admin_username', 'User Name', array($this, 'admin_username_callback'), 'custom-setting-admin', 'setting_section_id'
        );

        add_settings_field(
                'admin_password', 'Password', array($this, 'admin_password_callback'), 'custom-setting-admin', 'setting_section_id'
        );

        add_settings_field(
                'admin_token', 'Key', array($this, 'admin_token_callback'), 'custom-setting-admin', 'setting_section_id'
        );
        add_settings_field(
                'church_name', 'Organization Name', array($this, 'church_name_callback'), 'custom-setting-admin', 'setting_section_id'
        );
        add_settings_field(
                'church_address1', 'Organization Address', array($this, 'church_address1_callback'), 'custom-setting-admin', 'setting_section_id'
        );
        add_settings_field(
                'church_address2', 'Organization Address Line 2', array($this, 'church_address2_callback'), 'custom-setting-admin', 'setting_section_id'
        );
        add_settings_field(
                'homepage_title', 'Homepage Title', array($this, 'homepage_title_callback'), 'custom-setting-admin', 'setting_section_id'
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input) {
        $new_input = array();
        if (isset($input['organization_id']))
            $new_input['organization_id'] = absint($input['organization_id']);

        if (isset($input['wshost']))
            $new_input['wshost'] = sanitize_text_field($input['wshost']);

        if (isset($input['username']))
            $new_input['username'] = sanitize_text_field($input['username']);
        if (isset($input['password']))
            $new_input['password'] = sanitize_text_field($input['password']);
        if (isset($input['token']))
            $new_input['token'] = sanitize_text_field($input['token']);


        if (isset($input['admin_username']))
            $new_input['admin_username'] = sanitize_text_field($input['admin_username']);
        if (isset($input['admin_password']))
            $new_input['admin_password'] = sanitize_text_field($input['admin_password']);
        if (isset($input['admin_token']))
            $new_input['admin_token'] = sanitize_text_field($input['admin_token']);

        if (isset($input['church_name']))
            $new_input['church_name'] = sanitize_text_field($input['church_name']);
        if (isset($input['church_address1']))
            $new_input['church_address1'] = sanitize_text_field($input['church_address1']);
        if (isset($input['church_address2']))
            $new_input['church_address2'] = sanitize_text_field($input['church_address2']);

        if (isset($input['homepage_title']))
            $new_input['homepage_title'] = sanitize_text_field($input['homepage_title']);

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info() {
        print 'Enter your settings below:';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function organization_id_callback() {
        printf(
                '<input type="text" id="organization_id" name="custom_payment_name[organization_id]" value="%s" />', isset($this->options['organization_id']) ? esc_attr($this->options['organization_id']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function wshost_callback() {
        printf(
                '<input type="text" id="wshost" name="custom_payment_name[wshost]" value="%s" />', isset($this->options['wshost']) ? esc_attr($this->options['wshost']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function username_callback() {
        printf(
                '<input type="text" id="username" name="custom_payment_name[username]" value="%s" />', isset($this->options['username']) ? esc_attr($this->options['username']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function password_callback() {
        printf(
                '<input type="text" id="password" name="custom_payment_name[password]" value="%s" />', isset($this->options['password']) ? esc_attr($this->options['password']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function token_callback() {
        printf(
                '<input type="text" id="token" name="custom_payment_name[token]" value="%s" />', isset($this->options['token']) ? esc_attr($this->options['token']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function admin_username_callback() {
        printf(
                '<input type="text" id="admin_username" name="custom_payment_name[admin_username]" value="%s" />', isset($this->options['admin_username']) ? esc_attr($this->options['admin_username']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function admin_password_callback() {
        printf(
                '<input type="text" id="admin_password" name="custom_payment_name[admin_password]" value="%s" />', isset($this->options['admin_password']) ? esc_attr($this->options['admin_password']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function admin_token_callback() {
        printf(
                '<input type="text" id="admin_token" name="custom_payment_name[admin_token]" value="%s" />', isset($this->options['admin_token']) ? esc_attr($this->options['admin_token']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function church_name_callback() {
        printf(
                '<input type="text" id="church_name" name="custom_payment_name[church_name]" value="%s" />', isset($this->options['church_name']) ? esc_attr($this->options['church_name']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function church_address1_callback() {
        printf(
                '<input type="text" id="church_address1" name="custom_payment_name[church_address1]" value="%s" />', isset($this->options['church_address1']) ? esc_attr($this->options['church_address1']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function church_address2_callback() {
        printf(
                '<input type="text" id="church_address2" name="custom_payment_name[church_address2]" value="%s" />', isset($this->options['church_address2']) ? esc_attr($this->options['church_address2']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function homepage_title_callback() {
        printf(
                '<input type="text" id="homepage_title" name="custom_payment_name[homepage_title]" value="%s" />', isset($this->options['homepage_title']) ? esc_attr($this->options['homepage_title']) : ''
        );
    }

}
?>