    <?php
    // Precompute URLs without relying on WP functions (for linters) while keeping behavior
    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    // Parse URI
    $parsed = parse_url($request_uri);
    $path = isset($parsed['path']) ? $parsed['path'] : '/';
    $query = [];
    if (!empty($parsed['query'])) {
        parse_str($parsed['query'], $query);
    }
    // Remove csseditor parameter for exit URL
    unset($query['csseditor']);
    // Build exit URL (relative)
    $exit_url = $path . (empty($query) ? '' : ('?' . http_build_query($query)));
    // Build preview URL by adding livecss_preview=1
    $preview_query = $query;
    $preview_query['livecss_preview'] = '1';
    $preview_src = $path . '?' . http_build_query($preview_query);
    ?>
    <editor>
    <div class="editor-container" role="application" aria-label="LiveCSS visual editor">
            <header class="header" role="banner">
                <div class="logo-container">
                    <img src="/wp-content/plugins/livecss/assets/images/logo-light.svg" alt="LiveCSS" class="logo logo-light">
                    <img src="/wp-content/plugins/livecss/assets/images/logo-dark.svg" alt="LiveCSS" class="logo logo-dark">
                </div>
                <div class="device-toggle" role="group" aria-label="Preview device">
                    <button type="button" id="btn-desktop" class="device-btn active" data-device="desktop" title="Desktop">Desktop</button>
                    <button type="button" id="btn-tablet" class="device-btn" data-device="tablet" title="Tablet">Tablet</button>
                    <button type="button" id="btn-mobile" class="device-btn" data-device="mobile" title="Mobile">Mobile</button>
                </div>
                <div class="header-actions">
                    <button id="preview-button" class="button button-preview" title="Preview full-screen (no editor panels)" aria-label="Toggle preview mode">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <span>Preview</span>
                    </button>
                    <button id="theme-toggle" class="button button-icon" title="Toggle Light/Dark Theme" aria-label="Toggle theme">
                        <svg class="icon-sun" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="5"></circle>
                            <line x1="12" y1="1" x2="12" y2="3"></line>
                            <line x1="12" y1="21" x2="12" y2="23"></line>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                            <line x1="1" y1="12" x2="3" y2="12"></line>
                            <line x1="21" y1="12" x2="23" y2="12"></line>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                        </svg>
                        <svg class="icon-moon hidden" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                        </svg>
                    </button>
                    <button id="save-button" class="button button-primary" title="Save your CSS changes (Ctrl+S)" aria-label="Save changes">Save Changes</button>
                    <a href="<?php echo $exit_url; ?>" class="button button-danger" title="Exit without editing" aria-label="Exit editor">Exit Editor</a>
                </div>
            </header>

        <main class="main-content">
            <aside id="editor-panel" class="editor-panel" role="complementary" aria-label="CSS controls sidebar">
                <section id="element-breadcrumb" class="breadcrumb-section"></section>
                <section class="selector-section" style="position: relative;">
                    <input type="text" id="selector-input" class="selector-input" placeholder="Enter CSS selector (e.g., .my-class, #my-id)" aria-label="CSS selector input" autocomplete="off" spellcheck="false">
                    <div id="selector-suggest" class="selector-suggest hidden" role="listbox" aria-label="Selector suggestions"></div>
                    <div class="pseudo-buttons">
                        <button class="pseudo-button" data-pseudo=":hover" aria-pressed="false">:hover</button>
                        <button class="pseudo-button" data-pseudo=":focus" aria-pressed="false">:focus</button>
                        <button class="pseudo-button" data-pseudo=":active" aria-pressed="false">:active</button>
                        <button class="pseudo-button" data-pseudo="::before" aria-pressed="false">::before</button>
                        <button class="pseudo-button" data-pseudo="::after" aria-pressed="false">::after</button>
                    </div>
                </section>

                <nav class="tabs" role="tablist" aria-label="Editor mode tabs">
                    <button id="tab-btn-visual" class="tab active" data-tab="visual" role="tab" aria-selected="true" aria-controls="tab-visual">Visual Editor</button>
                    <button id="tab-btn-code" class="tab" data-tab="code" role="tab" aria-selected="false" aria-controls="tab-code">Code Editor</button>
                    <button id="search-toggle-btn" class="search-toggle-btn" title="Toggle Search (Ctrl+F)" aria-label="Toggle search">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    </button>
                </nav>

                <!-- Search bar for Visual Editor -->
                <div class="search-container collapsed" id="visual-search-container">
                    <div class="search-wrapper">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        <input type="text" id="visual-search-input" class="search-input" placeholder="Search properties..." />
                        <button id="visual-search-clear" class="search-clear hidden" title="Clear search">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="search-results-info hidden" id="visual-search-info"></div>
                </div>

                <!-- Search bar for Code Editor (hidden initially) -->
                <div class="search-container hidden collapsed" id="code-search-container">
                    <div class="search-wrapper">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        <input type="text" id="code-search-input" class="search-input" placeholder="Search in CSS..." />
                        <button id="code-search-clear" class="search-clear hidden" title="Clear search">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="search-navigation hidden" id="code-search-nav">
                        <span class="search-results-count" id="code-search-count">0 of 0</span>
                        <div class="search-nav-buttons">
                            <button id="code-search-prev" class="search-nav-btn" title="Previous match (Shift+Enter)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="18 15 12 9 6 15"></polyline>
                                </svg>
                            </button>
                            <button id="code-search-next" class="search-nav-btn" title="Next match (Enter)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Visual Editor: controls rendered dynamically from VISUAL_EDITOR_SCHEMA -->
                <div class="tab-content" id="tab-visual" data-tab="visual" role="tabpanel" aria-labelledby="tab-btn-visual">
                    <!-- Populated by renderVisualEditor() at runtime -->
                </div>

                <div class="tab-content hidden" id="tab-code" data-tab="code" role="tabpanel" aria-labelledby="tab-btn-code">
                    <div class="code-editor" id="code-editor" aria-label="CSS code editor"></div>
                </div>
            </aside>

            <div id="sidebar-resizer" class="sidebar-resizer" role="separator" aria-orientation="vertical" aria-label="Resize sidebar" aria-controls="editor-panel" aria-expanded="true" tabindex="0" title="Drag to resize. Double-click or press Enter to collapse/expand."></div>

            <main class="preview-area">
                <iframe id="preview-iframe" class="preview-iframe" title="Live preview" src="<?php echo $preview_src; ?>"></iframe>
            </main>
        </main>

        <!-- Floating Preview Exit Button -->
        <button id="exit-preview-button" class="exit-preview-button hidden" title="Exit preview mode" aria-label="Exit preview mode">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
            <span>Exit Preview</span>
        </button>
    </div>

    <div id="status-message" class="status-message"></div>

    <div id="confirmation-popup" class="popup-overlay">
        <div class="popup-content">
            <p id="popup-message"></p>
            <div class="popup-actions">
                <button id="popup-button-yes" class="button button-primary">Yes</button>
                <button id="popup-button-no" class="button">No</button>
            </div>
        </div>
    </div>
    </editor>