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

    <style>
        .livecss-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .livecss-loader.hidden {
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.5s ease-out;
        }

        .livecss-loader-content {
            text-align: center;
            max-width: 400px;
            padding: 2rem;
        }

        .livecss-spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #e3e3e3;
            border-top: 4px solid #1aa2e6;
            border-radius: 50%;
            animation: livecss-spin 1s linear infinite;
            margin: 0 auto 1.5rem;
        }

        @keyframes livecss-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .livecss-loader-content h3 {
            margin: 0 0 1.5rem;
            color: #333;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .livecss-loader-progress {
            margin-top: 1rem;
        }

        .livecss-progress-bar {
            width: 100%;
            height: 8px;
            background: #e3e3e3;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 0.75rem;
        }

        .livecss-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #1aa2e6, #0d7fb8);
            border-radius: 4px;
            width: 0%;
            transition: width 0.3s ease;
        }

        .livecss-loader-status {
            font-size: 0.9rem;
            color: #666;
            min-height: 1.2em;
        }

        .livecss-loader-error {
            color: #d32f2f;
            font-weight: 500;
        }
    </style>

    <script>
        class LiveCSSLoader {
            constructor() {
                this.loader = document.getElementById('livecss-loader');
                this.progressBar = document.getElementById('loader-progress');
                this.statusText = document.getElementById('loader-status');
                this.progress = 0;
                this.maxRetries = 3;
                this.retryCount = 0;
                this.initializationSteps = [
                    { name: 'CodeMirror Library', weight: 15 },
                    { name: 'DOM Elements', weight: 10 },
                    { name: 'Code Editor', weight: 20 },
                    { name: 'Event Listeners', weight: 15 },
                    { name: 'Preview Iframe', weight: 25 },
                    { name: 'Final Verification', weight: 15 }
                ];
                this.currentStep = 0;
            }

            updateProgress(stepIndex, message) {
                this.currentStep = stepIndex;
                
                // Calculate cumulative progress
                let totalProgress = 0;
                for (let i = 0; i < stepIndex; i++) {
                    totalProgress += this.initializationSteps[i].weight;
                }
                
                this.progress = Math.min(totalProgress, 100);
                this.progressBar.style.width = this.progress + '%';
                this.statusText.textContent = message;
            }

            showError(message) {
                this.statusText.textContent = message;
                this.statusText.classList.add('livecss-loader-error');
            }

            hide() {
                this.updateProgress(this.initializationSteps.length, 'Ready!');
                this.progress = 100;
                this.progressBar.style.width = '100%';
                
                setTimeout(() => {
                    this.loader.classList.add('hidden');
                    setTimeout(() => {
                        this.loader.style.display = 'none';
                    }, 500);
                }, 300);
            }
        }

        class LiveCSSEditor {
            constructor() {
                console.log('LiveCSSEditor constructor called');
                this.currentSelector = '';
                this.cssRules = new Map();
                this.selectedElement = null;
                this.selectionHighlighted = [];
                this.iframe = null;
                this.iframeDoc = null;
                this.codeEditor = null;
                this.isUpdatingFromCode = false;
                this.selectorSuggestions = [];
                this.suggestActiveIndex = -1;
                this.currentDevice = 'desktop';
                this.breakpoints = {
                    tablet: '(max-width: 1024px)',
                    mobile: '(max-width: 640px)'
                };
                this.extraMediaCSS = '';
                this.extraRootCSS = '';
                this.loader = new LiveCSSLoader();
                this.isInitialized = false;
                
                this.init();
            }

            async init() {
                console.log('LiveCSSEditor init called');
                
                try {
                    // Step 1: Check CodeMirror
                    this.loader.updateProgress(0, 'Checking CodeMirror library...');
                    await this.waitForCodeMirror();
                    
                    // Step 2: Verify DOM elements
                    this.loader.updateProgress(1, 'Verifying DOM elements...');
                    await this.verifyDOMElements();
                    
                    // Step 3: Setup code editor
                    this.loader.updateProgress(2, 'Setting up code editor...');
                    await this.setupCodeEditor();
                    
                    // Step 4: Setup event listeners
                    this.loader.updateProgress(3, 'Initializing event listeners...');
                    await this.setupEventListeners();
                    
                    // Step 5: Setup iframe
                    this.loader.updateProgress(4, 'Loading preview iframe...');
                    await this.setupIframe();
                    
                    // Step 6: Load saved CSS and final verification
                    this.loader.updateProgress(5, 'Loading saved CSS and verifying...');
                    await this.loadSavedCSS();
                    await this.verifyFunctionality();
                    
                    this.isInitialized = true;
                    console.log('LiveCSSEditor fully initialized');
                    this.loader.hide();
                    
                } catch (error) {
                    console.error('LiveCSSEditor initialization failed:', error);
                    this.loader.showError('Initialization failed: ' + error.message);
                    
                    // Retry logic
                    if (this.loader.retryCount < this.loader.maxRetries) {
                        this.loader.retryCount++;
                        this.loader.updateProgress(0, `Retrying initialization (${this.loader.retryCount}/${this.loader.maxRetries})...`);
                        setTimeout(() => this.init(), 2000);
                    } else {
                        this.loader.showError('Failed to initialize after multiple attempts. Please refresh the page.');
                    }
                }
            }

            waitForCodeMirror() {
                return new Promise((resolve, reject) => {
                    let attempts = 0;
                    const maxAttempts = 50; // 5 seconds
                    
                    const checkCodeMirror = () => {
                        if (typeof CodeMirror !== 'undefined' && CodeMirror.modes && CodeMirror.modes.css) {
                            resolve();
                        } else if (attempts < maxAttempts) {
                            attempts++;
                            setTimeout(checkCodeMirror, 100);
                        } else {
                            reject(new Error('CodeMirror library failed to load'));
                        }
                    };
                    
                    checkCodeMirror();
                });
            }

            verifyDOMElements() {
                return new Promise((resolve, reject) => {
                    const requiredElements = [
                        'code-editor',
                        'preview-iframe',
                        'selector-input',
                        'save-button'
                    ];
                    
                    const missingElements = requiredElements.filter(id => !document.getElementById(id));
                    
                    if (missingElements.length > 0) {
                        reject(new Error(`Missing required elements: ${missingElements.join(', ')}`));
                    } else {
                        resolve();
                    }
                });
            }

            verifyFunctionality() {
                return new Promise((resolve, reject) => {
                    // Verify critical functionality
                    const checks = [
                        () => this.codeEditor !== null && typeof this.codeEditor.getValue === 'function',
                        () => this.iframe !== null,
                        () => this.iframeDoc !== null,
                        () => document.querySelectorAll('.tab').length > 0,
                        () => document.querySelectorAll('.control[data-property]').length > 0,
                        () => this.iframeDoc.getElementById('livecss-editor-styles') !== null // Verify styles are injected
                    ];
                    
                    const failedChecks = checks.filter((check, index) => {
                        try {
                            return !check();
                        } catch (e) {
                            console.error(`Verification check ${index} failed:`, e);
                            return true;
                        }
                    });
                    
                    if (failedChecks.length > 0) {
                        reject(new Error('Functionality verification failed'));
                    } else {
                        // Final test: ensure CSS can be applied to iframe
                        try {
                            this.updatePreview();
                            resolve();
                        } catch (error) {
                            reject(new Error('CSS preview functionality failed: ' + error.message));
                        }
                    }
                });
            }

            // Method to hide WordPress admin bar in iframe
            hideWordPressAdminBar() {
                if (!this.iframeDoc) return;
                
                try {
                    // Remove admin bar element if it exists
                    const adminBar = this.iframeDoc.getElementById('wpadminbar');
                    if (adminBar) {
                        adminBar.remove();
                        console.log('WordPress admin bar removed from iframe');
                    }
                    
                    // Remove admin bar related classes from html and body
                    const html = this.iframeDoc.documentElement;
                    const body = this.iframeDoc.body;
                    
                    if (html) {
                        html.classList.remove('wp-toolbar');
                        html.style.marginTop = '0';
                    }
                    
                    if (body) {
                        body.classList.remove('admin-bar');
                        body.style.marginTop = '0';
                        body.style.paddingTop = '0';
                    }
                    
                    // Remove any admin bar related inline styles
                    const adminBarStyles = this.iframeDoc.querySelectorAll('style[id*="admin"], link[id*="admin-bar"]');
                    adminBarStyles.forEach(style => {
                        if (style.textContent && style.textContent.includes('wpadminbar')) {
                            style.remove();
                        }
                    });
                    
                } catch (error) {
                    console.warn('Error removing admin bar:', error);
                }
            }

            // Method to force refresh all CSS in iframe
            forceRefreshPreview() {
                if (!this.iframeDoc) {
                    console.warn('Cannot refresh preview: iframe not ready');
                    return;
                }
                
                console.log('Force refreshing preview CSS...');
                
                // Remove all LiveCSS styles
                const existingStyles = this.iframeDoc.querySelectorAll('#livecss-custom-styles, #livecss-editor-styles');
                existingStyles.forEach(style => style.remove());
                
                // Re-inject editor styles with admin bar hiding
                const editorStyle = this.iframeDoc.createElement('style');
                editorStyle.id = 'livecss-editor-styles';
                editorStyle.type = 'text/css';
                editorStyle.textContent = `
                    .livecss-hover-highlight { outline: 1px dashed #1aa2e6 !important; cursor: pointer; }
                    .livecss-selection-highlight { outline: 1px solid #1aa2e6 !important; }
                    .element-highlight { outline: 2px solid #ff9800 !important; }
                    
                    /* Hide WordPress admin bar in preview */
                    #wpadminbar { display: none !important; }
                    html { margin-top: 0 !important; }
                    * html body { margin-top: 0 !important; }
                    @media screen and (max-width: 782px) {
                        html { margin-top: 0 !important; }
                        * html body { margin-top: 0 !important; }
                    }
                `;
                this.iframeDoc.head.appendChild(editorStyle);
                
                // Hide admin bar elements
                this.hideWordPressAdminBar();
                
                // Re-apply custom CSS
                this.updatePreview();
                
                console.log('Preview CSS refreshed');
            }
            
            setupCodeEditor() {
                return new Promise((resolve, reject) => {
                    try {
                        // Check if CodeMirror is available
                        if (typeof CodeMirror === 'undefined') {
                            reject(new Error('CodeMirror library not loaded'));
                            return;
                        }
                        
                        const editorElement = document.getElementById('code-editor');
                        if (!editorElement) {
                            reject(new Error('CodeMirror editor element not found'));
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

                        // Verify editor is working
                        setTimeout(() => {
                            if (this.codeEditor && typeof this.codeEditor.getValue === 'function') {
                                resolve();
                            } else {
                                reject(new Error('CodeMirror editor initialization failed'));
                            }
                        }, 100);
                        
                    } catch (error) {
                        reject(error);
                    }
                });
            }
            

            setupEventListeners() {
                return new Promise((resolve, reject) => {
                    try {
                        // Tab switching
                        const tabs = document.querySelectorAll('.tab');
                        console.log('Found tabs:', tabs.length);
                        
                        if (tabs.length === 0) {
                            reject(new Error('No tabs found in DOM'));
                            return;
                        }
                        
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
                        if (!selectorInput) {
                            reject(new Error('Selector input element not found'));
                            return;
                        }
                        
                        selectorInput.addEventListener('input', () => {
                            this.updateSelectionFromInput();
                            this.updateSelectorSuggestions();
                        });
                        selectorInput.addEventListener('focus', () => {
                            this.updateSelectorSuggestions();
                        });
                        selectorInput.addEventListener('blur', () => {
                            this.hideSelectorSuggestions();
                        });
                        selectorInput.addEventListener('keydown', (e) => {
                            const list = document.getElementById('selector-suggest');
                            if (!list || list.classList.contains('hidden')) return;
                            if (e.key === 'ArrowDown') { e.preventDefault(); this.moveSuggestActive(1); }
                            if (e.key === 'ArrowUp')   { e.preventDefault(); this.moveSuggestActive(-1); }
                            if (e.key === 'Enter')     { e.preventDefault(); this.applyActiveSuggestion(); }
                            if (e.key === 'Escape')    { e.preventDefault(); this.hideSelectorSuggestions(); }
                        });
                        document.addEventListener('click', (e) => {
                            const wrap = document.querySelector('.selector-section');
                            const list = document.getElementById('selector-suggest');
                            if (!wrap || !list) return;
                            if (!wrap.contains(e.target)) this.hideSelectorSuggestions();
                        });

                        // Pseudo-class buttons
                        document.querySelectorAll('.pseudo-button').forEach(button => {
                            button.addEventListener('click', () => {
                                const pseudo = button.dataset.pseudo;
                                if (!selectorInput.value.includes(pseudo)) {
                                    selectorInput.value += pseudo;
                                    this.currentSelector = selectorInput.value.trim();
                                    this.updateVisualControls();
                                    this.renderUsageDots();
                                }
                            });
                        });

                        // Visual controls
                        document.querySelectorAll('.control[data-property]').forEach(control => {
                            control.addEventListener('input', () => {
                                console.log('1. Control input detected:', { property: control.dataset.property, value: control.value });
                                this.updateCSSProperty(control.dataset.property, control.value);
                                this.renderUsageDots();
                            });
                        });

                        // Save button
                        const saveButton = document.getElementById('save-button');
                        if (!saveButton) {
                            reject(new Error('Save button not found'));
                            return;
                        }
                        saveButton.addEventListener('click', () => {
                            this.saveCSS();
                        });

                        // Device toggles
                        const deviceButtons = document.querySelectorAll('.device-btn');
                        deviceButtons.forEach(btn => {
                            btn.addEventListener('click', () => {
                                deviceButtons.forEach(b => b.classList.remove('active'));
                                btn.classList.add('active');
                                this.setDevice(btn.dataset.device);
                            });
                        });

                        // Breadcrumb clicks
                        const breadcrumb = document.getElementById('element-breadcrumb');
                        if (breadcrumb) {
                            breadcrumb.addEventListener('click', (e) => {
                                if (e.target.classList.contains('breadcrumb-item') && e.target.dataset.selector) {
                                    const selector = e.target.dataset.selector;
                                    const selectorInput = document.getElementById('selector-input');
                                    selectorInput.value = selector;

                                    // Manually trigger input event to update selection highlight
                                    const inputEvent = new Event('input', { bubbles: true });
                                    selectorInput.dispatchEvent(inputEvent);
                                    this.renderUsageDots();
                                }
                            });
                        }

                        // Verify event listeners are set up
                        setTimeout(() => {
                            resolve();
                        }, 50);
                        
                    } catch (error) {
                        reject(error);
                    }
                });
            }

            setupIframe() {
                return new Promise((resolve, reject) => {
                    try {
                        this.iframe = document.getElementById('preview-iframe');
                        
                        if (!this.iframe) {
                            reject(new Error('Preview iframe not found'));
                            return;
                        }
                        
                        // Set up timeout for iframe loading
                        const timeoutId = setTimeout(() => {
                            reject(new Error('Iframe loading timeout'));
                        }, 10000); // 10 second timeout
                        
                        this.iframe.addEventListener('load', () => {
                            clearTimeout(timeoutId);
                            
                            try {
                                console.log('A. Iframe has loaded.');
                                this.iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow.document;
                                
                                if (!this.iframeDoc) {
                                    reject(new Error('Could not access iframe document'));
                                    return;
                                }
                                
                                // Wait for iframe document to be fully ready
                                const setupIframeContent = () => {
                                    // Inject styles for element highlighting and selection + hide admin bar
                                    const style = this.iframeDoc.createElement('style');
                                    style.id = 'livecss-editor-styles';
                                    style.type = 'text/css';
                                    style.textContent = `
                                        .livecss-hover-highlight { outline: 1px dashed #1aa2e6 !important; cursor: pointer; }
                                        .livecss-selection-highlight { outline: 1px solid #1aa2e6 !important; }
                                        .element-highlight { outline: 2px solid #ff9800 !important; }
                                        
                                        /* Hide WordPress admin bar in preview */
                                        #wpadminbar { display: none !important; }
                                        html { margin-top: 0 !important; }
                                        * html body { margin-top: 0 !important; }
                                        @media screen and (max-width: 782px) {
                                            html { margin-top: 0 !important; }
                                            * html body { margin-top: 0 !important; }
                                        }
                                    `;
                                    this.iframeDoc.head.appendChild(style);
                                    
                                    // Also remove admin bar elements directly if they exist
                                    this.hideWordPressAdminBar();

                                    this.setupElementSelector();
                                    
                                    // Apply any existing CSS immediately
                                    this.updatePreview();

                                    // Initialize suggestions after iframe is ready
                                    this.updateSelectorSuggestions(true);

                                    // Apply initial device (desktop)
                                    this.applyPreviewDevice();
                                    
                                    // Set up admin bar monitoring
                                    this.startAdminBarMonitoring();
                                    
                                    resolve();
                                };
                                
                                // Check if iframe document is ready
                                if (this.iframeDoc.readyState === 'complete') {
                                    setupIframeContent();
                                } else {
                                    // Wait for iframe document to be ready
                                    this.iframeDoc.addEventListener('DOMContentLoaded', setupIframeContent);
                                    // Backup timeout in case DOMContentLoaded doesn't fire
                                    setTimeout(setupIframeContent, 1000);
                                }
                                
                            } catch (error) {
                                reject(error);
                            }
                        });
                        
                        this.iframe.addEventListener('error', () => {
                            clearTimeout(timeoutId);
                            reject(new Error('Iframe failed to load'));
                        });
                        
                        // If iframe is already loaded
                        if (this.iframe.contentDocument && this.iframe.contentDocument.readyState === 'complete') {
                            this.iframe.dispatchEvent(new Event('load'));
                        }
                        
                    } catch (error) {
                        reject(error);
                    }
                });
            }

            setupElementSelector() {
                if (!this.iframeDoc) return;

                this.iframeDoc.body.addEventListener('mouseover', (e) => {
                    e.target.classList.add('livecss-hover-highlight');
                });

                this.iframeDoc.body.addEventListener('mouseout', (e) => {
                    e.target.classList.remove('livecss-hover-highlight');
                });

                this.iframeDoc.body.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    const selector = this.generateSelector(e.target);
                    document.getElementById('selector-input').value = selector;
                    
                    // Manually trigger input event to update selection
                    const inputEvent = new Event('input', { bubbles: true });
                    document.getElementById('selector-input').dispatchEvent(inputEvent);

                    this.updateBreadcrumb(e.target);
                }, true);
            }

            updateSelectionFromInput() {
                // Clear previous selection
                this.selectionHighlighted.forEach(el => {
                    el.classList.remove('livecss-selection-highlight');
                });

                this.currentSelector = document.getElementById('selector-input').value.trim();

                if (this.currentSelector) {
                    try {
                        this.selectionHighlighted = Array.from(this.iframeDoc.querySelectorAll(this.currentSelector));
                        this.selectionHighlighted.forEach(el => {
                            el.classList.add('livecss-selection-highlight');
                        });
                    } catch (e) {
                        // Invalid selector
                        this.selectionHighlighted = [];
                    }
                }
                this.updateVisualControls();
                this.renderUsageDots();
            }

            updateBreadcrumb(element) {
                const breadcrumbContainer = document.getElementById('element-breadcrumb');
                breadcrumbContainer.innerHTML = '';
                let currentElement = element;
                let breadcrumbs = [];

                while (currentElement && currentElement.tagName.toLowerCase() !== 'html') {
                    const tag = currentElement.tagName.toLowerCase();
                    const id = currentElement.id ? `#${currentElement.id}` : '';
                    const classes = Array.from(currentElement.classList)
                        .filter(c => c !== 'livecss-hover-highlight' && c !== 'livecss-selection-highlight');

                    let partsHtml = [];
                    // Add tag name part
                    partsHtml.push(`<span class="breadcrumb-item" data-selector="${tag}">${tag}</span>`);

                    // Add ID part
                    if (id) {
                        partsHtml.push(`<span class="breadcrumb-item" data-selector="${id}">${id}</span>`);
                    }

                    // Add class parts
                    classes.forEach(cls => {
                        partsHtml.push(`<span class="breadcrumb-item" data-selector=".${cls}">.${cls}</span>`);
                    });

                    const breadcrumbHtml = `<span class="breadcrumb-part-group">${partsHtml.join('')}</span>`;
                    breadcrumbs.unshift(breadcrumbHtml);
                    currentElement = currentElement.parentElement;
                }

                breadcrumbContainer.innerHTML = breadcrumbs.join(' &gt; ');
            }

            generateSelector(element) {
                if (element.id) {
                    return '#' + element.id;
                }

                const classes = Array.from(element.classList)
                    .filter(cls => cls && cls !== 'livecss-hover-highlight' && cls !== 'livecss-selection-highlight');

                if (classes.length > 0) {
                    return '.' + classes.join('.');
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

                const key = this.getScopedSelectorKey(this.currentSelector);
                if (!this.cssRules.has(key)) {
                    this.cssRules.set(key, new Map());
                }

                const selectorRules = this.cssRules.get(key);

                // First, update the individual property in the map
                if (value && value.trim()) {
                    selectorRules.set(property, value.trim());
                } else {
                    selectorRules.delete(property);
                }

                // Now, if it's a special property, rebuild the combined value
                if (this.isTransformProperty(property)) {
                    this.updateCombinedProperty(selectorRules, 'transform', this.buildTransformValue.bind(this));
                } else if (this.isFilterProperty(property)) {
                    this.updateCombinedProperty(selectorRules, 'filter', this.buildFilterValue.bind(this));
                }

                console.log('2. Updating internal CSS state for:', this.currentSelector);
                this.updatePreview();
                this.updateCodeEditor();
            }

            updatePreview() {
                if (!this.iframeDoc) {
                    console.error('3. ERROR: Cannot update preview because iframe is not ready.');
                    return;
                }

                try {
                    // Remove old style tag if it exists
                    const oldStyleEl = this.iframeDoc.getElementById('livecss-custom-styles');
                    if (oldStyleEl) {
                        oldStyleEl.remove();
                    }

                    // Create and add a new one
                    const newStyleEl = this.iframeDoc.createElement('style');
                    newStyleEl.id = 'livecss-custom-styles';
                    newStyleEl.type = 'text/css';
                    
                    const css = this.generateCSS({ is_preview: true });
                    console.log('3. Updating preview with new CSS:', css);
                    
                    // Set CSS content with fallback methods
                    if (newStyleEl.styleSheet) {
                        // IE support
                        newStyleEl.styleSheet.cssText = css;
                    } else {
                        newStyleEl.textContent = css;
                    }

                    // Ensure it's added to the head
                    if (this.iframeDoc.head) {
                        this.iframeDoc.head.appendChild(newStyleEl);
                    } else {
                        // Fallback if head doesn't exist yet
                        this.iframeDoc.documentElement.appendChild(newStyleEl);
                    }
                    
                    // Force a repaint
                    this.iframeDoc.body.offsetHeight;
                    
                } catch (error) {
                    console.error('Error updating preview:', error);
                }
            }

            updateCodeEditor() {
                if (!this.codeEditor) return;
                
                this.isUpdatingFromCode = true;
                this.codeEditor.setValue(this.generateCSS());
                this.isUpdatingFromCode = false;
            }

            updateVisualControls() {
                const key = this.getScopedSelectorKey(this.currentSelector || '');
                if (!this.currentSelector || !this.cssRules.has(key)) {
                    document.querySelectorAll('.control[data-property]').forEach(control => {
                        control.value = '';
                    });
                    // Still refresh usage dots to clear them
                    this.renderUsageDots(true);
                    return;
                }

                const selectorRules = this.cssRules.get(key);
                
                document.querySelectorAll('.control[data-property]').forEach(control => {
                    const property = control.dataset.property;
                    const value = selectorRules.get(property) || '';
                    control.value = value;
                });

                this.renderUsageDots();
            }

            // ===== Selector suggestions =====
            collectSelectorsFromDom() {
                if (!this.iframeDoc) return [];
                const selectors = new Set();

                const all = this.iframeDoc.querySelectorAll('*');
                all.forEach(el => {
                    const tag = el.tagName.toLowerCase();
                    selectors.add(tag);
                    if (el.id) selectors.add('#' + el.id);
                    el.classList.forEach(cls => selectors.add('.' + cls));
                });

                return Array.from(selectors);
            }

            updateSelectorSuggestions(initial = false) {
                const input = document.getElementById('selector-input');
                const list = document.getElementById('selector-suggest');
                if (!input || !list) return;
                // Only show suggestions when the input is focused
                if (document.activeElement !== input) {
                    this.hideSelectorSuggestions();
                    return;
                }

                const q = (input.value || '').trim();
                const candidates = this.collectSelectorsFromDom();
                let items = candidates;
                if (q) {
                    const qLower = q.toLowerCase();
                    items = candidates.filter(s => s.toLowerCase().includes(qLower));
                }

                // Prioritize ID > class > tag
                items.sort((a,b)=>{
                    const rank = s => s.startsWith('#') ? 0 : (s.startsWith('.') ? 1 : 2);
                    const ra = rank(a), rb = rank(b);
                    if (ra !== rb) return ra - rb;
                    return a.length - b.length;
                });

                // Limit
                items = items.slice(0, 100);
                this.selectorSuggestions = items;
                this.suggestActiveIndex = initial ? -1 : 0;
                this.renderSelectorSuggestions();
            }

            renderSelectorSuggestions() {
                const list = document.getElementById('selector-suggest');
                if (!list) return;

                const input = document.getElementById('selector-input');
                if (document.activeElement !== input) {
                    this.hideSelectorSuggestions();
                    return;
                }

                if (!this.selectorSuggestions.length) {
                    this.hideSelectorSuggestions();
                    return;
                }

                list.innerHTML = '';
                this.selectorSuggestions.forEach((s, idx) => {
                    const item = document.createElement('div');
                    item.className = 'selector-suggest-item' + (idx === this.suggestActiveIndex ? ' active' : '');
                    item.setAttribute('role', 'option');
                    item.dataset.index = String(idx);

                    const type = s.startsWith('#') ? 'ID' : (s.startsWith('.') ? 'Class' : 'Tag');
                    const typeEl = document.createElement('span');
                    typeEl.className = 'selector-suggest-type';
                    typeEl.textContent = type;
                    const textEl = document.createElement('span');
                    textEl.className = 'selector-suggest-text';
                    textEl.textContent = s;

                    item.appendChild(typeEl);
                    item.appendChild(textEl);

                    item.addEventListener('mouseenter', () => {
                        this.setSuggestActive(idx);
                        // Highlight matches in preview
                        this.previewHighlightSelector(s);
                    });
                    item.addEventListener('mouseleave', () => {
                        this.clearPreviewHighlight();
                    });
                    // Use mousedown to apply before the input loses focus
                    item.addEventListener('mousedown', (ev) => {
                        ev.preventDefault();
                        this.applySuggestion(idx);
                    });

                    list.appendChild(item);
                });

                list.classList.remove('hidden');
            }

            hideSelectorSuggestions() {
                const list = document.getElementById('selector-suggest');
                if (list) list.classList.add('hidden');
                this.clearPreviewHighlight();
                this.suggestActiveIndex = -1;
            }

            setSuggestActive(idx){
                this.suggestActiveIndex = idx;
                const list = document.getElementById('selector-suggest');
                if (!list) return;
                list.querySelectorAll('.selector-suggest-item').forEach((el,i)=>{
                    el.classList.toggle('active', i === idx);
                });
            }

            moveSuggestActive(delta){
                if (!this.selectorSuggestions.length) return;
                let idx = this.suggestActiveIndex + delta;
                if (idx < 0) idx = this.selectorSuggestions.length - 1;
                if (idx >= this.selectorSuggestions.length) idx = 0;
                this.setSuggestActive(idx);
                const s = this.selectorSuggestions[idx];
                this.previewHighlightSelector(s);
            }

            applyActiveSuggestion(){
                if (this.suggestActiveIndex < 0) return;
                this.applySuggestion(this.suggestActiveIndex);
            }

            applySuggestion(idx){
                const s = this.selectorSuggestions[idx];
                if (!s) return;
                const input = document.getElementById('selector-input');
                input.value = s;
                const evt = new Event('input', { bubbles: true });
                input.dispatchEvent(evt);
                this.hideSelectorSuggestions();
            }

            previewHighlightSelector(sel){
                if (!this.iframeDoc) return;
                this.clearPreviewHighlight();
                try{
                    const els = this.iframeDoc.querySelectorAll(sel);
                    els.forEach(el=> el.classList.add('element-highlight'));
                }catch(e){ /* invalid selector */ }
            }

            clearPreviewHighlight(){
                if (!this.iframeDoc) return;
                this.iframeDoc.querySelectorAll('.element-highlight').forEach(el=> el.classList.remove('element-highlight'));
            }

            // ===== Usage dots and reset logic =====
            createUsageDot({ isSection = false, onReset }) {
                const dot = document.createElement('span');
                dot.className = 'usage-dot' + (isSection ? ' usage-dot--section' : '');
                dot.setAttribute('title', isSection ? 'Reset section' : 'Reset property');
                dot.setAttribute('role', 'button');
                dot.setAttribute('tabindex', '0');
                dot.addEventListener('click', (e) => {
                    e.stopPropagation();
                    onReset();
                });
                dot.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); onReset(); }
                });
                return dot;
            }

            renderUsageDots(clearOnly = false) {
                // Remove all existing usage dots first
                document.querySelectorAll('.usage-dot').forEach(d => d.remove());
                
                if (clearOnly) return;
                if (!this.currentSelector) return;
                
                const key = this.getScopedSelectorKey(this.currentSelector);
                const selectorRules = this.cssRules.get(key) || new Map();

                // For each accordion section, check if any property is used
                document.querySelectorAll('.accordion-item').forEach(item => {
                    const header = item.querySelector('.accordion-header');
                    const content = item.querySelector('.accordion-content');
                    if (!header || !content) return;

                    const controls = content.querySelectorAll('.control[data-property]');
                    const props = Array.from(controls).map(c => c.dataset.property).filter(Boolean);
                    const usedProps = props.filter(p => selectorRules.has(p) && selectorRules.get(p));

                    // Section-level dot if any property used
                    if (usedProps.length > 0) {
                        const dot = this.createUsageDot({
                            isSection: true,
                            onReset: () => {
                                usedProps.forEach(p => this.updateCSSProperty(p, ''));
                                this.updateVisualControls();
                            }
                        });
                        header.appendChild(dot);
                    }

                    // Property-level dots
                    Array.from(controls).forEach(control => {
                        const prop = control.dataset.property;
                        if (!prop) return;
                        if (selectorRules.has(prop) && selectorRules.get(prop)) {
                            const group = control.closest('.control-group');
                            const label = group ? group.querySelector('.control-label') : null;
                            const target = label || group || control;
                            const dot = this.createUsageDot({
                                isSection: false,
                                onReset: () => {
                                    this.updateCSSProperty(prop, '');
                                    this.updateVisualControls();
                                }
                            });
                            // If label text and dot collide, use a small wrapper
                            target && target.appendChild(dot);
                        }
                    });
                });
            }

            generateCSS(options = { is_preview: false }) {
                let css = '';
                // Organize rules by device scope
                const rootRules = [];
                const tabletRules = [];
                const mobileRules = [];

                for (const [selectorKey, rules] of this.cssRules) {
                    const { scope, selector } = this.parseScopedSelectorKey(selectorKey);
                    const bucket = scope === 'mobile' ? mobileRules : (scope === 'tablet' ? tabletRules : rootRules);
                    bucket.push([selector, rules]);
                }

                const renderBlock = (pairs) => {
                    let out = '';
                    for (const [selector, rules] of pairs) {
                    if (rules.size === 0) continue;
                    
                        out += `${selector} {\n`;
                    
                    const combinedRules = new Map(rules);
                    
                    if (this.hasTransformProperties(rules)) {
                        const transformValue = this.buildTransformValue(rules);
                        if (transformValue) {
                            combinedRules.set('transform', transformValue);
                        }
                        this.removeTransformProperties(combinedRules);
                    }
                    
                    if (this.hasFilterProperties(rules)) {
                        const filterValue = this.buildFilterValue(rules);
                        if (filterValue) {
                            combinedRules.set('filter', filterValue);
                        }
                        this.removeFilterProperties(combinedRules);
                    }
                    
                        for (const [property, value] of combinedRules) {
                            let finalLine = `  ${property}: ${value}`;
                            if (options.is_preview && !value.includes('!important')) {
                                finalLine += ' !important';
                            }
                            finalLine += ';\n';
                            out += finalLine;
                        }
                        
                        out += `}\n`;
                    }
                    return out;
                };

                css += renderBlock(rootRules);
                if (tabletRules.length) {
                    css += `@media ${this.breakpoints.tablet} {\n` + renderBlock(tabletRules) + `}\n`;
                }
                if (mobileRules.length) {
                    css += `@media ${this.breakpoints.mobile} {\n` + renderBlock(mobileRules) + `}\n`;
                }

                // Append any extra CSS we don't parse but want to preserve
                if (this.extraRootCSS && this.extraRootCSS.trim()) css += `\n${this.extraRootCSS}\n`;
                if (this.extraMediaCSS && this.extraMediaCSS.trim()) css += `\n${this.extraMediaCSS}\n`;

                return css;
            }

            parseCSS(css) {
                this.cssRules.clear();

                const extractMedia = (input) => {
                    const blocks = [];
                    const rootParts = [];
                    let i = 0;
                    let last = 0;
                    while (true) {
                        const at = input.indexOf('@media', i);
                        if (at === -1) break;
                        // push root segment before this @media
                        rootParts.push(input.slice(last, at));
                        // find query start to first '{'
                        const brace = input.indexOf('{', at);
                        if (brace === -1) break;
                        const query = input.slice(at + 6, brace).trim();
                        // scan to matching closing brace
                        let pos = brace + 1;
                        let depth = 1;
                        while (pos < input.length && depth > 0) {
                            const ch = input[pos];
                            if (ch === '{') depth++;
                            else if (ch === '}') depth--;
                            pos++;
                        }
                        const body = input.slice(brace + 1, pos - 1);
                        blocks.push({ query, body });
                        last = pos;
                        i = pos;
                    }
                    rootParts.push(input.slice(last));
                    return { blocks, root: rootParts.join('') };
                };

                const parseRuleBlock = (block, scope = 'desktop') => {
                    const rules = block.match(/[^{}]+{[^{}]+}/g) || [];
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
                            const key = this.getScopedSelectorKey(selector, scope);
                            this.cssRules.set(key, selectorRules);
                        }
                    });
                };

                const scopeFromQuery = (q) => {
                    const norm = q.replace(/\s+/g, ' ').toLowerCase();
                    if (/max-width\s*:\s*640px/.test(norm)) return 'mobile';
                    if (/max-width\s*:\s*1024px/.test(norm)) return 'tablet';
                    return 'desktop';
                };

                // Reset extras
                this.extraMediaCSS = '';
                this.extraRootCSS = '';

                const { blocks, root } = extractMedia(css || '');

                // Parse root and collect leftover non-rule CSS
                parseRuleBlock(root, 'desktop');
                let leftoverRoot = root;
                const matchedRootRules = root.match(/[^{}]+{[^{}]+}/g) || [];
                matchedRootRules.forEach(r => { leftoverRoot = leftoverRoot.replace(r, ''); });
                this.extraRootCSS = leftoverRoot.trim();

                // Parse supported media blocks; stash unknown ones intact
                blocks.forEach(({ query, body }) => {
                    const scope = scopeFromQuery(query);
                    if (scope === 'desktop') {
                        // Unknown/custom media – keep as-is
                        this.extraMediaCSS += `\n@media ${query} {\n${body}\n}\n`;
                    } else {
                        parseRuleBlock(body, scope);
                    }
                });
            }

            // ===== Device helpers =====
            setDevice(device) {
                this.currentDevice = device || 'desktop';
                this.applyPreviewDevice();
                this.updateVisualControls();
            }

            applyPreviewDevice() {
                const iframe = document.getElementById('preview-iframe');
                if (!iframe) return;
                if (this.currentDevice === 'desktop') {
                    iframe.style.width = '100%';
                } else if (this.currentDevice === 'tablet') {
                    iframe.style.width = '1024px';
                } else {
                    iframe.style.width = '640px';
                }
            }

            getScopedSelectorKey(selector, scope = this.currentDevice) {
                // Use JSON tuple to avoid collisions with selectors like ::before
                try { return JSON.stringify([scope, selector]); } catch(e) { return scope + '|' + selector; }
            }

            parseScopedSelectorKey(key) {
                try {
                    const [scope, selector] = JSON.parse(key);
                    return { scope: scope || 'desktop', selector: selector || '' };
                } catch(e) {
                    // Fallback for legacy keys 'scope::selector' or 'scope|selector'
                    if (key.includes('::')) {
                        const idx = key.indexOf('::');
                        return { scope: key.substring(0, idx), selector: key.substring(idx + 2) };
                    }
                    if (key.includes('|')) {
                        const idx = key.indexOf('|');
                        return { scope: key.substring(0, idx), selector: key.substring(idx + 1) };
                    }
                    return { scope: 'desktop', selector: key };
                }
            }

            loadSavedCSS() {
                return new Promise((resolve, reject) => {
                    try {
                        <?php
                            $saved_css = '';
                            if (function_exists('wp_upload_dir')) {
                                $upload_dir = call_user_func('wp_upload_dir');
                                $css_file_path = $upload_dir['basedir'] . '/livecss/main.css';
                                if (file_exists($css_file_path)) {
                                    $saved_css = file_get_contents($css_file_path);
                                }
                            }
                        ?>
                        const savedCSS = <?php echo json_encode($saved_css); ?>;
                        if (savedCSS && savedCSS.trim()) {
                            console.log('Loading saved CSS:', savedCSS.substring(0, 200) + '...');
                            this.parseCSS(savedCSS);
                            this.updateCodeEditor();
                            
                            // Force update preview with loaded CSS
                            setTimeout(() => {
                                this.updatePreview();
                            }, 100);
                        } else {
                            console.log('No saved CSS found or CSS is empty');
                        }
                        resolve();
                    } catch (error) {
                        // Don't reject for saved CSS loading errors, just log them
                        console.warn('Error loading saved CSS:', error);
                        resolve();
                    }
                });
            }

            saveCSS() {
                const css = this.generateCSS();
                
                const formData = new FormData();
                formData.append('action', 'livecss_save');
                formData.append('css', css);
                formData.append('nonce', <?php echo function_exists('wp_create_nonce') ? json_encode(call_user_func('wp_create_nonce', 'livecss_save')) : "''"; ?>);

                fetch('<?php echo function_exists('admin_url') ? call_user_func('admin_url', 'admin-ajax.php') : '/wp-admin/admin-ajax.php'; ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.showStatusMessage('CSS saved successfully!', 'success');
                    } else {
                        // Check for the specific file write error
                        if (data.data && data.data === 'Failed to write to CSS file.') {
                            this.showConfirmationPopup('Error: Could not save because the css file may be missing. Would you like to try and recreate it?', (confirmed) => {
                                if (confirmed) {
                                    this.recreateFile();
                                } else {
                                    this.showStatusMessage('Save failed. Please check file permissions or recreate the file manually.', 'error');
                                }
                            });
                        } else {
                            this.showStatusMessage('Error saving CSS: ' + (data.data || 'Unknown error'), 'error');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.showStatusMessage('Error saving CSS. Please try again.', 'error');
                });
            }

            showConfirmationPopup(message, callback) {
                const popup = document.getElementById('confirmation-popup');
                const messageEl = document.getElementById('popup-message');
                const yesButton = document.getElementById('popup-button-yes');
                const noButton = document.getElementById('popup-button-no');

                messageEl.textContent = message;

                const handleYes = () => {
                    this.hideConfirmationPopup();
                    callback(true);
                    yesButton.removeEventListener('click', handleYes);
                    noButton.removeEventListener('click', handleNo);
                };

                const handleNo = () => {
                    this.hideConfirmationPopup();
                    callback(false);
                    yesButton.removeEventListener('click', handleYes);
                    noButton.removeEventListener('click', handleNo);
                };

                yesButton.addEventListener('click', handleYes);
                noButton.addEventListener('click', handleNo);

                popup.classList.add('visible');
            }

            hideConfirmationPopup() {
                const popup = document.getElementById('confirmation-popup');
                popup.classList.remove('visible');
            }

            showStatusMessage(message, type = 'success') {
                const statusEl = document.getElementById('status-message');
                statusEl.textContent = message;
                statusEl.className = `status-message ${type} show`;
                
                setTimeout(() => {
                    statusEl.classList.remove('show');
                }, 3000);
            }

            recreateFile() {
                const formData = new FormData();
                formData.append('action', 'livecss_recreate_file');
                formData.append('nonce', <?php echo function_exists('wp_create_nonce') ? json_encode(call_user_func('wp_create_nonce', 'livecss_recreate_file')) : "''"; ?>);

                fetch('<?php echo function_exists('admin_url') ? call_user_func('admin_url', 'admin-ajax.php') : '/wp-admin/admin-ajax.php'; ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.showStatusMessage('File recreated! Retrying save...', 'success');
                        // Retry saving the CSS now that the file exists
                        this.saveCSS();
                    } else {
                        this.showStatusMessage('Error: Could not recreate file. ' + (data.data || ''), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error recreating file:', error);
                    this.showStatusMessage('A critical error occurred while trying to recreate the file.', 'error');
                });
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

            // Public method to check if editor is ready
            isReady() {
                return this.isInitialized && 
                       this.codeEditor !== null && 
                       this.iframe !== null && 
                       this.iframeDoc !== null;
            }

            // Method to get initialization status for debugging
            getStatus() {
                const status = {
                    isInitialized: this.isInitialized,
                    hasCodeEditor: this.codeEditor !== null,
                    hasIframe: this.iframe !== null,
                    hasIframeDoc: this.iframeDoc !== null,
                    currentDevice: this.currentDevice,
                    cssRulesCount: this.cssRules.size,
                    iframeReady: false,
                    stylesInjected: false
                };
                
                if (this.iframeDoc) {
                    status.iframeReady = true;
                    status.stylesInjected = !!this.iframeDoc.getElementById('livecss-editor-styles');
                    status.customStylesPresent = !!this.iframeDoc.getElementById('livecss-custom-styles');
                }
                
                return status;
            }

            // Debug method to show current CSS
            getCurrentCSS() {
                return this.generateCSS();
            }

            // Method to start monitoring for admin bar and hide it
            startAdminBarMonitoring() {
                if (!this.iframeDoc) return;
                
                // Initial hide
                this.hideWordPressAdminBar();
                
                // Set up a MutationObserver to watch for admin bar being added back
                try {
                    if (window.MutationObserver) {
                        const observer = new MutationObserver((mutations) => {
                            mutations.forEach((mutation) => {
                                if (mutation.type === 'childList') {
                                    mutation.addedNodes.forEach((node) => {
                                        if (node.nodeType === Node.ELEMENT_NODE) {
                                            // Check if admin bar was added
                                            if (node.id === 'wpadminbar' || 
                                                (node.querySelector && node.querySelector('#wpadminbar'))) {
                                                setTimeout(() => this.hideWordPressAdminBar(), 0);
                                            }
                                        }
                                    });
                                }
                            });
                        });
                        
                        observer.observe(this.iframeDoc.body, {
                            childList: true,
                            subtree: true
                        });
                        
                        // Store observer for cleanup
                        this.adminBarObserver = observer;
                    }
                } catch (error) {
                    console.warn('Could not set up admin bar observer:', error);
                }
                
                // Fallback: periodic check every 2 seconds
                this.adminBarInterval = setInterval(() => {
                    if (this.iframeDoc && this.iframeDoc.getElementById('wpadminbar')) {
                        this.hideWordPressAdminBar();
                    }
                }, 2000);
            }

            // Method to stop admin bar monitoring (for cleanup)
            stopAdminBarMonitoring() {
                if (this.adminBarObserver) {
                    this.adminBarObserver.disconnect();
                    this.adminBarObserver = null;
                }
                
                if (this.adminBarInterval) {
                    clearInterval(this.adminBarInterval);
                    this.adminBarInterval = null;
                }
            }
        }

        // Global reference for debugging
        window.LiveCSSEditor = LiveCSSEditor;
        window.liveCSSInstance = null;
        
        // Global debugging functions
        window.debugLiveCSS = () => {
            if (window.liveCSSInstance) {
                console.log('LiveCSS Status:', window.liveCSSInstance.getStatus());
                console.log('Current CSS:', window.liveCSSInstance.getCurrentCSS());
                return window.liveCSSInstance.getStatus();
            } else {
                console.log('LiveCSS instance not available');
                return null;
            }
        };
        
        window.refreshLiveCSSPreview = () => {
            if (window.liveCSSInstance) {
                window.liveCSSInstance.forceRefreshPreview();
                console.log('Preview refreshed');
            } else {
                console.log('LiveCSS instance not available');
            }
        };
        
        window.hideAdminBar = () => {
            if (window.liveCSSInstance) {
                window.liveCSSInstance.hideWordPressAdminBar();
                console.log('Admin bar hidden');
            } else {
                console.log('LiveCSS instance not available');
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOM loaded, initializing LiveCSSEditor');
            // Add a small delay to ensure all resources are loaded
            setTimeout(() => {
                window.liveCSSInstance = new LiveCSSEditor();

                // Sidebar resizing/collapsing logic
                (function(){
                    const panel = document.getElementById('editor-panel');
                    const resizer = document.getElementById('sidebar-resizer');
                    if (!panel || !resizer) return;

                    const STORAGE_KEY = 'livecss_sidebar_width_v1';
                    const COLLAPSED_KEY = 'livecss_sidebar_collapsed_v1';
                    const MIN_WIDTH = 260; // min expanded width
                    const MAX_WIDTH_PCT = 0.7; // 70% of viewport

                    function applyWidth(px){
                        const max = Math.floor(window.innerWidth * MAX_WIDTH_PCT);
                        const clamped = Math.max(MIN_WIDTH, Math.min(max, px));
                        panel.style.width = clamped + 'px';
                        panel.style.minWidth = clamped + 'px';
                    }

                    // Restore state
                    try {
                        const collapsed = localStorage.getItem(COLLAPSED_KEY) === '1';
                        if (collapsed) {
                            panel.classList.add('is-collapsed');
                            resizer.setAttribute('aria-expanded', 'false');
                        } else {
                            const saved = parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10);
                            if (saved > 0) applyWidth(saved);
                            resizer.setAttribute('aria-expanded', 'true');
                        }
                    } catch(e) {}

                    let dragging = false;
                    let startX = 0;
                    let startWidth = 0;

                    function startDrag(e){
                        if (panel.classList.contains('is-collapsed')) return;
                        dragging = true;
                        startX = e.clientX || (e.touches && e.touches[0].clientX) || 0;
                        startWidth = panel.getBoundingClientRect().width;
                        document.body.style.userSelect = 'none';
                        document.body.style.cursor = 'col-resize';
                    }
                    function onDrag(e){
                        if (!dragging) return;
                        const clientX = e.clientX || (e.touches && e.touches[0].clientX) || 0;
                        const delta = clientX - startX;
                        const newWidth = startWidth + delta;
                        applyWidth(newWidth);
                    }
                    function endDrag(){
                        if (!dragging) return;
                        dragging = false;
                        document.body.style.userSelect = '';
                        document.body.style.cursor = '';
                        try {
                            const w = Math.floor(panel.getBoundingClientRect().width);
                            localStorage.setItem(STORAGE_KEY, String(w));
                        } catch(e) {}
                    }

                    resizer.addEventListener('mousedown', startDrag);
                    resizer.addEventListener('touchstart', startDrag, { passive: true });
                    window.addEventListener('mousemove', onDrag);
                    window.addEventListener('touchmove', onDrag, { passive: true });
                    window.addEventListener('mouseup', endDrag);
                    window.addEventListener('touchend', endDrag);

                    // Double-click/Enter toggles collapsed state
                    function toggleCollapse(){
                        const collapsed = panel.classList.toggle('is-collapsed');
                        resizer.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                        try { localStorage.setItem(COLLAPSED_KEY, collapsed ? '1' : '0'); } catch(e) {}
                    }
                    resizer.addEventListener('dblclick', toggleCollapse);
                    resizer.addEventListener('keydown', (e)=>{
                        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggleCollapse(); }
                        // Keyboard resizing
                        const STEP = (e.shiftKey ? 40 : 20);
                        if (!panel.classList.contains('is-collapsed')) {
                            if (e.key === 'ArrowLeft') {
                                e.preventDefault();
                                const w = panel.getBoundingClientRect().width - STEP;
                                applyWidth(w); try { localStorage.setItem(STORAGE_KEY, String(Math.floor(w))); } catch(err) {}
                            } else if (e.key === 'ArrowRight') {
                                e.preventDefault();
                                const w = panel.getBoundingClientRect().width + STEP;
                                applyWidth(w); try { localStorage.setItem(STORAGE_KEY, String(Math.floor(w))); } catch(err) {}
                            }
                        }
                    });
                })();
            }, 100);
        });
    </script>

    <?php if (function_exists('wp_footer')) { call_user_func('wp_footer'); } ?>
</body>
</html>