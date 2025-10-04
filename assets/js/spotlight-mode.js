/**
 * CodeMirror Auto-Spotlight Mode for LiveCSS Editor
 * Automatically highlights current selector while blurring/dimming others
 * 
 * @version 2.0.0
 * @author LiveCSS Team
 */

class SpotlightMode {
    constructor(editor) {
        this.editor = editor; // LiveCSSEditor instance
        this.codeEditor = null; // CodeMirror instance
        this.isActive = false;
        this.currentSelectorRange = null;
        this.blurMarkers = [];
        this.currentDevice = 'desktop'; // Track current device (desktop/tablet/mobile)
    }

    /**
     * Initialize spotlight mode
     */
    init(codeEditor) {
        this.codeEditor = codeEditor;
        this.injectStyles();
        this.setupKeyboardShortcuts();
        
        console.log('[SpotlightMode] Auto-spotlight initialized');
    }

    /**
     * Setup keyboard shortcuts (ESC to exit spotlight)
     */
    setupKeyboardShortcuts() {
        this.codeEditor.on('keydown', (cm, event) => {
            // ESC key pressed while spotlight is active
            if (event.key === 'Escape' && this.isActive) {
                console.log('[SpotlightMode] ESC pressed - clearing selector');
                
                // Clear the selector input
                const selectorInput = document.getElementById('selector-input');
                if (selectorInput) {
                    selectorInput.value = '';
                    
                    // Trigger input event to update editor
                    const inputEvent = new Event('input', { bubbles: true });
                    selectorInput.dispatchEvent(inputEvent);
                }
                
                // Prevent default ESC behavior
                event.preventDefault();
                event.stopPropagation();
            }
        });
    }

    /**
     * Inject CSS styles for spotlight effects
     */
    injectStyles() {
        if (document.getElementById('spotlight-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'spotlight-styles';
        style.textContent = `
            /* Blurred/dimmed non-selected CSS */
            .cm-spotlight-blur {
                opacity: 0.25;
                filter: blur(1.5px);
                pointer-events: none !important;
                user-select: none;
                background: rgba(0, 0, 0, 0.03);
                transition: opacity 0.3s ease;
            }

            /* Highlighted current selector */
            .cm-spotlight-active {
                background: rgba(26, 162, 230, 0.08) !important;
                box-shadow: 0 2px 16px rgba(26, 162, 230, 0.2);
                position: relative;
                z-index: 10;
                pointer-events: auto !important;
            }
            
            /* Special highlight for @media query lines */
            .cm-spotlight-media {
                background: rgba(138, 43, 226, 0.12) !important;
                font-weight: 600 !important;
                box-shadow: 0 2px 16px rgba(138, 43, 226, 0.25);
                position: relative;
                z-index: 11;
                pointer-events: auto !important;
            }

            /* Animated pulse on active selector */
            @keyframes spotlight-pulse {
                0%, 100% {
                    box-shadow: 0 2px 16px rgba(26, 162, 230, 0.2);
                    border-left-color: rgba(26, 162, 230, 0.7);
                }
                50% {
                    box-shadow: 0 4px 24px rgba(26, 162, 230, 0.35);
                    border-left-color: rgba(26, 162, 230, 0.9);
                }
            }
            
            /* Animated pulse for @media lines */
            @keyframes spotlight-media-pulse {
                0%, 100% {
                    box-shadow: 0 2px 16px rgba(138, 43, 226, 0.25);
                    border-left-color: rgba(138, 43, 226, 0.8);
                }
                50% {
                    box-shadow: 0 4px 24px rgba(138, 43, 226, 0.4);
                    border-left-color: rgba(138, 43, 226, 1);
                }
            }

            .cm-spotlight-active {
                animation: spotlight-pulse 2.5s ease-in-out infinite;
            }
            
            .cm-spotlight-media {
                animation: spotlight-media-pulse 2.5s ease-in-out infinite;
            }

            /* Make CodeMirror wrapper relative for overlays */
            .CodeMirror.spotlight-mode-active {
                position: relative;
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Activate spotlight mode when Code Editor tab is active
     */
    activate() {
        if (this.isActive) {
            // Already active, just update
            this.updateSpotlight();
            return;
        }
        
        if (!this.editor.currentSelector) {
            console.log('[SpotlightMode] No selector - spotlight not activated');
            return;
        }

        console.log('[SpotlightMode] Activating for selector:', this.editor.currentSelector);
        
        this.isActive = true;
        this.codeEditor.getWrapperElement().classList.add('spotlight-mode-active');
        
        // Apply spotlight effect
        this.updateSpotlight();
    }

    /**
     * Deactivate spotlight mode
     */
    deactivate() {
        if (!this.isActive) return;
        
        console.log('[SpotlightMode] Deactivating');
        
        this.clearMarkers();
        this.isActive = false;
        this.codeEditor.getWrapperElement().classList.remove('spotlight-mode-active');
    }

    /**
     * Update spotlight to highlight current selector
     */
    updateSpotlight() {
        if (!this.isActive) return;
        
        if (!this.editor.currentSelector) {
            console.log('[SpotlightMode] No selector - clearing spotlight');
            this.clearMarkers();
            return;
        }

        console.log('[SpotlightMode] 🔍 Updating spotlight for:', this.editor.currentSelector, 'Device:', this.currentDevice);

        // Clear existing markers
        this.clearMarkers();

        // Find current selector's position in CSS
        const selectorRange = this.findSelectorRange(this.editor.currentSelector);
        
        if (!selectorRange) {
            console.log('[SpotlightMode] ❌ Selector not found in CSS');
            return;
        }

        console.log('[SpotlightMode] 📍 Found range:', {
            from: `Line ${selectorRange.from.line}`,
            to: `Line ${selectorRange.to.line}`,
            lines: selectorRange.to.line - selectorRange.from.line + 1
        });

        this.currentSelectorRange = selectorRange;

        // Get total lines
        const totalLines = this.codeEditor.lineCount();

        // Blur everything before the selector
        if (selectorRange.from.line > 0) {
            this.blurRange(
                { line: 0, ch: 0 },
                { line: selectorRange.from.line, ch: 0 }
            );
        }

        // Blur everything after the selector
        if (selectorRange.to.line < totalLines - 1) {
            const lastLineLength = this.codeEditor.getLine(totalLines - 1).length;
            this.blurRange(
                { line: selectorRange.to.line + 1, ch: 0 },
                { line: totalLines - 1, ch: lastLineLength }
            );
        }

        // Check if the range starts with @media query
        const firstLine = this.codeEditor.getLine(selectorRange.from.line);
        console.log('[SpotlightMode] 🔍 First line of range:', firstLine);
        console.log('[SpotlightMode] 📊 Range info:', {
            fromLine: selectorRange.from.line,
            toLine: selectorRange.to.line,
            startsWithMedia: firstLine.trim().startsWith('@media')
        });
        
        if (firstLine.trim().startsWith('@media')) {
            console.log('[SpotlightMode] 🎨 Special highlighting for @media line');
            console.log('[SpotlightMode] 🟣 Highlighting @media line:', selectorRange.from.line);
            
            // Highlight the @media line with special purple style
            this.highlightRange(
                { line: selectorRange.from.line, ch: 0 },
                { line: selectorRange.from.line, ch: firstLine.length },
                true // isMediaQuery flag
            );
            
            console.log('[SpotlightMode] 🔵 Highlighting content lines:', 
                (selectorRange.from.line + 1), 'to', selectorRange.to.line);
            
            // Highlight the rest with normal blue style
            if (selectorRange.from.line < selectorRange.to.line) {
                this.highlightRange(
                    { line: selectorRange.from.line + 1, ch: 0 },
                    selectorRange.to,
                    false // regular selector
                );
            }
        } else {
            console.log('[SpotlightMode] 🔵 Regular selector - single highlight');
            // Regular selector - highlight normally
            this.highlightRange(selectorRange.from, selectorRange.to, false);
        }

        // Scroll to show the highlighted selector
        setTimeout(() => {
            this.codeEditor.scrollIntoView({ line: selectorRange.from.line, ch: 0 }, 150);
        }, 100);
    }

    /**
     * Find the line range for a specific selector in CodeMirror
     */
    findSelectorRange(selector) {
        const content = this.codeEditor.getValue();
        const lines = content.split('\n');
        
        // Escape special regex characters in selector
        const escapedSelector = selector.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        
        // Try to find exact selector match
        const selectorPattern = new RegExp('^\\s*' + escapedSelector + '\\s*\\{', 'i');
        
        // Find ALL matches of this selector
        const matches = [];
        for (let i = 0; i < lines.length; i++) {
            if (selectorPattern.test(lines[i])) {
                const match = this.getSelectorBlock(lines, i);
                if (match) {
                    // Also store the scope information
                    match.scope = this.getDeviceScope(i);
                    match.mediaQueryLine = this.findMediaQueryLine(i);
                    matches.push(match);
                }
            }
        }
        
        if (matches.length === 0) {
            console.warn('[SpotlightMode] ❌ Could not find selector:', selector);
            return null;
        }
        
        // If only one match, return it
        if (matches.length === 1) {
            console.log('[SpotlightMode] ✅ Found 1 match for selector at line', matches[0].range.from.line);
            return this.expandRangeForMediaQuery(matches[0]);
        }
        
        // Multiple matches - find the one that matches current device
        console.log(`[SpotlightMode] Found ${matches.length} matches, filtering by device:`, this.currentDevice);
        
        for (const match of matches) {
            console.log(`[SpotlightMode] Match at line ${match.range.from.line}, scope: ${match.scope}`);
            
            // Match based on current device
            if (this.currentDevice === 'desktop' && match.scope === 'desktop') {
                console.log('[SpotlightMode] ✅ Found desktop match (root CSS)');
                return this.expandRangeForMediaQuery(match);
            } else if (this.currentDevice === 'tablet' && match.scope === 'tablet') {
                console.log('[SpotlightMode] ✅ Found tablet match (@media max-width: 1024px)');
                return this.expandRangeForMediaQuery(match);
            } else if (this.currentDevice === 'mobile' && match.scope === 'mobile') {
                console.log('[SpotlightMode] ✅ Found mobile match (@media max-width: 640px)');
                return this.expandRangeForMediaQuery(match);
            }
        }
        
        // Fallback: return first match if no device-specific match found
        console.warn('[SpotlightMode] ⚠️ No device-specific match found, using first match');
        return this.expandRangeForMediaQuery(matches[0]);
    }
    
    /**
     * Expand range to include @media query block if selector is inside one
     */
    expandRangeForMediaQuery(match) {
        console.log('[SpotlightMode] 🔧 expandRangeForMediaQuery called:', {
            originalRange: `Line ${match.range.from.line} to ${match.range.to.line}`,
            mediaQueryLine: match.mediaQueryLine,
            scope: match.scope
        });
        
        if (match.mediaQueryLine !== null) {
            // Selector is inside @media query - expand range to include entire @media block
            console.log(`[SpotlightMode] 📦 Expanding range to include @media query at line ${match.mediaQueryLine}`);
            const content = this.codeEditor.getValue();
            const lines = content.split('\n');
            
            // Show the @media line
            console.log(`[SpotlightMode] @media line content: "${lines[match.mediaQueryLine]}"`);
            
            // Find the end of the @media block
            let braceDepth = 0;
            let mediaEndLine = -1;
            
            for (let i = match.mediaQueryLine; i < lines.length; i++) {
                const line = lines[i];
                braceDepth += (line.match(/\{/g) || []).length;
                braceDepth -= (line.match(/\}/g) || []).length;
                
                if (braceDepth === 0 && i > match.mediaQueryLine) {
                    mediaEndLine = i;
                    break;
                }
            }
            
            if (mediaEndLine !== -1) {
                const expandedRange = {
                    from: { line: match.mediaQueryLine, ch: 0 },
                    to: { line: mediaEndLine, ch: lines[mediaEndLine].length }
                };
                console.log('[SpotlightMode] ✅ Expanded range:', {
                    from: `Line ${expandedRange.from.line}`,
                    to: `Line ${expandedRange.to.line}`,
                    totalLines: mediaEndLine - match.mediaQueryLine + 1
                });
                return expandedRange;
            } else {
                console.warn('[SpotlightMode] ⚠️ Could not find end of @media block');
            }
        } else {
            console.log('[SpotlightMode] ℹ️ Not in @media query - returning original range');
        }
        
        // Not in @media or couldn't find end - return original range
        return match.range;
    }
    
    /**
     * Find the line number of the @media query containing the given line
     * Returns line number or null if not in a media query
     */
    findMediaQueryLine(lineNumber) {
        const content = this.codeEditor.getValue();
        const lines = content.split('\n');
        
        let currentMediaQueryLine = null;
        let braceDepth = 0;
        
        // Scan forward from start to track which @media block we're in
        for (let i = 0; i <= lineNumber; i++) {
            const line = lines[i];
            const trimmedLine = line.trim();
            
            // Check if this is an @media line
            if (trimmedLine.startsWith('@media')) {
                currentMediaQueryLine = i;
                braceDepth = 0;
            }
            
            // Count braces to track depth
            const openBraces = (line.match(/\{/g) || []).length;
            const closeBraces = (line.match(/\}/g) || []).length;
            braceDepth += openBraces - closeBraces;
            
            // If we close the @media block, reset
            if (braceDepth === 0 && currentMediaQueryLine !== null && i > currentMediaQueryLine) {
                currentMediaQueryLine = null;
            }
        }
        
        console.log('[findMediaQueryLine] Line', lineNumber, '→ @media line:', currentMediaQueryLine);
        return currentMediaQueryLine;
    }
    
    /**
     * Get a complete selector block with its range
     */
    getSelectorBlock(lines, startLine) {
        let endLine = -1;
        let braceDepth = 0;
        
        // Count opening brace on start line
        const openBraces = (lines[startLine].match(/\{/g) || []).length;
        const closeBraces = (lines[startLine].match(/\}/g) || []).length;
        braceDepth = openBraces - closeBraces;
        
        // Check if it's a one-liner
        if (braceDepth === 0 && openBraces > 0) {
            endLine = startLine;
        } else {
            // Find matching closing brace
            for (let i = startLine + 1; i < lines.length; i++) {
                const line = lines[i];
                braceDepth += (line.match(/\{/g) || []).length;
                braceDepth -= (line.match(/\}/g) || []).length;
                
                if (braceDepth === 0) {
                    endLine = i;
                    break;
                }
            }
        }
        
        if (endLine === -1) {
            // Selector not properly closed, highlight to end
            endLine = lines.length - 1;
        }
        
        return {
            range: {
                from: { line: startLine, ch: 0 },
                to: { line: endLine, ch: lines[endLine].length }
            }
        };
    }
    
    /**
     * Determine if a line is inside a media query and which device it targets
     * Returns: 'desktop', 'tablet', or 'mobile'
     */
    getDeviceScope(lineNumber) {
        const mediaQuery = this.findContainingMediaQuery(lineNumber);
        
        console.log(`[SpotlightMode] 🔍 getDeviceScope(${lineNumber}):`, mediaQuery);
        
        if (!mediaQuery) {
            console.log('[SpotlightMode] → Scope: desktop (no @media)');
            return 'desktop'; // Not in a media query = desktop (root CSS)
        }
        
        // Check if it's a tablet query (max-width: 1024px)
        if (mediaQuery.includes('1024px')) {
            console.log('[SpotlightMode] → Scope: tablet (found 1024px)');
            return 'tablet';
        }
        
        // Check if it's a mobile query (max-width: 640px)
        if (mediaQuery.includes('640px')) {
            console.log('[SpotlightMode] → Scope: mobile (found 640px)');
            return 'mobile';
        }
        
        // Default to desktop for unrecognized media queries
        console.log('[SpotlightMode] → Scope: desktop (unrecognized @media)');
        return 'desktop';
    }
    
    /**
     * Find the @media query that contains a given line number
     * Returns the media query text or null if not in a media query
     */
    findContainingMediaQuery(lineNumber) {
        const content = this.codeEditor.getValue();
        const lines = content.split('\n');
        
        console.log(`[SpotlightMode] 🔎 findContainingMediaQuery for line ${lineNumber}`);
        
        // First, check if we're inside ANY @media block
        let currentMediaQuery = null;
        let currentMediaLine = -1;
        let depth = 0;
        
        for (let i = 0; i <= lineNumber; i++) {
            const line = lines[i].trim();
            
            // Check if this line starts a @media query
            if (line.startsWith('@media')) {
                currentMediaQuery = line;
                currentMediaLine = i;
                console.log(`[SpotlightMode]   Found @media at line ${i}: "${line}"`);
            }
            
            // Count braces to track depth
            const openBraces = (lines[i].match(/\{/g) || []).length;
            const closeBraces = (lines[i].match(/\}/g) || []).length;
            depth += openBraces - closeBraces;
            
            // If we hit depth 0 after an @media, we've exited that block
            if (depth === 0 && currentMediaQuery && i > currentMediaLine) {
                console.log(`[SpotlightMode]   Exited @media block at line ${i}`);
                currentMediaQuery = null;
                currentMediaLine = -1;
            }
        }
        
        if (currentMediaQuery) {
            console.log(`[SpotlightMode] ✅ Line ${lineNumber} is inside @media: "${currentMediaQuery}"`);
        } else {
            console.log(`[SpotlightMode] ❌ Line ${lineNumber} is NOT inside any @media query`);
        }
        
        return currentMediaQuery;
    }

    /**
     * Blur a range of lines (make them dimmed and read-only)
     */
    blurRange(from, to) {
        try {
            // Ensure valid range
            if (from.line < 0 || to.line >= this.codeEditor.lineCount()) {
                return;
            }
            
            // Adjust 'to' position if needed
            if (to.line === this.codeEditor.lineCount() - 1) {
                to.ch = this.codeEditor.getLine(to.line).length;
            }
            
            const blurMarker = this.codeEditor.markText(from, to, {
                className: 'cm-spotlight-blur',
                readOnly: true,
                atomic: false,
                inclusiveLeft: true,
                inclusiveRight: false
            });
            
            this.blurMarkers.push(blurMarker);
        } catch (error) {
            console.warn('[SpotlightMode] Error creating blur marker:', error);
        }
    }

    /**
     * Highlight the active selector range
     */
    highlightRange(from, to, isMediaQuery = false) {
        try {
            const className = isMediaQuery ? 'cm-spotlight-media' : 'cm-spotlight-active';
            console.log(`[SpotlightMode] 💡 Applying ${className} from line ${from.line} to ${to.line}`);
            
            const highlightMarker = this.codeEditor.markText(from, to, {
                className: className,
                readOnly: false,
                inclusiveLeft: true,
                inclusiveRight: true
            });
            
            this.blurMarkers.push(highlightMarker);
            console.log(`[SpotlightMode] ✅ Marker created successfully with class: ${className}`);
        } catch (error) {
            console.error('[SpotlightMode] ❌ Error creating highlight marker:', error);
        }
    }

    /**
     * Clear all markers
     */
    clearMarkers() {
        this.blurMarkers.forEach(marker => {
            try {
                marker.clear();
            } catch (e) {
                // Marker already cleared
            }
        });
        this.blurMarkers = [];
    }

    /**
     * Update spotlight when selector changes
     */
    onSelectorChange(newSelector) {
        console.log('[SpotlightMode] Selector changed to:', newSelector);
        
        if (this.isActive) {
            this.updateSpotlight();
        }
    }

    /**
     * Update spotlight when device changes (Desktop/Tablet/Mobile)
     * Called when user clicks device toggle buttons
     */
    onDeviceChange(newDevice) {
        console.log('[SpotlightMode] 📱 Device changed from', this.currentDevice, 'to', newDevice);
        this.currentDevice = newDevice;
        
        if (this.isActive && this.editor.currentSelector) {
            console.log('[SpotlightMode] 🔄 Updating spotlight for new device with selector:', this.editor.currentSelector);
            this.updateSpotlight();
        } else {
            if (!this.isActive) {
                console.log('[SpotlightMode] ⚠️ Spotlight not active, skipping update');
            }
            if (!this.editor.currentSelector) {
                console.log('[SpotlightMode] ⚠️ No selector selected, skipping update');
            }
        }
    }

    /**
     * Check if spotlight is active
     */
    isSpotlightActive() {
        return this.isActive;
    }
}

// Export for global access
window.SpotlightMode = SpotlightMode;
console.log('✅ SpotlightMode library loaded');
