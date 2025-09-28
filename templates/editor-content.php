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
    <div class="editor-container" role="application" aria-label="LiveCSS visual editor">
            <header class="header" role="banner">
                <h1>LiveCSS Editor</h1>
                <div class="device-toggle" role="group" aria-label="Preview device">
                    <button type="button" id="btn-desktop" class="device-btn active" data-device="desktop" title="Desktop">Desktop</button>
                    <button type="button" id="btn-tablet" class="device-btn" data-device="tablet" title="Tablet">Tablet</button>
                    <button type="button" id="btn-mobile" class="device-btn" data-device="mobile" title="Mobile">Mobile</button>
                </div>
                <div class="header-actions">
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
                </nav>

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
                                <label class="control-label">Background Size</label>
                                <select class="control" data-property="background-size">
                                    <option value="">Default</option>
                                    <option value="cover">Cover</option>
                                    <option value="contain">Contain</option>
                                    <option value="100%">100%</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Background Position</label>
                                <input type="text" class="control" data-property="background-position" placeholder="center, 10px 20px">
                            </div>
                            <div class="control-group">
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
                            <div class="control-group">
                                <label class="control-label">Background Attachment</label>
                                <select class="control" data-property="background-attachment">
                                    <option value="">Default</option>
                                    <option value="scroll">Scroll</option>
                                    <option value="fixed">Fixed</option>
                                    <option value="local">Local</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Background Clip</label>
                                <select class="control" data-property="background-clip">
                                    <option value="">Default</option>
                                    <option value="border-box">Border Box</option>
                                    <option value="padding-box">Padding Box</option>
                                    <option value="content-box">Content Box</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Background Origin</label>
                                <select class="control" data-property="background-origin">
                                    <option value="">Default</option>
                                    <option value="border-box">Border Box</option>
                                    <option value="padding-box">Padding Box</option>
                                    <option value="content-box">Content Box</option>
                                </select>
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
                            <div class="control-group">
                                <label class="control-label">Top</label>
                                <input type="text" class="control" data-property="top" placeholder="10px, 1em, 50%">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Right</label>
                                <input type="text" class="control" data-property="right" placeholder="10px, 1em, 50%">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Bottom</label>
                                <input type="text" class="control" data-property="bottom" placeholder="10px, 1em, 50%">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Left</label>
                                <input type="text" class="control" data-property="left" placeholder="10px, 1em, 50%">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Float</label>
                                <select class="control" data-property="float">
                                    <option value="">Default</option>
                                    <option value="left">Left</option>
                                    <option value="right">Right</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                            <div class="control-group">
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
                            <div class="control-group">
                                <label class="control-label">Z-Index</label>
                                <input type="text" class="control" data-property="z-index" placeholder="1, 10, 999">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Flex Direction</label>
                                <select class="control" data-property="flex-direction">
                                    <option value="">Default</option>
                                    <option value="row">Row</option>
                                    <option value="row-reverse">Row Reverse</option>
                                    <option value="column">Column</option>
                                    <option value="column-reverse">Column Reverse</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Flex Wrap</label>
                                <select class="control" data-property="flex-wrap">
                                    <option value="">Default</option>
                                    <option value="nowrap">No Wrap</option>
                                    <option value="wrap">Wrap</option>
                                    <option value="wrap-reverse">Wrap Reverse</option>
                                </select>
                            </div>
                            <div class="control-group">
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
                            <div class="control-group">
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
                            <div class="control-group">
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
                            <div class="control-group">
                                <label class="control-label">Flex Grow</label>
                                <input type="text" class="control" data-property="flex-grow" placeholder="0, 1, 2">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Flex Shrink</label>
                                <input type="text" class="control" data-property="flex-shrink" placeholder="0, 1, 2">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Flex Basis</label>
                                <input type="text" class="control" data-property="flex-basis" placeholder="auto, 100px, 50%">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Grid Template Columns</label>
                                <input type="text" class="control" data-property="grid-template-columns" placeholder="1fr 1fr 1fr, repeat(3, 1fr)">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Grid Template Rows</label>
                                <input type="text" class="control" data-property="grid-template-rows" placeholder="1fr 1fr 1fr, repeat(3, 1fr)">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Grid Column</label>
                                <input type="text" class="control" data-property="grid-column" placeholder="1 / 3, span 2">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Grid Row</label>
                                <input type="text" class="control" data-property="grid-row" placeholder="1 / 3, span 2">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Gap</label>
                                <input type="text" class="control" data-property="gap" placeholder="10px, 1em">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Row Gap</label>
                                <input type="text" class="control" data-property="row-gap" placeholder="10px, 1em">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Column Gap</label>
                                <input type="text" class="control" data-property="column-gap" placeholder="10px, 1em">
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
                            <div class="control-group">
                                <label class="control-label">Transform Origin</label>
                                <input type="text" class="control" data-property="transform-origin" placeholder="center, 10px 15px">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Rotate</label>
                                <input type="text" class="control" data-property="rotate" placeholder="45deg, 1turn">
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
                            <div class="control-group">
                                <label class="control-label">Border Color</label>
                                <input type="color" class="control" data-property="border-color">
                            </div>
                            <div class="control-group">
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
                                <input type="range" class="control" data-property="opacity" min="0" max="1" step="0.1">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Transform</label>
                                <input type="text" class="control" data-property="transform" placeholder="rotate(45deg), scale(1.2)">
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