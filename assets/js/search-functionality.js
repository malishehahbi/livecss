/**
 * Search Functionality for LiveCSS Editor
 * Provides search capabilities for both Visual Editor (properties) and Code Editor (CSS text)
 * 
 * @version 1.0.0
 * @author LiveCSS Team
 */

class SearchFunctionality {
    constructor(editor) {
        this.editor = editor; // LiveCSSEditor instance
        this.codeEditor = null; // CodeMirror instance
        
        // Visual Editor search
        this.visualSearchInput = null;
        this.visualSearchClear = null;
        this.visualSearchInfo = null;
        this.visualSearchContainer = null;
        
        // Code Editor search
        this.codeSearchInput = null;
        this.codeSearchClear = null;
        this.codeSearchNav = null;
        this.codeSearchCount = null;
        this.codeSearchPrev = null;
        this.codeSearchNext = null;
        this.codeSearchContainer = null;
        
        // Search toggle button
        this.searchToggleBtn = null;
        this.isSearchVisible = false;
        
        // Search state
        this.codeSearchMarkers = [];
        this.currentMatchIndex = 0;
        this.totalMatches = 0;
    }

    /**
     * Initialize search functionality
     */
    init(codeEditor) {
        this.codeEditor = codeEditor;
        this.setupElements();
        this.setupEventListeners();
        
        console.log('[SearchFunctionality] Search initialized');
    }

    /**
     * Setup DOM element references
     */
    setupElements() {
        // Visual Editor elements
        this.visualSearchInput = document.getElementById('visual-search-input');
        this.visualSearchClear = document.getElementById('visual-search-clear');
        this.visualSearchInfo = document.getElementById('visual-search-info');
        this.visualSearchContainer = document.getElementById('visual-search-container');
        
        // Code Editor elements
        this.codeSearchInput = document.getElementById('code-search-input');
        this.codeSearchClear = document.getElementById('code-search-clear');
        this.codeSearchNav = document.getElementById('code-search-nav');
        this.codeSearchCount = document.getElementById('code-search-count');
        this.codeSearchPrev = document.getElementById('code-search-prev');
        this.codeSearchNext = document.getElementById('code-search-next');
        this.codeSearchContainer = document.getElementById('code-search-container');
        
        // Search toggle button
        this.searchToggleBtn = document.getElementById('search-toggle-btn');
    }

    /**
     * Setup event listeners for search inputs and buttons
     */
    setupEventListeners() {
        // Search toggle button
        if (this.searchToggleBtn) {
            this.searchToggleBtn.addEventListener('click', () => {
                this.toggleSearch();
            });
        }

        // Visual Editor search
        if (this.visualSearchInput) {
            this.visualSearchInput.addEventListener('input', () => {
                this.performVisualSearch();
            });
            
            this.visualSearchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.toggleSearch(); // Close search on ESC
                }
            });
        }

        if (this.visualSearchClear) {
            this.visualSearchClear.addEventListener('click', () => {
                this.clearVisualSearch();
            });
        }

        // Code Editor search
        if (this.codeSearchInput) {
            this.codeSearchInput.addEventListener('input', () => {
                this.performCodeSearch();
            });
            
            this.codeSearchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (e.shiftKey) {
                        this.navigateToPrevMatch();
                    } else {
                        this.navigateToNextMatch();
                    }
                } else if (e.key === 'Escape') {
                    this.toggleSearch(); // Close search on ESC
                }
            });
        }

        if (this.codeSearchClear) {
            this.codeSearchClear.addEventListener('click', () => {
                this.clearCodeSearch();
            });
        }

        if (this.codeSearchPrev) {
            this.codeSearchPrev.addEventListener('click', () => {
                this.navigateToPrevMatch();
            });
        }

        if (this.codeSearchNext) {
            this.codeSearchNext.addEventListener('click', () => {
                this.navigateToNextMatch();
            });
        }
    }

    /**
     * Show/hide search containers based on active tab
     */
    updateSearchVisibility(activeTab) {
        // Always keep the inactive tab's search hidden
        if (activeTab === 'visual') {
            // Show visual search container (but may still be collapsed)
            this.visualSearchContainer?.classList.remove('hidden');
            // Always hide code search when visual tab is active
            this.codeSearchContainer?.classList.add('hidden');
            // Also collapse code search
            this.codeSearchContainer?.classList.add('collapsed');
        } else if (activeTab === 'code') {
            // Always hide visual search when code tab is active
            this.visualSearchContainer?.classList.add('hidden');
            // Show code search container (but may still be collapsed)
            this.codeSearchContainer?.classList.remove('hidden');
            // Also collapse visual search
            this.visualSearchContainer?.classList.add('collapsed');
        }
    }

    /**
     * Toggle search bar visibility
     */
    toggleSearch() {
        this.isSearchVisible = !this.isSearchVisible;
        
        const activeTab = document.querySelector('.tab.active')?.dataset.tab;
        const activeContainer = activeTab === 'visual' 
            ? this.visualSearchContainer 
            : this.codeSearchContainer;
        
        const inactiveContainer = activeTab === 'visual'
            ? this.codeSearchContainer
            : this.visualSearchContainer;
        
        if (this.isSearchVisible) {
            // Show search for active tab only
            activeContainer?.classList.remove('collapsed');
            this.searchToggleBtn?.classList.add('active');
            
            // Ensure inactive tab's search is hidden and collapsed
            inactiveContainer?.classList.add('hidden');
            inactiveContainer?.classList.add('collapsed');
            
            // Focus search input
            setTimeout(() => {
                this.focusSearch(activeTab);
            }, 100);
            
            console.log('[SearchFunctionality] Search opened for:', activeTab);
        } else {
            // Hide all search bars
            this.visualSearchContainer?.classList.add('collapsed');
            this.codeSearchContainer?.classList.add('collapsed');
            this.searchToggleBtn?.classList.remove('active');
            
            // Clear searches
            this.clearVisualSearch();
            this.clearCodeSearch();
            
            console.log('[SearchFunctionality] Search closed');
        }
    }

    /**
     * Open search bar if it's closed
     */
    openSearch() {
        if (!this.isSearchVisible) {
            this.toggleSearch();
        }
    }

    /**
     * VISUAL EDITOR SEARCH
     */

    /**
     * Perform search in Visual Editor (properties)
     */
    performVisualSearch() {
        const query = this.visualSearchInput.value.trim().toLowerCase();
        
        // Show/hide clear button
        if (query) {
            this.visualSearchClear?.classList.remove('hidden');
        } else {
            this.visualSearchClear?.classList.add('hidden');
            this.clearVisualSearch();
            return;
        }

        console.log('[SearchFunctionality] Searching properties for:', query);

        // Get all control groups and accordion items
        const controlGroups = document.querySelectorAll('#tab-visual .control-group');
        const accordionItems = document.querySelectorAll('#tab-visual .accordion-item');
        
        let matchCount = 0;
        let visibleAccordions = new Set();

        // Search through control groups
        controlGroups.forEach(group => {
            const label = group.querySelector('.control-label')?.textContent.toLowerCase() || '';
            const isMatch = label.includes(query);
            
            if (isMatch) {
                group.classList.add('search-match');
                group.classList.remove('search-hidden');
                matchCount++;
                
                // Mark parent accordion as visible
                const parentAccordion = group.closest('.accordion-item');
                if (parentAccordion) {
                    visibleAccordions.add(parentAccordion);
                }
            } else {
                group.classList.remove('search-match');
                group.classList.add('search-hidden');
            }
        });

        // Show/hide accordion sections based on matches
        accordionItems.forEach(accordion => {
            if (visibleAccordions.has(accordion)) {
                accordion.classList.remove('search-hidden');
                // Auto-expand accordion with matches
                const content = accordion.querySelector('.accordion-content');
                const header = accordion.querySelector('.accordion-header');
                if (content && header) {
                    // Add active class instead of setting inline style
                    content.classList.add('active', 'search-expanded');
                    header.classList.add('active');
                }
            } else {
                accordion.classList.add('search-hidden');
            }
        });

        // Update info
        this.updateVisualSearchInfo(matchCount, query);
    }

    /**
     * Update visual search results info
     */
    updateVisualSearchInfo(count, query) {
        if (count > 0) {
            this.visualSearchInfo.textContent = `Found ${count} propert${count === 1 ? 'y' : 'ies'} matching "${query}"`;
            this.visualSearchInfo.classList.remove('hidden');
        } else {
            this.visualSearchInfo.textContent = `No properties found matching "${query}"`;
            this.visualSearchInfo.classList.remove('hidden');
        }
    }

    /**
     * Clear visual search
     */
    clearVisualSearch() {
        if (this.visualSearchInput) {
            this.visualSearchInput.value = '';
        }
        
        this.visualSearchClear?.classList.add('hidden');
        this.visualSearchInfo?.classList.add('hidden');

        // Remove all search classes
        const controlGroups = document.querySelectorAll('#tab-visual .control-group');
        const accordionItems = document.querySelectorAll('#tab-visual .accordion-item');
        
        controlGroups.forEach(group => {
            group.classList.remove('search-match', 'search-hidden');
        });

        accordionItems.forEach(accordion => {
            accordion.classList.remove('search-hidden');
            // Remove search-expanded class from accordion content
            const content = accordion.querySelector('.accordion-content');
            if (content) {
                content.classList.remove('search-expanded');
            }
        });

        console.log('[SearchFunctionality] Visual search cleared');
    }

    /**
     * CODE EDITOR SEARCH
     */

    /**
     * Perform search in Code Editor (CodeMirror)
     */
    performCodeSearch() {
        const query = this.codeSearchInput.value.trim();
        
        // Show/hide clear button
        if (query) {
            this.codeSearchClear?.classList.remove('hidden');
        } else {
            this.codeSearchClear?.classList.add('hidden');
            this.clearCodeSearch();
            return;
        }

        console.log('[SearchFunctionality] Searching code for:', query);

        // Clear previous markers
        this.clearCodeMarkers();

        if (!query || !this.codeEditor) {
            return;
        }

        // Search for all matches manually (CodeMirror 5 doesn't have getSearchCursor)
        const content = this.codeEditor.getValue();
        const queryLower = query.toLowerCase();
        const contentLower = content.toLowerCase();
        
        let searchIndex = 0;
        while (searchIndex < contentLower.length) {
            const foundIndex = contentLower.indexOf(queryLower, searchIndex);
            if (foundIndex === -1) break;
            
            // Convert string index to CodeMirror position
            const from = this.codeEditor.posFromIndex(foundIndex);
            const to = this.codeEditor.posFromIndex(foundIndex + query.length);
            
            const marker = this.codeEditor.markText(from, to, {
                className: 'cm-search-match',
                clearWhenEmpty: false
            });
            
            this.codeSearchMarkers.push({ marker, from, to });
            searchIndex = foundIndex + 1;
        }

        this.totalMatches = this.codeSearchMarkers.length;
        
        if (this.totalMatches > 0) {
            this.currentMatchIndex = 0;
            this.highlightCurrentMatch();
            this.codeSearchNav?.classList.remove('hidden');
        } else {
            this.codeSearchNav?.classList.add('hidden');
        }

        this.updateCodeSearchCount();
    }

    /**
     * Navigate to next match
     */
    navigateToNextMatch() {
        if (this.totalMatches === 0) return;
        
        this.currentMatchIndex = (this.currentMatchIndex + 1) % this.totalMatches;
        this.highlightCurrentMatch();
        this.updateCodeSearchCount();
    }

    /**
     * Navigate to previous match
     */
    navigateToPrevMatch() {
        if (this.totalMatches === 0) return;
        
        this.currentMatchIndex = (this.currentMatchIndex - 1 + this.totalMatches) % this.totalMatches;
        this.highlightCurrentMatch();
        this.updateCodeSearchCount();
    }

    /**
     * Highlight current match and scroll to it
     */
    highlightCurrentMatch() {
        if (this.totalMatches === 0) return;

        // Remove previous selection highlight
        this.codeSearchMarkers.forEach((match, index) => {
            match.marker.clear();
            
            const className = index === this.currentMatchIndex 
                ? 'cm-search-match-selected' 
                : 'cm-search-match';
            
            match.marker = this.codeEditor.markText(match.from, match.to, {
                className: className,
                clearWhenEmpty: false
            });
        });

        // Scroll to current match
        const currentMatch = this.codeSearchMarkers[this.currentMatchIndex];
        if (currentMatch) {
            this.codeEditor.scrollIntoView(currentMatch.from, 100);
            this.codeEditor.setCursor(currentMatch.from);
        }
    }

    /**
     * Update code search count display
     */
    updateCodeSearchCount() {
        if (this.codeSearchCount) {
            if (this.totalMatches > 0) {
                this.codeSearchCount.textContent = `${this.currentMatchIndex + 1} of ${this.totalMatches}`;
            } else {
                this.codeSearchCount.textContent = 'No matches';
            }
        }

        // Update button states
        if (this.codeSearchPrev && this.codeSearchNext) {
            const hasMatches = this.totalMatches > 0;
            this.codeSearchPrev.disabled = !hasMatches;
            this.codeSearchNext.disabled = !hasMatches;
        }
    }

    /**
     * Clear code search markers
     */
    clearCodeMarkers() {
        this.codeSearchMarkers.forEach(match => {
            match.marker.clear();
        });
        this.codeSearchMarkers = [];
        this.currentMatchIndex = 0;
        this.totalMatches = 0;
    }

    /**
     * Clear code search
     */
    clearCodeSearch() {
        if (this.codeSearchInput) {
            this.codeSearchInput.value = '';
        }
        
        this.codeSearchClear?.classList.add('hidden');
        this.codeSearchNav?.classList.add('hidden');
        this.clearCodeMarkers();

        console.log('[SearchFunctionality] Code search cleared');
    }

    /**
     * Focus search input based on active tab
     */
    focusSearch(tab) {
        if (tab === 'visual' && this.visualSearchInput) {
            setTimeout(() => this.visualSearchInput.focus(), 100);
        } else if (tab === 'code' && this.codeSearchInput) {
            setTimeout(() => this.codeSearchInput.focus(), 100);
        }
    }
}

// Export for use in editor-js.php
if (typeof window !== 'undefined') {
    window.SearchFunctionality = SearchFunctionality;
    console.log('✅ SearchFunctionality library loaded');
}
