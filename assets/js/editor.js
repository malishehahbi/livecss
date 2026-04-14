/**
 * LiveCSS Editor — Main Application Script
 * Extracted from editor-js.php for proper caching and maintainability.
 *
 * @version 2.1.0
 *
 * Expects a global `window.livecssConfig` object set by PHP before this file
 * loads, containing: { savedCSS, saveNonce, recreateNonce, ajaxUrl }
 */

/* =========================================================================
   Logger — Structured debug logging
   ========================================================================= */

const LiveCSSLog = (() => {
    // Set to true to enable verbose debug logging
    const DEBUG = (window.livecssConfig && window.livecssConfig.debug) || false;

    const PREFIX = '[LiveCSS]';
    const styles = {
        info:  'color: #1aa2e6; font-weight: bold',
        ok:    'color: #22c55e; font-weight: bold',
        warn:  'color: #f59e0b; font-weight: bold',
        error: 'color: #ef4444; font-weight: bold',
    };

    return {
        /** Always shown — critical milestones */
        info:  (...args) => console.log(`%c${PREFIX}`, styles.info, ...args),
        /** Debug-only — verbose operational output */
        debug: (...args) => { if (DEBUG) console.log(`%c${PREFIX}`, styles.info, ...args); },
        /** Always shown — success checkpoints */
        ok:    (...args) => console.log(`%c${PREFIX} ✅`, styles.ok, ...args),
        /** Always shown — non-fatal issues */
        warn:  (...args) => console.warn(`%c${PREFIX} ⚠️`, styles.warn, ...args),
        /** Always shown — errors */
        error: (...args) => console.error(`%c${PREFIX} ❌`, styles.error, ...args),
    };
})();

/* =========================================================================
   LiveCSSLoader — Init progress overlay
   ========================================================================= */

class LiveCSSLoader {
    constructor() {
        this.loader = document.getElementById('livecss-loader');
        this.progressBar = document.getElementById('loader-progress');
        this.statusText = document.getElementById('loader-status');
        this.progress = 0;
        this.maxRetries = 3;
        this.retryCount = 0;
        this.initializationSteps = [
            { name: 'CodeMirror Library', weight: 10 },
            { name: 'Feature Libraries', weight: 10 },
            { name: 'Visual Editor Schema', weight: 8 },
            { name: 'DOM Elements', weight: 7 },
            { name: 'Code Editor', weight: 15 },
            { name: 'Event Listeners', weight: 10 },
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
        if (this.progressBar) this.progressBar.style.width = this.progress + '%';
        if (this.statusText) this.statusText.textContent = message;
    }

    showError(message) {
        if (this.statusText) {
            this.statusText.textContent = message;
            this.statusText.classList.add('livecss-loader-error');
        }
    }

    hide() {
        this.updateProgress(this.initializationSteps.length, 'Ready!');
        this.progress = 100;
        if (this.progressBar) this.progressBar.style.width = '100%';
        
        setTimeout(() => {
            if (this.loader) {
                this.loader.classList.add('hidden');
                setTimeout(() => {
                    this.loader.style.display = 'none';
                }, 500);
            }
        }, 300);
    }
}

/* =========================================================================
   IFRAME_STYLES — DRY constant for styles injected into the preview iframe
   ========================================================================= */

const IFRAME_EDITOR_STYLES = `
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

/* =========================================================================
   LiveCSSEditor — Core application class
   ========================================================================= */

class LiveCSSEditor {
    constructor() {
        LiveCSSLog.info('Initializing editor...');
        this.currentSelector = '';
        this.cssRules = new Map();
        this.nestedCSS = new Map();
        this.selectedElement = null;
        this.selectionHighlighted = [];
        this.iframe = null;
        this.iframeDoc = null;
        this.codeEditor = null;
        this.spotlightMode = null;
        this.searchFunctionality = null;
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
        
        // Unsaved changes tracking
        this.hasUnsavedChanges = false;
        this.initialCSSState = null;
        
        // History for undo/redo
        this.history = [];
        this.historyIndex = -1;
        this.maxHistorySize = 50;
        
        this.initializeTheme();
        this.init();
    }

    initializeTheme() {
        const savedTheme = localStorage.getItem('livecss_theme');
        const editorTag = document.querySelector('editor');
        if (savedTheme === 'light') {
            editorTag.classList.add('theme-light');
            setTimeout(() => this.updateThemeIcon('light'), 0);
        } else {
            editorTag.classList.remove('theme-light');
            setTimeout(() => this.updateThemeIcon('dark'), 0);
        }
    }

    updateThemeIcon(theme) {
        const sun = document.querySelector('.icon-sun');
        const moon = document.querySelector('.icon-moon');
        if (!sun || !moon) return;
        
        if (theme === 'light') {
            sun.classList.add('hidden');
            moon.classList.remove('hidden');
        } else {
            sun.classList.remove('hidden');
            moon.classList.add('hidden');
        }
    }

    /**
     * Async initialization pipeline with sequential step execution.
     * Each step depends on the previous one completing successfully.
     */
    async init() {
        LiveCSSLog.debug('Init pipeline starting...');
        
        try {
            // Step 1: Check CodeMirror
            this.loader.updateProgress(0, 'Loading CodeMirror library...');
            await this.waitForCodeMirror();
            
            // Step 2: Check Feature Libraries
            this.loader.updateProgress(1, 'Loading feature libraries...');
            await this.waitForFeatureLibraries();
            
            // Step 3: Render visual editor from JSON schema
            this.loader.updateProgress(2, 'Building visual editor...');
            if (typeof renderVisualEditor === 'function') {
                renderVisualEditor(document.getElementById('tab-visual'));
                LiveCSSLog.ok('Visual editor rendered from schema (' + VISUAL_EDITOR_SCHEMA.length + ' sections)');
            }
            
            // Step 4: Verify DOM elements
            this.loader.updateProgress(3, 'Verifying DOM elements...');
            await this.verifyDOMElements();
            
            // Step 5: Setup code editor
            this.loader.updateProgress(4, 'Setting up code editor...');
            await this.setupCodeEditor();
            
            // Step 6: Setup event listeners
            this.loader.updateProgress(5, 'Initializing event listeners...');
            await this.setupEventListeners();
            
            // Step 7: Setup iframe
            this.loader.updateProgress(6, 'Loading preview iframe...');
            await this.setupIframe();
            
            // Step 8: Load saved CSS and final verification
            this.loader.updateProgress(7, 'Loading saved CSS and verifying...');
            await this.loadSavedCSS();
            await this.verifyFunctionality();
            
            this.isInitialized = true;
            LiveCSSLog.ok('Editor fully initialized');
            this.loader.hide();
            
        } catch (error) {
            LiveCSSLog.error('Initialization failed:', error.message);
            this.loader.showError('Initialization failed: ' + error.message);
            
            // Retry logic
            if (this.loader.retryCount < this.loader.maxRetries) {
                this.loader.retryCount++;
                LiveCSSLog.warn(`Retrying (${this.loader.retryCount}/${this.loader.maxRetries})...`);
                this.loader.updateProgress(0, `Retrying initialization (${this.loader.retryCount}/${this.loader.maxRetries})...`);
                setTimeout(() => this.init(), 2000);
            } else {
                this.loader.showError('Failed to initialize after multiple attempts. Please refresh the page.');
            }
        }
    }

    /**
     * Polls for CodeMirror availability (CDN-loaded).
     * @returns {Promise<void>}
     */
    waitForCodeMirror() {
        return new Promise((resolve, reject) => {
            let attempts = 0;
            const maxAttempts = 50; // 5 seconds
            
            const checkCodeMirror = () => {
                if (typeof CodeMirror !== 'undefined' && CodeMirror.modes && CodeMirror.modes.css) {
                    LiveCSSLog.ok('CodeMirror loaded');
                    resolve();
                } else if (attempts < maxAttempts) {
                    attempts++;
                    setTimeout(checkCodeMirror, 100);
                } else {
                    reject(new Error('CodeMirror library failed to load after 5s. Check your internet connection.'));
                }
            };
            
            checkCodeMirror();
        });
    }

    /**
     * Polls for all feature libraries (SpotlightMode, SearchFunctionality, PropertyDependencies).
     * @returns {Promise<void>}
     */
    waitForFeatureLibraries() {
        return new Promise((resolve, reject) => {
            let attempts = 0;
            const maxAttempts = 50; // 5 seconds
            
            const checkFeatures = () => {
                const features = {
                    'SpotlightMode': typeof SpotlightMode !== 'undefined',
                    'SearchFunctionality': typeof SearchFunctionality !== 'undefined',
                    'PropertyDependencies': typeof PropertyDependencies !== 'undefined'
                };
                
                const loadedFeatures = Object.entries(features)
                    .filter(([, loaded]) => loaded)
                    .map(([name]) => name);
                
                const missingFeatures = Object.entries(features)
                    .filter(([, loaded]) => !loaded)
                    .map(([name]) => name);
                
                if (missingFeatures.length === 0) {
                    LiveCSSLog.ok('Feature libraries loaded:', loadedFeatures.join(', '));
                    resolve();
                } else if (attempts < maxAttempts) {
                    attempts++;
                    if (attempts % 10 === 0) {
                        LiveCSSLog.debug(`Waiting for: ${missingFeatures.join(', ')} (${attempts * 100}ms)`);
                    }
                    setTimeout(checkFeatures, 100);
                } else {
                    reject(new Error(`Feature libraries timed out: ${missingFeatures.join(', ')}`));
                }
            };
            
            checkFeatures();
        });
    }

    /**
     * Verifies that critical DOM elements exist before initialization continues.
     * @returns {Promise<void>}
     */
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
                reject(new Error(`Missing DOM elements: ${missingElements.join(', ')}`));
            } else {
                LiveCSSLog.ok('DOM elements verified');
                resolve();
            }
        });
    }

    /**
     * Initializes SpotlightMode and SearchFunctionality sub-modules.
     */
    initializeFeatures() {
        try {
            if (typeof SpotlightMode !== 'undefined') {
                this.spotlightMode = new SpotlightMode(this);
                this.spotlightMode.init(this.codeEditor);
                LiveCSSLog.ok('SpotlightMode initialized');
            } else {
                LiveCSSLog.warn('SpotlightMode not available');
            }
            
            if (typeof SearchFunctionality !== 'undefined') {
                this.searchFunctionality = new SearchFunctionality(this);
                this.searchFunctionality.init(this.codeEditor);
                LiveCSSLog.ok('SearchFunctionality initialized');
            } else {
                LiveCSSLog.warn('SearchFunctionality not available');
            }
        } catch (error) {
            LiveCSSLog.error('Feature initialization error:', error);
            throw error;
        }
    }

    /**
     * Final health-check: verifies code editor, iframe, DOM, and style injection.
     * @returns {Promise<void>}
     */
    verifyFunctionality() {
        return new Promise((resolve, reject) => {
            const checks = [
                { name: 'CodeMirror instance', fn: () => this.codeEditor !== null && typeof this.codeEditor.getValue === 'function' },
                { name: 'Iframe element',      fn: () => this.iframe !== null },
                { name: 'Iframe document',     fn: () => this.iframeDoc !== null },
                { name: 'Tab buttons',         fn: () => document.querySelectorAll('.tab').length > 0 },
                { name: 'CSS controls',        fn: () => document.querySelectorAll('.control[data-property]').length > 0 },
                { name: 'Iframe styles',       fn: () => this.iframeDoc.getElementById('livecss-editor-styles') !== null },
            ];
            
            const failedChecks = checks.filter(({ name, fn }) => {
                try {
                    return !fn();
                } catch (e) {
                    LiveCSSLog.error(`Verify "${name}" threw:`, e);
                    return true;
                }
            });
            
            if (failedChecks.length > 0) {
                const names = failedChecks.map(c => c.name).join(', ');
                reject(new Error(`Verification failed: ${names}`));
            } else {
                try {
                    this.updatePreview();
                    resolve();
                } catch (error) {
                    reject(new Error('Preview render test failed: ' + error.message));
                }
            }
        });
    }

    // ===================================================================
    // IFRAME & PREVIEW
    // ===================================================================

    /**
     * Hides the WordPress admin bar inside the preview iframe.
     */
    hideWordPressAdminBar() {
        if (!this.iframeDoc) return;
        
        try {
            const adminBar = this.iframeDoc.getElementById('wpadminbar');
            if (adminBar) {
                adminBar.remove();
                LiveCSSLog.debug('Admin bar removed from iframe');
            }
            
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
            
            // Remove admin bar related inline styles
            const adminBarStyles = this.iframeDoc.querySelectorAll('style[id*="admin"], link[id*="admin-bar"]');
            adminBarStyles.forEach(style => {
                if (style.textContent && style.textContent.includes('wpadminbar')) {
                    style.remove();
                }
            });
        } catch (error) {
            LiveCSSLog.warn('Error removing admin bar:', error);
        }
    }

    /**
     * Tears down and re-injects all LiveCSS styles in the iframe.
     */
    forceRefreshPreview() {
        if (!this.iframeDoc) {
            LiveCSSLog.warn('Cannot refresh preview: iframe not ready');
            return;
        }
        
        // Remove all LiveCSS styles
        const existingStyles = this.iframeDoc.querySelectorAll('#livecss-custom-styles, #livecss-editor-styles');
        existingStyles.forEach(style => style.remove());
        
        // Re-inject editor styles
        const editorStyle = this.iframeDoc.createElement('style');
        editorStyle.id = 'livecss-editor-styles';
        editorStyle.type = 'text/css';
        editorStyle.textContent = IFRAME_EDITOR_STYLES;
        this.iframeDoc.head.appendChild(editorStyle);
        
        this.hideWordPressAdminBar();
        this.updatePreview();
        
        LiveCSSLog.debug('Preview CSS refreshed');
    }
    
    /**
     * Initializes the CodeMirror CSS editor instance.
     * @returns {Promise<void>}
     */
    setupCodeEditor() {
        return new Promise((resolve, reject) => {
            try {
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
                    extraKeys: {
                        "Ctrl-Space": "autocomplete"
                    },
                    hintOptions: {
                        completeSingle: false,
                        closeOnUnfocus: true
                    }
                });
                
                // Auto-complete trigger on typing
                this.codeEditor.on('inputRead', (cm, change) => {
                    if (change.text[0] !== ';' && change.text[0] !== ' ' && change.text[0] !== '\n') {
                        cm.showHint();
                    }
                });
            
                // Sync code changes → visual controls + preview
                this.codeEditor.on('change', () => {
                    if (!this.isUpdatingFromCode) {
                        this.parseCSS(this.codeEditor.getValue());
                        this.updatePreview();
                        this.updateVisualControls();
                        this.markAsChanged();
                        this.captureHistory();
                    }
                });

                // Verify editor is working
                setTimeout(() => {
                    if (this.codeEditor && typeof this.codeEditor.getValue === 'function') {
                        this.initializeFeatures();
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

    /**
     * Binds all UI event listeners (tabs, controls, shortcuts, etc.).
     * @returns {Promise<void>}
     */
    setupEventListeners() {
        return new Promise((resolve, reject) => {
            try {
                // ── Tab switching ──
                const tabs = document.querySelectorAll('.tab');
                if (tabs.length === 0) {
                    reject(new Error('No tabs found in DOM'));
                    return;
                }
                tabs.forEach(tab => {
                    tab.addEventListener('click', (e) => {
                        this.switchTab(e.target.dataset.tab);
                    });
                });

                // ── Theme toggle ──
                const themeToggle = document.getElementById('theme-toggle');
                if (themeToggle) {
                    themeToggle.addEventListener('click', () => {
                        const editorTag = document.querySelector('editor');
                        editorTag.classList.toggle('theme-light');
                        const isLight = editorTag.classList.contains('theme-light');
                        localStorage.setItem('livecss_theme', isLight ? 'light' : 'dark');
                        this.updateThemeIcon(isLight ? 'light' : 'dark');
                    });
                }

                // ── Accordion ──
                document.querySelectorAll('.accordion-header').forEach(header => {
                    header.addEventListener('click', () => {
                        this.toggleAccordion(header);
                    });
                });

                // ── Selector input ──
                const selectorInput = document.getElementById('selector-input');
                if (!selectorInput) {
                    reject(new Error('Selector input element not found'));
                    return;
                }
                
                selectorInput.addEventListener('input', () => {
                    this.updateSelectionFromInput();
                    this.updateSelectorSuggestions();
                    
                    if (this.spotlightMode && this.spotlightMode.isSpotlightActive()) {
                        setTimeout(() => {
                            this.spotlightMode.onSelectorChange(this.currentSelector);
                        }, 100);
                    }
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

                // ── Pseudo-class buttons ──
                document.querySelectorAll('.pseudo-button').forEach(button => {
                    button.addEventListener('click', () => {
                        const pseudo = button.dataset.pseudo;
                        const isPressed = button.getAttribute('aria-pressed') === 'true';
                        
                        if (isPressed) {
                            selectorInput.value = selectorInput.value.replace(pseudo, '');
                            button.setAttribute('aria-pressed', 'false');
                        } else {
                            if (!selectorInput.value.includes(pseudo)) {
                                selectorInput.value += pseudo;
                                button.setAttribute('aria-pressed', 'true');
                            }
                        }
                        
                        const inputEvent = new Event('input', { bubbles: true });
                        selectorInput.dispatchEvent(inputEvent);
                    });
                });

                // ── Visual controls ──
                document.querySelectorAll('.control[data-property]').forEach(control => {
                    control.addEventListener('input', () => {
                        LiveCSSLog.debug('Control changed:', control.dataset.property, '=', control.value);
                        this.updateCSSProperty(control.dataset.property, control.value);
                        this.renderUsageDots();
                    });
                });

                // ── Save button ──
                const saveButton = document.getElementById('save-button');
                if (!saveButton) {
                    reject(new Error('Save button not found'));
                    return;
                }
                saveButton.addEventListener('click', () => {
                    this.saveCSS();
                });

                // ── Preview button ──
                const previewButton = document.getElementById('preview-button');
                const exitPreviewButton = document.getElementById('exit-preview-button');
                if (previewButton && exitPreviewButton) {
                    previewButton.addEventListener('click', () => {
                        this.togglePreviewMode(true);
                    });
                    exitPreviewButton.addEventListener('click', () => {
                        this.togglePreviewMode(false);
                    });
                }

                // ── Device toggles ──
                const deviceButtons = document.querySelectorAll('.device-btn');
                deviceButtons.forEach(btn => {
                    btn.addEventListener('click', () => {
                        deviceButtons.forEach(b => b.classList.remove('active'));
                        btn.classList.add('active');
                        this.setDevice(btn.dataset.device);
                        
                        if (this.spotlightMode && this.spotlightMode.isSpotlightActive()) {
                            this.spotlightMode.onDeviceChange(btn.dataset.device);
                        }
                    });
                });

                // ── Breadcrumb clicks ──
                const breadcrumb = document.getElementById('element-breadcrumb');
                if (breadcrumb) {
                    breadcrumb.addEventListener('click', (e) => {
                        if (e.target.classList.contains('breadcrumb-item') && e.target.dataset.selector) {
                            const selector = e.target.dataset.selector;
                            const sInput = document.getElementById('selector-input');
                            sInput.value = selector;
                            const inputEvent = new Event('input', { bubbles: true });
                            sInput.dispatchEvent(inputEvent);
                            this.renderUsageDots();
                        }
                    });
                }

                // ── Global keyboard shortcuts ──
                document.addEventListener('keydown', (e) => {
                    const isMod = e.ctrlKey || e.metaKey;
                    const key = e.key.toLowerCase();

                    // Ctrl+S / Cmd+S — Save
                    if (isMod && key === 's') {
                        e.preventDefault();
                        this.saveCSS();
                        return;
                    }

                    // Ctrl+F / Cmd+F — Toggle search
                    if (isMod && key === 'f') {
                        e.preventDefault();
                        if (this.searchFunctionality) {
                            this.searchFunctionality.toggleSearch();
                        }
                        return;
                    }
                    
                    // Escape — Exit preview mode
                    if (e.key === 'Escape') {
                        const editorContainer = document.querySelector('.editor-container');
                        if (editorContainer?.classList.contains('preview-mode')) {
                            this.togglePreviewMode(false);
                        }
                        return;
                    }
                    
                    // Ctrl+Z — Undo
                    if (isMod && key === 'z' && !e.shiftKey) {
                        e.preventDefault();
                        this.undo();
                        return;
                    }
                    
                    // Ctrl+Y or Ctrl+Shift+Z — Redo
                    if (isMod && (key === 'y' || (key === 'z' && e.shiftKey))) {
                        e.preventDefault();
                        this.redo();
                        return;
                    }
                });

                // ── Warn before leaving with unsaved changes ──
                window.addEventListener('beforeunload', (e) => {
                    if (this.hasUnsavedChanges) {
                        e.preventDefault();
                        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                        return e.returnValue;
                    }
                });

                // ── Intercept Exit Editor button ──
                const exitButton = document.querySelector('a.button-danger');
                if (exitButton) {
                    exitButton.addEventListener('click', (e) => {
                        if (this.hasUnsavedChanges) {
                            const confirmExit = confirm('You have unsaved changes. Do you really want to exit without saving?');
                            if (!confirmExit) {
                                e.preventDefault();
                            }
                        }
                    });
                }

                // Done
                setTimeout(() => resolve(), 50);
                
            } catch (error) {
                reject(error);
            }
        });
    }

    /**
     * Sets up the preview iframe, injects editor styles, and binds element selector.
     * Includes a guard against the double-fire bug where both DOMContentLoaded
     * and the backup setTimeout could trigger setupIframeContent.
     * @returns {Promise<void>}
     */
    setupIframe() {
        return new Promise((resolve, reject) => {
            try {
                this.iframe = document.getElementById('preview-iframe');
                
                if (!this.iframe) {
                    reject(new Error('Preview iframe not found'));
                    return;
                }
                
                // Timeout safety net
                const timeoutId = setTimeout(() => {
                    reject(new Error('Iframe loading timeout (10s)'));
                }, 10000);
                
                this.iframe.addEventListener('load', () => {
                    clearTimeout(timeoutId);
                    
                    try {
                        this.iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow.document;
                        
                        if (!this.iframeDoc) {
                            reject(new Error('Could not access iframe document'));
                            return;
                        }
                        
                        // Guard against double-fire
                        let iframeContentReady = false;
                        const setupIframeContent = () => {
                            if (iframeContentReady) return; // Prevent double execution
                            iframeContentReady = true;
                            
                            // Inject editor styles
                            const style = this.iframeDoc.createElement('style');
                            style.id = 'livecss-editor-styles';
                            style.type = 'text/css';
                            style.textContent = IFRAME_EDITOR_STYLES;
                            this.iframeDoc.head.appendChild(style);
                            
                            this.hideWordPressAdminBar();
                            this.setupElementSelector();
                            this.updatePreview();
                            this.updateSelectorSuggestions(true);
                            this.applyPreviewDevice();
                            this.startAdminBarMonitoring();
                            
                            LiveCSSLog.ok('Iframe ready');
                            resolve();
                        };
                        
                        if (this.iframeDoc.readyState === 'complete') {
                            setupIframeContent();
                        } else {
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

    /**
     * Binds mouseover/mouseout/click on iframe body for element selection.
     */
    setupElementSelector() {
        if (!this.iframeDoc) return;

        this.iframeDoc.body.addEventListener('mouseover', (e) => {
            const editorContainer = document.querySelector('.editor-container');
            if (editorContainer?.classList.contains('preview-mode')) return;
            e.target.classList.add('livecss-hover-highlight');
        });

        this.iframeDoc.body.addEventListener('mouseout', (e) => {
            e.target.classList.remove('livecss-hover-highlight');
        });

        this.iframeDoc.body.addEventListener('click', (e) => {
            const editorContainer = document.querySelector('.editor-container');
            if (editorContainer?.classList.contains('preview-mode')) return;
            
            e.preventDefault();
            e.stopPropagation();

            const selector = this.generateSelector(e.target);
            document.getElementById('selector-input').value = selector;
            
            const inputEvent = new Event('input', { bubbles: true });
            document.getElementById('selector-input').dispatchEvent(inputEvent);
            this.updateBreadcrumb(e.target);

            // Phase 3: Intelligent Contextual Discovery
            const tag = e.target.tagName.toLowerCase();
            let expectedSection = 'Sizing'; // Default fallback
            
            if (['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'a', 'strong', 'em', 'blockquote', 'label'].includes(tag)) {
                expectedSection = 'Typography';
            } else if (['img', 'video', 'svg', 'canvas', 'picture', 'iframe'].includes(tag)) {
                expectedSection = 'Filters'; // or Sizing, but filters is highly visual for media
            } else if (['ul', 'ol', 'li', 'dl', 'dt', 'dd'].includes(tag)) {
                expectedSection = 'Lists';
            } else if (['div', 'section', 'article', 'header', 'footer', 'nav', 'main', 'aside'].includes(tag)) {
                expectedSection = 'Layout';
            } else if (['button', 'input', 'select', 'textarea', 'form'].includes(tag)) {
                expectedSection = 'Borders'; // Useful for forms
            }
            
            this.autoExpandSection(expectedSection);
        }, true);
    }

    autoExpandSection(sectionTitle) {
        const headers = document.querySelectorAll('.accordion-header');
        for (const header of headers) {
            if (header.textContent.trim() === sectionTitle) {
                // If it's already active, do nothing
                if (!header.classList.contains('active')) {
                    this.toggleAccordion(header);
                    // Add a subtle highlight pulse to indicate AI selection
                    const item = header.closest('.accordion-item');
                    if (item) {
                        item.style.animation = 'none';
                        setTimeout(() => {
                            item.style.animation = 'spotlight-pulse 1.5s ease';
                        }, 50);
                    }
                    // Scroll it into view smoothly
                    header.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                break;
            }
        }
    }

    // ===================================================================
    // SELECTOR & SELECTION
    // ===================================================================

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
                // Invalid selector — silently ignore
                this.selectionHighlighted = [];
            }
        }
        this.updateVisualControls();
        this.renderUsageDots();
        this.updatePseudoButtonStates();
    }

    updatePseudoButtonStates() {
        const selectorInput = document.getElementById('selector-input');
        if (!selectorInput) return;
        
        const selectorValue = selectorInput.value;
        document.querySelectorAll('.pseudo-button').forEach(button => {
            const pseudo = button.dataset.pseudo;
            button.setAttribute('aria-pressed', selectorValue.includes(pseudo) ? 'true' : 'false');
        });
    }

    updateBreadcrumb(element) {
        const breadcrumbContainer = document.getElementById('element-breadcrumb');
        if (!breadcrumbContainer) return;
        breadcrumbContainer.innerHTML = '';
        let currentElement = element;
        let breadcrumbs = [];

        while (currentElement && currentElement.tagName.toLowerCase() !== 'html') {
            const tag = currentElement.tagName.toLowerCase();
            const id = currentElement.id ? `#${currentElement.id}` : '';
            const classes = Array.from(currentElement.classList)
                .filter(c => c !== 'livecss-hover-highlight' && c !== 'livecss-selection-highlight');

            let partsHtml = [];
            partsHtml.push(`<span class="breadcrumb-item" data-selector="${tag}">${tag}</span>`);
            if (id) {
                partsHtml.push(`<span class="breadcrumb-item" data-selector="${id}">${id}</span>`);
            }
            classes.forEach(cls => {
                partsHtml.push(`<span class="breadcrumb-item" data-selector=".${cls}">.${cls}</span>`);
            });

            const breadcrumbHtml = `<span class="breadcrumb-part-group">${partsHtml.join('')}</span>`;
            breadcrumbs.unshift(breadcrumbHtml);
            currentElement = currentElement.parentElement;
        }

        const separator = `<svg class="breadcrumb-separator" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>`;
        breadcrumbContainer.innerHTML = breadcrumbs.join(separator);
    }

    /**
     * Generates a CSS selector for a given DOM element.
     * Priority: #ID > .class > tagName.
     */
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

    // ===================================================================
    // UI ACTIONS
    // ===================================================================

    switchTab(tabName) {
        LiveCSSLog.debug('Switching to tab:', tabName);
        document.querySelectorAll('.tab').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.tab === tabName);
        });

        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.toggle('hidden', content.dataset.tab !== tabName);
        });

        // Update search visibility and close any active search
        if (this.searchFunctionality) {
            if (this.searchFunctionality.isSearchVisible) {
                this.searchFunctionality.isSearchVisible = false;
                this.searchFunctionality.searchToggleBtn?.classList.remove('active');
            }
            this.searchFunctionality.updateSearchVisibility(tabName);
        }

        if (tabName === 'code' && this.codeEditor) {
            setTimeout(() => {
                this.codeEditor.refresh();
                
                if (this.spotlightMode && this.currentSelector) {
                    this.spotlightMode.activate();
                    const activeDeviceBtn = document.querySelector('.device-btn.active');
                    if (activeDeviceBtn?.dataset.device) {
                        this.spotlightMode.onDeviceChange(activeDeviceBtn.dataset.device);
                    }
                }
            }, 50);
        } else if (tabName === 'visual' && this.spotlightMode) {
            this.spotlightMode.deactivate();
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
            
            // Smart scroll to position accordion at top
            const tabContent = header.closest('.tab-content');
            if (tabContent) {
                 setTimeout(() => {
                     const item = header.parentElement;
                     tabContent.scrollTo({
                         top: item.offsetTop - 20, // offset slightly
                         behavior: 'smooth'
                     });
                 }, 50);
            }
        }
    }

    togglePreviewMode(enable) {
        const editorContainer = document.querySelector('.editor-container');
        const exitPreviewButton = document.getElementById('exit-preview-button');
        const previewButton = document.getElementById('preview-button');

        if (enable) {
            editorContainer?.classList.add('preview-mode');
            exitPreviewButton?.classList.remove('hidden');
            previewButton?.classList.add('hidden');
            
            if (this.iframeDoc) {
                this.iframeDoc.querySelectorAll('.livecss-selection-highlight').forEach(el => {
                    el.classList.remove('livecss-selection-highlight');
                });
                this.iframeDoc.querySelectorAll('.livecss-hover-highlight').forEach(el => {
                    el.classList.remove('livecss-hover-highlight');
                });
                
                const style = this.iframeDoc.createElement('style');
                style.id = 'livecss-preview-mode-styles';
                style.textContent = `
                    .livecss-selection-highlight,
                    .livecss-hover-highlight {
                        outline: none !important;
                        border: none !important;
                    }
                `;
                this.iframeDoc.head.appendChild(style);
            }
        } else {
            editorContainer?.classList.remove('preview-mode');
            exitPreviewButton?.classList.add('hidden');
            previewButton?.classList.remove('hidden');
            
            if (this.iframeDoc) {
                const previewStyles = this.iframeDoc.getElementById('livecss-preview-mode-styles');
                if (previewStyles) previewStyles.remove();
                this.updateSelectionFromInput();
            }
        }
    }

    // ===================================================================
    // CSS PROPERTY MANAGEMENT
    // ===================================================================

    updateCSSProperty(property, value) {
        if (!this.currentSelector || !property) return;

        const key = this.getScopedSelectorKey(this.currentSelector);
        if (!this.cssRules.has(key)) {
            this.cssRules.set(key, new Map());
        }

        const selectorRules = this.cssRules.get(key);

        if (value && value.trim()) {
            selectorRules.set(property, value.trim());
        } else {
            selectorRules.delete(property);
        }

        // Rebuild combined transform/filter values
        if (this.isTransformProperty(property)) {
            this.updateCombinedProperty(selectorRules, 'transform', this.buildTransformValue.bind(this));
        } else if (this.isFilterProperty(property)) {
            this.updateCombinedProperty(selectorRules, 'filter', this.buildFilterValue.bind(this));
        }
        
        this.markAsChanged();
        this.captureHistory();
        this.updatePreview();
        this.updateCodeEditor();
    }

    /**
     * Injects the generated CSS into the preview iframe via a <style> tag.
     */
    updatePreview() {
        if (!this.iframeDoc) {
            LiveCSSLog.debug('Preview skipped: iframe not ready');
            return;
        }

        try {
            const oldStyleEl = this.iframeDoc.getElementById('livecss-custom-styles');
            if (oldStyleEl) oldStyleEl.remove();

            const newStyleEl = this.iframeDoc.createElement('style');
            newStyleEl.id = 'livecss-custom-styles';
            newStyleEl.type = 'text/css';
            
            const css = this.generateCSS({ is_preview: true });
            
            if (newStyleEl.styleSheet) {
                newStyleEl.styleSheet.cssText = css; // IE support
            } else {
                newStyleEl.textContent = css;
            }

            (this.iframeDoc.head || this.iframeDoc.documentElement).appendChild(newStyleEl);
            
            // Force a repaint
            this.iframeDoc.body.offsetHeight;
        } catch (error) {
            LiveCSSLog.error('Preview update failed:', error);
        }
    }

    /**
     * Syncs the in-memory CSS rules back to the CodeMirror editor.
     */
    updateCodeEditor() {
        if (!this.codeEditor) return;
        
        this.isUpdatingFromCode = true;
        this.codeEditor.setValue(this.generateCSS());
        this.isUpdatingFromCode = false;
        
        // Refresh spotlight after code editor update
        if (this.spotlightMode && this.spotlightMode.isSpotlightActive()) {
            setTimeout(() => {
                this.spotlightMode.updateSpotlight();
            }, 50);
        }
    }

    /**
     * Reads CSS rule values from the map and sets them on the visual controls.
     */
    updateVisualControls() {
        const key = this.getScopedSelectorKey(this.currentSelector || '');
        if (!this.currentSelector || !this.cssRules.has(key)) {
            document.querySelectorAll('.control[data-property]').forEach(control => {
                control.value = '';
            });
            this.renderUsageDots(true);
            return;
        }

        const selectorRules = this.cssRules.get(key);
        
        document.querySelectorAll('.control[data-property]').forEach(control => {
            const property = control.dataset.property;
            control.value = selectorRules.get(property) || '';
        });

        this.renderUsageDots();
    }

    // ===================================================================
    // SELECTOR SUGGESTIONS
    // ===================================================================

    collectSelectorsFromDom() {
        if (!this.iframeDoc) return [];
        const selectors = new Set();

        this.iframeDoc.querySelectorAll('*').forEach(el => {
            selectors.add(el.tagName.toLowerCase());
            if (el.id) selectors.add('#' + el.id);
            el.classList.forEach(cls => selectors.add('.' + cls));
        });

        return Array.from(selectors);
    }

    updateSelectorSuggestions(initial = false) {
        const input = document.getElementById('selector-input');
        const list = document.getElementById('selector-suggest');
        if (!input || !list) return;
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

        // Prioritize: ID > class > tag
        items.sort((a, b) => {
            const rank = s => s.startsWith('#') ? 0 : (s.startsWith('.') ? 1 : 2);
            const diff = rank(a) - rank(b);
            return diff !== 0 ? diff : a.length - b.length;
        });

        items = items.slice(0, 100);
        this.selectorSuggestions = items;
        this.suggestActiveIndex = initial ? -1 : 0;
        this.renderSelectorSuggestions();
    }

    renderSelectorSuggestions() {
        const list = document.getElementById('selector-suggest');
        if (!list) return;

        const input = document.getElementById('selector-input');
        if (document.activeElement !== input || !this.selectorSuggestions.length) {
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
                this.previewHighlightSelector(s);
            });
            item.addEventListener('mouseleave', () => {
                this.clearPreviewHighlight();
            });
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

    setSuggestActive(idx) {
        this.suggestActiveIndex = idx;
        const list = document.getElementById('selector-suggest');
        if (!list) return;
        list.querySelectorAll('.selector-suggest-item').forEach((el, i) => {
            el.classList.toggle('active', i === idx);
        });
    }

    moveSuggestActive(delta) {
        if (!this.selectorSuggestions.length) return;
        let idx = this.suggestActiveIndex + delta;
        if (idx < 0) idx = this.selectorSuggestions.length - 1;
        if (idx >= this.selectorSuggestions.length) idx = 0;
        this.setSuggestActive(idx);
        this.previewHighlightSelector(this.selectorSuggestions[idx]);
    }

    applyActiveSuggestion() {
        if (this.suggestActiveIndex < 0) return;
        this.applySuggestion(this.suggestActiveIndex);
    }

    applySuggestion(idx) {
        const s = this.selectorSuggestions[idx];
        if (!s) return;
        const input = document.getElementById('selector-input');
        input.value = s;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        this.hideSelectorSuggestions();
    }

    previewHighlightSelector(sel) {
        if (!this.iframeDoc) return;
        this.clearPreviewHighlight();
        try {
            this.iframeDoc.querySelectorAll(sel).forEach(el => el.classList.add('element-highlight'));
        } catch(e) { /* invalid selector */ }
    }

    clearPreviewHighlight() {
        if (!this.iframeDoc) return;
        this.iframeDoc.querySelectorAll('.element-highlight').forEach(el => el.classList.remove('element-highlight'));
    }

    // ===================================================================
    // USAGE DOTS & RESET
    // ===================================================================

    createUsageDot({ isSection = false, onReset }) {
        const dot = document.createElement('span');
        dot.className = 'usage-dot' + (isSection ? ' usage-dot--section' : '');
        dot.setAttribute('title', isSection ? 'Reset section' : 'Reset property');
        dot.setAttribute('role', 'button');
        dot.setAttribute('tabindex', '0');
        dot.addEventListener('click', (e) => { e.stopPropagation(); onReset(); });
        dot.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); onReset(); }
        });
        return dot;
    }

    renderUsageDots(clearOnly = false) {
        document.querySelectorAll('.usage-dot').forEach(d => d.remove());
        
        if (clearOnly || !this.currentSelector) return;
        
        const key = this.getScopedSelectorKey(this.currentSelector);
        const selectorRules = this.cssRules.get(key) || new Map();

        document.querySelectorAll('.accordion-item').forEach(item => {
            const header = item.querySelector('.accordion-header');
            const content = item.querySelector('.accordion-content');
            if (!header || !content) return;

            const controls = content.querySelectorAll('.control[data-property]');
            const props = Array.from(controls).map(c => c.dataset.property).filter(Boolean);
            const usedProps = props.filter(p => selectorRules.has(p) && selectorRules.get(p));

            // Section-level dot
            if (usedProps.length > 0) {
                header.appendChild(this.createUsageDot({
                    isSection: true,
                    onReset: () => {
                        usedProps.forEach(p => this.updateCSSProperty(p, ''));
                        this.updateVisualControls();
                    }
                }));
            }

            // Property-level dots
            Array.from(controls).forEach(control => {
                const prop = control.dataset.property;
                if (!prop || !selectorRules.has(prop) || !selectorRules.get(prop)) return;
                
                const group = control.closest('.control-group');
                const label = group?.querySelector('.control-label');
                const target = label || group || control;
                target.appendChild(this.createUsageDot({
                    isSection: false,
                    onReset: () => {
                        this.updateCSSProperty(prop, '');
                        this.updateVisualControls();
                    }
                }));
            });
        });
    }

    // ===================================================================
    // CSS GENERATION & PARSING
    // ===================================================================

    /**
     * Generates a complete CSS string from the in-memory rule map.
     * @param {Object} options
     * @param {boolean} options.is_preview — if true, appends !important to all values
     * @returns {string}
     */
    generateCSS(options = { is_preview: false }) {
        let css = '';
        const rootRules = [];
        const tabletRules = [];
        const mobileRules = [];

        for (const [selectorKey, rules] of this.cssRules) {
            const { scope, selector } = this.parseScopedSelectorKey(selectorKey);
            const bucket = scope === 'mobile' ? mobileRules : (scope === 'tablet' ? tabletRules : rootRules);
            bucket.push([selector, rules, selectorKey]);
        }

        const renderBlock = (pairs) => {
            let out = '';
            for (const [selector, rules, selectorKey] of pairs) {
                const nestedContent = this.nestedCSS.get(selectorKey) || '';
                if (rules.size === 0 && !nestedContent) continue;
                
                out += `${selector} {\n`;
                
                const combinedRules = new Map(rules);
                
                if (this.hasTransformProperties(rules)) {
                    const transformValue = this.buildTransformValue(rules);
                    if (transformValue) combinedRules.set('transform', transformValue);
                    this.removeTransformProperties(combinedRules);
                }
                
                if (this.hasFilterProperties(rules)) {
                    const filterValue = this.buildFilterValue(rules);
                    if (filterValue) combinedRules.set('filter', filterValue);
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
                
                // Append nested CSS rules (e.g. `p { color: red }` inside a parent)
                if (nestedContent) {
                    out += nestedContent + '\n';
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

        if (this.extraRootCSS?.trim()) css += `\n${this.extraRootCSS}\n`;
        if (this.extraMediaCSS?.trim()) css += `\n${this.extraMediaCSS}\n`;

        return css;
    }

    /**
     * Parses a CSS string into the in-memory rule map.
     * Handles @media blocks, transform shorthand decomposition, and filter decomposition.
     * @param {string} css
     */
    parseCSS(css) {
        this.cssRules.clear();
        this.nestedCSS.clear();

        /**
         * Extracts @media blocks from a CSS string using brace-depth tracking.
         * Returns { blocks: [{query, body}], root: string }
         */
        const extractMedia = (input) => {
            const blocks = [];
            const rootParts = [];
            let i = 0;
            let last = 0;
            while (true) {
                const at = input.indexOf('@media', i);
                if (at === -1) break;
                rootParts.push(input.slice(last, at));
                const brace = input.indexOf('{', at);
                if (brace === -1) break;
                const query = input.slice(at + 6, brace).trim();
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

        /**
         * Extracts top-level CSS rule blocks from a string using brace-depth
         * tracking (handles nested CSS correctly).
         * Returns { rules: [{selector, body}], leftover: string }
         */
        const extractTopLevelRules = (block) => {
            const rules = [];
            let leftover = '';
            let i = 0;

            while (i < block.length) {
                // Skip whitespace
                while (i < block.length && /\s/.test(block[i])) i++;
                if (i >= block.length) break;

                const bracePos = block.indexOf('{', i);
                if (bracePos === -1) {
                    leftover += block.slice(i);
                    break;
                }

                const selector = block.slice(i, bracePos).trim();

                // Find matching closing brace (depth-aware)
                let depth = 1;
                let pos = bracePos + 1;
                while (pos < block.length && depth > 0) {
                    if (block[pos] === '{') depth++;
                    else if (block[pos] === '}') depth--;
                    pos++;
                }

                if (!selector) {
                    i = pos;
                    continue;
                }

                const body = block.slice(bracePos + 1, pos - 1);
                rules.push({ selector, body });
                i = pos;
            }

            return { rules, leftover };
        };

        /**
         * Separates direct CSS declarations from nested rule blocks inside
         * a rule body. For example, given:
         *   "color: blue; p { color: red }"
         * Returns:
         *   declarations: "color: blue"
         *   nested: "  p { color: red }\n"
         */
        const separateDeclarationsAndNested = (body) => {
            let declarations = '';
            let nested = '';
            let i = 0;
            let currentChunk = '';

            while (i < body.length) {
                const ch = body[i];

                if (ch === '{') {
                    // We hit a nested block. `currentChunk` may contain:
                    //   "color: blue;\n  p " → declarations + nested selector
                    // Split at the last semicolon.
                    const lastSemi = currentChunk.lastIndexOf(';');
                    if (lastSemi !== -1) {
                        declarations += currentChunk.slice(0, lastSemi + 1);
                        currentChunk = currentChunk.slice(lastSemi + 1);
                    }

                    const nestedSelector = currentChunk.trim();
                    currentChunk = '';

                    // Find matching closing brace
                    let depth = 1;
                    let pos = i + 1;
                    while (pos < body.length && depth > 0) {
                        if (body[pos] === '{') depth++;
                        else if (body[pos] === '}') depth--;
                        pos++;
                    }

                    const nestedBody = body.slice(i + 1, pos - 1);
                    if (nestedSelector) {
                        nested += `  ${nestedSelector} {${nestedBody}}\n`;
                    }
                    i = pos;
                } else {
                    currentChunk += ch;
                    i++;
                }
            }

            // Remaining chunk is declarations
            declarations += currentChunk;
            return { declarations: declarations.trim(), nested: nested.trimEnd() };
        };

        /**
         * Parses a CSS block (which may contain nested rules) into the
         * cssRules and nestedCSS maps.
         * Returns any leftover text that wasn't part of a rule.
         */
        const parseRuleBlock = (block, scope = 'desktop') => {
            const { rules, leftover } = extractTopLevelRules(block);

            rules.forEach(({ selector, body }) => {
                const { declarations, nested } = separateDeclarationsAndNested(body);

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

                const key = this.getScopedSelectorKey(selector, scope);

                if (selectorRules.size > 0 || nested) {
                    this.cssRules.set(key, selectorRules);
                }
                if (nested) {
                    this.nestedCSS.set(key, nested);
                }
            });

            return leftover;
        };

        const scopeFromQuery = (q) => {
            const norm = q.replace(/\s+/g, ' ').toLowerCase();
            if (/max-width\s*:\s*640px/.test(norm)) return 'mobile';
            if (/max-width\s*:\s*1024px/.test(norm)) return 'tablet';
            return 'desktop';
        };

        this.extraMediaCSS = '';
        this.extraRootCSS = '';

        const { blocks, root } = extractMedia(css || '');

        const rootLeftover = parseRuleBlock(root, 'desktop');
        this.extraRootCSS = (rootLeftover || '').trim();

        blocks.forEach(({ query, body }) => {
            const scope = scopeFromQuery(query);
            if (scope === 'desktop') {
                this.extraMediaCSS += `\n@media ${query} {\n${body}\n}\n`;
            } else {
                parseRuleBlock(body, scope);
            }
        });
    }

    // ===================================================================
    // DEVICE HELPERS
    // ===================================================================

    setDevice(device) {
        this.currentDevice = device || 'desktop';
        this.applyPreviewDevice();
        this.updateVisualControls();
    }

    /**
     * Sets a fixed width on the preview iframe based on the selected device.
     * The iframe is intentionally NON-responsive — it simulates a fixed device viewport.
     */
    applyPreviewDevice() {
        const iframe = document.getElementById('preview-iframe');
        if (!iframe) return;
        
        const widths = { desktop: '1920px', tablet: '1024px', mobile: '640px' };
        const w = widths[this.currentDevice] || widths.desktop;
        iframe.style.width = w;
        iframe.style.minWidth = w;
        iframe.style.maxWidth = w;
    }

    /**
     * Creates a scoped key for the CSS rules map: [scope, selector] as JSON.
     */
    getScopedSelectorKey(selector, scope = this.currentDevice) {
        try { return JSON.stringify([scope, selector]); } catch(e) { return scope + '|' + selector; }
    }

    /**
     * Parses a scoped key back into { scope, selector }.
     */
    parseScopedSelectorKey(key) {
        try {
            const [scope, selector] = JSON.parse(key);
            return { scope: scope || 'desktop', selector: selector || '' };
        } catch(e) {
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

    // ===================================================================
    // SAVE / LOAD
    // ===================================================================

    /**
     * Loads saved CSS from the PHP data bridge (window.livecssConfig.savedCSS).
     * @returns {Promise<void>}
     */
    loadSavedCSS() {
        return new Promise((resolve) => {
            try {
                const config = window.livecssConfig || {};
                const savedCSS = config.savedCSS || '';

                if (savedCSS && savedCSS.trim()) {
                    LiveCSSLog.info('Loading saved CSS (' + savedCSS.length + ' chars)');
                    this.parseCSS(savedCSS);
                    this.updateCodeEditor();
                    this.initialCSSState = savedCSS;
                    this.captureHistory();
                    
                    setTimeout(() => { this.updatePreview(); }, 100);
                } else {
                    LiveCSSLog.debug('No saved CSS found');
                    this.initialCSSState = '';
                }
                resolve();
            } catch (error) {
                LiveCSSLog.warn('Error loading saved CSS:', error);
                resolve(); // Don't reject — loading is non-critical
            }
        });
    }

    /**
     * Persists CSS to the server via WordPress AJAX.
     */
    saveCSS() {
        const css = this.generateCSS();
        const config = window.livecssConfig || {};
        
        // Visual feedback that save is in progress
        this.showStatusMessage('Saving...', 'success');
        
        const formData = new FormData();
        formData.append('action', 'livecss_save');
        formData.append('css', css);
        formData.append('nonce', config.saveNonce || '');

        fetch(config.ajaxUrl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showStatusMessage('CSS saved successfully!', 'success');
                this.hasUnsavedChanges = false;
                this.updateSaveButtonState();
                this.initialCSSState = this.generateCSS();
                LiveCSSLog.ok('CSS saved');
            } else {
                if (data.data && data.data === 'Failed to write to CSS file.') {
                    this.showConfirmationPopup(
                        'Error: Could not save because the css file may be missing. Would you like to try and recreate it?',
                        (confirmed) => {
                            if (confirmed) {
                                this.recreateFile();
                            } else {
                                this.showStatusMessage('Save failed. Please check file permissions or recreate the file manually.', 'error');
                            }
                        }
                    );
                } else {
                    this.showStatusMessage('Error saving CSS: ' + (data.data || 'Unknown error'), 'error');
                }
            }
        })
        .catch(error => {
            LiveCSSLog.error('Save failed:', error);
            this.showStatusMessage('Error saving CSS. Please try again.', 'error');
        });
    }

    /**
     * Shows a Yes/No confirmation popup.
     */
    showConfirmationPopup(message, callback) {
        const popup = document.getElementById('confirmation-popup');
        const messageEl = document.getElementById('popup-message');
        const yesButton = document.getElementById('popup-button-yes');
        const noButton = document.getElementById('popup-button-no');

        if (!popup || !messageEl || !yesButton || !noButton) return;

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
        if (popup) popup.classList.remove('visible');
    }

    /**
     * Shows a temporary status toast message.
     */
    showStatusMessage(message, type = 'success') {
        const statusEl = document.getElementById('status-message');
        if (!statusEl) return;
        statusEl.textContent = message;
        statusEl.className = `status-message ${type} show`;
        
        setTimeout(() => {
            statusEl.classList.remove('show');
        }, 3000);
    }

    /**
     * Attempts to recreate the CSS output file on the server, then retries save.
     */
    recreateFile() {
        const config = window.livecssConfig || {};

        const formData = new FormData();
        formData.append('action', 'livecss_recreate_file');
        formData.append('nonce', config.recreateNonce || '');

        fetch(config.ajaxUrl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showStatusMessage('File recreated! Retrying save...', 'success');
                this.saveCSS();
            } else {
                this.showStatusMessage('Error: Could not recreate file. ' + (data.data || ''), 'error');
            }
        })
        .catch(error => {
            LiveCSSLog.error('File recreation failed:', error);
            this.showStatusMessage('A critical error occurred while trying to recreate the file.', 'error');
        });
    }

    // ===================================================================
    // CHANGE TRACKING & HISTORY (UNDO/REDO)
    // ===================================================================
    
    markAsChanged() {
        if (!this.hasUnsavedChanges) {
            this.hasUnsavedChanges = true;
            this.updateSaveButtonState();
        }
    }
    
    updateSaveButtonState() {
        const saveButton = document.getElementById('save-button');
        if (saveButton) {
            if (this.hasUnsavedChanges) {
                saveButton.classList.add('has-changes');
                saveButton.setAttribute('title', 'Save CSS (Ctrl+S) — You have unsaved changes');
            } else {
                saveButton.classList.remove('has-changes');
                saveButton.setAttribute('title', 'Save CSS (Ctrl+S)');
            }
        }
    }
    
    captureHistory() {
        clearTimeout(this.historyTimeout);
        this.historyTimeout = setTimeout(() => {
            const currentState = this.serializeState();
            
            if (this.history.length > 0 && 
                JSON.stringify(currentState) === JSON.stringify(this.history[this.historyIndex])) {
                return;
            }
            
            this.history = this.history.slice(0, this.historyIndex + 1);
            this.history.push(currentState);
            
            if (this.history.length > this.maxHistorySize) {
                this.history.shift();
            } else {
                this.historyIndex++;
            }
            
            this.updateHistoryButtons();
        }, 500);
    }
    
    serializeState() {
        return {
            css: this.generateCSS(),
            currentSelector: this.currentSelector,
            currentDevice: this.currentDevice,
            timestamp: Date.now()
        };
    }
    
    restoreState(state) {
        if (!state) return;
        
        const wasTracking = this.hasUnsavedChanges;
        
        this.parseCSS(state.css);
        this.updateCodeEditor();
        this.updatePreview();
        
        const selectorInput = document.getElementById('selector-input');
        if (selectorInput) {
            selectorInput.value = state.currentSelector || '';
            this.currentSelector = state.currentSelector || '';
            this.updateSelectionFromInput();
        }
        
        this.updateVisualControls();
        this.renderUsageDots();
        
        if (state.currentDevice && state.currentDevice !== this.currentDevice) {
            this.setDevice(state.currentDevice);
        }
        
        this.hasUnsavedChanges = wasTracking;
    }
    
    undo() {
        if (this.historyIndex > 0) {
            this.historyIndex--;
            this.restoreState(this.history[this.historyIndex]);
            this.updateHistoryButtons();
            this.showStatusMessage('Undo successful', 'success');
        } else {
            this.showStatusMessage('Nothing to undo', 'warning');
        }
    }
    
    redo() {
        if (this.historyIndex < this.history.length - 1) {
            this.historyIndex++;
            this.restoreState(this.history[this.historyIndex]);
            this.updateHistoryButtons();
            this.showStatusMessage('Redo successful', 'success');
        } else {
            this.showStatusMessage('Nothing to redo', 'warning');
        }
    }
    
    updateHistoryButtons() {
        // Ready for future undo/redo button UI
        // const undoBtn = document.getElementById('undo-button');
        // const redoBtn = document.getElementById('redo-button');
        // if (undoBtn) undoBtn.disabled = this.historyIndex <= 0;
        // if (redoBtn) redoBtn.disabled = this.historyIndex >= this.history.length - 1;
    }

    // ===================================================================
    // TRANSFORM & FILTER HELPERS
    // ===================================================================

    isTransformProperty(property) {
        return ['rotate', 'scale', 'scaleX', 'scaleY', 'translate', 'translateX', 'translateY', 'skew', 'skewX', 'skewY'].includes(property);
    }

    isFilterProperty(property) {
        return ['blur', 'brightness', 'contrast', 'grayscale', 'hue-rotate', 'invert', 'opacity', 'saturate', 'sepia', 'drop-shadow'].includes(property);
    }

    hasTransformProperties(rules) {
        for (const property of rules.keys()) {
            if (this.isTransformProperty(property)) return true;
        }
        return false;
    }

    hasFilterProperties(rules) {
        for (const property of rules.keys()) {
            if (this.isFilterProperty(property)) return true;
        }
        return false;
    }

    removeTransformProperties(rules) {
        ['rotate', 'scale', 'scaleX', 'scaleY', 'translate', 'translateX', 'translateY', 'skew', 'skewX', 'skewY']
            .forEach(prop => rules.delete(prop));
    }

    removeFilterProperties(rules) {
        ['blur', 'brightness', 'contrast', 'grayscale', 'hue-rotate', 'invert', 'opacity', 'saturate', 'sepia', 'drop-shadow']
            .forEach(prop => rules.delete(prop));
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
        const parts = [];
        
        if (rules.has('rotate') && rules.get('rotate'))       parts.push(`rotate(${rules.get('rotate')})`);
        
        if (rules.has('scale') && rules.get('scale')) {
            parts.push(`scale(${rules.get('scale')})`);
        } else {
            if (rules.has('scaleX') && rules.get('scaleX'))   parts.push(`scaleX(${rules.get('scaleX')})`);
            if (rules.has('scaleY') && rules.get('scaleY'))   parts.push(`scaleY(${rules.get('scaleY')})`);
        }
        
        if (rules.has('translate') && rules.get('translate')) {
            parts.push(`translate(${rules.get('translate')})`);
        } else {
            if (rules.has('translateX') && rules.get('translateX')) parts.push(`translateX(${rules.get('translateX')})`);
            if (rules.has('translateY') && rules.get('translateY')) parts.push(`translateY(${rules.get('translateY')})`);
        }
        
        if (rules.has('skew') && rules.get('skew')) {
            parts.push(`skew(${rules.get('skew')})`);
        } else {
            if (rules.has('skewX') && rules.get('skewX'))     parts.push(`skewX(${rules.get('skewX')})`);
            if (rules.has('skewY') && rules.get('skewY'))     parts.push(`skewY(${rules.get('skewY')})`);
        }

        return parts.join(' ');
    }

    buildFilterValue(rules) {
        const parts = [];
        ['blur', 'brightness', 'contrast', 'grayscale', 'hue-rotate', 'invert', 'opacity', 'saturate', 'sepia', 'drop-shadow']
            .forEach(prop => {
                if (rules.has(prop) && rules.get(prop)) {
                    parts.push(`${prop}(${rules.get(prop)})`);
                }
            });
        return parts.join(' ');
    }

    parseTransformValue(selectorRules, value) {
        selectorRules.set('transform', value);
        
        const funcs = value.match(/(\w+)\([^)]*\)/g) || [];
        funcs.forEach(func => {
            const m = func.match(/(\w+)\(([^)]*)\)/);
            if (!m) return;
            const [, name, val] = m;
            if (['rotate', 'scale', 'scaleX', 'scaleY', 'translate', 'translateX', 'translateY', 'skew', 'skewX', 'skewY'].includes(name)) {
                selectorRules.set(name, val);
            }
        });
    }

    parseFilterValue(selectorRules, value) {
        selectorRules.set('filter', value);
        
        const funcs = value.match(/(\w+-?\w*)\([^)]*\)/g) || [];
        funcs.forEach(func => {
            const m = func.match(/(\w+-?\w*)\(([^)]*)\)/);
            if (!m) return;
            const [, name, val] = m;
            if (['blur', 'brightness', 'contrast', 'grayscale', 'hue-rotate', 'invert', 'opacity', 'saturate', 'sepia', 'drop-shadow'].includes(name)) {
                selectorRules.set(name, val);
            }
        });
    }

    // ===================================================================
    // PUBLIC API & DEBUGGING
    // ===================================================================

    /** @returns {boolean} Whether the editor is fully initialized */
    isReady() {
        return this.isInitialized && 
               this.codeEditor !== null && 
               this.iframe !== null && 
               this.iframeDoc !== null;
    }

    /** @returns {Object} Current status snapshot for debugging */
    getStatus() {
        const status = {
            isInitialized: this.isInitialized,
            hasCodeEditor: this.codeEditor !== null,
            hasIframe: this.iframe !== null,
            hasIframeDoc: this.iframeDoc !== null,
            currentDevice: this.currentDevice,
            cssRulesCount: this.cssRules.size,
            hasUnsavedChanges: this.hasUnsavedChanges,
            historyLength: this.history.length,
            historyIndex: this.historyIndex,
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

    /** @returns {string} Currently generated CSS */
    getCurrentCSS() {
        return this.generateCSS();
    }

    /**
     * Sets up a MutationObserver + interval to auto-hide any re-appearing admin bar.
     */
    startAdminBarMonitoring() {
        if (!this.iframeDoc) return;
        
        this.hideWordPressAdminBar();
        
        try {
            if (window.MutationObserver) {
                const observer = new MutationObserver((mutations) => {
                    for (const mutation of mutations) {
                        if (mutation.type !== 'childList') continue;
                        for (const node of mutation.addedNodes) {
                            if (node.nodeType !== Node.ELEMENT_NODE) continue;
                            if (node.id === 'wpadminbar' || node.querySelector?.('#wpadminbar')) {
                                setTimeout(() => this.hideWordPressAdminBar(), 0);
                                return;
                            }
                        }
                    }
                });
                
                observer.observe(this.iframeDoc.body, { childList: true, subtree: true });
                this.adminBarObserver = observer;
            }
        } catch (error) {
            LiveCSSLog.warn('Admin bar observer setup failed:', error);
        }
        
        this.adminBarInterval = setInterval(() => {
            if (this.iframeDoc?.getElementById('wpadminbar')) {
                this.hideWordPressAdminBar();
            }
        }, 2000);
    }

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

/* =========================================================================
   Global references & debugging helpers
   ========================================================================= */

window.LiveCSSEditor = LiveCSSEditor;
window.liveCSSInstance = null;

window.debugLiveCSS = () => {
    if (window.liveCSSInstance) {
        const status = window.liveCSSInstance.getStatus();
        LiveCSSLog.info('Status:', status);
        LiveCSSLog.info('CSS:', window.liveCSSInstance.getCurrentCSS());
        return status;
    }
    LiveCSSLog.warn('Instance not available');
    return null;
};

window.refreshLiveCSSPreview = () => {
    if (window.liveCSSInstance) {
        window.liveCSSInstance.forceRefreshPreview();
    } else {
        LiveCSSLog.warn('Instance not available');
    }
};

window.hideAdminBar = () => {
    if (window.liveCSSInstance) {
        window.liveCSSInstance.hideWordPressAdminBar();
    } else {
        LiveCSSLog.warn('Instance not available');
    }
};

/* =========================================================================
   Bootstrap — runs on DOMContentLoaded
   ========================================================================= */

document.addEventListener('DOMContentLoaded', () => {
    LiveCSSLog.info('DOM ready — bootstrapping...');
    
    setTimeout(() => {
        window.liveCSSInstance = new LiveCSSEditor();

        // Sidebar resizing/collapsing logic
        (function(){
            const panel = document.getElementById('editor-panel');
            const resizer = document.getElementById('sidebar-resizer');
            if (!panel || !resizer) return;

            const STORAGE_KEY = 'livecss_sidebar_width_v1';
            const COLLAPSED_KEY = 'livecss_sidebar_collapsed_v1';
            const MIN_WIDTH = 260;
            const MAX_WIDTH_PCT = 0.7;

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
            let ticking = false;

            function startDrag(e){
                if (panel.classList.contains('is-collapsed')) return;
                dragging = true;
                panel.classList.add('is-dragging');
                startX = e.clientX || (e.touches && e.touches[0].clientX) || 0;
                startWidth = panel.getBoundingClientRect().width;
                document.body.style.userSelect = 'none';
                document.body.style.cursor = 'col-resize';
            }
            function onDrag(e){
                if (!dragging) return;
                const clientX = e.clientX || (e.touches && e.touches[0].clientX) || 0;
                
                if (!ticking) {
                    window.requestAnimationFrame(() => {
                        applyWidth(startWidth + (clientX - startX));
                        ticking = false;
                    });
                    ticking = true;
                }
            }
            function endDrag(){
                if (!dragging) return;
                dragging = false;
                panel.classList.remove('is-dragging');
                document.body.style.userSelect = '';
                document.body.style.cursor = '';
                try {
                    localStorage.setItem(STORAGE_KEY, String(Math.floor(panel.getBoundingClientRect().width)));
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
            resizer.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggleCollapse(); }
                const STEP = (e.shiftKey ? 40 : 20);
                if (!panel.classList.contains('is-collapsed')) {
                    if (e.key === 'ArrowLeft') {
                        e.preventDefault();
                        const w = panel.getBoundingClientRect().width - STEP;
                        applyWidth(w);
                        try { localStorage.setItem(STORAGE_KEY, String(Math.floor(w))); } catch(err) {}
                    } else if (e.key === 'ArrowRight') {
                        e.preventDefault();
                        const w = panel.getBoundingClientRect().width + STEP;
                        applyWidth(w);
                        try { localStorage.setItem(STORAGE_KEY, String(Math.floor(w))); } catch(err) {}
                    }
                }
            });
        })();
    }, 100);
});
