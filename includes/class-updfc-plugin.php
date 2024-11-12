<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://profiles.wordpress.org/priyanksukhadiya/
 * @since      1.0.0
 *
 * @package    UPDFC_Plugin
 * @subpackage UPDFC_Plugin/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    UPDFC_Plugin
 * @subpackage UPDFC_Plugin/includes
 * @author     Priyank Sukhadiya <priyanksukhadiya2001@gmail.com>
 */
// Exit if accessed directly.
if (!defined('WPINC')) {
    die;
}

class UPDFC_Plugin
{
    private $plugin_slug = 'updfc'; // Plugin slug for options

    public function __construct()
    {
        // Initialize version
        if (defined('UPDFC_CURRENT_VERSION')) {
            $this->version = UPDFC_CURRENT_VERSION;
        } else {
            $this->version = '1.0.0';
        }

        $this->plugin_name = 'user-profile-dashboard-fields-control';
    }
    public $fields_control = [
        'admin_color_scheme' => 'Admin Color Scheme',
        'toolbar' => 'Toolbar',
        'username' => 'Username',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'nickname' => 'Nickname',
        'display_name' => 'Display Name',
        'email' => 'Email',
        'url' => 'Website',
        'description' => 'Biographical Info',
        'profile_picture' => 'Profile Picture',
        'password' => 'Password',
        'sessions' => 'Sessions',
        'application_passwords' => 'Application Passwords',
        'disable_headline_fields' => 'Disable Headline Fields',
    ];

    // Add admin menu.
    function updfc_add_admin_menu()
    {
        add_menu_page(
            'User Profile & Dashboard Fields Control',
            'User Profile & Dashboard Fields',
            'manage_options',
            'user-profile-dashboard-fields-control',
            array($this, 'updfc_admin_page')
        );
    }

    // Declare settings for each field.
    public function updfc_declare_settings()
    {
        $array = [
            'disable_headline_fields' => false,
            'disabled_headline_fields' => "Personal Options,Name,Contact Info,About Yourself,Account Management",
        ];

        foreach ($this->fields_control as $key => $val) {
            register_setting($this->plugin_slug, 'updfc_enable_' . $key);
        }

        return $array; // Return the settings array
    }

    // Render the admin page.
    function updfc_admin_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions to access this page.');
        }

        // Check if form is submitted.
        if (isset($_POST['updfc_save_roles']) && check_admin_referer('updfc_save_roles_nonce')) {
            $selected_roles = isset($_POST['updfc_roles']) ? array_map('sanitize_text_field', wp_unslash($_POST['updfc_roles'])) : array();

            if (!empty($selected_roles) && is_array($selected_roles)) {
                $selected_roles = array_map('sanitize_text_field', $selected_roles);
            } else {
                $selected_roles = array();
            }
            update_option('updfc_selected_roles', $selected_roles);

            foreach ($this->fields_control as $key => $val) {
                $updfc_field_option = 'updfc_enable_' . $key;

                // Check if the field is set in $_POST, otherwise retain its current value.
                if (isset($_POST[$this->plugin_slug]['updfc_enable_' . $key])) {
                    $field_value = $_POST[$this->plugin_slug]['updfc_enable_' . $key] == '1' ? 1 : 0;
                } else {
                    $field_value = get_option($updfc_field_option, 1);
                }

                update_option($updfc_field_option, $field_value);
            }

            echo '<div class="updated"><p>Settings saved.</p></div>';
        }

        // Get selected roles.
        $selected_roles = get_option('updfc_selected_roles', array());

        ?>
        <div class="updfc_myplugin mainbox">
            <h1 class="updfc-title">User Profile &amp; Dashboard Fields Control</h1>
            <div class="updfc_innerbox">
                <form method="post" action="">
                    <?php wp_nonce_field('updfc_save_roles_nonce'); ?>

                    <h2>User Role Controls</h2>
                    <!-- User Roles Selection -->
                    <table class="form-table updfc-firstsection">
                        <tr valign="top">
                            <th scope="row">Select User Roles:</th>
                            <td>
                                <?php
                                global $wp_roles;
                                $roles = $wp_roles->roles;

                                // Get all roles with assigned users.
                                $roles_with_users = array();
                                $all_users = get_users();
                                foreach ($all_users as $user) {
                                    $user_roles = $user->roles;
                                    foreach ($user_roles as $role_key) {
                                        if (!isset($roles_with_users[$role_key])) {
                                            $roles_with_users[$role_key] = true;
                                        }
                                    }
                                }
                                // Check if there are any roles with users
                                if (!empty($roles_with_users)) {
                                    // Display roles with users, excluding specific roles.
                                    foreach ($roles_with_users as $role_key => $value) {
                                        if (in_array($role_key, array('administrator'), true)) {
                                            continue;
                                        }
                                        ?>
                                        <label>
                                            <input type="checkbox" name="updfc_roles[]" value="<?php echo esc_attr($role_key); ?>" <?php checked(in_array($role_key, $selected_roles)); ?> />
                                            <?php echo esc_html($roles[$role_key]['name']); ?>
                                        </label><br />
                                        <?php
                                    }
                                } else {
                                    // Display a message if no roles have assigned users
                                    ?>
                                    <p class="no-roles-message">Please assign the related roles to the users. No roles with assigned
                                        users are currently available to display.</p>
                                    <?php
                                }
                                ?>
                            </td>
                        </tr>
                    </table>

                    <!-- Additional Settings -->
                    <h2>Field Controls</h2>
                    <table class="form-table updfc-secondsection">
                        <?php foreach ($this->fields_control as $key => $val) { ?>
                            <tr class="def">
                                <th scope="row">
                                    <?php
                                    /* translators: %s is the name of the field being displayed. */
                                    $display_text = sprintf(
                                        /* translators: %s is the name of the field being displayed. */
                                        __('Display field: <code>%s</code>', 'user-profile-dashboard-fields-controls'),
                                        esc_html($val)
                                    );
                                    // Output the escaped content.
                                    echo wp_kses_post($display_text);
                                    ?>
                                </th>

                                <td>
                                    <fieldset>
                                        <div class="radio-switch">
                                            <label>
                                                <input
                                                    name="<?php echo esc_attr($this->plugin_slug); ?>[updfc_enable_<?php echo esc_attr($key); ?>]"
                                                    type="radio" value="0" <?php checked(get_option('updfc_enable_' . $key, 1), 0); ?>>
                                                No
                                            </label>
                                            <label>
                                                <input
                                                    name="<?php echo esc_attr($this->plugin_slug); ?>[updfc_enable_<?php echo esc_attr($key); ?>]"
                                                    type="radio" value="1" <?php checked(get_option('updfc_enable_' . $key, 1), 1); ?>>
                                                Yes
                                            </label>
                                        </div>
                                        <?php
                                        if ($key === 'disable_headline_fields'): ?>
                                            <p class="note" style="font-weight: bold;">
                                                Note: Disabling this option will hide the following titles: Personal Options, Name,
                                                Contact Info, About Yourself, Account Management.
                                            </p>
                                        <?php endif; ?>
                                    </fieldset>
                                </td>
                            </tr>
                        <?php } ?>
                        <!-- Add other settings rows as needed -->
                    </table>

                    <!-- Submit Button -->
                    <p class="submit">
                        <input type="submit" name="updfc_save_roles" class="button-primary" value="Save Settings">
                    </p>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue plugin styles.
     */
    public function updfc_enqueue_styles()
    {
        wp_enqueue_style(
            'updfc_plugin_styles',
            plugin_dir_url(__FILE__) . '../assets/css/updfc-styles.css',
            array(),
            $this->version
        );
    }

    /**
     * Hide fields based on settings.
     */
    // Hide fields based on selected roles and field settings.
    public function updfc_hide_fields()
    {
        $selected_roles = get_option('updfc_selected_roles', array());
        $current_user = wp_get_current_user();
        if (array_intersect($selected_roles, $current_user->roles)) {
            $custom_css = '';

            foreach ($this->fields_control as $key => $val) {
                $field_enabled = get_option('updfc_enable_' . $key, 1);

                if ($field_enabled == 0) { // 0 means the field should be hidden
                    if ($key == 'admin_color_scheme') {
                        $custom_css .= '#profile-page .user-admin-color-wrap{display:none;}';
                    } else if ($key == 'toolbar') {
                        $custom_css .= '#profile-page .user-admin-bar-front-wrap{display:none;}';
                    } else if ($key == 'username') {
                        $custom_css .= '#profile-page .user-user-login-wrap{display:none;}';
                    } else if ($key == 'first_name') {
                        $custom_css .= '#profile-page .user-first-name-wrap{display:none;}';
                    } else if ($key == 'last_name') {
                        $custom_css .= '#profile-page .user-last-name-wrap{display:none;}';
                    } else if ($key == 'nickname') {
                        $custom_css .= '#profile-page .user-nickname-wrap{display:none;}';
                    } else if ($key == 'display_name') {
                        $custom_css .= '#profile-page .user-display-name-wrap{display:none;}';
                    } else if ($key == 'email') {
                        $custom_css .= '#profile-page .user-email-wrap{display:none;}';
                    } else if ($key == 'url') {
                        $custom_css .= '#profile-page .user-url-wrap{display:none;}';
                    } else if ($key == 'description') {
                        $custom_css .= '#profile-page .user-description-wrap{display:none;}';
                    } else if ($key == 'profile_picture') {
                        $custom_css .= '#profile-page .user-profile-picture{display:none;}';
                    } else if ($key == 'password') {
                        $custom_css .= '#profile-page .user-pass1-wrap{display:none;}';
                    } else if ($key == 'sessions') {
                        $custom_css .= '#profile-page .user-sessions-wrap{ display: none; }';
                    } else if ($key == 'application_passwords') {
                        $custom_css .= '#profile-page .application-passwords{display:none;}';
                    } else if ($key == 'disable_headline_fields') {
                        // echo wp_kses_post($this->enqueue_disable_headlines_script());
                        add_action('wp_print_scripts', array($this, 'updfc_enqueue_disable_headlines_script'));
                    }
                }
            }

            if (!empty($custom_css)) {
                wp_add_inline_style('wp-admin', $custom_css);
            }
        }



    }

    public function updfc_enqueue_disable_headlines_script() {
        // Register the script
        wp_register_script(
            'updfc-admin-script', // Handle
            plugin_dir_url(__FILE__) . '../assets/js/updfc-admin-script.js', // Path to your JS file
            array(), // Dependencies
            $this->version,
            true
        );
    
        // Localize the script with data from PHP
        wp_localize_script('updfc-admin-script', 'myPluginData', array(
            'titles' => array_filter(explode(',', $this->updfc_declare_settings()["disabled_headline_fields"]))
        ));
    
        // Enqueue the script
        wp_enqueue_script('updfc-admin-script');
    }

}
