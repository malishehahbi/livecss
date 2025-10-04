/**
 * CSS Property Dependency Manager for LiveCSS Editor
 * Handles conditional show/hide of CSS properties based on parent property values
 * 
 * @version 1.0.0
 * @author LiveCSS Team
 */

class PropertyDependencyManager {
    constructor() {
        this.dependencies = new Map();
        this.propertyCache = new Map();
        this.initialized = false;
    }

    /**
     * Initialize the dependency manager
     */
    init() {
        if (this.initialized) return;
        
        console.log('[PropertyDependencies] Initializing...');
        
        // Find all elements with dependencies
        const dependentElements = document.querySelectorAll('[data-depends-on]');
        console.log(`[PropertyDependencies] Found ${dependentElements.length} dependent elements`);
        
        dependentElements.forEach(element => {
            const parentProp = element.getAttribute('data-depends-on');
            const dependsValue = element.getAttribute('data-depends-value');
            
            // Store dependency info
            if (!this.dependencies.has(parentProp)) {
                this.dependencies.set(parentProp, []);
            }
            
            this.dependencies.get(parentProp).push({
                element: element,
                config: this.parseValues(dependsValue)
            });
        });

        // Attach listeners to all parent properties
        this.attachListeners();
        
        // Initial evaluation
        this.evaluateAll();
        
        this.initialized = true;
        console.log('[PropertyDependencies] Initialization complete');
    }

    /**
     * Parse data-depends-value attribute
     * Supports formats:
     * - "value1,value2" - Show when parent equals these values
     * - "!value1,!value2" - Show when parent DOES NOT equal these values
     * - "!" - Show when parent is NOT empty
     * - "" (empty) - Show when parent IS empty
     */
    parseValues(valueString) {
        if (!valueString) {
            return {
                type: 'empty',
                inverted: false,
                values: []
            };
        }

        const values = valueString.split(',').map(v => v.trim());
        const hasInverted = values.some(v => v.startsWith('!'));
        
        // Check for "not empty" condition
        if (valueString === '!') {
            return {
                type: 'not-empty',
                inverted: false,
                values: []
            };
        }

        // Check for "empty" condition
        if (valueString === '') {
            return {
                type: 'empty',
                inverted: false,
                values: []
            };
        }

        return {
            type: 'value-match',
            inverted: hasInverted,
            values: values.map(v => v.replace(/^!/, ''))
        };
    }

    /**
     * Attach event listeners to parent properties
     */
    attachListeners() {
        this.dependencies.forEach((dependents, parentProp) => {
            const parentElements = document.querySelectorAll(`[data-property="${parentProp}"]`);
            
            if (parentElements.length === 0) {
                console.warn(`[PropertyDependencies] Parent property "${parentProp}" not found`);
                return;
            }

            parentElements.forEach(parentEl => {
                // Cache the element for faster lookups
                this.propertyCache.set(parentProp, parentEl);

                // Listen to multiple event types for comprehensive coverage
                ['change', 'input', 'blur'].forEach(eventType => {
                    parentEl.addEventListener(eventType, () => {
                        this.evaluate(parentProp);
                    }, { passive: true });
                });
            });
        });
    }

    /**
     * Get current value of a property
     */
    getPropertyValue(propertyName) {
        // Try cache first
        let element = this.propertyCache.get(propertyName);
        
        // If not cached, query it
        if (!element) {
            element = document.querySelector(`[data-property="${propertyName}"]`);
            if (element) {
                this.propertyCache.set(propertyName, element);
            }
        }

        if (!element) return '';

        // Get value based on element type
        if (element.type === 'checkbox') {
            return element.checked ? 'checked' : '';
        }
        
        return element.value || '';
    }

    /**
     * Evaluate dependencies for a specific parent property
     */
    evaluate(parentProp) {
        const dependents = this.dependencies.get(parentProp);
        if (!dependents) return;

        const currentValue = this.getPropertyValue(parentProp);

        // Evaluate each dependent
        dependents.forEach(({ element, config }) => {
            const shouldShow = this.shouldShow(currentValue, config);
            this.toggleVisibility(element, shouldShow);
        });
    }

    /**
     * Determine if an element should be shown based on parent value and config
     */
    shouldShow(currentValue, config) {
        const trimmedValue = String(currentValue).trim();

        switch (config.type) {
            case 'empty':
                return !trimmedValue;

            case 'not-empty':
                return !!trimmedValue;

            case 'value-match':
                const matches = config.values.some(val => {
                    // Support wildcard matching
                    if (val === '*') return !!trimmedValue;
                    return val === trimmedValue;
                });
                return config.inverted ? !matches : matches;

            default:
                return true;
        }
    }

    /**
     * Toggle visibility of an element
     */
    toggleVisibility(element, show) {
        if (show) {
            element.style.display = '';
            element.classList.remove('property-hidden');
            element.classList.add('property-visible');
        } else {
            element.style.display = 'none';
            element.classList.add('property-hidden');
            element.classList.remove('property-visible');
        }
    }

    /**
     * Evaluate all dependencies
     */
    evaluateAll() {
        this.dependencies.forEach((_, parentProp) => {
            this.evaluate(parentProp);
        });
    }

    /**
     * Manually refresh all dependencies
     */
    refresh() {
        console.log('[PropertyDependencies] Manual refresh triggered');
        this.evaluateAll();
    }

    /**
     * Add a new dependency dynamically
     */
    addDependency(element, parentProp, dependsValue) {
        const config = this.parseValues(dependsValue);
        
        if (!this.dependencies.has(parentProp)) {
            this.dependencies.set(parentProp, []);
        }
        
        this.dependencies.get(parentProp).push({
            element: element,
            config: config
        });

        // Attach listener to parent if not already attached
        if (!this.propertyCache.has(parentProp)) {
            const parentElement = document.querySelector(`[data-property="${parentProp}"]`);
            if (parentElement) {
                this.propertyCache.set(parentProp, parentElement);
                ['change', 'input', 'blur'].forEach(eventType => {
                    parentElement.addEventListener(eventType, () => {
                        this.evaluate(parentProp);
                    }, { passive: true });
                });
            }
        }

        // Evaluate immediately
        this.evaluate(parentProp);
    }

    /**
     * Get statistics about dependencies
     */
    getStats() {
        const totalDependencies = Array.from(this.dependencies.values())
            .reduce((sum, deps) => sum + deps.length, 0);

        return {
            parentProperties: this.dependencies.size,
            totalDependencies: totalDependencies,
            cachedProperties: this.propertyCache.size
        };
    }
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPropertyDependencies);
} else {
    // DOM is already ready
    initPropertyDependencies();
}

function initPropertyDependencies() {
    // Wait a bit to ensure all other scripts have initialized
    setTimeout(() => {
        window.propertyDependencyManager = new PropertyDependencyManager();
        window.propertyDependencyManager.init();

        // Log stats
        const stats = window.propertyDependencyManager.getStats();
        console.log('[PropertyDependencies] Stats:', stats);
    }, 100);
}

// Expose global functions for manual control
window.refreshPropertyDependencies = () => {
    if (window.propertyDependencyManager) {
        window.propertyDependencyManager.refresh();
    }
};

window.getPropertyDependencyStats = () => {
    if (window.propertyDependencyManager) {
        return window.propertyDependencyManager.getStats();
    }
    return null;
};

// Export class for loading detection
window.PropertyDependencies = PropertyDependencyManager;
console.log('✅ PropertyDependencies library loaded');
