<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LiveCSS Editor</title>
    <?php wp_head(); ?>
    
    <style>
        body{
            margin-top: -32px !important;
        }
        #wpadminbar{
            display:none;
        }
        /* Reset and base styles */
        :root {
            --livecss-background: #ffffff;
            --livecss-foreground: #000000;
            --livecss-card: #ffffff;
            --livecss-card-foreground: #000000;
            --livecss-popover: #ffffff;
            --livecss-popover-foreground: #000000;
            --livecss-primary: #000000;
            --livecss-primary-foreground: #ffffff;
            --livecss-secondary: #f0f0f0;
            --livecss-secondary-foreground: #000000;
            --livecss-muted: #f8f8f8;
            --livecss-muted-foreground: #666666;
            --livecss-accent: #f0f0f0;
            --livecss-accent-foreground: #000000;
            --livecss-destructive: #ef4444;
            --livecss-destructive-foreground: #ffffff;
            --livecss-border: #e0e0e0;
            --livecss-input: #e0e0e0;
            --livecss-ring: #000000;

            --livecss-spacing-unit: 10px;
        }

        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            color: var(--livecss-foreground);
        }
        
        /* Main layout */
        .livecss-editor-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
            background-color: var(--livecss-background);
        }
        
        /* Header styles */
        .livecss-header {
            background: var(--livecss-card);
            color: var(--livecss-card-foreground);
            padding: var(--livecss-spacing-unit) calc(2 * var(--livecss-spacing-unit));
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 50px;
            box-sizing: border-box;
            border-bottom: 1px solid var(--livecss-border);
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        
        .livecss-header h1 {
            margin: 0;
            font-size: 1.1em;
            font-weight: 600;
        }
        
        .livecss-header .livecss-actions {
            display: flex;
            gap: var(--livecss-spacing-unit);
        }
        
        /* Main content area */
        .livecss-content {
            display: flex;
            flex: 1;
            overflow: hidden;
        }
        
        /* Editor panel */
        .livecss-panel {
            width: 350px;
            min-width: 300px;
            background: var(--livecss-background);
            border-right: 1px solid var(--livecss-border);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        /* Tabs */
        .livecss-tabs {
            display: flex;
            background: var(--livecss-muted);
            border-bottom: 1px solid var(--livecss-border);
        }
        
        .livecss-tab {
            padding: var(--livecss-spacing-unit) 15px;
            cursor: pointer;
            border-right: 1px solid var(--livecss-border);
            font-weight: 500;
            color: var(--livecss-muted-foreground);
            transition: background 0.2s ease, color 0.2s ease;
        }
        
        .livecss-tab:hover {
            background: var(--livecss-secondary);
            color: var(--livecss-secondary-foreground);
        }
        
        .livecss-tab.active {
            background: var(--livecss-background);
            border-bottom: 2px solid var(--livecss-primary);
            color: var(--livecss-primary);
        }
        
        /* Tab content */
        .livecss-tab-content {
            display: none;
            padding: calc(1.5 * var(--livecss-spacing-unit));
            overflow-y: auto;
            flex: 1;
        }
        
        .livecss-tab-content.active {
            display: block;
        }

        /* Accordion styles */
        .livecss-accordion-item {
            border: 1px solid var(--livecss-border);
            margin-bottom: 10px;
            border-radius: var(--livecss-radius);
            overflow: hidden;
        }

        .livecss-accordion-header {
            background-color: var(--livecss-secondary);
            padding: 10px 15px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.2s ease;
        }
        .livecss-accordion-header > h3 {
            margin: 0px;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .livecss-accordion-header:hover {
            background-color: var(--livecss-accent);
        }

        .livecss-accordion-header::after {
            content: '+';
            font-size: 1.2em;
            transition: transform 0.2s ease;
        }

        .livecss-accordion-header.active::after {
            content: '-';
            transform: rotate(180deg);
        }

        .livecss-accordion-content {
            padding: 15px;
            border-top: 1px solid var(--livecss-border);
            display: none;
        }

        .livecss-accordion-content.active {
            display: block;
        }
        
        /* Selector area */
        .livecss-selector {
            padding: calc(1.5 * var(--livecss-spacing-unit));
            background: var(--livecss-card);
            border-bottom: 1px solid var(--livecss-border);
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .livecss-selector-input {
            width: 100%;
            padding: 8px;
            margin-bottom: var(--livecss-spacing-unit);
            box-sizing: border-box;
            border: 1px solid var(--livecss-input);
            border-radius: var(--livecss-radius);
            font-size: 0.9em;
            background-color: var(--livecss-background);
            color: var(--livecss-foreground);
        }
        
        .livecss-pseudo-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .livecss-pseudo-button {
            padding: 5px 10px;
            background: var(--livecss-secondary);
            border: 1px solid var(--livecss-border);
            border-radius: var(--livecss-radius);
            cursor: pointer;
            font-size: 0.85em;
            transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
            color: var(--livecss-secondary-foreground);
        }
        
        .livecss-pseudo-button:hover {
            background: var(--livecss-accent);
            border-color: var(--livecss-ring);
            color: var(--livecss-accent-foreground);
        }
        
        /* Preview iframe */
        .livecss-preview {
            flex: 1;
            position: relative;
            background-color: var(--livecss-background);
        }
        
        .livecss-iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        /* Controls */
        .livecss-control-group {
            margin-bottom: calc(1.5 * var(--livecss-spacing-unit));
        }
        
        .livecss-control-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            font-size: 0.9em;
            color: var(--livecss-foreground);
        }
        
        .livecss-control {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid var(--livecss-input);
            border-radius: var(--livecss-radius);
            font-size: 0.9em;
            background-color: var(--livecss-background);
            color: var(--livecss-foreground);
        }

        .livecss-control[type="color"] {
            padding: 4px;
            height: 36px; /* Adjust height to align with other inputs */
        }
        
        /* Color picker */
        .livecss-color-control {
            display: flex;
            align-items: center;
            gap: var(--livecss-spacing-unit);
        }
        
        .livecss-color-preview {
            width: 30px;
            height: 30px;
            border: 1px solid var(--livecss-border);
            border-radius: var(--livecss-radius);
            flex-shrink: 0;
        }
        
        /* Buttons */
        .livecss-button {
            padding: 8px 16px;
            background: var(--livecss-primary);
            color: var(--livecss-primary-foreground);
            border: none;
            border-radius: var(--livecss-radius);
            cursor: pointer;
            font-size: 0.9em;
            font-weight: 500;
            transition: background 0.2s ease;
        }
        
        .livecss-button:hover {
            background: var(--livecss-foreground);
        }
        
        .livecss-button.livecss-save {
            background: var(--livecss-primary);
        }
        
        .livecss-button.livecss-save:hover {
            background: var(--livecss-foreground);
        }
        
        .livecss-button.livecss-cancel {
            background: var(--livecss-destructive);
        }
        
        .livecss-button.livecss-cancel:hover {
            background: #dc2626;
        }
        
        /* Code editor */
        .livecss-code-editor {
            height: 300px;
            border: 1px solid var(--livecss-border);
            border-radius: var(--livecss-radius);
            overflow: hidden; /* Ensures editor content stays within bounds */
        }

        /* Utility classes for spacing */
        .mt-10 { margin-top: var(--livecss-spacing-unit); }
        .mb-10 { margin-bottom: var(--livecss-spacing-unit); }
        .ml-10 { margin-left: var(--livecss-spacing-unit); }
        .mr-10 { margin-right: var(--livecss-spacing-unit); }

        /* Responsive adjustments (basic example) */
        @media (max-width: 768px) {
            .livecss-content {
                flex-direction: column;
            }

            .livecss-panel {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid var(--livecss-border);
            }
        }

    </style>
    <!-- CodeMirror CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css">
</head>
<body>
    <div class="livecss-editor-container">
        <!-- Header -->
        <div class="livecss-header">
            <h1>LiveCSS Editor</h1>
            <div class="livecss-actions">
                <button id="livecss-save" class="livecss-button livecss-save">Save & Publish</button>
                <a href="<?php echo remove_query_arg('csseditor'); ?>" class="livecss-button livecss-cancel">Exit</a>
            </div>
        </div>
        
        <!-- Main content -->
        <div class="livecss-content">
            <!-- Editor panel -->
            <div class="livecss-panel">
                <!-- Selector area -->
                <div class="livecss-selector">
                    <input type="text" id="livecss-selector-input" class="livecss-selector-input" placeholder=".selector">
                    <div class="livecss-pseudo-buttons">
                        <span class="livecss-pseudo-button" data-pseudo=":hover">:hover</span>
                        <span class="livecss-pseudo-button" data-pseudo=":focus">:focus</span>
                        <span class="livecss-pseudo-button" data-pseudo=":active">:active</span>
                        <span class="livecss-pseudo-button" data-pseudo=":before">:before</span>
                        <span class="livecss-pseudo-button" data-pseudo=":after">:after</span>
                    </div>
                </div>
                
                <!-- Tabs -->
                <div class="livecss-tabs">
                    <div class="livecss-tab active" data-tab="visual">Visual</div>
                    <div class="livecss-tab" data-tab="code">Code</div>
                </div>
                
                <!-- Tab content -->
                <div class="livecss-tab-content active" data-tab="visual">
                    <!-- Background -->
                    <div class="livecss-accordion-item">
                        <div class="livecss-accordion-header"><h3>Background</h3></div>
                        <div class="livecss-accordion-content">
                            <label class="livecss-control-label">Background Color</label>
                            <input type="color" id="bg-color" class="livecss-control" data-css-property="background-color">
                            
                            <label class="livecss-control-label">Background Image</label>
                            <input type="text" id="bg-image" class="livecss-control" data-css-property="background-image" placeholder="e.g., url('image.jpg')">
                            
                            <label class="livecss-control-label">Background Size</label>
                            <input type="text" id="bg-size" class="livecss-control" data-css-property="background-size" placeholder="e.g., cover, contain, 100%">
                            
                            <label class="livecss-control-label">Background Position</label>
                            <input type="text" id="bg-position" class="livecss-control" data-css-property="background-position" placeholder="e.g., center, top right, 50% 50%">
                            
                            <label class="livecss-control-label">Background Repeat</label>
                            <select id="bg-repeat" class="livecss-control" data-css-property="background-repeat">
                                <option value="repeat">repeat</option>
                                <option value="repeat-x">repeat-x</option>
                                <option value="repeat-y">repeat-y</option>
                                <option value="no-repeat">no-repeat</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Typography -->
                    <div class="livecss-accordion-item">
                        <div class="livecss-accordion-header"><h3>Typography</h3></div>
                        <div class="livecss-accordion-content">
                            <label class="livecss-control-label">Font Size</label>
                            <input type="text" id="font-size" class="livecss-control" data-css-property="font-size" placeholder="e.g., 16px, 1em, 1.2rem">
                            
                            <label class="livecss-control-label">Font Weight</label>
                            <select id="font-weight" class="livecss-control" data-css-property="font-weight">
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
                            
                            <label class="livecss-control-label">Font Family</label>
                            <input type="text" id="font-family" class="livecss-control" data-css-property="font-family" placeholder="e.g., Arial, 'Times New Roman'">
                            
                            <label class="livecss-control-label">Line Height</label>
                            <input type="text" id="line-height" class="livecss-control" data-css-property="line-height" placeholder="e.g., 1.5, 20px">
                            
                            <label class="livecss-control-label">Text Align</label>
                            <select id="text-align" class="livecss-control" data-css-property="text-align">
                                <option value="left">Left</option>
                                <option value="center">Center</option>
                                <option value="right">Right</option>
                                <option value="justify">Justify</option>
                            </select>
                            
                            <label class="livecss-control-label">Text Decoration</label>
                            <input type="text" id="text-decoration" class="livecss-control" data-css-property="text-decoration" placeholder="e.g., underline, none">
                            
                            <label class="livecss-control-label">Text Color</label>
                            <input type="color" id="text-color" class="livecss-control" data-css-property="color">
                        </div>
                    </div>
                    
                    <!-- Borders -->
                    <div class="livecss-accordion-item">
                        <div class="livecss-accordion-header"><h3>Borders</h3></div>
                        <div class="livecss-accordion-content">
                            <label class="livecss-control-label">Border Width</label>
                            <input type="text" id="border-width" class="livecss-control" data-css-property="border-width" placeholder="e.g., 1px, medium, 0.1em">
                            
                            <label class="livecss-control-label">Border Style</label>
                            <select id="border-style" class="livecss-control" data-css-property="border-style">
                                <option value="none">None</option>
                                <option value="solid">Solid</option>
                                <option value="dashed">Dashed</option>
                                <option value="dotted">Dotted</option>
                                <option value="double">Double</option>
                            </select>
                            
                            <label class="livecss-control-label">Border Color</label>
                            <input type="color" id="border-color" class="livecss-control" data-css-property="border-color">
                            
                            <label class="livecss-control-label">Border Radius</label>
                            <input type="text" id="border-radius" class="livecss-control" data-css-property="border-radius" placeholder="e.g., 5px, 50%, 0.5em">
                        </div>
                    </div>
                    
                    <!-- Spacing -->
                    <div class="livecss-accordion-item">
                        <div class="livecss-accordion-header"><h3>Spacing</h3></div>
                        <div class="livecss-accordion-content">
                            <label class="livecss-control-label">Padding</label>
                            <input type="text" id="padding" class="livecss-control" data-css-property="padding" placeholder="e.g., 10px, 1em, 5%">
                            
                            <label class="livecss-control-label">Margin</label>
                            <input type="text" id="margin" class="livecss-control" data-css-property="margin" placeholder="e.g., 10px, 1em, auto">
                        </div>
                    </div>
                    
                    <label class="livecss-control-label">Transform</label>
                    <div class="livecss-accordion-item">
                        <div class="livecss-accordion-header"><h3>Transform</h3></div>
                        <div class="livecss-accordion-content">
                            <label class="livecss-control-label">Rotate</label>
                            <input type="text" id="transform-rotate" class="livecss-control" data-css-property="transform" data-transform-type="rotate" placeholder="e.g., 45deg">
                            
                            <label class="livecss-control-label">Scale</label>
                            <input type="text" id="transform-scale" class="livecss-control" data-css-property="transform" data-transform-type="scale" placeholder="e.g., 1.2, 0.8">
                            
                            <label class="livecss-control-label">Translate X</label>
                            <input type="text" id="transform-translate-x" class="livecss-control" data-css-property="transform" data-transform-type="translateX" placeholder="e.g., 10px, 50%">
                            
                            <label class="livecss-control-label">Translate Y</label>
                            <input type="text" id="transform-translate-y" class="livecss-control" data-css-property="transform" data-transform-type="translateY" placeholder="e.g., 10px, 50%">
                            
                            <label class="livecss-control-label">Skew X</label>
                            <input type="text" id="transform-skew-x" class="livecss-control" data-css-property="transform" data-transform-type="skewX" placeholder="e.g., 10deg">
                            
                            <label class="livecss-control-label">Skew Y</label>
                            <input type="text" id="transform-skew-y" class="livecss-control" data-css-property="transform" data-transform-type="skewY" placeholder="e.g., 10deg">
                        </div>
                    </div>
                    
                    <!-- Filters -->
                    <div class="livecss-accordion-item">
                        <div class="livecss-accordion-header"><h3>Filters</h3></div>
                        <div class="livecss-accordion-content">
                            <label class="livecss-control-label">Blur</label>
                            <input type="text" id="filter-blur" class="livecss-control" data-css-property="filter" data-filter-type="blur" placeholder="e.g., 5px">
                            
                            <label class="livecss-control-label">Brightness</label>
                            <input type="text" id="filter-brightness" class="livecss-control" data-css-property="filter" data-filter-type="brightness" placeholder="e.g., 1.5, 75%">
                            
                            <label class="livecss-control-label">Contrast</label>
                            <input type="text" id="filter-contrast" class="livecss-control" data-css-property="filter" data-filter-type="contrast" placeholder="e.g., 200%, 0.5">
                            
                            <label class="livecss-control-label">Drop Shadow</label>
                            <input type="text" id="filter-drop-shadow" class="livecss-control" data-css-property="filter" data-filter-type="drop-shadow" placeholder="e.g., 2px 2px 5px rgba(0,0,0,0.3)">
                            
                            <label class="livecss-control-label">Grayscale</label>
                            <input type="text" id="filter-grayscale" class="livecss-control" data-css-property="filter" data-filter-type="grayscale" placeholder="e.g., 100%, 0.5">
                            
                            <label class="livecss-control-label">Hue Rotate</label>
                            <input type="text" id="filter-hue-rotate" class="livecss-control" data-css-property="filter" data-filter-type="hue-rotate" placeholder="e.g., 90deg">
                            
                            <label class="livecss-control-label">Invert</label>
                            <input type="text" id="filter-invert" class="livecss-control" data-css-property="filter" data-filter-type="invert" placeholder="e.g., 100%, 0.5">
                            
                            <label class="livecss-control-label">Saturate</label>
                            <input type="text" id="filter-saturate" class="livecss-control" data-css-property="filter" data-filter-type="saturate" placeholder="e.g., 200%, 0.5">
                            
                            <label class="livecss-control-label">Sepia</label>
                            <input type="text" id="filter-sepia" class="livecss-control" data-css-property="filter" data-filter-type="sepia" placeholder="e.g., 100%, 0.5">
                        </div>
                    </div>
                    
                    <!-- Lists -->
                    <div class="livecss-accordion-item">
                        <div class="livecss-accordion-header"><h3>Lists</h3></div>
                        <div class="livecss-accordion-content">
                            <label class="livecss-control-label">List Style Type</label>
                            <input type="text" id="list-style-type" class="livecss-control" data-css-property="list-style-type" data-list-type="type" placeholder="e.g., disc, decimal, none">
                            
                            <label class="livecss-control-label">List Style Position</label>
                            <input type="text" id="list-style-position" class="livecss-control" data-css-property="list-style-position" data-list-type="position" placeholder="e.g., inside, outside">
                        </div>
                    </div>
                    
                    <!-- Layout -->
                    <div class="livecss-accordion-item">
                        <div class="livecss-accordion-header"><h3>Layout</h3></div>
                        <div class="livecss-accordion-content">
                            <label class="livecss-control-label">Display</label>
                            <select id="display" class="livecss-control" data-css-property="display">
                                <option value="block">block</option>
                                <option value="inline">inline</option>
                                <option value="inline-block">inline-block</option>
                                <option value="flex">flex</option>
                                <option value="grid">grid</option>
                                <option value="none">none</option>
                            </select>
                        
                            <label class="livecss-control-label">Flex Direction</label>
                            <select id="flex-direction" class="livecss-control" data-css-property="flex-direction">
                                <option value="row">row</option>
                                <option value="row-reverse">row-reverse</option>
                                <option value="column">column</option>
                                <option value="column-reverse">column-reverse</option>
                            </select>
                        
                            <label class="livecss-control-label">Justify Content</label>
                            <select id="justify-content" class="livecss-control" data-css-property="justify-content">
                                <option value="flex-start">flex-start</option>
                                <option value="flex-end">flex-end</option>
                                <option value="center">center</option>
                                <option value="space-between">space-between</option>
                                <option value="space-around">space-around</option>
                                <option value="space-evenly">space-evenly</option>
                            </select>
                        
                            <label class="livecss-control-label">Align Items</label>
                            <select id="align-items" class="livecss-control" data-css-property="align-items">
                                <option value="flex-start">flex-start</option>
                                <option value="flex-end">flex-end</option>
                                <option value="center">center</option>
                                <option value="baseline">baseline</option>
                                <option value="stretch">stretch</option>
                            </select>
                        
                            <label class="livecss-control-label">Flex Wrap</label>
                            <select id="flex-wrap" class="livecss-control" data-css-property="flex-wrap">
                                <option value="nowrap">nowrap</option>
                                <option value="wrap">wrap</option>
                                <option value="wrap-reverse">wrap-reverse</option>
                            </select>
                        
                            <label class="livecss-control-label">Gap</label>
                            <input type="text" id="gap" class="livecss-control" data-css-property="gap" placeholder="e.g., 10px, 1em">
                        
                            <label class="livecss-control-label">Grid Template Columns</label>
                            <input type="text" id="grid-template-columns" class="livecss-control" data-css-property="grid-template-columns" placeholder="e.g., 1fr 1fr, repeat(3, 1fr)">
                        
                            <label class="livecss-control-label">Grid Gap</label>
                            <input type="text" id="grid-gap" class="livecss-control" data-css-property="grid-gap" placeholder="e.g., 10px, 1em">
                        
                            <label class="livecss-control-label">Overflow</label>
                            <select id="overflow" class="livecss-control" data-css-property="overflow">
                                <option value="visible">visible</option>
                                <option value="hidden">hidden</option>
                                <option value="scroll">scroll</option>
                                <option value="auto">auto</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Effects -->
                    <div class="livecss-accordion-item">
                        <div class="livecss-accordion-header"><h3>Effects</h3></div>
                        <div class="livecss-accordion-content">
                            <label class="livecss-control-label">Box Shadow</label>
                            <input type="text" id="box-shadow" class="livecss-control" data-css-property="box-shadow" placeholder="e.g., 2px 2px 5px rgba(0,0,0,0.3)">
                        
                            <label class="livecss-control-label">Opacity</label>
                            <input type="text" id="opacity" class="livecss-control" data-css-property="opacity" placeholder="e.g., 1, 0.5">
                        
                            <label class="livecss-control-label">Mix Blend Mode</label>
                            <select id="mix-blend-mode" class="livecss-control" data-css-property="mix-blend-mode">
                                <option value="normal">normal</option>
                                <option value="multiply">multiply</option>
                                <option value="screen">screen</option>
                                <option value="overlay">overlay</option>
                                <option value="darken">darken</option>
                                <option value="lighten">lighten</option>
                                <option value="color-dodge">color-dodge</option>
                                <option value="color-burn">color-burn</option>
                                <option value="hard-light">hard-light</option>
                                <option value="soft-light">soft-light</option>
                                <option value="difference">difference</option>
                                <option value="exclusion">exclusion</option>
                                <option value="hue">hue</option>
                                <option value="saturation">saturation</option>
                                <option value="color">color</option>
                                <option value="luminosity">luminosity</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Size -->
                    <div class="livecss-accordion-item">
                        <div class="livecss-accordion-header"><h3>Size</h3></div>
                        <div class="livecss-accordion-content">
                            <label class="livecss-control-label">Width</label>
                            <input type="text" id="width" class="livecss-control" data-css-property="width" placeholder="e.g., 100px, 50%, auto">
                        
                            <label class="livecss-control-label">Height</label>
                            <input type="text" id="height" class="livecss-control" data-css-property="height" placeholder="e.g., 10px, 50%, auto">
                        
                            <label class="livecss-control-label">Min Width</label>
                            <input type="text" id="min-width" class="livecss-control" data-css-property="min-width" placeholder="e.g., 100px, 50%">
                        
                            <label class="livecss-control-label">Max Height</label>
                            <input type="text" id="max-height" class="livecss-control" data-css-property="max-height" placeholder="e.g., 100px, 50%">
                        
                            <label class="livecss-control-label">Aspect Ratio</label>
                            <input type="text" id="aspect-ratio" class="livecss-control" data-css-property="aspect-ratio" placeholder="e.g., 16/9, 1/1">
                        </div>
                    </div>
                    
                    <!-- Position -->
                    <div class="livecss-accordion-item">
                        <div class="livecss-accordion-header"><h3>Position</h3></div>
                        <div class="livecss-accordion-content">
                            <label class="livecss-control-label">Position</label>
                            <select id="position" class="livecss-control" data-css-property="position">
                                <option value="static">static</option>
                                <option value="relative">relative</option>
                                <option value="absolute">absolute</option>
                                <option value="fixed">fixed</option>
                                <option value="sticky">sticky</option>
                            </select>
                        
                            <label class="livecss-control-label">Top</label>
                            <input type="text" id="top" class="livecss-control" data-css-property="top" placeholder="e.g., 0, 10px, 5%">
                        
                            <label class="livecss-control-label">Right</label>
                            <input type="text" id="right" class="livecss-control" data-css-property="right" placeholder="e.g., 0, 10px, 5%">
                            
                            <label class="livecss-control-label">Bottom</label>
                            <input type="text" id="bottom" class="livecss-control" data-css-property="bottom" placeholder="e.g., 0, 10px, 5%">
                            
                            <label class="livecss-control-label">Left</label>
                            <input type="text" id="left" class="livecss-control" data-css-property="left" placeholder="e.g., 0, 10px, 5%">
                            
                            <label class="livecss-control-label">Z-Index</label>
                            <input type="number" id="z-index" class="livecss-control" data-css-property="z-index" placeholder="e.g., 1, 999">
                        </div>
                    </div>
                </div>
                
                <div class="livecss-tab-content" data-tab="code">
                    <div class="livecss-code-editor" id="livecss-code-editor"></div>
                </div>
            </div>
            
            <!-- Preview iframe -->
            <div class="livecss-preview">
                <iframe id="livecss-iframe" class="livecss-iframe" src="<?php echo remove_query_arg('csseditor'); ?>"></iframe>
            </div>
        </div>
    </div>
    
    <!-- CodeMirror JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
    
    <script>
    (function() {
        // Initialize variables
        let currentSelector = '';
        let cssRules = {};
        let iframe = document.getElementById('livecss-iframe');
        let iframeDoc = null;
        
        // Initialize CodeMirror
        const codeEditor = CodeMirror(document.getElementById('livecss-code-editor'), {
            mode: 'css',
            theme: 'monokai',
            lineNumbers: true,
            indentUnit: 4,
            lineWrapping: true
        });
        
        // Wait for iframe to load
        iframe.addEventListener('load', function() {
            iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
            window.iframeWindow = iframe.contentWindow; // Assign to window object
            
            // Load saved CSS
            const savedCSS = <?php echo json_encode(get_option("livecss_custom_css", "")); ?>;
            if (savedCSS) {
                parseCSS(savedCSS);
                updateCodeEditor();
            }
            
            // Initialize element selector
            initElementSelector();
        });
        
        // Tab switching
        document.querySelectorAll('.livecss-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Update active tab
                document.querySelectorAll('.livecss-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Update active content
                document.querySelectorAll('.livecss-tab-content').forEach(content => {
                    content.classList.remove('active');
                    if (content.getAttribute('data-tab') === tabId) {
                        content.classList.add('active');
                    }
                });
                
                // Refresh CodeMirror when switching to code tab
                if (tabId === 'code') {
                    codeEditor.refresh();
                }
            });
        });
        
        // Pseudo-class buttons
        document.querySelectorAll('.livecss-pseudo-button').forEach(button => {
            button.addEventListener('click', function() {
                const pseudo = this.getAttribute('data-pseudo');
                const selectorInput = document.getElementById('livecss-selector-input');
                
                // Check if the pseudo-class is already in the selector
                if (!selectorInput.value.includes(pseudo)) {
                    selectorInput.value += pseudo;
                    updateCurrentSelector(selectorInput.value);
                }
            });
        });
        
        // Selector input change
        document.getElementById('livecss-selector-input').addEventListener('input', function() {
            updateCurrentSelector(this.value);
        });
        
        // Visual controls
        document.querySelectorAll('.livecss-control').forEach(control => {
            control.addEventListener('input', function() {
                if (!currentSelector) return;
                
                const property = this.getAttribute('data-css-property');
                const transformType = this.getAttribute('data-transform-type');
                const filterType = this.getAttribute('data-filter-type');
                const listType = this.getAttribute('data-list-type');
                let value = this.value;
                
                // Update value display for range inputs
                if (this.type === 'range') {
                    document.getElementById(`${this.id}-value`).textContent = value;
                }
                
                // Update color preview for color inputs
                // if (control.type === 'color') {
                //     document.getElementById(`${control.id}-preview`).style.backgroundColor = value;
                // }
                });
                
                // Update CSS rule
                if (!cssRules[currentSelector]) {
                    cssRules[currentSelector] = {};
                }

                if (transformType) {
                    // Handle transforms
                    let currentTransform = cssRules[currentSelector]['transform'] || '';
                    const regex = new RegExp(`${transformType}\\(([^)]+)\\)`);
                    if (currentTransform.match(regex)) {
                        currentTransform = currentTransform.replace(regex, `${transformType}(${value})`);
                    } else {
                        currentTransform += ` ${transformType}(${value})`;
                    }
                    cssRules[currentSelector]['transform'] = currentTransform.trim();
                } else if (filterType) {
                    // Handle filters
                    let currentFilter = cssRules[currentSelector]['filter'] || '';
                    const regex = new RegExp(`${filterType}\\(([^)]+)\\)`);
                    if (currentFilter.match(regex)) {
                        currentFilter = currentFilter.replace(regex, `${filterType}(${value})`);
                    } else {
                        currentFilter += ` ${filterType}(${value})`;
                    }
                    cssRules[currentSelector]['filter'] = currentFilter.trim();
                } else if (listType) {
                   // Handle list styles
                   cssRules[currentSelector][property] = value;
                } else {
                    cssRules[currentSelector][property] = value;
                }
                
                // Update preview
                updatePreview();
                
                // Update code editor
                updateCodeEditor();
            });
        });
        
        // Accordion functionality
        document.querySelectorAll('.livecss-accordion-header').forEach((header, index) => {
            header.addEventListener('click', function() {
                const item = this.closest('.livecss-accordion-item');
                const content = item.querySelector('.livecss-accordion-content');
                
                // Close all other accordions
                document.querySelectorAll('.livecss-accordion-item').forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.querySelector('.livecss-accordion-header').classList.remove('active');
                        otherItem.querySelector('.livecss-accordion-content').style.display = 'none';
                    }
                });
                
                // Toggle current accordion
                this.classList.toggle('active');
                if (content.style.display === 'block') {
                    content.style.display = 'none';
                } else {
                    content.style.display = 'block';
                }
            });
        
            // Open the first accordion item by default
            if (index === 0) {
                header.classList.add('active');
                header.nextElementSibling.style.display = 'block';
            }
        });
        
        // Code editor change
        codeEditor.on('change', function() {
            const css = codeEditor.getValue();
            parseCSS(css);
            updatePreview();
            updateVisualControls();
        });
        
        // Save button
        document.getElementById('livecss-save').addEventListener('click', function() {
            const css = generateCSS();
            
            // Send AJAX request to save CSS
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert('CSS saved successfully!');
                    } else {
                        alert('Error saving CSS: ' + response.data);
                    }
                } else {
                    alert('Error saving CSS. Please try again.');
                }
            };
            xhr.send('action=livecss_save&css=' + encodeURIComponent(css) + '&nonce=<?php echo wp_create_nonce('livecss_save'); ?>');
        });
        
        // Initialize element selector
        function initElementSelector() {
            if (!iframeDoc) {
                console.error("iframeDoc is not available.");
                return;
            }
            
            // Add event listener to iframe document
            iframeDoc.addEventListener('mouseover', function(e) {
                 // Remove previous highlight
                 const prevHighlight = iframeDoc.querySelector('.livecss-highlight');
                 if (prevHighlight) {
                     prevHighlight.classList.remove('livecss-highlight');
                     prevHighlight.style.outline = '';
                 }
                 
                 // Highlight current element
                 e.target.classList.add('livecss-highlight');
                 e.target.style.outline = '2px solid #0073aa';
             });
             
             iframeDoc.addEventListener('mouseout', function(e) {
                 // Remove highlight
                 e.target.classList.remove('livecss-highlight');
                 e.target.style.outline = '';
             });
             
             iframeDoc.addEventListener('click', function(e) {
                  e.preventDefault();
                  e.stopPropagation();
                  
                   // Get selector for clicked element
                   const selector = getSelector(e.target);
                   
                   // Update selector input
                   document.getElementById('livecss-selector-input').value = selector;
                   updateCurrentSelector(selector);
                   
                   // Remove highlight
                   e.target.classList.remove('livecss-highlight');
                   e.target.style.outline = '';
               });
           }
           
           // Get CSS selector for an element
         function getSelector(element) {
             // Simple selector generation - can be improved for more specificity
             if (element.id) {
                 return '#' + element.id;
             } else if (element.className) {
                 const classes = element.className.split(' ').filter(c => c && c !== 'livecss-highlight');
                 if (classes.length > 0) {
                     // Ensure the class name itself is not just '.'
                     if (classes[0] === '.') {
                         // If the class name is '.', it's likely an error or a malformed class.
                         // Fallback to tag name to avoid the invalid selector error.
                         return element.tagName.toLowerCase();
                     }
                     return '.' + classes[0];
                 }
             }
             
             return element.tagName.toLowerCase();
         }
         
         // Update current selector
         function updateCurrentSelector(selector) {
             currentSelector = selector;
             
             // Update visual controls based on current selector
             updateVisualControls();
         }
         
         // Update visual controls based on current selector
         function updateVisualControls() {
             if (!currentSelector || !iframeDoc) return;

             const selectedElement = iframeDoc.querySelector(currentSelector);
             if (!selectedElement) return;

             const computedStyle = window.iframeWindow.getComputedStyle(selectedElement);

             // Clear previous values in controls
             document.querySelectorAll('.livecss-control').forEach(control => {
                 control.value = '';
                 if (control.type === 'range') {
                     document.getElementById(`${control.id}-value`).textContent = '';
                 }
                 if (control.type === 'color') {
                     document.getElementById(`${control.id}-preview`).style.backgroundColor = '';
                 }
             });

             // Update each control with computed style
             document.querySelectorAll('.livecss-control').forEach(control => {
                 const property = control.getAttribute('data-property');
                 if (!property) return;

                 let value = computedStyle.getPropertyValue(property);

                 if (value) {
                     const unit = control.getAttribute('data-unit') || '';
 
                     if (unit && value.endsWith(unit)) {
                         value = value.substring(0, value.length - unit.length);
                     }
 
                     control.value = value;
 
                     // Update value display for range inputs
                     if (control.type === 'range') {
                         document.getElementById(`${control.id}-value`).textContent = value + unit;
                     }
 
                     // Update color preview for color inputs
                     if (control.type === 'color') {
                         document.getElementById(`${control.id}-preview`).style.backgroundColor = value;
                     }
                 }
             });
             
             // Handle border-radius separately as it's a shorthand property
             const borderRadiusControl = document.getElementById('border-radius');
             if (borderRadiusControl) {
                 const borderRadiusValue = computedStyle.getPropertyValue('border-radius');
                 if (borderRadiusValue) {
                     borderRadiusControl.value = borderRadiusValue;
                 }
             }
             
             // Handle transform properties
             const transform = computedStyle.getPropertyValue('transform');
             if (transform && transform !== 'none') {
                 // This is a simplified example. A full implementation would parse the transform string.
                 // For now, we'll just set the value if it's a simple transform.
                 document.getElementById('transform-rotate').value = transform.includes('rotate') ? transform.match(/rotate\(([^)]+)\)/)[1] : '';
                 document.getElementById('transform-scale').value = transform.includes('scale') ? transform.match(/scale\(([^)]+)\)/)[1] : '';
                 document.getElementById('transform-translate-x').value = transform.includes('translateX') ? transform.match(/translateX\(([^)]+)\)/)[1] : '';
                 document.getElementById('transform-translate-y').value = transform.includes('translateY') ? transform.match(/translateY\(([^)]+)\)/)[1] : '';
                 document.getElementById('transform-skew-x').value = transform.includes('skewX') ? transform.match(/skewX\(([^)]+)\)/)[1] : '';
                 document.getElementById('transform-skew-y').value = transform.includes('skewY') ? transform.match(/skewY\(([^)]+)\)/)[1] : '';
             }
             
             // Handle filter properties
             const filter = computedStyle.getPropertyValue('filter');
             if (filter && filter !== 'none') {
                 document.getElementById('filter-blur').value = filter.includes('blur') ? filter.match(/blur\(([^)]+)\)/)[1] : '';
                 document.getElementById('filter-brightness').value = filter.includes('brightness') ? filter.match(/brightness\(([^)]+)\)/)[1] : '';
                 document.getElementById('filter-contrast').value = filter.includes('contrast') ? filter.match(/contrast\(([^)]+)\)/)[1] : '';
                 document.getElementById('filter-drop-shadow').value = filter.includes('drop-shadow') ? filter.match(/drop-shadow\(([^)]+)\)/)[1] : '';
                 document.getElementById('filter-grayscale').value = filter.includes('grayscale') ? filter.match(/grayscale\(([^)]+)\)/)[1] : '';
                 document.getElementById('filter-hue-rotate').value = filter.includes('hue-rotate') ? filter.match(/hue-rotate\(([^)]+)\)/)[1] : '';
                 document.getElementById('filter-invert').value = filter.includes('invert') ? filter.match(/invert\(([^)]+)\)/)[1] : '';
                 document.getElementById('filter-saturate').value = filter.includes('saturate') ? filter.match(/saturate\(([^)]+)\)/)[1] : '';
                 document.getElementById('filter-sepia').value = filter.includes('sepia') ? filter.match(/sepia\(([^)]+)\)/)[1] : '';
             }
             
             // Handle list style properties
             const listStyleTypeControl = document.getElementById('list-style-type');
             if (listStyleTypeControl) {
                 const listStyleTypeValue = computedStyle.getPropertyValue('list-style-type');
                 if (listStyleTypeValue) {
                     listStyleTypeControl.value = listStyleTypeValue;
                 }
             }
             
             const listStylePositionControl = document.getElementById('list-style-position');
             if (listStylePositionControl) {
                 const listStylePositionValue = computedStyle.getPropertyValue('list-style-position');
                 if (listStylePositionValue) {
                     listStylePositionControl.value = listStylePositionValue;
                 }
             }
             
             // Handle layout properties
             const displayControl = document.getElementById('display');
             if (displayControl) {
                 const displayValue = computedStyle.getPropertyValue('display');
                 if (displayValue) {
                     displayControl.value = displayValue;
                 }
             }
             
             const flexDirectionControl = document.getElementById('flex-direction');
             if (flexDirectionControl) {
                 const flexDirectionValue = computedStyle.getPropertyValue('flex-direction');
                 if (flexDirectionValue) {
                     flexDirectionControl.value = flexDirectionValue;
                 }
             }
             
             const justifyContentControl = document.getElementById('justify-content');
             if (justifyContentControl) {
                 const justifyContentValue = computedStyle.getPropertyValue('justify-content');
                 if (justifyContentValue) {
                     justifyContentControl.value = justifyContentValue;
                 }
             }
             
             const alignItemsControl = document.getElementById('align-items');
             if (alignItemsControl) {
                 const alignItemsValue = computedStyle.getPropertyValue('align-items');
                 if (alignItemsValue) {
                     alignItemsControl.value = alignItemsValue;
                 }
             }
             
             const flexWrapControl = document.getElementById('flex-wrap');
             if (flexWrapControl) {
                 const flexWrapValue = computedStyle.getPropertyValue('flex-wrap');
                 if (flexWrapValue) {
                     flexWrapControl.value = flexWrapValue;
                 }
             }
             
             const overflowControl = document.getElementById('overflow');
             if (overflowControl) {
                 const overflowValue = computedStyle.getPropertyValue('overflow');
                 if (overflowValue) {
                     overflowControl.value = overflowValue;
                 }
             }
             
             // Handle gap properties
             const gapControl = document.getElementById('gap');
             if (gapControl) {
                 const gapValue = computedStyle.getPropertyValue('gap');
                 if (gapValue) {
                     gapControl.value = gapValue;
                 }
             }
             
             // Handle grid properties
             const gridTemplateColumnsControl = document.getElementById('grid-template-columns');
             if (gridTemplateColumnsControl) {
                 const gridTemplateColumnsValue = computedStyle.getPropertyValue('grid-template-columns');
                 if (gridTemplateColumnsValue) {
                     gridTemplateColumnsControl.value = gridTemplateColumnsValue;
                 }
             }
             
             const gridGapControl = document.getElementById('grid-gap');
             if (gridGapControl) {
                 const gridGapValue = computedStyle.getPropertyValue('grid-gap');
                 if (gridGapValue) {
                     gridGapControl.value = gridGapValue;
                 }
             }
             
             // Handle effects properties
             const boxShadowControl = document.getElementById('box-shadow');
             if (boxShadowControl) {
                 const boxShadowValue = computedStyle.getPropertyValue('box-shadow');
                 if (boxShadowValue) {
                     boxShadowControl.value = boxShadowValue;
                 }
             }
             
             const opacityControl = document.getElementById('opacity');
             if (opacityControl) {
                 const opacityValue = computedStyle.getPropertyValue('opacity');
                 if (opacityValue) {
                     opacityControl.value = opacityValue;
                 }
             }
             
             // Handle size properties
             const widthControl = document.getElementById('width');
             if (widthControl) {
                 const widthValue = computedStyle.getPropertyValue('width');
                 if (widthValue) {
                     widthControl.value = widthValue;
                 }
             }
             
             const heightControl = document.getElementById('height');
             if (heightControl) {
                 const heightValue = computedStyle.getPropertyValue('height');
                 if (heightValue) {
                     heightControl.value = heightValue;
                 }
             }
             
             const minWidthControl = document.getElementById('min-width');
             if (minWidthControl) {
                 const minWidthValue = computedStyle.getPropertyValue('min-width');
                 if (minWidthValue) {
                     minWidthControl.value = minWidthValue;
                 }
             }
             
             const maxHeightControl = document.getElementById('max-height');
             if (maxHeightControl) {
                 const maxHeightValue = computedStyle.getPropertyValue('max-height');
                 if (maxHeightValue) {
                     maxHeightControl.value = maxHeightValue;
                 }
             }
             
             const aspectRatioControl = document.getElementById('aspect-ratio');
             if (aspectRatioControl) {
                 const aspectRatioValue = computedStyle.getPropertyValue('aspect-ratio');
                 if (aspectRatioValue) {
                     aspectRatioControl.value = aspectRatioValue;
                 }
             }
             
             // Handle position properties
             const positionControl = document.getElementById('position');
             if (positionControl) {
                 const positionValue = computedStyle.getPropertyValue('position');
                 if (positionValue) {
                     positionControl.value = positionValue;
                 }
             }
             
             const topControl = document.getElementById('top');
             if (topControl) {
                 const topValue = computedStyle.getPropertyValue('top');
                 if (topValue) {
                     topControl.value = topValue;
                 }
             }
             
             const rightControl = document.getElementById('right');
             if (rightControl) {
                 const rightValue = computedStyle.getPropertyValue('right');
                 if (rightValue) {
                     rightControl.value = rightValue;
                 }
             }
             
             const bottomControl = document.getElementById('bottom');
             if (bottomControl) {
                 const bottomValue = computedStyle.getPropertyValue('bottom');
                 if (bottomValue) {
                     bottomControl.value = bottomValue;
                 }
             }
             
             const leftControl = document.getElementById('left');
             if (leftControl) {
                 const leftValue = computedStyle.getPropertyValue('left');
                 if (leftValue) {
                     leftControl.value = leftValue;
                 }
             }
             
             const zIndexControl = document.getElementById('z-index');
             if (zIndexControl) {
                 const zIndexValue = computedStyle.getPropertyValue('z-index');
                 if (zIndexValue) {
                     zIndexControl.value = zIndexValue;
                 }
             }
         }

         // Helper function to convert RGB to Hex
         function rgbToHex(rgb) {
             if (!rgb || rgb.startsWith('#')) return rgb; // Already hex or invalid

             const rgbMatch = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
             if (!rgbMatch) return rgb; // Not a valid rgb string

             const toHex = (c) => {
                 const hex = parseInt(c).toString(16);
                 return hex.length === 1 ? '0' + hex : hex;
             };

             return "#" + toHex(rgbMatch[1]) + toHex(rgbMatch[2]) + toHex(rgbMatch[3]);
         }

         // Update preview
         function updatePreview() {
             if (!iframeDoc) return;
             
             // Get or create style element
             let styleEl = iframeDoc.getElementById('livecss-preview-styles');
             if (!styleEl) {
                 styleEl = iframeDoc.createElement('style');
                 styleEl.id = 'livecss-preview-styles';
                 iframeDoc.head.appendChild(styleEl);
             }
             
             // Update style element
             styleEl.textContent = generateCSS();
         }
         
         // Update code editor
         function updateCodeEditor() {
             codeEditor.setValue(generateCSS());
         }
         
         // Generate CSS from rules
         function generateCSS() {
             let css = '';
             
             for (const selector in cssRules) {
                 if (Object.keys(cssRules[selector]).length === 0) continue;
                 
                 css += selector + ' {\n';
                 
                 for (const property in cssRules[selector]) {
                     css += '    ' + property + ': ' + cssRules[selector][property] + ';\n';
                 }
                 
                 css += '}\n\n';
             }
             
             return css;
         }
         
         // Parse CSS string into rules object
         function parseCSS(css) {
             // Simple CSS parser - can be improved for more complex CSS
             cssRules = {};
             
             const ruleRegex = /([^{]+)\s*{\s*([^}]+)\s*}/g;
             const propertyRegex = /\s*([^:]+)\s*:\s*([^;]+)\s*;/g;
             
             let match;
             while ((match = ruleRegex.exec(css)) !== null) {
                 const selector = match[1].trim();
                 const properties = match[2];
                 
                 cssRules[selector] = {};
                 
                 let propMatch;
                 while ((propMatch = propertyRegex.exec(properties)) !== null) {
                     const property = propMatch[1].trim();
                     const value = propMatch[2].trim();
                     
                     cssRules[selector][property] = value;
                 }
             }
         }
     })();
     </script>
     <?php wp_footer(); ?>
 </body>
 </html>