<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LiveCSS Editor</title>
    <?php wp_head(); ?>
<style>
    :root {
        --background: 0 0% 100%;
        --foreground: 0 0% 3.9%;
        --card: 0 0% 100%;
        --card-foreground: 0 0% 3.9%;
        --popover: 0 0% 100%;
        --popover-foreground: 0 0% 3.9%;
        --primary: 0 0% 9%;
        --primary-foreground: 0 0% 98%;
        --secondary: 0 0% 96.1%;
        --secondary-foreground: 0 0% 9%;
        --muted: 0 0% 96.1%;
        --muted-foreground: 0 0% 45.1%;
        --accent: 0 0% 96.1%;
        --accent-foreground: 0 0% 9%;
        --destructive: 0 0% 50%;
        --destructive-foreground: 0 0% 98%;
        --border: 0 0% 89.8%;
        --input: 0 0% 89.8%;
        --ring: 0 0% 3.9%;
        --radius: 0.5rem;
        --spacing: 1rem;
    }

    * {
        box-sizing: border-box;
        border-color: hsl(var(--border));
    }

    body, html {
        margin: 0;
        padding: 0;
        height: 100vh;
        color: hsl(var(--foreground));
        background-color: hsl(var(--background));
        font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
        overflow: hidden;
    }

    .editor-container {
        display: flex;
        flex-direction: column;
        height: 100vh;
        background: hsl(var(--background));
    }

    .header {
        background: hsl(var(--background));
        border-bottom: 1px solid hsl(var(--border));
        padding: var(--spacing) calc(var(--spacing) * 1.5);
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 60px;
    }

    .header h1 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: hsl(var(--foreground));
    }

    .header-actions {
        display: flex;
        gap: calc(var(--spacing) / 2);
    }

    .main-content {
        display: flex;
        flex: 1;
        overflow: hidden;
    }

    .editor-panel {
        width: 400px;
        min-width: 350px;
        background: hsl(var(--card));
        border-right: 1px solid hsl(var(--border));
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .selector-section {
        background: hsl(var(--background));
        border-bottom: 1px solid hsl(var(--border));
        padding: var(--spacing);
    }

    .selector-input {
        width: 100%;
        padding: 0.625rem 0.75rem;
        border: 1px solid hsl(var(--input));
        border-radius: calc(var(--radius) - 2px);
        font-size: 0.875rem;
        margin-bottom: calc(var(--spacing) / 2);
        background: hsl(var(--background));
        transition: border-color 0.2s;
        color: black;
    }
    option{
        color: black;
    }
    .selector-input:focus {
        outline: none;
        border-color: hsl(var(--ring));
        box-shadow: 0 0 0 1px hsl(var(--ring));
    }

    .pseudo-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 0.375rem;
    }

    .pseudo-button {
        padding: 0.25rem 0.5rem;
        background: hsl(var(--secondary));
        color: hsl(var(--secondary-foreground));
        border: 1px solid hsl(var(--border));
        border-radius: calc(var(--radius) - 2px);
        cursor: pointer;
        font-size: 0.75rem;
        font-weight: 500;
        transition: all 0.2s;
    }

    .pseudo-button:hover {
        background: hsl(var(--accent));
        color: hsl(var(--accent-foreground));
    }

    .tabs {
        display: flex;
        background: hsl(var(--muted));
        border-bottom: 1px solid hsl(var(--border));
    }

    .tab {
        padding: 0.75rem 1.25rem;
        cursor: pointer;
        border: none;
        background: none;
        font-weight: 500;
        font-size: 0.875rem;
        color: hsl(var(--muted-foreground));
        transition: all 0.2s;
        position: relative;
    }

    .tab:hover {
        color: hsl(var(--foreground));
        background: hsl(var(--accent));
    }

    .tab.active {
        color: hsl(var(--foreground));
        background: hsl(var(--background));
    }

    .tab.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: hsl(var(--foreground));
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
        background: hsl(var(--card));
        border: 1px solid hsl(var(--border));
        border-radius: var(--radius);
        margin-bottom: var(--spacing);
        overflow: hidden;
    }

    .accordion-header {
        background: hsl(var(--background));
        padding: 0.875rem 1rem;
        cursor: pointer;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background-color 0.2s;
        user-select: none;
        font-size: 0.875rem;
    }

    .accordion-header:hover {
        background: hsl(var(--accent));
    }

    .accordion-header::after {
        content: '+';
        font-size: 1.125rem;
        transition: transform 0.2s;
    }

    .accordion-header.active::after {
        content: '−';
    }

    .accordion-content {
        padding: 1rem;
        border-top: 1px solid hsl(var(--border));
        display: none;
    }

    .accordion-content.active {
        display: block;
    }

    .control-group {
        margin-bottom: 1rem;
    }

    .control-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        font-size: 0.875rem;
        color: hsl(var(--foreground));
    }

    .control {
        width: 100%;
        padding: 0.625rem 0.75rem;
        border: 1px solid hsl(var(--input));
        border-radius: calc(var(--radius) - 2px);
        font-size: 0.875rem;
        background: hsl(var(--background));
        transition: border-color 0.2s;
    }

    .control:focus {
        outline: none;
        border-color: hsl(var(--ring));
        box-shadow: 0 0 0 1px hsl(var(--ring));
    }

    .control[type="color"] {
        padding: 0.125rem;
        height: 2.5rem;
        cursor: pointer;
    }

    .button {
        padding: 0.625rem 1.25rem;
        border: 1px solid transparent;
        border-radius: var(--radius);
        cursor: pointer;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .button-primary {
        background: hsl(var(--primary));
        color: hsl(var(--primary-foreground));
        border-color: hsl(var(--primary));
    }

    .button-primary:hover {
        background: hsl(var(--primary) / 0.9);
        border-color: hsl(var(--primary) / 0.9);
    }

    .button-danger {
        background: hsl(var(--destructive));
        color: hsl(var(--destructive-foreground));
        border-color: hsl(var(--destructive));
    }

    .button-danger:hover {
        background: hsl(var(--destructive) / 0.9);
        border-color: hsl(var(--destructive) / 0.9);
    }

    .preview-area {
        flex: 1;
        position: relative;
        background: hsl(var(--background));
    }

    .preview-iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    .code-editor {
        height: 400px;
        border: 1px solid hsl(var(--border));
        border-radius: var(--radius);
        overflow: hidden;
    }

    .element-highlight {
        outline: 2px solid hsl(var(--primary)) !important;
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
            border-bottom: 1px solid hsl(var(--border));
        }
    }

    .status-message {
        position: fixed;
        top: 70px;
        right: 20px;
        padding: 0.75rem 1rem;
        border-radius: var(--radius);
        color: hsl(var(--primary-foreground));
        z-index: 1000;
        opacity: 0;
        transform: translateY(-1rem);
        transition: all 0.2s;
        font-size: 0.875rem;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border: 1px solid hsl(var(--border));
    }

    .status-message.show {
        opacity: 1;
        transform: translateY(0);
    }

    .status-message.success {
        background: hsl(var(--background));
        color: hsl(var(--foreground));
        border-color: hsl(var(--border));
    }

    .status-message.error {
        background: hsl(var(--destructive));
        color: hsl(var(--destructive-foreground));
        border-color: hsl(var(--destructive));
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
                <button id="save-button" class="button button-primary">Save Changes</button>
                <a href="<?php echo remove_query_arg('csseditor'); ?>" class="button button-danger">Exit Editor</a>
            </div>
        </header>

        <main class="main-content">
            <aside class="editor-panel">
                <section class="selector-section">
                    <input type="text" id="selector-input" class="selector-input" placeholder="Enter CSS selector (e.g., .my-class, #my-id)">
                    <div class="pseudo-buttons">
                        <button class="pseudo-button" data-pseudo=":hover">:hover</button>
                        <button class="pseudo-button" data-pseudo=":focus">:focus</button>
                        <button class="pseudo-button" data-pseudo=":active">:active</button>
                        <button class="pseudo-button" data-pseudo="::before">::before</button>
                        <button class="pseudo-button" data-pseudo="::after">::after</button>
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
                document.querySelectorAll('.pseudo-button').forEach(button => {
                    button.addEventListener('click', () => {
                        const pseudo = button.dataset.pseudo;
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
                document.getElementById('save-button').addEventListener('click', () => {
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