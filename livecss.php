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
        add_action('wp_ajax_livecss_recreate_file', array($this, 'recreate_css_file'));
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Set up the livecss directory and main.css file
        $upload_dir = wp_upload_dir();
        $livecss_dir = $upload_dir['basedir'] . '/livecss';

        if (!file_exists($livecss_dir)) {
            wp_mkdir_p($livecss_dir);
        }

        $css_file = $livecss_dir . '/main.css';
        if (!file_exists($css_file)) {
            file_put_contents($css_file, '/* LiveCSS Custom Styles */');
        }

        // Remove the old database option
        delete_option('livecss_custom_css');
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
        // Don't load the saved CSS file in the editor preview iframe
        if (isset($_GET['livecss_preview'])) {
            return;
        }

        $upload_dir = wp_upload_dir();
        $css_file_path = $upload_dir['basedir'] . '/livecss/main.css';
        $css_file_url = $upload_dir['baseurl'] . '/livecss/main.css';

        if (file_exists($css_file_path)) {
            // Use file modification time as version for cache busting
            $version = filemtime($css_file_path);
            wp_enqueue_style('livecss-custom', $css_file_url, array(), $version);
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
        
        // Get path to the CSS file
        $upload_dir = wp_upload_dir();
        $css_file = $upload_dir['basedir'] . '/livecss/main.css';

        // Save to file
        $result = file_put_contents($css_file, $css);

        if ($result === false) {
            wp_send_json_error('Failed to write to CSS file.');
        } else {
            wp_send_json_success('CSS saved successfully');
        }
        
        exit;
    }

    /**
     * Recreate CSS file via AJAX
     */
    public function recreate_css_file() {
        // Check nonce and permissions
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'livecss_recreate_file') || !current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
            exit;
        }

        // Set up the livecss directory and main.css file
        $upload_dir = wp_upload_dir();
        $livecss_dir = $upload_dir['basedir'] . '/livecss';

        if (!file_exists($livecss_dir)) {
            wp_mkdir_p($livecss_dir);
        }

        $css_file = $livecss_dir . '/main.css';
        if (!file_exists($css_file)) {
            if (file_put_contents($css_file, '/* LiveCSS Custom Styles */') === false) {
                wp_send_json_error('Could not create CSS file. Check directory permissions.');
                exit;
            }
        }

        wp_send_json_success('File recreated successfully.');
        exit;
    }
}

// Initialize the plugin
$livecss = new LiveCSS();