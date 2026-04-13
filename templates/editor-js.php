<!-- CodeMirror Core -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.js"></script>

<!-- CSS Mode -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/css/css.min.js"></script>

<!-- Addons for Auto-complete and Auto-close -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/hint/show-hint.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/hint/show-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/hint/css-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/edit/closebrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/edit/closetag.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/edit/matchbrackets.min.js"></script>

<!-- Property Dependencies Manager -->
<script src="<?php echo plugins_url('assets/js/property-dependencies.js', dirname(__FILE__)); ?>"></script>

<!-- Spotlight Mode for Code Editor -->
<script src="<?php echo plugins_url('assets/js/spotlight-mode.js', dirname(__FILE__)); ?>"></script>

<!-- Search Functionality -->
<script src="<?php echo plugins_url('assets/js/search-functionality.js', dirname(__FILE__)); ?>"></script>

   <!-- Loading Overlay -->
    <div id="livecss-loader" class="livecss-loader">
        <div class="livecss-loader-content">
            <div class="livecss-spinner"></div>
            <h3>Initializing LiveCSS Editor</h3>
            <div class="livecss-loader-progress">
                <div class="livecss-progress-bar">
                    <div class="livecss-progress-fill" id="loader-progress"></div>
                </div>
                <div class="livecss-loader-status" id="loader-status">Loading libraries...</div>
            </div>
        </div>
    </div>

    <!-- PHP Data Bridge: pass dynamic server values to the external JS -->
    <script>
        window.livecssConfig = {
            savedCSS: <?php
                $saved_css = '';
                if (function_exists('wp_upload_dir')) {
                    $upload_dir = call_user_func('wp_upload_dir');
                    $css_file_path = $upload_dir['basedir'] . '/livecss/main.css';
                    if (file_exists($css_file_path)) {
                        $saved_css = file_get_contents($css_file_path);
                    }
                }
                echo json_encode($saved_css);
            ?>,
            saveNonce: <?php echo function_exists('wp_create_nonce') ? json_encode(call_user_func('wp_create_nonce', 'livecss_save')) : "''"; ?>,
            recreateNonce: <?php echo function_exists('wp_create_nonce') ? json_encode(call_user_func('wp_create_nonce', 'livecss_recreate_file')) : "''"; ?>,
            ajaxUrl: <?php echo function_exists('admin_url') ? json_encode(call_user_func('admin_url', 'admin-ajax.php')) : "'/wp-admin/admin-ajax.php'"; ?>
        };
    </script>

    <!-- Visual Editor Schema (JSON-driven controls) -->
    <script src="<?php echo plugins_url('assets/js/visual-editor-schema.js', dirname(__FILE__)); ?>?v=<?php echo defined('LIVECSS_VERSION') ? LIVECSS_VERSION : '2.0.0'; ?>"></script>

    <!-- Main Editor Application (reads from window.livecssConfig) -->
    <script src="<?php echo plugins_url('assets/js/editor.js', dirname(__FILE__)); ?>?v=<?php echo defined('LIVECSS_VERSION') ? LIVECSS_VERSION : '2.0.0'; ?>"></script>

    <?php if (function_exists('wp_footer')) { call_user_func('wp_footer'); } ?>
</body>
</html>