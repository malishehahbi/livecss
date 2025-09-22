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
   <script>
        class LiveCSSEditor {
            constructor() {
                console.log('LiveCSSEditor constructor called');
                this.currentSelector = '';
                this.cssRules = new Map();
                this.iframe = null;
                this.iframeDoc = null;
                this.codeEditor = null;
                this.isUpdatingFromCode = false;
                
                this.init();
            }

            init() {
                console.log('LiveCSSEditor init called');
                this.setupCodeEditor();
                this.setupEventListeners();
                this.setupIframe();
                this.loadSavedCSS();
            }
            
            setupCodeEditor() {
                // Check if CodeMirror is available
                if (typeof CodeMirror === 'undefined') {
                    console.error('CodeMirror library not loaded');
                    return;
                }
                
                const editorElement = document.getElementById('code-editor');
                if (!editorElement) {
                    console.error('CodeMirror editor element not found');
                    return;
                }
                
                this.codeEditor = CodeMirror(editorElement, {
                    mode: 'css',
                    theme: 'default',
                    lineNumbers: true,
                    indentUnit: 2,
                    lineWrapping: true,
                    autoCloseBrackets: true,
                    matchBrackets: true,
                    // Auto-complete configuration
                    extraKeys: {
                        "Ctrl-Space": "autocomplete"
                        
                    },
                    hintOptions: {
                        completeSingle: false,
                        closeOnUnfocus: true
                    }
                });
                
                // Add auto-complete trigger on typing
                this.codeEditor.on('inputRead', (cm, change) => {
                    if (change.text[0] !== ';' && change.text[0] !== ' ' && change.text[0] !== '\n') {
                        cm.showHint();
                }
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
                const tabs = document.querySelectorAll('.tab');
                console.log('Found tabs:', tabs.length);
                tabs.forEach(tab => {
                    tab.addEventListener('click', (e) => {
                        console.log('Tab clicked:', e.target.dataset.tab);
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
                console.log('Switching to tab:', tabName);
                document.querySelectorAll('.tab').forEach(tab => {
                    tab.classList.toggle('active', tab.dataset.tab === tabName);
                });

                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.toggle('hidden', content.dataset.tab !== tabName);
                });

                if (tabName === 'code' && this.codeEditor) {
                    console.log('Refreshing CodeMirror editor');
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
                
                // Handle special combined properties
                if (this.isTransformProperty(property)) {
                    this.updateCombinedProperty(selectorRules, 'transform', this.buildTransformValue.bind(this));
                } else if (this.isFilterProperty(property)) {
                    this.updateCombinedProperty(selectorRules, 'filter', this.buildFilterValue.bind(this));
                } else if (value && value.trim()) {
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
                    
                    css += `${selector} {
`;
                    
                    // Handle special combined properties
                    const combinedRules = new Map(rules);
                    
                    // Handle transform properties
                    if (this.hasTransformProperties(rules)) {
                        const transformValue = this.buildTransformValue(rules);
                        if (transformValue) {
                            combinedRules.set('transform', transformValue);
                        }
                        // Remove individual transform properties
                        this.removeTransformProperties(combinedRules);
                    }
                    
                    // Handle filter properties
                    if (this.hasFilterProperties(rules)) {
                        const filterValue = this.buildFilterValue(rules);
                        if (filterValue) {
                            combinedRules.set('filter', filterValue);
                        }
                        // Remove individual filter properties
                        this.removeFilterProperties(combinedRules);
                    }
                    
                    for (const [property, value] of combinedRules) {
                        css += `  ${property}: ${value};
`;
                    }
                    
                    css += `}
`;
                }
                
                return css;
            }

            parseCSS(css) {
                this.cssRules.clear();
                
                const rules = css.match(/[^{}]+{[^{}]+}/g) || [];
                
                rules.forEach(rule => {
                    const match = rule.match(/^([^{]+){([^}]+)}$/);
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
                            // Handle special properties
                            if (property === 'transform') {
                                this.parseTransformValue(selectorRules, value);
                            } else if (property === 'filter') {
                                this.parseFilterValue(selectorRules, value);
                            } else {
                                selectorRules.set(property, value);
                            }
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

            // Helper methods for special properties
            isTransformProperty(property) {
                return ['rotate', 'scale', 'scaleX', 'scaleY', 'translate', 'translateX', 'translateY', 'skew', 'skewX', 'skewY'].includes(property);
            }

            isFilterProperty(property) {
                return ['blur', 'brightness', 'contrast', 'grayscale', 'hue-rotate', 'invert', 'opacity', 'saturate', 'sepia', 'drop-shadow'].includes(property);
            }

            hasTransformProperties(rules) {
                for (const property of rules.keys()) {
                    if (this.isTransformProperty(property)) {
                        return true;
                    }
                }
                return false;
            }

            hasFilterProperties(rules) {
                for (const property of rules.keys()) {
                    if (this.isFilterProperty(property)) {
                        return true;
                    }
                }
                return false;
            }

            removeTransformProperties(rules) {
                const transformProps = ['rotate', 'scale', 'scaleX', 'scaleY', 'translate', 'translateX', 'translateY', 'skew', 'skewX', 'skewY'];
                transformProps.forEach(prop => rules.delete(prop));
            }

            removeFilterProperties(rules) {
                const filterProps = ['blur', 'brightness', 'contrast', 'grayscale', 'hue-rotate', 'invert', 'opacity', 'saturate', 'sepia', 'drop-shadow'];
                filterProps.forEach(prop => rules.delete(prop));
            }

            updateCombinedProperty(selectorRules, combinedProperty, buildValueFn) {
                const combinedValue = buildValueFn(selectorRules);
                if (combinedValue) {
                    selectorRules.set(combinedProperty, combinedValue);
                } else {
                    selectorRules.delete(combinedProperty);
                }
            }

            buildTransformValue(rules) {
                let transformParts = [];

                // Handle rotate
                if (rules.has('rotate') && rules.get('rotate')) {
                    transformParts.push(`rotate(${rules.get('rotate')})`);
                }

                // Handle scale
                if (rules.has('scale') && rules.get('scale')) {
                    transformParts.push(`scale(${rules.get('scale')})`);
                } else {
                    if (rules.has('scaleX') && rules.get('scaleX')) {
                        transformParts.push(`scaleX(${rules.get('scaleX')})`);
                    }
                    if (rules.has('scaleY') && rules.get('scaleY')) {
                        transformParts.push(`scaleY(${rules.get('scaleY')})`);
                    }
                }

                // Handle translate
                if (rules.has('translate') && rules.get('translate')) {
                    transformParts.push(`translate(${rules.get('translate')})`);
                } else {
                    if (rules.has('translateX') && rules.get('translateX')) {
                        transformParts.push(`translateX(${rules.get('translateX')})`);
                    }
                    if (rules.has('translateY') && rules.get('translateY')) {
                        transformParts.push(`translateY(${rules.get('translateY')})`);
                    }
                }

                // Handle skew
                if (rules.has('skew') && rules.get('skew')) {
                    transformParts.push(`skew(${rules.get('skew')})`);
                } else {
                    if (rules.has('skewX') && rules.get('skewX')) {
                        transformParts.push(`skewX(${rules.get('skewX')})`);
                    }
                    if (rules.has('skewY') && rules.get('skewY')) {
                        transformParts.push(`skewY(${rules.get('skewY')})`);
                    }
                }

                return transformParts.join(' ');
            }

            buildFilterValue(rules) {
                let filterParts = [];

                // Handle all filter properties
                const filterProps = ['blur', 'brightness', 'contrast', 'grayscale', 'hue-rotate', 'invert', 'opacity', 'saturate', 'sepia', 'drop-shadow'];
                filterProps.forEach(prop => {
                    if (rules.has(prop) && rules.get(prop)) {
                        if (prop === 'hue-rotate') {
                            filterParts.push(`hue-rotate(${rules.get(prop)})`);
                        } else {
                            filterParts.push(`${prop}(${rules.get(prop)})`);
                        }
                    }
                });

                return filterParts.join(' ');
            }

            parseTransformValue(selectorRules, value) {
                // Set the combined transform value
                selectorRules.set('transform', value);
                
                // Parse individual transform functions (simplified parsing)
                const transformFunctions = value.match(/(\\w+)\([^)]*\)/g) || [];
                
                transformFunctions.forEach(func => {
                    const match = func.match(/(\\w+)\(([^)]*)\)/);
                    if (match) {
                        const funcName = match[1];
                        const funcValue = match[2];
                        
                        // Map to individual properties
                        switch (funcName) {
                            case 'rotate':
                                selectorRules.set('rotate', funcValue);
                                break;
                            case 'scale':
                                selectorRules.set('scale', funcValue);
                                break;
                            case 'scaleX':
                                selectorRules.set('scaleX', funcValue);
                                break;
                            case 'scaleY':
                                selectorRules.set('scaleY', funcValue);
                                break;
                            case 'translate':
                                selectorRules.set('translate', funcValue);
                                break;
                            case 'translateX':
                                selectorRules.set('translateX', funcValue);
                                break;
                            case 'translateY':
                                selectorRules.set('translateY', funcValue);
                                break;
                            case 'skew':
                                selectorRules.set('skew', funcValue);
                                break;
                            case 'skewX':
                                selectorRules.set('skewX', funcValue);
                                break;
                            case 'skewY':
                                selectorRules.set('skewY', funcValue);
                                break;
                        }
                    }
                });
            }

            parseFilterValue(selectorRules, value) {
                // Set the combined filter value
                selectorRules.set('filter', value);
                
                // Parse individual filter functions (simplified parsing)
                const filterFunctions = value.match(/(\\w+-?\\w*)\([^)]*\)/g) || [];
                
                filterFunctions.forEach(func => {
                    const match = func.match(/(\\w+-?\\w*)\(([^)]*)\)/);
                    if (match) {
                        const funcName = match[1];
                        const funcValue = match[2];
                        
                        // Map to individual properties
                        switch (funcName) {
                            case 'blur':
                                selectorRules.set('blur', funcValue);
                                break;
                            case 'brightness':
                                selectorRules.set('brightness', funcValue);
                                break;
                            case 'contrast':
                                selectorRules.set('contrast', funcValue);
                                break;
                            case 'grayscale':
                                selectorRules.set('grayscale', funcValue);
                                break;
                            case 'hue-rotate':
                                selectorRules.set('hue-rotate', funcValue);
                                break;
                            case 'invert':
                                selectorRules.set('invert', funcValue);
                                break;
                            case 'opacity':
                                selectorRules.set('opacity', funcValue);
                                break;
                            case 'saturate':
                                selectorRules.set('saturate', funcValue);
                                break;
                            case 'sepia':
                                selectorRules.set('sepia', funcValue);
                                break;
                            case 'drop-shadow':
                                selectorRules.set('drop-shadow', funcValue);
                                break;
                        }
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOM loaded, initializing LiveCSSEditor');
            // Add a small delay to ensure all resources are loaded
            setTimeout(() => {
                new LiveCSSEditor();
            }, 100);
        });
    </script>

    <?php wp_footer(); ?>
</body>
</html>