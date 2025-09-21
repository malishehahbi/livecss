<?php
/**
 * Plugin Name: LiveCSS
 * Plugin URI: https://example.com/livecss
 * Description: A visual CSS editor with real-time preview for WordPress sites.
 * Version: 1.0.0
 * Author: LiveCSS Team
 * Author URI: https://example.com
 * Text Domain: livecss
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('LIVECSS_VERSION', '1.0.0');
define('LIVECSS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LIVECSS_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main LiveCSS Plugin Class
 */
class LiveCSS {
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize the plugin
        add_action('plugins_loaded', array($this, 'init'));
        
        // Register activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        // Add the "Edit CSS" button to the admin bar
        add_action('admin_bar_menu', array($this, 'add_edit_css_button'), 100);
        
        // Check if we're in CSS editor mode
        if (isset($_GET['csseditor']) && $_GET['csseditor'] === 'run') {
            add_action('template_redirect', array($this, 'load_css_editor'));
        }
        
        // Load saved CSS on the frontend
        add_action('wp_head', array($this, 'load_saved_css'));
        
        // Register AJAX handlers for saving CSS
        add_action('wp_ajax_livecss_save', array($this, 'save_css'));
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create database table or options if needed
        add_option('livecss_custom_css', '');
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Cleanup if needed
    }

    /**
     * Add "Edit CSS" button to the admin bar
     */
    public function add_edit_css_button($wp_admin_bar) {
        // Only show to administrators
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Get current URL
        $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $current_url = remove_query_arg('csseditor', $current_url);
        $editor_url = add_query_arg('csseditor', 'run', $current_url);
        
        // Add the button
        $wp_admin_bar->add_node(array(
            'id'    => 'livecss-edit',
            'title' => 'Edit CSS',
            'href'  => $editor_url,
            'meta'  => array(
                'title' => 'Edit CSS with LiveCSS',
                'class' => 'livecss-edit-button'
            )
        ));
    }

    /**
     * Load the CSS editor interface
     */
    public function load_css_editor() {
        // Only allow administrators
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Include the editor template
        include_once LIVECSS_PLUGIN_DIR . 'templates/editor.php';
        exit;
    }

    /**
     * Load saved CSS on the frontend
     */
    public function load_saved_css() {
        $custom_css = get_option('livecss_custom_css', '');
        if (!empty($custom_css)) {
            echo '<style id="livecss-custom-styles">' . wp_strip_all_tags($custom_css) . '</style>';
        }
    }

    /**
     * Save CSS via AJAX
     */
    public function save_css() {
        // Check nonce and permissions
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'livecss_save') || !current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
            exit;
        }
        
        // Get and sanitize the CSS
        $css = isset($_POST['css']) ? $_POST['css'] : '';
        
        // Save to database
        update_option('livecss_custom_css', $css);
        
        wp_send_json_success('CSS saved successfully');
        exit;
    }
}

// Initialize the plugin
$livecss = new LiveCSS();