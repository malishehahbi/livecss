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
                <h1>LiveCSS Editor</h1>
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

                <div class="tab-content" id="tab-visual" data-tab="visual" role="tabpanel" aria-labelledby="tab-btn-visual">
                    <div class="accordion-item">
                        <div class="accordion-header">Typography</div>
                        <div class="accordion-content">
                            <div class="control-group">
                                <label class="control-label">Font Family</label>
                                <input type="text" class="control" data-property="font-family" placeholder="Arial, sans-serif">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Font Size</label>
                                <input type="text" class="control" data-property="font-size" placeholder="16px, 1em, 1.2rem">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Font Weight</label>
                                <select class="control" data-property="font-weight">
                                    <option value="">Default</option>
                                    <option value="normal">Normal</option>
                                    <option value="bold">Bold</option>
                                    <option value="100">100</option>
                                    <option value="200">200</option>
                                    <option value="300">300</option>
                                    <option value="400">400</option>
                                    <option value="500">500</option>
                                    <option value="600">600</option>
                                    <option value="700">700</option>
                                    <option value="800">800</option>
                                    <option value="900">900</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Font Style</label>
                                <select class="control" data-property="font-style">
                                    <option value="">Default</option>
                                    <option value="normal">Normal</option>
                                    <option value="italic">Italic</option>
                                    <option value="oblique">Oblique</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Text Color</label>
                                <input type="color" class="control" data-property="color">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Line Height</label>
                                <input type="text" class="control" data-property="line-height" placeholder="1.5, 24px">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Text Align</label>
                                <select class="control" data-property="text-align">
                                    <option value="">Default</option>
                                    <option value="left">Left</option>
                                    <option value="center">Center</option>
                                    <option value="right">Right</option>
                                    <option value="justify">Justify</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Text Decoration</label>
                                <select class="control" data-property="text-decoration">
                                    <option value="">Default</option>
                                    <option value="none">None</option>
                                    <option value="underline">Underline</option>
                                    <option value="overline">Overline</option>
                                    <option value="line-through">Line Through</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Text Transform</label>
                                <select class="control" data-property="text-transform">
                                    <option value="">Default</option>
                                    <option value="none">None</option>
                                    <option value="uppercase">Uppercase</option>
                                    <option value="lowercase">Lowercase</option>
                                    <option value="capitalize">Capitalize</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Letter Spacing</label>
                                <input type="text" class="control" data-property="letter-spacing" placeholder="normal, 2px, 0.1em">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Word Spacing</label>
                                <input type="text" class="control" data-property="word-spacing" placeholder="normal, 2px, 0.1em">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Text Shadow</label>
                                <input type="text" class="control" data-property="text-shadow" placeholder="2px 2px 4px rgba(0,0,0,0.5)">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Text Overflow</label>
                                <select class="control" data-property="text-overflow">
                                    <option value="">Default</option>
                                    <option value="clip">Clip</option>
                                    <option value="ellipsis">Ellipsis</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">White Space</label>
                                <select class="control" data-property="white-space">
                                    <option value="">Default</option>
                                    <option value="normal">Normal</option>
                                    <option value="nowrap">No Wrap</option>
                                    <option value="pre">Pre</option>
                                    <option value="pre-wrap">Pre Wrap</option>
                                    <option value="pre-line">Pre Line</option>
                                    <option value="break-spaces">Break Spaces</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Vertical Align</label>
                                <select class="control" data-property="vertical-align">
                                    <option value="">Default</option>
                                    <option value="baseline">Baseline</option>
                                    <option value="top">Top</option>
                                    <option value="middle">Middle</option>
                                    <option value="bottom">Bottom</option>
                                    <option value="text-top">Text Top</option>
                                    <option value="text-bottom">Text Bottom</option>
                                    <option value="sub">Sub</option>
                                    <option value="super">Super</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Font Variant</label>
                                <select class="control" data-property="font-variant">
                                    <option value="">Default</option>
                                    <option value="normal">Normal</option>
                                    <option value="small-caps">Small Caps</option>
                                    <option value="all-small-caps">All Small Caps</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Text Indent</label>
                                <input type="text" class="control" data-property="text-indent" placeholder="20px, 2em, 5%">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Word Break</label>
                                <select class="control" data-property="word-break">
                                    <option value="">Default</option>
                                    <option value="normal">Normal</option>
                                    <option value="break-all">Break All</option>
                                    <option value="keep-all">Keep All</option>
                                    <option value="break-word">Break Word</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Overflow Wrap</label>
                                <select class="control" data-property="overflow-wrap">
                                    <option value="">Default</option>
                                    <option value="normal">Normal</option>
                                    <option value="break-word">Break Word</option>
                                    <option value="anywhere">Anywhere</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Hyphens</label>
                                <select class="control" data-property="hyphens">
                                    <option value="">Default</option>
                                    <option value="none">None</option>
                                    <option value="manual">Manual</option>
                                    <option value="auto">Auto</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header">Background</div>
                        <div class="accordion-content">
                            <div class="control-group">
                                <label class="control-label">Background Color</label>
                                <input type="color" class="control" data-property="background-color">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Background Image</label>
                                <input type="text" class="control" data-property="background-image" placeholder="url('image.jpg')">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Background Gradient</label>
                                <input type="text" class="control" data-property="background-image" placeholder="linear-gradient(to right, #ff0000, #0000ff)">
                            </div>
                            <div class="control-group" data-depends-on="background-image" data-depends-value="!">
                                <label class="control-label">Background Size</label>
                                <select class="control" data-property="background-size">
                                    <option value="">Default</option>
                                    <option value="cover">Cover</option>
                                    <option value="contain">Contain</option>
                                    <option value="100%">100%</option>
                                </select>
                            </div>
                            <div class="control-group" data-depends-on="background-image" data-depends-value="!">
                                <label class="control-label">Background Position</label>
                                <input type="text" class="control" data-property="background-position" placeholder="center, 10px 20px">
                            </div>
                            <div class="control-group" data-depends-on="background-image" data-depends-value="!">
                                <label class="control-label">Background Repeat</label>
                                <select class="control" data-property="background-repeat">
                                    <option value="">Default</option>
                                    <option value="no-repeat">No Repeat</option>
                                    <option value="repeat">Repeat</option>
                                    <option value="repeat-x">Repeat X</option>
                                    <option value="repeat-y">Repeat Y</option>
                                    <option value="space">Space</option>
                                    <option value="round">Round</option>
                                </select>
                            </div>
                            <div class="control-group" data-depends-on="background-image" data-depends-value="!">
                                <label class="control-label">Background Attachment</label>
                                <select class="control" data-property="background-attachment">
                                    <option value="">Default</option>
                                    <option value="scroll">Scroll</option>
                                    <option value="fixed">Fixed</option>
                                    <option value="local">Local</option>
                                </select>
                            </div>
                            <div class="control-group" data-depends-on="background-image" data-depends-value="!">
                                <label class="control-label">Background Clip</label>
                                <select class="control" data-property="background-clip">
                                    <option value="">Default</option>
                                    <option value="border-box">Border Box</option>
                                    <option value="padding-box">Padding Box</option>
                                    <option value="content-box">Content Box</option>
                                    <option value="text">Text (Gradient Text)</option>
                                </select>
                            </div>
                            <div class="control-group" data-depends-on="background-image" data-depends-value="!">
                                <label class="control-label">Background Origin</label>
                                <select class="control" data-property="background-origin">
                                    <option value="">Default</option>
                                    <option value="border-box">Border Box</option>
                                    <option value="padding-box">Padding Box</option>
                                    <option value="content-box">Content Box</option>
                                </select>
                            </div>
                            <div class="control-group" data-depends-on="background-image" data-depends-value="!">
                                <label class="control-label">Background Blend Mode</label>
                                <select class="control" data-property="background-blend-mode">
                                    <option value="">Default</option>
                                    <option value="normal">Normal</option>
                                    <option value="multiply">Multiply</option>
                                    <option value="screen">Screen</option>
                                    <option value="overlay">Overlay</option>
                                    <option value="darken">Darken</option>
                                    <option value="lighten">Lighten</option>
                                    <option value="color-dodge">Color Dodge</option>
                                    <option value="color-burn">Color Burn</option>
                                    <option value="difference">Difference</option>
                                    <option value="exclusion">Exclusion</option>
                                    <option value="hue">Hue</option>
                                    <option value="saturation">Saturation</option>
                                    <option value="color">Color</option>
                                    <option value="luminosity">Luminosity</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Clip Path</label>
                                <input type="text" class="control" data-property="clip-path" placeholder="circle(50%), polygon(0 0, 100% 0, 100% 100%)">
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header">Sizing</div>
                        <div class="accordion-content">
                            <div class="control-group">
                                <label class="control-label">Width</label>
                                <input type="text" class="control" data-property="width" placeholder="100px, 50%, auto">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Height</label>
                                <input type="text" class="control" data-property="height" placeholder="100px, 50%, auto">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Min Width</label>
                                <input type="text" class="control" data-property="min-width" placeholder="100px, 50%">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Max Width</label>
                                <input type="text" class="control" data-property="max-width" placeholder="1200px, 100%">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Min Height</label>
                                <input type="text" class="control" data-property="min-height" placeholder="100px, 50vh">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Max Height</label>
                                <input type="text" class="control" data-property="max-height" placeholder="800px, 100vh">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Box Sizing</label>
                                <select class="control" data-property="box-sizing">
                                    <option value="">Default</option>
                                    <option value="content-box">Content Box</option>
                                    <option value="border-box">Border Box</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Aspect Ratio</label>
                                <input type="text" class="control" data-property="aspect-ratio" placeholder="16/9, 1/1, auto">
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header">Layout</div>
                        <div class="accordion-content">
                            <div class="control-group">
                                <label class="control-label">Display</label>
                                <select class="control" data-property="display">
                                    <option value="">Default</option>
                                    <option value="block">Block</option>
                                    <option value="inline">Inline</option>
                                    <option value="inline-block">Inline Block</option>
                                    <option value="flex">Flex</option>
                                    <option value="inline-flex">Inline Flex</option>
                                    <option value="grid">Grid</option>
                                    <option value="inline-grid">Inline Grid</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Position</label>
                                <select class="control" data-property="position">
                                    <option value="">Default</option>
                                    <option value="static">Static</option>
                                    <option value="relative">Relative</option>
                                    <option value="absolute">Absolute</option>
                                    <option value="fixed">Fixed</option>
                                    <option value="sticky">Sticky</option>
                                </select>
                            </div>
                            <div class="control-group" data-depends-on="position" data-depends-value="!static,!">
                                <label class="control-label">Top</label>
                                <input type="text" class="control" data-property="top" placeholder="10px, 1em, 50%">
                            </div>
                            <div class="control-group" data-depends-on="position" data-depends-value="!static,!">
                                <label class="control-label">Right</label>
                                <input type="text" class="control" data-property="right" placeholder="10px, 1em, 50%">
                            </div>
                            <div class="control-group" data-depends-on="position" data-depends-value="!static,!">
                                <label class="control-label">Bottom</label>
                                <input type="text" class="control" data-property="bottom" placeholder="10px, 1em, 50%">
                            </div>
                            <div class="control-group" data-depends-on="position" data-depends-value="!static,!">
                                <label class="control-label">Left</label>
                                <input type="text" class="control" data-property="left" placeholder="10px, 1em, 50%">
                            </div>
                            <div class="control-group" data-depends-on="display" data-depends-value="!flex,!inline-flex,!grid,!inline-grid">
                                <label class="control-label">Float</label>
                                <select class="control" data-property="float">
                                    <option value="">Default</option>
                                    <option value="left">Left</option>
                                    <option value="right">Right</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                            <div class="control-group" data-depends-on="display" data-depends-value="!flex,!inline-flex,!grid,!inline-grid">
                                <label class="control-label">Clear</label>
                                <select class="control" data-property="clear">
                                    <option value="">Default</option>
                                    <option value="left">Left</option>
                                    <option value="right">Right</option>
                                    <option value="both">Both</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Overflow</label>
                                <select class="control" data-property="overflow">
                                    <option value="">Default</option>
                                    <option value="visible">Visible</option>
                                    <option value="hidden">Hidden</option>
                                    <option value="scroll">Scroll</option>
                                    <option value="auto">Auto</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Overflow X</label>
                                <select class="control" data-property="overflow-x">
                                    <option value="">Default</option>
                                    <option value="visible">Visible</option>
                                    <option value="hidden">Hidden</option>
                                    <option value="scroll">Scroll</option>
                                    <option value="auto">Auto</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Overflow Y</label>
                                <select class="control" data-property="overflow-y">
                                    <option value="">Default</option>
                                    <option value="visible">Visible</option>
                                    <option value="hidden">Hidden</option>
                                    <option value="scroll">Scroll</option>
                                    <option value="auto">Auto</option>
                                </select>
                            </div>
                            <div class="control-group" data-depends-on="position" data-depends-value="!static,!">
                                <label class="control-label">Z-Index</label>
                                <input type="text" class="control" data-property="z-index" placeholder="1, 10, 999">
                            </div>
                            <div class="control-group" data-depends-on="display" data-depends-value="flex,inline-flex">
                                <label class="control-label">Flex Direction</label>
                                <select class="control" data-property="flex-direction">
                                    <option value="">Default</option>
                                    <option value="row">Row</option>
                                    <option value="row-reverse">Row Reverse</option>
                                    <option value="column">Column</option>
                                    <option value="column-reverse">Column Reverse</option>
                                </select>
                            </div>
                            <div class="control-group" data-depends-on="display" data-depends-value="flex,inline-flex">
                                <label class="control-label">Flex Wrap</label>
                                <select class="control" data-property="flex-wrap">
                                    <option value="">Default</option>
                                    <option value="nowrap">No Wrap</option>
                                    <option value="wrap">Wrap</option>
                                    <option value="wrap-reverse">Wrap Reverse</option>
                                </select>
                            </div>
                            <div class="control-group" data-depends-on="display" data-depends-value="flex,inline-flex,grid,inline-grid">
                                <label class="control-label">Justify Content</label>
                                <select class="control" data-property="justify-content">
                                    <option value="">Default</option>
                                    <option value="flex-start">Flex Start</option>
                                    <option value="flex-end">Flex End</option>
                                    <option value="center">Center</option>
                                    <option value="space-between">Space Between</option>
                                    <option value="space-around">Space Around</option>
                                    <option value="space-evenly">Space Evenly</option>
                                </select>
                            </div>
                            <div class="control-group" data-depends-on="display" data-depends-value="flex,inline-flex,grid,inline-grid">
                                <label class="control-label">Align Items</label>
                                <select class="control" data-property="align-items">
                                    <option value="">Default</option>
                                    <option value="flex-start">Flex Start</option>
                                    <option value="flex-end">Flex End</option>
                                    <option value="center">Center</option>
                                    <option value="baseline">Baseline</option>
                                    <option value="stretch">Stretch</option>
                                </select>
                            </div>
                            <div class="control-group" data-depends-on="display" data-depends-value="flex,inline-flex,grid,inline-grid">
                                <label class="control-label">Align Content</label>
                                <select class="control" data-property="align-content">
                                    <option value="">Default</option>
                                    <option value="flex-start">Flex Start</option>
                                    <option value="flex-end">Flex End</option>
                                    <option value="center">Center</option>
                                    <option value="space-between">Space Between</option>
                                    <option value="space-around">Space Around</option>
                                    <option value="stretch">Stretch</option>
                                </select>
                            </div>
                            <div class="control-group" data-depends-on="display" data-depends-value="flex,inline-flex,grid,inline-grid">
                                <label class="control-label">Align Self</label>
                                <select class="control" data-property="align-self">
                                    <option value="">Default</option>
                                    <option value="auto">Auto</option>
                                    <option value="flex-start">Flex Start</option>
                                    <option value="flex-end">Flex End</option>
                                    <option value="center">Center</option>
                                    <option value="baseline">Baseline</option>
                                    <option value="stretch">Stretch</option>
                                </select>
                            </div>
                            <div class="control-group" data-depends-on="display" data-depends-value="flex,inline-flex,grid,inline-grid">
                                <label class="control-label">Order</label>
                                <input type="text" class="control" data-property="order" placeholder="0, 1, -1">
                            </div>
                            <div class="control-group" data-depends-on="display" data-depends-value="flex,inline-flex">
                                <label class="control-label">Flex Grow</label>
                                <input type="text" class="control" data-property="flex-grow" placeholder="0, 1, 2">
                            </div>
                            <div class="control-group" data-depends-on="display" data-depends-value="flex,inline-flex">
                                <label class="control-label">Flex Shrink</label>
                                <input type="text" class="control" data-property="flex-shrink" placeholder="0, 1, 2">
                            </div>
                            <div class="control-group" data-depends-on="display" data-depends-value="flex,inline-flex">
                                <label class="control-label">Flex Basis</label>
                                <input type="text" class="control" data-property="flex-basis" placeholder="auto, 100px, 50%">
                            </div>
                            <div class="control-group" data-depends-on="display" data-depends-value="grid,inline-grid">
                                <label class="control-label">Grid Template Columns</label>
                                <input type="text" class="control" data-property="grid-template-columns" placeholder="1fr 1fr 1fr, repeat(3, 1fr)">
                            </div>
                            <div class="control-group" data-depends-on="display" data-depends-value="grid,inline-grid">
                                <label class="control-label">Grid Template Rows</label>
                                <input type="text" class="control" data-property="grid-template-rows" placeholder="1fr 1fr 1fr, repeat(3, 1fr)">
                            </div>
                            <div class="control-group" data-depends-on="display" data-depends-value="grid,inline-grid">
                                <label class="control-label">Grid Column</label>
                                <input type="text" class="control" data-property="grid-column" placeholder="1 / 3, span 2">
                            </div>
                            <div class="control-group" data-depends-on="display" data-depends-value="grid,inline-grid">
                                <label class="control-label">Grid Row</label>
                                <input type="text" class="control" data-property="grid-row" placeholder="1 / 3, span 2">
                            </div>
                            <div class="control-group" data-depends-on="display" data-depends-value="flex,inline-flex,grid,inline-grid">
                                <label class="control-label">Gap</label>
                                <input type="text" class="control" data-property="gap" placeholder="10px, 1em">
                            </div>
                            <div class="control-group" data-depends-on="display" data-depends-value="flex,inline-flex,grid,inline-grid">
                                <label class="control-label">Row Gap</label>
                                <input type="text" class="control" data-property="row-gap" placeholder="10px, 1em">
                            </div>
                            <div class="control-group" data-depends-on="display" data-depends-value="flex,inline-flex,grid,inline-grid">
                                <label class="control-label">Column Gap</label>
                                <input type="text" class="control" data-property="column-gap" placeholder="10px, 1em">
                            </div>
                            <div class="control-group" data-depends-on="display" data-depends-value="grid,inline-grid">
                                <label class="control-label">Grid Auto Flow</label>
                                <select class="control" data-property="grid-auto-flow">
                                    <option value="">Default</option>
                                    <option value="row">Row</option>
                                    <option value="column">Column</option>
                                    <option value="dense">Dense</option>
                                    <option value="row dense">Row Dense</option>
                                    <option value="column dense">Column Dense</option>
                                </select>
                            </div>
                            <div class="control-group" data-depends-on="display" data-depends-value="grid,inline-grid">
                                <label class="control-label">Grid Auto Columns</label>
                                <input type="text" class="control" data-property="grid-auto-columns" placeholder="auto, 1fr, 100px">
                            </div>
                            <div class="control-group" data-depends-on="display" data-depends-value="grid,inline-grid">
                                <label class="control-label">Grid Auto Rows</label>
                                <input type="text" class="control" data-property="grid-auto-rows" placeholder="auto, 1fr, 100px">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Visibility</label>
                                <select class="control" data-property="visibility">
                                    <option value="">Default</option>
                                    <option value="visible">Visible</option>
                                    <option value="hidden">Hidden</option>
                                    <option value="collapse">Collapse</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Cursor</label>
                                <select class="control" data-property="cursor">
                                    <option value="">Default</option>
                                    <option value="auto">Auto</option>
                                    <option value="pointer">Pointer</option>
                                    <option value="default">Default</option>
                                    <option value="text">Text</option>
                                    <option value="move">Move</option>
                                    <option value="wait">Wait</option>
                                    <option value="help">Help</option>
                                    <option value="not-allowed">Not Allowed</option>
                                    <option value="grab">Grab</option>
                                    <option value="grabbing">Grabbing</option>
                                    <option value="crosshair">Crosshair</option>
                                    <option value="zoom-in">Zoom In</option>
                                    <option value="zoom-out">Zoom Out</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Object Fit</label>
                                <select class="control" data-property="object-fit">
                                    <option value="">Default</option>
                                    <option value="fill">Fill</option>
                                    <option value="contain">Contain</option>
                                    <option value="cover">Cover</option>
                                    <option value="none">None</option>
                                    <option value="scale-down">Scale Down</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Object Position</label>
                                <input type="text" class="control" data-property="object-position" placeholder="center, 50% 50%">
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header">Lists</div>
                        <div class="accordion-content">
                            <div class="control-group">
                                <label class="control-label">List Style Type</label>
                                <select class="control" data-property="list-style-type">
                                    <option value="">Default</option>
                                    <option value="none">None</option>
                                    <option value="disc">Disc</option>
                                    <option value="circle">Circle</option>
                                    <option value="square">Square</option>
                                    <option value="decimal">Decimal</option>
                                    <option value="decimal-leading-zero">Decimal Leading Zero</option>
                                    <option value="lower-roman">Lower Roman</option>
                                    <option value="upper-roman">Upper Roman</option>
                                    <option value="lower-greek">Lower Greek</option>
                                    <option value="lower-latin">Lower Latin</option>
                                    <option value="upper-latin">Upper Latin</option>
                                    <option value="armenian">Armenian</option>
                                    <option value="georgian">Georgian</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">List Style Position</label>
                                <select class="control" data-property="list-style-position">
                                    <option value="">Default</option>
                                    <option value="inside">Inside</option>
                                    <option value="outside">Outside</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">List Style Image</label>
                                <input type="text" class="control" data-property="list-style-image" placeholder="url('bullet.png')">
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header">Transitions & Animations</div>
                        <div class="accordion-content">
                            <div class="control-group">
                                <label class="control-label">Transition</label>
                                <input type="text" class="control" data-property="transition" placeholder="all 0.3s ease">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Transition Property</label>
                                <input type="text" class="control" data-property="transition-property" placeholder="all, opacity, transform">
                            </div>
                            <div class="control-group" data-depends-on="transition-property" data-depends-value="!">
                                <label class="control-label">Transition Duration</label>
                                <input type="text" class="control" data-property="transition-duration" placeholder="0.3s, 300ms">
                            </div>
                            <div class="control-group" data-depends-on="transition-property" data-depends-value="!">
                                <label class="control-label">Transition Timing Function</label>
                                <select class="control" data-property="transition-timing-function">
                                    <option value="">Default</option>
                                    <option value="ease">Ease</option>
                                    <option value="linear">Linear</option>
                                    <option value="ease-in">Ease In</option>
                                    <option value="ease-out">Ease Out</option>
                                    <option value="ease-in-out">Ease In Out</option>
                                    <option value="step-start">Step Start</option>
                                    <option value="step-end">Step End</option>
                                    <option value="steps(4, end)">Steps (4, end)</option>
                                    <option value="cubic-bezier(0.4, 0, 0.2, 1)">Cubic Bezier</option>
                                </select>
                            </div>
                            <div class="control-group" data-depends-on="transition-property" data-depends-value="!">
                                <label class="control-label">Transition Delay</label>
                                <input type="text" class="control" data-property="transition-delay" placeholder="0s, 100ms">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Animation</label>
                                <input type="text" class="control" data-property="animation" placeholder="name 1s ease infinite">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Animation Name</label>
                                <input type="text" class="control" data-property="animation-name" placeholder="fadeIn, slideUp">
                            </div>
                            <div class="control-group" data-depends-on="animation-name" data-depends-value="!">
                                <label class="control-label">Animation Duration</label>
                                <input type="text" class="control" data-property="animation-duration" placeholder="1s, 500ms">
                            </div>
                            <div class="control-group" data-depends-on="animation-name" data-depends-value="!">
                                <label class="control-label">Animation Timing Function</label>
                                <select class="control" data-property="animation-timing-function">
                                    <option value="">Default</option>
                                    <option value="ease">Ease</option>
                                    <option value="linear">Linear</option>
                                    <option value="ease-in">Ease In</option>
                                    <option value="ease-out">Ease Out</option>
                                    <option value="ease-in-out">Ease In Out</option>
                                    <option value="step-start">Step Start</option>
                                    <option value="step-end">Step End</option>
                                    <option value="steps(4, end)">Steps (4, end)</option>
                                    <option value="cubic-bezier(0.4, 0, 0.2, 1)">Cubic Bezier</option>
                                </select>
                            </div>
                            <div class="control-group" data-depends-on="animation-name" data-depends-value="!">
                                <label class="control-label">Animation Delay</label>
                                <input type="text" class="control" data-property="animation-delay" placeholder="0s, 100ms">
                            </div>
                            <div class="control-group" data-depends-on="animation-name" data-depends-value="!">
                                <label class="control-label">Animation Iteration Count</label>
                                <input type="text" class="control" data-property="animation-iteration-count" placeholder="1, infinite, 3">
                            </div>
                            <div class="control-group" data-depends-on="animation-name" data-depends-value="!">
                                <label class="control-label">Animation Direction</label>
                                <select class="control" data-property="animation-direction">
                                    <option value="">Default</option>
                                    <option value="normal">Normal</option>
                                    <option value="reverse">Reverse</option>
                                    <option value="alternate">Alternate</option>
                                    <option value="alternate-reverse">Alternate Reverse</option>
                                </select>
                            </div>
                            <div class="control-group" data-depends-on="animation-name" data-depends-value="!">
                                <label class="control-label">Animation Fill Mode</label>
                                <select class="control" data-property="animation-fill-mode">
                                    <option value="">Default</option>
                                    <option value="none">None</option>
                                    <option value="forwards">Forwards</option>
                                    <option value="backwards">Backwards</option>
                                    <option value="both">Both</option>
                                </select>
                            </div>
                            <div class="control-group" data-depends-on="animation-name" data-depends-value="!">
                                <label class="control-label">Animation Play State</label>
                                <select class="control" data-property="animation-play-state">
                                    <option value="">Default</option>
                                    <option value="running">Running</option>
                                    <option value="paused">Paused</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Will Change</label>
                                <select class="control" data-property="will-change">
                                    <option value="">Default</option>
                                    <option value="auto">Auto</option>
                                    <option value="transform">Transform</option>
                                    <option value="opacity">Opacity</option>
                                    <option value="scroll-position">Scroll Position</option>
                                    <option value="contents">Contents</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Contain</label>
                                <select class="control" data-property="contain">
                                    <option value="">Default</option>
                                    <option value="none">None</option>
                                    <option value="layout">Layout</option>
                                    <option value="style">Style</option>
                                    <option value="paint">Paint</option>
                                    <option value="size">Size</option>
                                    <option value="content">Content</option>
                                    <option value="strict">Strict</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header">Filters</div>
                        <div class="accordion-content">
                            <div class="control-group">
                                <label class="control-label">Filter</label>
                                <input type="text" class="control" data-property="filter" placeholder="blur(5px), brightness(1.5)">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Blur</label>
                                <input type="text" class="control" data-property="blur" placeholder="5px">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Brightness</label>
                                <input type="text" class="control" data-property="brightness" placeholder="1.5, 150%">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Contrast</label>
                                <input type="text" class="control" data-property="contrast" placeholder="1.5, 150%">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Grayscale</label>
                                <input type="text" class="control" data-property="grayscale" placeholder="50%, 0.5">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Hue Rotate</label>
                                <input type="text" class="control" data-property="hue-rotate" placeholder="90deg">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Invert</label>
                                <input type="text" class="control" data-property="invert" placeholder="50%, 0.5">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Opacity</label>
                                <input type="text" class="control" data-property="opacity" placeholder="50%, 0.5">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Saturate</label>
                                <input type="text" class="control" data-property="saturate" placeholder="1.5, 150%">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Sepia</label>
                                <input type="text" class="control" data-property="sepia" placeholder="50%, 0.5">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Drop Shadow</label>
                                <input type="text" class="control" data-property="drop-shadow" placeholder="2px 2px 4px rgba(0,0,0,0.5)">
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <div class="accordion-header">Transform</div>
                        <div class="accordion-content">
                            <div class="control-group">
                                <label class="control-label">Transform</label>
                                <input type="text" class="control" data-property="transform" placeholder="rotate(45deg), scale(1.2)">
                            </div>
                            <div class="control-group" data-depends-on="transform" data-depends-value="!">
                                <label class="control-label">Transform Origin</label>
                                <input type="text" class="control" data-property="transform-origin" placeholder="center, 10px 15px">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Rotate</label>
                                <input type="text" class="control" data-property="rotate" placeholder="45deg, 1turn">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Rotate X</label>
                                <input type="text" class="control" data-property="rotateX" placeholder="45deg">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Rotate Y</label>
                                <input type="text" class="control" data-property="rotateY" placeholder="45deg">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Rotate Z</label>
                                <input type="text" class="control" data-property="rotateZ" placeholder="45deg">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Rotate 3D</label>
                                <input type="text" class="control" data-property="rotate3d" placeholder="1, 1, 0, 45deg">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Perspective</label>
                                <input type="text" class="control" data-property="perspective" placeholder="1000px, 500px">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Scale</label>
                                <input type="text" class="control" data-property="scale" placeholder="1.5, 1.2 1.5">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Scale X</label>
                                <input type="text" class="control" data-property="scaleX" placeholder="1.5">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Scale Y</label>
                                <input type="text" class="control" data-property="scaleY" placeholder="1.5">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Translate</label>
                                <input type="text" class="control" data-property="translate" placeholder="10px 20px, 50% 25%">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Translate X</label>
                                <input type="text" class="control" data-property="translateX" placeholder="10px, 50%">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Translate Y</label>
                                <input type="text" class="control" data-property="translateY" placeholder="20px, 25%">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Skew</label>
                                <input type="text" class="control" data-property="skew" placeholder="10deg 15deg">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Skew X</label>
                                <input type="text" class="control" data-property="skewX" placeholder="10deg">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Skew Y</label>
                                <input type="text" class="control" data-property="skewY" placeholder="15deg">
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <div class="accordion-header">Spacing</div>
                        <div class="accordion-content">
                            <div class="control-group">
                                <label class="control-label">Padding</label>
                                <input type="text" class="control" data-property="padding" placeholder="10px, 1em">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Padding Top</label>
                                <input type="text" class="control" data-property="padding-top" placeholder="10px, 1em">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Padding Right</label>
                                <input type="text" class="control" data-property="padding-right" placeholder="10px, 1em">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Padding Bottom</label>
                                <input type="text" class="control" data-property="padding-bottom" placeholder="10px, 1em">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Padding Left</label>
                                <input type="text" class="control" data-property="padding-left" placeholder="10px, 1em">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Margin</label>
                                <input type="text" class="control" data-property="margin" placeholder="10px, 1em, auto">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Margin Top</label>
                                <input type="text" class="control" data-property="margin-top" placeholder="10px, 1em, auto">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Margin Right</label>
                                <input type="text" class="control" data-property="margin-right" placeholder="10px, 1em, auto">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Margin Bottom</label>
                                <input type="text" class="control" data-property="margin-bottom" placeholder="10px, 1em, auto">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Margin Left</label>
                                <input type="text" class="control" data-property="margin-left" placeholder="10px, 1em, auto">
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <div class="accordion-header">Radius</div>
                        <div class="accordion-content">
                            <div class="control-group">
                                <label class="control-label">Border Radius</label>
                                <input type="text" class="control" data-property="border-radius" placeholder="5px, 50%">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Top Left Radius</label>
                                <input type="text" class="control" data-property="border-top-left-radius" placeholder="5px, 50%">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Top Right Radius</label>
                                <input type="text" class="control" data-property="border-top-right-radius" placeholder="5px, 50%">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Bottom Right Radius</label>
                                <input type="text" class="control" data-property="border-bottom-right-radius" placeholder="5px, 50%">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Bottom Left Radius</label>
                                <input type="text" class="control" data-property="border-bottom-left-radius" placeholder="5px, 50%">
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <div class="accordion-header">Borders</div>
                        <div class="accordion-content">
                            <div class="control-group">
                                <label class="control-label">Border Style</label>
                                <select class="control" data-property="border-style">
                                    <option value="">Default</option>
                                    <option value="solid">Solid</option>
                                    <option value="dashed">Dashed</option>
                                    <option value="dotted">Dotted</option>
                                    <option value="double">Double</option>
                                    <option value="groove">Groove</option>
                                    <option value="ridge">Ridge</option>
                                    <option value="inset">Inset</option>
                                    <option value="outset">Outset</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                            <div class="control-group" data-depends-on="border-style" data-depends-value="!none,!">
                                <label class="control-label">Border Color</label>
                                <input type="color" class="control" data-property="border-color">
                            </div>
                            <div class="control-group" data-depends-on="border-style" data-depends-value="!none,!">
                                <label class="control-label">Border Width</label>
                                <input type="text" class="control" data-property="border-width" placeholder="1px, 2px">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Top Border Width</label>
                                <input type="text" class="control" data-property="border-top-width" placeholder="1px, 2px">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Right Border Width</label>
                                <input type="text" class="control" data-property="border-right-width" placeholder="1px, 2px">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Bottom Border Width</label>
                                <input type="text" class="control" data-property="border-bottom-width" placeholder="1px, 2px">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Left Border Width</label>
                                <input type="text" class="control" data-property="border-left-width" placeholder="1px, 2px">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Top Border Style</label>
                                <select class="control" data-property="border-top-style">
                                    <option value="">Default</option>
                                    <option value="solid">Solid</option>
                                    <option value="dashed">Dashed</option>
                                    <option value="dotted">Dotted</option>
                                    <option value="double">Double</option>
                                    <option value="groove">Groove</option>
                                    <option value="ridge">Ridge</option>
                                    <option value="inset">Inset</option>
                                    <option value="outset">Outset</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Right Border Style</label>
                                <select class="control" data-property="border-right-style">
                                    <option value="">Default</option>
                                    <option value="solid">Solid</option>
                                    <option value="dashed">Dashed</option>
                                    <option value="dotted">Dotted</option>
                                    <option value="double">Double</option>
                                    <option value="groove">Groove</option>
                                    <option value="ridge">Ridge</option>
                                    <option value="inset">Inset</option>
                                    <option value="outset">Outset</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Bottom Border Style</label>
                                <select class="control" data-property="border-bottom-style">
                                    <option value="">Default</option>
                                    <option value="solid">Solid</option>
                                    <option value="dashed">Dashed</option>
                                    <option value="dotted">Dotted</option>
                                    <option value="double">Double</option>
                                    <option value="groove">Groove</option>
                                    <option value="ridge">Ridge</option>
                                    <option value="inset">Inset</option>
                                    <option value="outset">Outset</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Left Border Style</label>
                                <select class="control" data-property="border-left-style">
                                    <option value="">Default</option>
                                    <option value="solid">Solid</option>
                                    <option value="dashed">Dashed</option>
                                    <option value="dotted">Dotted</option>
                                    <option value="double">Double</option>
                                    <option value="groove">Groove</option>
                                    <option value="ridge">Ridge</option>
                                    <option value="inset">Inset</option>
                                    <option value="outset">Outset</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Top Border Color</label>
                                <input type="color" class="control" data-property="border-top-color">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Right Border Color</label>
                                <input type="color" class="control" data-property="border-right-color">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Bottom Border Color</label>
                                <input type="color" class="control" data-property="border-bottom-color">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Left Border Color</label>
                                <input type="color" class="control" data-property="border-left-color">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Border Image</label>
                                <input type="text" class="control" data-property="border-image" placeholder="url('border.png') 30 round">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Border Collapse</label>
                                <select class="control" data-property="border-collapse">
                                    <option value="">Default</option>
                                    <option value="separate">Separate</option>
                                    <option value="collapse">Collapse</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header">Outline</div>
                        <div class="accordion-content">
                            <div class="control-group">
                                <label class="control-label">Outline Style</label>
                                <select class="control" data-property="outline-style">
                                    <option value="">Default</option>
                                    <option value="solid">Solid</option>
                                    <option value="dashed">Dashed</option>
                                    <option value="dotted">Dotted</option>
                                    <option value="double">Double</option>
                                    <option value="groove">Groove</option>
                                    <option value="ridge">Ridge</option>
                                    <option value="inset">Inset</option>
                                    <option value="outset">Outset</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Outline Color</label>
                                <input type="color" class="control" data-property="outline-color">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Outline Width</label>
                                <input type="text" class="control" data-property="outline-width" placeholder="1px, 2px, thin, medium, thick">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Outline Offset</label>
                                <input type="text" class="control" data-property="outline-offset" placeholder="2px, 5px">
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header">Effects</div>
                        <div class="accordion-content">
                            <div class="control-group">
                                <label class="control-label">Box Shadow</label>
                                <input type="text" class="control" data-property="box-shadow" placeholder="2px 2px 4px rgba(0,0,0,0.2)">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Opacity</label>
                                <input type="range" class="control" data-property="opacity" min="0" max="1" step="0.01">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Mix Blend Mode</label>
                                <select class="control" data-property="mix-blend-mode">
                                    <option value="">Default</option>
                                    <option value="normal">Normal</option>
                                    <option value="multiply">Multiply</option>
                                    <option value="screen">Screen</option>
                                    <option value="overlay">Overlay</option>
                                    <option value="darken">Darken</option>
                                    <option value="lighten">Lighten</option>
                                    <option value="color-dodge">Color Dodge</option>
                                    <option value="color-burn">Color Burn</option>
                                    <option value="difference">Difference</option>
                                    <option value="exclusion">Exclusion</option>
                                    <option value="hue">Hue</option>
                                    <option value="saturation">Saturation</option>
                                    <option value="color">Color</option>
                                    <option value="luminosity">Luminosity</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Backdrop Filter</label>
                                <input type="text" class="control" data-property="backdrop-filter" placeholder="blur(10px), brightness(1.5)">
                            </div>
                        </div>
                    </div>
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