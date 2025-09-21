<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LiveCSS Editor</title>
    <?php wp_head(); ?>
    
    <style>
        body {
            margin-top: 0 !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        
        #wpadminbar {
            display: none !important;
        }

        :root {
            --primary-color: #0073aa;
            --primary-hover: #005177;
            --secondary-color: #f1f1f1;
            --danger-color: #dc3232;
            --danger-hover: #c62d2d;
            --border-color: #ddd;
            --text-color: #333;
            --bg-color: #fff;
            --panel-bg: #f9f9f9;
            --shadow: 0 2px 4px rgba(0,0,0,0.1);
            --radius: 4px;
            --spacing: 12px;
        }

        * {
            box-sizing: border-box;
        }

        body, html {
            margin: 0;
            padding: 0;
            height: 100vh;
            color: var(--text-color);
            overflow: hidden;
        }

        .editor-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: var(--bg-color);
        }

        .header {
            background: var(--bg-color);
            border-bottom: 1px solid var(--border-color);
            padding: var(--spacing) calc(var(--spacing) * 2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 60px;
            box-shadow: var(--shadow);
            z-index: 100;
        }

        .header h1 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .header-actions {
            display: flex;
            gap: var(--spacing);
        }

        .main-content {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        .editor-panel {
            width: 400px;
            min-width: 350px;
            background: var(--panel-bg);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .selector-section {
            background: var(--bg-color);
            border-bottom: 1px solid var(--border-color);
            padding: var(--spacing);
        }

        .selector-input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            font-size: 14px;
            margin-bottom: var(--spacing);
        }

        .pseudo-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .pseudo-btn {
            padding: 4px 8px;
            background: var(--secondary-color);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
        }

        .pseudo-btn:hover {
            background: var(--primary-color);
            color: white;
        }

        .tabs {
            display: flex;
            background: var(--secondary-color);
            border-bottom: 1px solid var(--border-color);
        }

        .tab {
            padding: 12px 20px;
            cursor: pointer;
            border-right: 1px solid var(--border-color);
            transition: background 0.2s;
            font-weight: 500;
            background: none;
            border-top: none;
            border-left: none;
            color: var(--text-color);
        }

        .tab:hover {
            background: rgba(0,115,170,0.1);
        }

        .tab.active {
            background: var(--bg-color);
            border-bottom: 2px solid var(--primary-color);
            margin-bottom: -1px;
        }

        .tab-content {
            flex: 1;
            overflow-y: auto;
            padding: var(--spacing);
        }

        .tab-content.hidden {
            display: none;
        }

        .accordion-item {
            background: var(--bg-color);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            margin-bottom: var(--spacing);
            overflow: hidden;
        }

        .accordion-header {
            background: var(--secondary-color);
            padding: 12px 16px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s;
            user-select: none;
        }

        .accordion-header:hover {
            background: #e8e8e8;
        }

        .accordion-header::after {
            content: '+';
            font-size: 18px;
            transition: transform 0.2s;
        }

        .accordion-header.active::after {
            content: '−';
        }

        .accordion-content {
            padding: 16px;
            border-top: 1px solid var(--border-color);
            display: none;
        }

        .accordion-content.active {
            display: block;
        }

        .control-group {
            margin-bottom: 16px;
        }

        .control-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            font-size: 13px;
            color: var(--text-color);
        }

        .control {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            font-size: 14px;
            background: var(--bg-color);
        }

        .control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(0,115,170,0.2);
        }

        .control[type="color"] {
            padding: 2px;
            height: 36px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background: var(--danger-hover);
        }

        .preview-area {
            flex: 1;
            position: relative;
            background: var(--bg-color);
        }

        .preview-iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .code-editor {
            height: 400px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .element-highlight {
            outline: 2px solid var(--primary-color) !important;
            outline-offset: -2px;
        }

        @media (max-width: 768px) {
            .main-content {
                flex-direction: column;
            }

            .editor-panel {
                width: 100%;
                height: 50%;
                border-right: none;
                border-bottom: 1px solid var(--border-color);
            }
        }

        .status-message {
            position: fixed;
            top: 70px;
            right: 20px;
            padding: 10px 15px;
            border-radius: var(--radius);
            color: white;
            z-index: 1000;
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.3s;
        }

        .status-message.show {
            opacity: 1;
            transform: translateY(0);
        }

        .status-message.success {
            background: #4caf50;
        }

        .status-message.error {
            background: var(--danger-color);
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/default.min.css">
</head>
<body>
    <div class="editor-container">
        <header class="header">
            <h1>LiveCSS Editor</h1>
            <div class="header-actions">
                <button id="save-btn" class="btn btn-primary">Save Changes</button>
                <a href="<?php echo remove_query_arg('csseditor'); ?>" class="btn btn-danger">Exit Editor</a>
            </div>
        </header>

        <main class="main-content">
            <aside class="editor-panel">
                <section class="selector-section">
                    <input type="text" id="selector-input" class="selector-input" placeholder="Enter CSS selector (e.g., .my-class, #my-id)">
                    <div class="pseudo-buttons">
                        <button class="pseudo-btn" data-pseudo=":hover">:hover</button>
                        <button class="pseudo-btn" data-pseudo=":focus">:focus</button>
                        <button class="pseudo-btn" data-pseudo=":active">:active</button>
                        <button class="pseudo-btn" data-pseudo="::before">::before</button>
                        <button class="pseudo-btn" data-pseudo="::after">::after</button>
                    </div>
                </section>

                <nav class="tabs">
                    <button class="tab active" data-tab="visual">Visual Editor</button>
                    <button class="tab" data-tab="code">Code Editor</button>
                </nav>

                <div class="tab-content" data-tab="visual">
                    <div class="accordion-item">
                        <div class="accordion-header">Typography</div>
                        <div class="accordion-content">
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
                                <label class="control-label">Font Family</label>
                                <input type="text" class="control" data-property="font-family" placeholder="Arial, sans-serif">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Text Color</label>
                                <input type="color" class="control" data-property="color">
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
                                <label class="control-label">Line Height</label>
                                <input type="text" class="control" data-property="line-height" placeholder="1.5, 24px">
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
                                <label class="control-label">Background Repeat</label>
                                <select class="control" data-property="background-repeat">
                                    <option value="">Default</option>
                                    <option value="no-repeat">No Repeat</option>
                                    <option value="repeat">Repeat</option>
                                    <option value="repeat-x">Repeat X</option>
                                    <option value="repeat-y">Repeat Y</option>
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
                                    <option value="grid">Grid</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Width</label>
                                <input type="text" class="control" data-property="width" placeholder="100px, 50%, auto">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Height</label>
                                <input type="text" class="control" data-property="height" placeholder="100px, 50%, auto">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Padding</label>
                                <input type="text" class="control" data-property="padding" placeholder="10px, 1em">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Margin</label>
                                <input type="text" class="control" data-property="margin" placeholder="10px, 1em, auto">
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header">Borders</div>
                        <div class="accordion-content">
                            <div class="control-group">
                                <label class="control-label">Border Width</label>
                                <input type="text" class="control" data-property="border-width" placeholder="1px, 2px">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Border Style</label>
                                <select class="control" data-property="border-style">
                                    <option value="">Default</option>
                                    <option value="solid">Solid</option>
                                    <option value="dashed">Dashed</option>
                                    <option value="dotted">Dotted</option>
                                    <option value="double">Double</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Border Color</label>
                                <input type="color" class="control" data-property="border-color">
                            </div>
                            <div class="control-group">
                                <label class="control-label">Border Radius</label>
                                <input type="text" class="control" data-property="border-radius" placeholder="5px, 50%">
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

                <div class="tab-content hidden" data-tab="code">
                    <div class="code-editor" id="code-editor"></div>
                </div>
            </aside>

            <main class="preview-area">
                <iframe id="preview-iframe" class="preview-iframe" src="<?php echo remove_query_arg('csseditor'); ?>"></iframe>
            </main>
        </main>
    </div>

    <div id="status-message" class="status-message"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>

    <script>
        class LiveCSSEditor {
            constructor() {
                this.currentSelector = '';
                this.cssRules = new Map();
                this.iframe = null;
                this.iframeDoc = null;
                this.codeEditor = null;
                this.isUpdatingFromCode = false;
                
                this.init();
            }

            init() {
                this.setupCodeEditor();
                this.setupEventListeners();
                this.setupIframe();
                this.loadSavedCSS();
            }

            setupCodeEditor() {
                this.codeEditor = CodeMirror(document.getElementById('code-editor'), {
                    mode: 'css',
                    theme: 'default',
                    lineNumbers: true,
                    indentUnit: 2,
                    lineWrapping: true,
                    autoCloseBrackets: true,
                    matchBrackets: true
                });

                this.codeEditor.on('change', () => {
                    if (!this.isUpdatingFromCode) {
                        this.parseCSS(this.codeEditor.getValue());
                        this.updatePreview();
                        this.updateVisualControls();
                    }
                });
            }

            setupEventListeners() {
                // Tab switching
                document.querySelectorAll('.tab').forEach(tab => {
                    tab.addEventListener('click', (e) => {
                        this.switchTab(e.target.dataset.tab);
                    });
                });

                // Accordion functionality
                document.querySelectorAll('.accordion-header').forEach(header => {
                    header.addEventListener('click', () => {
                        this.toggleAccordion(header);
                    });
                });

                // Selector input
                const selectorInput = document.getElementById('selector-input');
                selectorInput.addEventListener('input', () => {
                    this.currentSelector = selectorInput.value.trim();
                    this.updateVisualControls();
                });

                // Pseudo-class buttons
                document.querySelectorAll('.pseudo-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const pseudo = btn.dataset.pseudo;
                        if (!selectorInput.value.includes(pseudo)) {
                            selectorInput.value += pseudo;
                            this.currentSelector = selectorInput.value.trim();
                            this.updateVisualControls();
                        }
                    });
                });

                // Visual controls
                document.querySelectorAll('.control[data-property]').forEach(control => {
                    const event = control.type === 'range' ? 'input' : 'change';
                    control.addEventListener(event, () => {
                        this.updateCSSProperty(control.dataset.property, control.value);
                    });
                });

                // Save button
                document.getElementById('save-btn').addEventListener('click', () => {
                    this.saveCSS();
                });
            }

            setupIframe() {
                this.iframe = document.getElementById('preview-iframe');
                
                this.iframe.addEventListener('load', () => {
                    this.iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow.document;
                    this.setupElementSelector();
                    this.updatePreview();
                });
            }

            setupElementSelector() {
                if (!this.iframeDoc) return;

                let highlightedElement = null;

                this.iframeDoc.addEventListener('mouseover', (e) => {
                    if (highlightedElement) {
                        highlightedElement.classList.remove('element-highlight');
                    }
                    e.target.classList.add('element-highlight');
                    highlightedElement = e.target;
                });

                this.iframeDoc.addEventListener('mouseout', (e) => {
                    e.target.classList.remove('element-highlight');
                });

                this.iframeDoc.addEventListener('click', (e) => {
                    e.preventDefault();
                    const selector = this.generateSelector(e.target);
                    document.getElementById('selector-input').value = selector;
                    this.currentSelector = selector;
                    this.updateVisualControls();
                    
                    e.target.classList.remove('element-highlight');
                });
            }

            generateSelector(element) {
                if (element.id) {
                    return '#' + element.id;
                }
                
                if (element.className) {
                    const classes = element.className.split(' ')
                        .filter(cls => cls && cls !== 'element-highlight');
                    if (classes.length > 0) {
                        return '.' + classes[0];
                    }
                }
                
                return element.tagName.toLowerCase();
            }

            switchTab(tabName) {
                document.querySelectorAll('.tab').forEach(tab => {
                    tab.classList.toggle('active', tab.dataset.tab === tabName);
                });

                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.toggle('hidden', content.dataset.tab !== tabName);
                });

                if (tabName === 'code' && this.codeEditor) {
                    setTimeout(() => this.codeEditor.refresh(), 50);
                }
            }

            toggleAccordion(header) {
                const content = header.nextElementSibling;
                const isActive = header.classList.contains('active');

                document.querySelectorAll('.accordion-header').forEach(h => {
                    h.classList.remove('active');
                    h.nextElementSibling.classList.remove('active');
                });

                if (!isActive) {
                    header.classList.add('active');
                    content.classList.add('active');
                }
            }

            updateCSSProperty(property, value) {
                if (!this.currentSelector || !property) return;

                if (!this.cssRules.has(this.currentSelector)) {
                    this.cssRules.set(this.currentSelector, new Map());
                }

                const selectorRules = this.cssRules.get(this.currentSelector);
                
                if (value && value.trim()) {
                    selectorRules.set(property, value.trim());
                } else {
                    selectorRules.delete(property);
                }

                this.updatePreview();
                this.updateCodeEditor();
            }

            updatePreview() {
                if (!this.iframeDoc) return;

                let styleEl = this.iframeDoc.getElementById('livecss-custom-styles');
                if (!styleEl) {
                    styleEl = this.iframeDoc.createElement('style');
                    styleEl.id = 'livecss-custom-styles';
                    this.iframeDoc.head.appendChild(styleEl);
                }

                styleEl.textContent = this.generateCSS();
            }

            updateCodeEditor() {
                if (!this.codeEditor) return;
                
                this.isUpdatingFromCode = true;
                this.codeEditor.setValue(this.generateCSS());
                this.isUpdatingFromCode = false;
            }

            updateVisualControls() {
                if (!this.currentSelector || !this.cssRules.has(this.currentSelector)) {
                    document.querySelectorAll('.control[data-property]').forEach(control => {
                        control.value = '';
                    });
                    return;
                }

                const selectorRules = this.cssRules.get(this.currentSelector);
                
                document.querySelectorAll('.control[data-property]').forEach(control => {
                    const property = control.dataset.property;
                    const value = selectorRules.get(property) || '';
                    control.value = value;
                });
            }

            generateCSS() {
                let css = '';
                
                for (const [selector, rules] of this.cssRules) {
                    if (rules.size === 0) continue;
                    
                    css += `${selector} {\n`;
                    
                    for (const [property, value] of rules) {
                        css += `  ${property}: ${value};\n`;
                    }
                    
                    css += '}\n\n';
                }
                
                return css;
            }

            parseCSS(css) {
                this.cssRules.clear();
                
                const rules = css.match(/[^{}]+\{[^{}]+\}/g) || [];
                
                rules.forEach(rule => {
                    const match = rule.match(/^([^{]+)\{([^}]+)\}$/);
                    if (!match) return;
                    
                    const selector = match[1].trim();
                    const declarations = match[2].trim();
                    
                    const selectorRules = new Map();
                    
                    declarations.split(';').forEach(declaration => {
                        const colonIndex = declaration.indexOf(':');
                        if (colonIndex === -1) return;
                        
                        const property = declaration.substring(0, colonIndex).trim();
                        const value = declaration.substring(colonIndex + 1).trim();
                        
                        if (property && value) {
                            selectorRules.set(property, value);
                        }
                    });
                    
                    if (selectorRules.size > 0) {
                        this.cssRules.set(selector, selectorRules);
                    }
                });
            }

            loadSavedCSS() {
                const savedCSS = <?php echo json_encode(get_option('livecss_custom_css', '')); ?>;
                if (savedCSS) {
                    this.parseCSS(savedCSS);
                    this.updateCodeEditor();
                    this.updatePreview();
                }
            }

            saveCSS() {
                const css = this.generateCSS();
                
                const formData = new FormData();
                formData.append('action', 'livecss_save');
                formData.append('css', css);
                formData.append('nonce', <?php echo json_encode(wp_create_nonce('livecss_save')); ?>);

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.showStatusMessage('CSS saved successfully!', 'success');
                    } else {
                        this.showStatusMessage('Error saving CSS: ' + (data.data || 'Unknown error'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.showStatusMessage('Error saving CSS. Please try again.', 'error');
                });
            }

            showStatusMessage(message, type = 'success') {
                const statusEl = document.getElementById('status-message');
                statusEl.textContent = message;
                statusEl.className = `status-message ${type} show`;
                
                setTimeout(() => {
                    statusEl.classList.remove('show');
                }, 3000);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            new LiveCSSEditor();
        });
    </script>

    <?php wp_footer(); ?>
</body>
</html>