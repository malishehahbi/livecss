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

            /* Animated pulse on active selector */
            @keyframes spotlight-pulse {
                0%, 100% {s
                    box-shadow: 0 2px 16px rgba(26, 162, 230, 0.2);
                    border-left-color: rgba(26, 162, 230, 0.7);
                }
                50% {
                    box-shadow: 0 4px 24px rgba(26, 162, 230, 0.35);
                    border-left-color: rgba(26, 162, 230, 0.9);
                }
            }

            .cm-spotlight-active {
                animation: spotlight-pulse 2.5s ease-in-out infinite;
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

        console.log('[SpotlightMode] Updating spotlight for:', this.editor.currentSelector);

        // Clear existing markers
        this.clearMarkers();

        // Find current selector's position in CSS
        const selectorRange = this.findSelectorRange(this.editor.currentSelector);
        
        if (!selectorRange) {
            console.log('[SpotlightMode] Selector not found in CSS');
            return;
        }

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

        // Highlight current selector (editable)
        this.highlightRange(selectorRange.from, selectorRange.to);

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
        
        let startLine = -1;
        let endLine = -1;
        let braceDepth = 0;
        
        // Find selector start
        for (let i = 0; i < lines.length; i++) {
            if (selectorPattern.test(lines[i])) {
                startLine = i;
                // Count opening brace
                const openBraces = (lines[i].match(/\{/g) || []).length;
                const closeBraces = (lines[i].match(/\}/g) || []).length;
                braceDepth = openBraces - closeBraces;
                
                // Check if it's a one-liner
                if (braceDepth === 0 && openBraces > 0) {
                    endLine = i;
                    break;
                }
                break;
            }
        }
        
        if (startLine === -1) {
            console.warn('[SpotlightMode] Could not find selector:', selector);
            return null;
        }
        
        // If not a one-liner, find matching closing brace
        if (endLine === -1) {
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
            from: { line: startLine, ch: 0 },
            to: { line: endLine, ch: lines[endLine].length }
        };
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
    highlightRange(from, to) {
        try {
            const highlightMarker = this.codeEditor.markText(from, to, {
                className: 'cm-spotlight-active',
                readOnly: false,
                inclusiveLeft: true,
                inclusiveRight: true
            });
            
            this.blurMarkers.push(highlightMarker);
        } catch (error) {
            console.warn('[SpotlightMode] Error creating highlight marker:', error);
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
     * Check if spotlight is active
     */
    isSpotlightActive() {
        return this.isActive;
    }
}

// Export for global access
window.SpotlightMode = SpotlightMode;
console.log('✅ SpotlightMode library loaded');
