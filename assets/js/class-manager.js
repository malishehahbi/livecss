/**
 * LiveCSS Class Manager - Gutenberg Integration
 */

(function() {
    'use strict';

    // Wait for WordPress to be ready
    function waitForWP(callback) {
        if (typeof window.wp !== 'undefined' && window.wp.element && window.wp.components) {
            callback();
        } else {
            setTimeout(function() { waitForWP(callback); }, 100);
        }
    }

    function initClassManager() {
        const { wp } = window;
        
        if (!wp || !wp.element || !wp.components) {
            console.log('LiveCSS: WordPress components not ready');
            return;
        }

        const { 
            addFilter
        } = wp.hooks;
        const { 
            createElement: el, 
            Fragment, 
            useState, 
            useEffect 
        } = wp.element;
        const { 
            InspectorControls,
            InspectorAdvancedControls 
        } = wp.blockEditor || wp.editor;
        const { 
            PanelBody,
            BaseControl,
            Button,
            Modal,
            TextControl,
            TextareaControl,
            Notice
        } = wp.components;
        const { 
            useSelect 
        } = wp.data;
        const { 
            __ 
        } = wp.i18n;

        // Extract classes from actual page content being edited
        const extractPageClasses = () => {
            const classSet = new Set();
            
            // Method 1: Get from WordPress post content
            if (wp.data && wp.data.select('core/editor')) {
                try {
                    const currentPost = wp.data.select('core/editor').getCurrentPost();
                    if (currentPost && currentPost.content) {
                        // Extract classes from HTML content using regex
                        const classMatches = currentPost.content.match(/class=["']([^"']*)["']/g);
                        if (classMatches) {
                            classMatches.forEach(match => {
                                const classString = match.replace(/class=["']([^"']*)["']/, '$1');
                                const classes = classString.split(/\s+/);
                                classes.forEach(cls => {
                                    if (cls.trim() && 
                                        !cls.startsWith('wp-') && 
                                        !cls.startsWith('block-') &&
                                        !cls.startsWith('editor-') &&
                                        !cls.startsWith('components-') &&
                                        !cls.startsWith('is-') &&
                                        !cls.startsWith('has-') &&
                                        cls.length > 1) {
                                        classSet.add(cls.trim());
                                    }
                                });
                            });
                        }
                    }
                } catch (error) {
                    console.log('Could not extract from post content:', error);
                }
            }
            
            // Method 2: Get from blocks data
            if (wp.data && wp.data.select('core/block-editor')) {
                try {
                    const blocks = wp.data.select('core/block-editor').getBlocks();
                    const extractFromBlocks = (blockList) => {
                        blockList.forEach(block => {
                            // Check block attributes for className
                            if (block.attributes && block.attributes.className) {
                                const classes = block.attributes.className.split(/\s+/);
                                classes.forEach(cls => {
                                    if (cls.trim() && cls.length > 1) {
                                        classSet.add(cls.trim());
                                    }
                                });
                            }
                            
                            // Recursively check inner blocks
                            if (block.innerBlocks && block.innerBlocks.length > 0) {
                                extractFromBlocks(block.innerBlocks);
                            }
                        });
                    };
                    
                    extractFromBlocks(blocks);
                } catch (error) {
                    console.log('Could not extract from blocks:', error);
                }
            }
            
            // Method 3: Fallback - scan visible content elements (but filter better)
            try {
                const contentArea = document.querySelector('.block-editor-block-list__layout');
                if (contentArea) {
                    const contentElements = contentArea.querySelectorAll('[class]');
                    contentElements.forEach(element => {
                        // Only get classes from actual content, not editor UI
                        if (!element.closest('.block-editor-block-contextual-toolbar') &&
                            !element.closest('.block-editor-inserter') &&
                            !element.closest('.components-popover')) {
                            
                            const classes = element.className.split(/\s+/);
                            classes.forEach(cls => {
                                if (cls.trim() && 
                                    !cls.startsWith('wp-') && 
                                    !cls.startsWith('block-') &&
                                    !cls.startsWith('editor-') &&
                                    !cls.startsWith('components-') &&
                                    !cls.startsWith('is-') &&
                                    !cls.startsWith('has-') &&
                                    cls.length > 1) {
                                    classSet.add(cls.trim());
                                }
                            });
                        }
                    });
                }
            } catch (error) {
                console.log('Could not extract from DOM:', error);
            }
            
            // Convert to array and return
            const classArray = Array.from(classSet);
            console.log('Extracted classes from page:', classArray);
            
            return classArray.map((cls, index) => ({
                id: 'extracted_' + index,
                name: cls
            }));
        };

        // Get plugin data
        const pluginData = window.livecssClassManager || {
            restUrl: '/wp-json/livecss/v1/',
            nonce: '',
            cssClasses: [],
            userSettings: {}
        };
        
        // Get extracted classes and user-defined classes only (no defaults)
        const extractedClasses = extractPageClasses();
        const userClasses = pluginData.cssClasses || [];
        const allClasses = [...extractedClasses, ...userClasses];

        // Simple store with error handling
        let cssClasses = [];
        let subscribers = [];
        
        // Initialize classes safely
        try {
            cssClasses = allClasses || [];
        } catch (error) {
            console.error('Error initializing classes:', error);
            cssClasses = [];
        }

        const store = {
            getClasses: () => cssClasses,
            addClass: (classData) => {
                try {
                    cssClasses.push(classData);
                    store.notify();
                } catch (error) {
                    console.error('Error adding class:', error);
                }
            },
            deleteClass: (id) => {
                try {
                    cssClasses = cssClasses.filter(c => c.id !== id);
                    store.notify();
                } catch (error) {
                    console.error('Error deleting class:', error);
                }
            },
            refreshClasses: () => {
                try {
                    const extractedClasses = extractPageClasses();
                    const userClasses = cssClasses.filter(cls => !cls.id.startsWith('extracted_'));
                    cssClasses = [...extractedClasses, ...userClasses];
                    store.notify();
                } catch (error) {
                    console.error('Error refreshing classes:', error);
                }
            },
            subscribe: (callback) => {
                subscribers.push(callback);
                return () => {
                    subscribers = subscribers.filter(sub => sub !== callback);
                };
            },
            notify: () => {
                try {
                    subscribers.forEach(callback => callback());
                } catch (error) {
                    console.error('Error notifying subscribers:', error);
                }
            }
        };

        // API helper
        const api = {
            post: async (endpoint, data) => {
                try {
                    const response = await fetch(pluginData.restUrl + endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': pluginData.nonce
                        },
                        body: JSON.stringify(data)
                    });
                    return await response.json();
                } catch (error) {
                    console.error('API Error:', error);
                    return { error: error.message };
                }
            }
        };

        // Unified CSS Class Control with pills inside search bar
        const CSSClassControl = ({ className, onChange }) => {
            const [inputValue, setInputValue] = useState('');
            const [isModalOpen, setIsModalOpen] = useState(false);
            const [selectedClasses, setSelectedClasses] = useState([]);
            const [classes, setClasses] = useState(store.getClasses());
            const [showSuggestions, setShowSuggestions] = useState(false);
            const [focusedSuggestion, setFocusedSuggestion] = useState(-1);

            // Subscribe to store changes
            useEffect(() => {
                const unsubscribe = store.subscribe(() => {
                    setClasses(store.getClasses());
                });
                return unsubscribe;
            }, []);

            // Parse current className
            useEffect(() => {
                if (className) {
                    const classArray = className.split(' ')
                        .filter(cls => cls.trim().length > 0)
                        .map(cls => cls.trim());
                    setSelectedClasses(classArray);
                } else {
                    setSelectedClasses([]);
                }
            }, [className]);

            // Filter suggestions based on input
            const suggestions = classes.filter(cls => 
                inputValue.trim() && 
                cls.name.toLowerCase().includes(inputValue.toLowerCase()) &&
                !selectedClasses.includes(cls.name)
            ).slice(0, 5); // Limit to 5 suggestions

            const handleRemoveClass = (classToRemove, e) => {
                e?.stopPropagation();
                const newClasses = selectedClasses.filter(cls => cls !== classToRemove);
                setSelectedClasses(newClasses);
                onChange(newClasses.join(' '));
            };

            const handleAddClass = (classToAdd) => {
                const trimmedClass = classToAdd.trim();
                if (trimmedClass && !selectedClasses.includes(trimmedClass)) {
                    const newClasses = [...selectedClasses, trimmedClass];
                    setSelectedClasses(newClasses);
                    onChange(newClasses.join(' '));
                    
                    // Add to store if it doesn't exist
                    const exists = classes.find(cls => cls.name === trimmedClass);
                    if (!exists) {
                        const classData = {
                            id: 'new_' + Date.now(),
                            name: trimmedClass
                        };
                        store.addClass(classData);
                    }
                }
                setInputValue('');
                setShowSuggestions(false);
                setFocusedSuggestion(-1);
            };

            const handleKeyDown = (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (focusedSuggestion >= 0 && suggestions[focusedSuggestion]) {
                        handleAddClass(suggestions[focusedSuggestion].name);
                    } else if (inputValue.trim()) {
                        handleAddClass(inputValue);
                    }
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    setFocusedSuggestion(prev => 
                        prev < suggestions.length - 1 ? prev + 1 : prev
                    );
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    setFocusedSuggestion(prev => prev > 0 ? prev - 1 : -1);
                } else if (e.key === 'Escape') {
                    setShowSuggestions(false);
                    setFocusedSuggestion(-1);
                } else if (e.key === 'Backspace' && inputValue === '' && selectedClasses.length > 0) {
                    // Remove last class when backspacing on empty input
                    handleRemoveClass(selectedClasses[selectedClasses.length - 1]);
                }
            };

            const handleInputChange = (value) => {
                setInputValue(value);
                setShowSuggestions(value.trim().length > 0);
                setFocusedSuggestion(-1);
            };

            const handleInputFocus = () => {
                if (inputValue.trim()) {
                    setShowSuggestions(true);
                }
            };

            const handleInputBlur = () => {
                // Delay hiding suggestions to allow clicking on them
                setTimeout(() => {
                    setShowSuggestions(false);
                    setFocusedSuggestion(-1);
                }, 150);
            };

            return el(Fragment, {},
                el(BaseControl, {
                    label: __('CSS Classes', 'livecss')
                },
                    // Unified search bar with pills inside
                    el('div', {
                        style: {
                            position: 'relative'
                        }
                    },
                        // Main input container with pills
                        el('div', {
                            style: {
                                display: 'flex',
                                flexWrap: 'wrap',
                                alignItems: 'center',
                                gap: '4px',
                                padding: '6px 8px',
                                border: '1px solid #ddd',
                                borderRadius: '6px',
                                backgroundColor: '#fff',
                                minHeight: '38px',
                                cursor: 'text'
                            },
                            onClick: () => {
                                // Focus the input when clicking anywhere in the container
                                const input = document.querySelector('.livecss-unified-input');
                                if (input) input.focus();
                            }
                        },
                            // Selected class pills
                            selectedClasses.map(cls => 
                                el('span', {
                                    key: cls,
                                    style: {
                                        display: 'inline-flex',
                                        alignItems: 'center',
                                        padding: '3px 8px',
                                        backgroundColor: '#0073aa',
                                        color: 'white',
                                        borderRadius: '12px',
                                        fontSize: '12px',
                                        fontWeight: '500',
                                        gap: '4px',
                                        margin: '1px'
                                    }
                                },
                                    cls,
                                    el('button', {
                                        onClick: (e) => handleRemoveClass(cls, e),
                                        style: {
                                            background: 'none',
                                            border: 'none',
                                            color: 'white',
                                            cursor: 'pointer',
                                            padding: '0',
                                            fontSize: '14px',
                                            lineHeight: '1',
                                            width: '14px',
                                            height: '14px',
                                            display: 'flex',
                                            alignItems: 'center',
                                            justifyContent: 'center',
                                            borderRadius: '50%'
                                        },
                                        onMouseOver: (e) => {
                                            e.target.style.backgroundColor = 'rgba(255,255,255,0.2)';
                                        },
                                        onMouseOut: (e) => {
                                            e.target.style.backgroundColor = 'transparent';
                                        }
                                    }, '×')
                                )
                            ),
                            
                            // Input field
                            el('input', {
                                className: 'livecss-unified-input',
                                type: 'text',
                                value: inputValue,
                                onChange: (e) => handleInputChange(e.target.value),
                                onKeyDown: handleKeyDown,
                                onFocus: handleInputFocus,
                                onBlur: handleInputBlur,
                                placeholder: selectedClasses.length === 0 ? __('Add or search CSS classes...', 'livecss') : '',
                                style: {
                                    border: 'none',
                                    outline: 'none',
                                    background: 'transparent',
                                    flex: '1',
                                    minWidth: '120px',
                                    fontSize: '14px',
                                    padding: '4px 0'
                                }
                            })
                        ),
                        
                        // Suggestions dropdown
                        showSuggestions && suggestions.length > 0 && el('div', {
                            style: {
                                position: 'absolute',
                                top: '100%',
                                left: '0',
                                right: '0',
                                backgroundColor: '#fff',
                                border: '1px solid #ddd',
                                borderTop: 'none',
                                borderRadius: '0 0 6px 6px',
                                boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
                                zIndex: 1000,
                                maxHeight: '150px',
                                overflowY: 'auto'
                            }
                        },
                            suggestions.map((cls, index) => 
                                el('div', {
                                    key: cls.id,
                                    onClick: () => handleAddClass(cls.name),
                                    style: {
                                        padding: '8px 12px',
                                        cursor: 'pointer',
                                        fontSize: '13px',
                                        backgroundColor: index === focusedSuggestion ? '#e7f3ff' : 'transparent',
                                        borderBottom: index < suggestions.length - 1 ? '1px solid #f0f0f0' : 'none',
                                        color: index === focusedSuggestion ? '#0073aa' : '#333'
                                    },
                                    onMouseEnter: () => setFocusedSuggestion(index),
                                    onMouseLeave: () => setFocusedSuggestion(-1)
                                }, cls.name)
                            ),
                            
                            // "Create new" option if no exact match
                            inputValue.trim() && !suggestions.find(s => s.name.toLowerCase() === inputValue.toLowerCase()) && 
                            el('div', {
                                onClick: () => handleAddClass(inputValue),
                                style: {
                                    padding: '8px 12px',
                                    cursor: 'pointer',
                                    fontSize: '13px',
                                    backgroundColor: '#f8f9fa',
                                    color: '#0073aa',
                                    borderTop: '1px solid #e0e0e0',
                                    fontWeight: '500'
                                }
                            }, __('Create "' + inputValue + '"', 'livecss'))
                        )
                    ),
                    
                    // Action buttons
                    el('div', { 
                        style: { 
                            marginTop: '8px',
                            display: 'flex',
                            gap: '8px',
                            alignItems: 'center'
                        } 
                    },
                        el(Button, {
                            variant: 'secondary',
                            size: 'small',
                            onClick: () => setIsModalOpen(true)
                        }, __('Manage Classes', 'livecss')),
                        
                        el(Button, {
                            variant: 'tertiary',
                            size: 'small',
                            onClick: () => {
                                store.refreshClasses();
                                console.log('Classes refreshed from page');
                            },
                            title: __('Refresh classes from page content', 'livecss')
                        }, __('🔄 Refresh', 'livecss'))
                    )
                ),

                // Modal
                isModalOpen && el(Modal, {
                    title: __('CSS Class Manager', 'livecss'),
                    onRequestClose: () => setIsModalOpen(false)
                },
                    el(ClassManagerContent, {
                        onClose: () => setIsModalOpen(false)
                    })
                )
            );
        };

        // Class Manager Modal Content - Simplified
        const ClassManagerContent = ({ onClose }) => {
            const [newClassName, setNewClassName] = useState('');
            const [editingClass, setEditingClass] = useState(null);
            const [isLoading, setIsLoading] = useState(false);
            const [notice, setNotice] = useState(null);
            const [classes, setClasses] = useState(store.getClasses());

            useEffect(() => {
                const unsubscribe = store.subscribe(() => {
                    setClasses(store.getClasses());
                });
                return unsubscribe;
            }, []);

            const showNotice = (message, type = 'success') => {
                setNotice({ message, type });
                setTimeout(() => setNotice(null), 3000);
            };

            const handleAddClass = () => {
                if (!newClassName.trim()) {
                    showNotice(__('Class name is required', 'livecss'), 'error');
                    return;
                }

                const exists = classes.find(cls => cls.name === newClassName.trim());
                if (exists) {
                    showNotice(__('Class already exists', 'livecss'), 'error');
                    return;
                }

                const classData = {
                    id: 'class_' + Date.now(),
                    name: newClassName.trim()
                };

                store.addClass(classData);
                setNewClassName('');
                showNotice(__('Class added successfully', 'livecss'));
            };

            const handleDeleteClass = (classId, className) => {
                if (confirm(__('Delete class "' + className + '"?', 'livecss'))) {
                    store.deleteClass(classId);
                    showNotice(__('Class deleted successfully', 'livecss'));
                }
            };

            const handleRenameClass = (classId, newName) => {
                if (!newName.trim()) return;
                
                const exists = classes.find(cls => cls.name === newName.trim() && cls.id !== classId);
                if (exists) {
                    showNotice(__('Class name already exists', 'livecss'), 'error');
                    return;
                }

                const classIndex = classes.findIndex(cls => cls.id === classId);
                if (classIndex !== -1) {
                    classes[classIndex].name = newName.trim();
                    store.notify();
                    setEditingClass(null);
                    showNotice(__('Class renamed successfully', 'livecss'));
                }
            };

            return el(Fragment, {},
                notice && el(Notice, {
                    status: notice.type,
                    onRemove: () => setNotice(null),
                    isDismissible: true
                }, notice.message),

                el(PanelBody, {
                    title: __('Add New Class', 'livecss'),
                    initialOpen: true
                },
                    el('div', {
                        style: { display: 'flex', gap: '8px' }
                    },
                        el(TextControl, {
                            value: newClassName,
                            onChange: setNewClassName,
                            placeholder: __('Enter class name...', 'livecss'),
                            onKeyPress: (e) => e.key === 'Enter' && handleAddClass()
                        }),
                        el(Button, {
                            variant: 'primary',
                            onClick: handleAddClass,
                            disabled: !newClassName.trim()
                        }, __('Add', 'livecss'))
                    )
                ),

                el(PanelBody, {
                    title: __('Manage Classes (' + classes.filter(cls => !cls.id.startsWith('extracted_')).length + ')', 'livecss'),
                    initialOpen: true
                },
                    classes.filter(cls => !cls.id.startsWith('extracted_')).length === 0 ? 
                        el('p', {}, __('No custom classes yet.', 'livecss')) :
                        classes.filter(cls => !cls.id.startsWith('extracted_')).map(cls => 
                            el('div', {
                                key: cls.id,
                                style: {
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'space-between',
                                    padding: '8px 12px',
                                    border: '1px solid #ddd',
                                    borderRadius: '4px',
                                    marginBottom: '6px',
                                    backgroundColor: '#fafafa'
                                }
                            },
                                editingClass === cls.id ? 
                                    el('input', {
                                        type: 'text',
                                        defaultValue: cls.name,
                                        autoFocus: true,
                                        onBlur: (e) => handleRenameClass(cls.id, e.target.value),
                                        onKeyPress: (e) => {
                                            if (e.key === 'Enter') {
                                                handleRenameClass(cls.id, e.target.value);
                                            }
                                        },
                                        style: {
                                            border: '1px solid #0073aa',
                                            borderRadius: '3px',
                                            padding: '4px 6px',
                                            fontSize: '14px'
                                        }
                                    }) :
                                    el('span', {
                                        onClick: () => setEditingClass(cls.id),
                                        style: {
                                            cursor: 'pointer',
                                            fontSize: '14px',
                                            fontWeight: '500'
                                        }
                                    }, cls.name),
                                
                                el('div', {
                                    style: { display: 'flex', gap: '4px' }
                                },
                                    el(Button, {
                                        size: 'small',
                                        variant: 'secondary',
                                        onClick: () => setEditingClass(editingClass === cls.id ? null : cls.id)
                                    }, editingClass === cls.id ? __('Cancel', 'livecss') : __('Rename', 'livecss')),
                                    
                                    el(Button, {
                                        size: 'small',
                                        isDestructive: true,
                                        onClick: () => handleDeleteClass(cls.id, cls.name)
                                    }, __('Delete', 'livecss'))
                                )
                            )
                        )
                ),

                el(PanelBody, {
                    title: __('Page Classes (' + classes.filter(cls => cls.id.startsWith('extracted_')).length + ')', 'livecss'),
                    initialOpen: false
                },
                    el('p', {
                        style: { fontSize: '13px', color: '#666', marginBottom: '12px' }
                    }, __('Classes found on the current page:', 'livecss')),
                    
                    classes.filter(cls => cls.id.startsWith('extracted_')).length === 0 ? 
                        el('p', {}, __('No classes extracted from page.', 'livecss')) :
                        el('div', {
                            style: {
                                display: 'flex',
                                flexWrap: 'wrap',
                                gap: '6px'
                            }
                        },
                            classes.filter(cls => cls.id.startsWith('extracted_')).map(cls => 
                                el('span', {
                                    key: cls.id,
                                    style: {
                                        padding: '4px 8px',
                                        backgroundColor: '#e3f2fd',
                                        color: '#1976d2',
                                        borderRadius: '12px',
                                        fontSize: '12px',
                                        fontWeight: '500'
                                    }
                                }, cls.name)
                            )
                        )
                )
            );
        };

        // Body Class Manager
        const BodyClassManager = () => {
            const [bodyClasses, setBodyClasses] = useState('');
            const [isVisible, setIsVisible] = useState(false);

            const selectedBlockId = useSelect((select) => {
                return select('core/block-editor') ? 
                    select('core/block-editor').getSelectedBlockClientId() : null;
            }, []);

            // Show when no block is selected
            useEffect(() => {
                setIsVisible(!selectedBlockId);
            }, [selectedBlockId]);

            const handleSave = async () => {
                try {
                    const formData = new FormData();
                    formData.append('action', 'livecss_save_body_classes');
                    formData.append('body_classes', bodyClasses);
                    formData.append('nonce', pluginData.nonce);

                    await fetch(pluginData.restUrl + '../wp-admin/admin-ajax.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    // Show success feedback
                    console.log('Body classes saved:', bodyClasses);
                } catch (error) {
                    console.error('Error saving body classes:', error);
                }
            };

            if (!isVisible) return null;

            return el('div', {
                id: 'livecss-body-class-manager',
                style: {
                    position: 'fixed',
                    top: '100px',
                    right: '20px',
                    background: '#fff',
                    border: '1px solid #ddd',
                    borderRadius: '4px',
                    padding: '16px',
                    boxShadow: '0 2px 10px rgba(0,0,0,0.1)',
                    zIndex: 9999,
                    minWidth: '280px'
                }
            },
                el('h4', { style: { margin: '0 0 12px 0' } }, 
                    __('Body CSS Classes', 'livecss')
                ),
                el(TextControl, {
                    value: bodyClasses,
                    onChange: setBodyClasses,
                    placeholder: __('e.g., custom-theme dark-mode', 'livecss')
                }),
                el(Button, {
                    variant: 'primary',
                    onClick: handleSave,
                    style: { marginTop: '8px' }
                }, __('Apply to Body', 'livecss'))
            );
        };

        // Enhance blocks
        const enhanceBlockEdit = (BlockEdit) => {
            return (props) => {
                const { attributes, setAttributes } = props;
                const { className } = attributes;

                const cssClassControl = el(CSSClassControl, {
                    className: className || '',
                    onChange: (newClassName) => {
                        setAttributes({ 
                            className: newClassName || undefined 
                        });
                    }
                });

                return el(Fragment, {},
                    el(BlockEdit, props),
                    el(InspectorAdvancedControls, {}, cssClassControl)
                );
            };
        };

        // Initialize with better error handling
        try {
            // Verify required WordPress APIs
            if (!addFilter || !wp.element || !wp.components) {
                throw new Error('Required WordPress APIs not available');
            }

            // Add block enhancement
            addFilter(
                'editor.BlockEdit',
                'livecss/enhance-css-classes',
                enhanceBlockEdit
            );

            // Add body class manager
            if (wp.plugins && wp.plugins.registerPlugin) {
                wp.plugins.registerPlugin('livecss-body-class-manager', {
                    render: BodyClassManager
                });
            }

            console.log('LiveCSS Class Manager initialized successfully');
            console.log('Available classes:', cssClasses.length);
            
            // Auto-refresh classes every 5 seconds to catch changes
            setInterval(() => {
                store.refreshClasses();
            }, 5000);
            
        } catch (error) {
            console.error('LiveCSS Class Manager initialization error:', error);
        }
    }

    // Start initialization
    waitForWP(initClassManager);

    // Admin page functionality
    window.LiveCSSClassManagerAdmin = {
        render: (container) => {
            container.innerHTML = `
                <div style="padding: 20px; background: #f9f9f9; border-radius: 4px; margin-top: 20px;">
                    <h3>✅ CSS Class Manager Active</h3>
                    <p>The CSS Class Manager is now working with modern features!</p>
                    <p><strong>New Unified Interface:</strong></p>
                    <ul>
                        <li>🎯 <strong>Auto-extract classes</strong> from actual page content</li>
                        <li>� <strong>Unified search bar</strong> - one clean interface</li>
                        <li>💊 <strong>Pills inside search bar</strong> - modern design</li>
                        <li>➕ <strong>Press Enter to create</strong> classes that don't exist</li>
                        <li>� <strong>Auto-suggestions</strong> while typing</li>
                        <li>✏️ <strong>Rename/Delete</strong> custom classes</li>
                    </ul>
                    <p><strong>How to use:</strong></p>
                    <ol>
                        <li>Edit any post or page</li>
                        <li>Select a block</li>
                        <li>In "CSS Classes": Type class name in the unified search bar</li>
                        <li>See suggestions appear or press Enter to create new class</li>
                        <li>Selected classes appear as pills inside the search bar</li>
                        <li>Click × on pills to remove, or backspace when input is empty</li>
                    </ol>
                    <p><strong>Body Classes:</strong> When no block is selected, you'll see a floating panel to add classes to the body element.</p>
                </div>
            `;
        }
    };

})();