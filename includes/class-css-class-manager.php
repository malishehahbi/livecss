<?php
/**
 * CSS Class Manager for LiveCSS Plugin
 * 
 * Provides CSS class management functionality integrated with Gutenberg
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class LiveCSS_Class_Manager {
    
    /**
     * Option name for storing CSS classes
     */
    const OPTION_NAME = 'livecss_css_classes';
    
    /**
     * Meta key for user settings
     */
    const USER_META_KEY = 'livecss_class_manager_settings';
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    /**
     * Initialize the class manager
     */
    public function init() {
        // Register REST API endpoints
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        
        // Enqueue Gutenberg assets
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register user meta for settings
        $this->register_user_meta();
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        // Get CSS classes
        register_rest_route('livecss/v1', '/css-classes', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_css_classes'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        // Save CSS classes
        register_rest_route('livecss/v1', '/css-classes', array(
            'methods' => 'POST',
            'callback' => array($this, 'save_css_classes'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        // Delete CSS class
        register_rest_route('livecss/v1', '/css-classes/(?P<id>[a-zA-Z0-9-_]+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_css_class'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        // Get user settings
        register_rest_route('livecss/v1', '/user-settings', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_user_settings'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        // Update user settings
        register_rest_route('livecss/v1', '/user-settings', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_user_settings'),
            'permission_callback' => array($this, 'check_permissions')
        ));
    }
    
    /**
     * Check permissions for REST API
     */
    public function check_permissions() {
        return current_user_can('edit_posts');
    }
    
    /**
     * Get CSS classes
     */
    public function get_css_classes() {
        $classes = get_option(self::OPTION_NAME, array());
        
        // Return only user-defined classes (no defaults)
        return rest_ensure_response($classes);
    }
    
    /**
     * Save CSS classes
     */
    public function save_css_classes($request) {
        $body = $request->get_json_params();
        
        if (!isset($body['name']) || !isset($body['description'])) {
            return new WP_Error('invalid_data', 'Name and description are required', array('status' => 400));
        }
        
        $classes = get_option(self::OPTION_NAME, array());
        
        $new_class = array(
            'id' => isset($body['id']) ? $body['id'] : uniqid('class_'),
            'name' => sanitize_html_class($body['name']),
            'description' => sanitize_text_field($body['description']),
            'created' => current_time('mysql')
        );
        
        // Check if updating existing class
        $updated = false;
        foreach ($classes as $index => $class) {
            if ($class['id'] === $new_class['id']) {
                $classes[$index] = $new_class;
                $updated = true;
                break;
            }
        }
        
        // Add new class if not updating
        if (!$updated) {
            $classes[] = $new_class;
        }
        
        update_option(self::OPTION_NAME, $classes);
        
        return rest_ensure_response($new_class);
    }
    
    /**
     * Delete CSS class
     */
    public function delete_css_class($request) {
        $id = $request->get_param('id');
        $classes = get_option(self::OPTION_NAME, array());
        
        $classes = array_filter($classes, function($class) use ($id) {
            return $class['id'] !== $id;
        });
        
        update_option(self::OPTION_NAME, array_values($classes));
        
        return rest_ensure_response(array('success' => true));
    }
    
    /**
     * Get user settings
     */
    public function get_user_settings() {
        $user_id = get_current_user_id();
        $settings = get_user_meta($user_id, self::USER_META_KEY, true);
        
        if (empty($settings)) {
            $settings = array(
                'showInOwnPanel' => false,
                'enableFuzzySearch' => true
            );
        }
        
        return rest_ensure_response($settings);
    }
    
    /**
     * Update user settings
     */
    public function update_user_settings($request) {
        $body = $request->get_json_params();
        $user_id = get_current_user_id();
        
        $settings = array(
            'showInOwnPanel' => isset($body['showInOwnPanel']) ? (bool)$body['showInOwnPanel'] : false,
            'enableFuzzySearch' => isset($body['enableFuzzySearch']) ? (bool)$body['enableFuzzySearch'] : true
        );
        
        update_user_meta($user_id, self::USER_META_KEY, $settings);
        
        return rest_ensure_response($settings);
    }
    
    /**
     * Register user meta
     */
    private function register_user_meta() {
        register_meta('user', self::USER_META_KEY, array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'object'
        ));
    }
    
    /**
     * Enqueue Gutenberg block editor assets
     */
    public function enqueue_block_editor_assets() {
        // Check if we're in the block editor
        if (!function_exists('get_current_screen')) {
            return;
        }
        
        $screen = get_current_screen();
        if (!$screen || !$screen->is_block_editor()) {
            return;
        }
        
        $asset_file = LIVECSS_PLUGIN_DIR . 'assets/js/class-manager.asset.php';
        $asset = file_exists($asset_file) ? include $asset_file : array(
            'dependencies' => array(
                'wp-blocks',
                'wp-block-editor',
                'wp-editor', 
                'wp-element',
                'wp-components',
                'wp-compose',
                'wp-data',
                'wp-hooks',
                'wp-i18n',
                'wp-plugins'
            ),
            'version' => defined('LIVECSS_VERSION') ? LIVECSS_VERSION : '1.0.0'
        );
        
        // Enqueue the block editor script
        wp_enqueue_script(
            'livecss-class-manager',
            LIVECSS_PLUGIN_URL . 'assets/js/class-manager.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );
        
        // Enqueue styles
        wp_enqueue_style(
            'livecss-class-manager',
            LIVECSS_PLUGIN_URL . 'assets/css/class-manager.css',
            array('wp-components'),
            LIVECSS_VERSION
        );
        
        // Localize script with data
        wp_localize_script('livecss-class-manager', 'livecssClassManager', array(
            'restUrl' => rest_url('livecss/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'cssClasses' => $this->get_css_classes()->get_data(),
            'userSettings' => $this->get_user_settings()->get_data(),
            'debug' => true,
            'pluginUrl' => LIVECSS_PLUGIN_URL
        ));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'tools.php',
            __('CSS Class Manager', 'livecss'),
            __('CSS Class Manager', 'livecss'),
            'manage_options',
            'livecss-class-manager',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('CSS Class Manager', 'livecss'); ?></h1>
            <div id="livecss-class-manager-admin"></div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Simple admin interface will be rendered here
            const container = document.getElementById('livecss-class-manager-admin');
            if (container && window.LiveCSSClassManagerAdmin) {
                window.LiveCSSClassManagerAdmin.render(container);
            }
        });
        </script>
        <?php
    }

}

// Initialize the CSS Class Manager
new LiveCSS_Class_Manager();