/**
 * Visual Editor Schema — JSON-driven definition of all CSS property controls.
 *
 * This replaces ~1,130 lines of hardcoded HTML accordion sections with a
 * compact, maintainable data structure. To add a new CSS property, just
 * append an entry to the relevant section's `controls` array.
 *
 * Control types:
 *   "text"   → <input type="text">
 *   "color"  → <input type="color">
 *   "select" → <select> with options: [[value, label], ...]
 *   "range"  → <input type="range"> with min/max/step
 *
 * Dependencies (optional):
 *   dependsOn:    CSS property name to watch
 *   dependsValue: Comma-separated allowed values, "!" prefix = "when has any value"
 *
 * @type {Array<{title: string, controls: Array}>}
 */
const VISUAL_EDITOR_SCHEMA = [
    // ─── TYPOGRAPHY ──────────────────────────────────────────────
    {
        title: 'Typography',
        controls: [
            { label: 'Font Family', property: 'font-family', type: 'text', placeholder: 'Arial, sans-serif' },
            { label: 'Font Size', property: 'font-size', type: 'text', placeholder: '16px, 1em, 1.2rem' },
            { label: 'Font Weight', property: 'font-weight', type: 'select', options: [
                ['', 'Default'], ['normal', 'Normal'], ['bold', 'Bold'],
                ['100', '100'], ['200', '200'], ['300', '300'], ['400', '400'],
                ['500', '500'], ['600', '600'], ['700', '700'], ['800', '800'], ['900', '900']
            ]},
            { label: 'Font Style', property: 'font-style', type: 'select', options: [
                ['', 'Default'], ['normal', 'Normal'], ['italic', 'Italic'], ['oblique', 'Oblique']
            ]},
            { label: 'Text Color', property: 'color', type: 'color' },
            { label: 'Line Height', property: 'line-height', type: 'text', placeholder: '1.5, 24px' },
            { label: 'Text Align', property: 'text-align', type: 'select', options: [
                ['', 'Default'], ['left', 'Left'], ['center', 'Center'], ['right', 'Right'], ['justify', 'Justify']
            ]},
            { label: 'Text Decoration', property: 'text-decoration', type: 'select', options: [
                ['', 'Default'], ['none', 'None'], ['underline', 'Underline'],
                ['overline', 'Overline'], ['line-through', 'Line Through']
            ]},
            { label: 'Text Transform', property: 'text-transform', type: 'select', options: [
                ['', 'Default'], ['none', 'None'], ['uppercase', 'Uppercase'],
                ['lowercase', 'Lowercase'], ['capitalize', 'Capitalize']
            ]},
            { label: 'Letter Spacing', property: 'letter-spacing', type: 'text', placeholder: 'normal, 2px, 0.1em' },
            { label: 'Word Spacing', property: 'word-spacing', type: 'text', placeholder: 'normal, 2px, 0.1em' },
            { label: 'Text Shadow', property: 'text-shadow', type: 'text', placeholder: '2px 2px 4px rgba(0,0,0,0.5)' },
            { label: 'Text Overflow', property: 'text-overflow', type: 'select', options: [
                ['', 'Default'], ['clip', 'Clip'], ['ellipsis', 'Ellipsis']
            ]},
            { label: 'White Space', property: 'white-space', type: 'select', options: [
                ['', 'Default'], ['normal', 'Normal'], ['nowrap', 'No Wrap'], ['pre', 'Pre'],
                ['pre-wrap', 'Pre Wrap'], ['pre-line', 'Pre Line'], ['break-spaces', 'Break Spaces']
            ]},
            { label: 'Vertical Align', property: 'vertical-align', type: 'select', options: [
                ['', 'Default'], ['baseline', 'Baseline'], ['top', 'Top'], ['middle', 'Middle'],
                ['bottom', 'Bottom'], ['text-top', 'Text Top'], ['text-bottom', 'Text Bottom'],
                ['sub', 'Sub'], ['super', 'Super']
            ]},
            { label: 'Font Variant', property: 'font-variant', type: 'select', options: [
                ['', 'Default'], ['normal', 'Normal'], ['small-caps', 'Small Caps'],
                ['all-small-caps', 'All Small Caps']
            ]},
            { label: 'Text Indent', property: 'text-indent', type: 'text', placeholder: '20px, 2em, 5%' },
            { label: 'Word Break', property: 'word-break', type: 'select', options: [
                ['', 'Default'], ['normal', 'Normal'], ['break-all', 'Break All'],
                ['keep-all', 'Keep All'], ['break-word', 'Break Word']
            ]},
            { label: 'Overflow Wrap', property: 'overflow-wrap', type: 'select', options: [
                ['', 'Default'], ['normal', 'Normal'], ['break-word', 'Break Word'], ['anywhere', 'Anywhere']
            ]},
            { label: 'Hyphens', property: 'hyphens', type: 'select', options: [
                ['', 'Default'], ['none', 'None'], ['manual', 'Manual'], ['auto', 'Auto']
            ]}
        ]
    },

    // ─── BACKGROUND ──────────────────────────────────────────────
    {
        title: 'Background',
        controls: [
            { label: 'Background Color', property: 'background-color', type: 'color' },
            { label: 'Background Image', property: 'background-image', type: 'text', placeholder: "url('image.jpg')" },
            { label: 'Background Gradient', property: 'background-image', type: 'text', placeholder: 'linear-gradient(to right, #ff0000, #0000ff)' },
            { label: 'Background Size', property: 'background-size', type: 'select', dependsOn: 'background-image', dependsValue: '!', options: [
                ['', 'Default'], ['cover', 'Cover'], ['contain', 'Contain'], ['100%', '100%']
            ]},
            { label: 'Background Position', property: 'background-position', type: 'text', dependsOn: 'background-image', dependsValue: '!', placeholder: 'center, 10px 20px' },
            { label: 'Background Repeat', property: 'background-repeat', type: 'select', dependsOn: 'background-image', dependsValue: '!', options: [
                ['', 'Default'], ['no-repeat', 'No Repeat'], ['repeat', 'Repeat'],
                ['repeat-x', 'Repeat X'], ['repeat-y', 'Repeat Y'], ['space', 'Space'], ['round', 'Round']
            ]},
            { label: 'Background Attachment', property: 'background-attachment', type: 'select', dependsOn: 'background-image', dependsValue: '!', options: [
                ['', 'Default'], ['scroll', 'Scroll'], ['fixed', 'Fixed'], ['local', 'Local']
            ]},
            { label: 'Background Clip', property: 'background-clip', type: 'select', dependsOn: 'background-image', dependsValue: '!', options: [
                ['', 'Default'], ['border-box', 'Border Box'], ['padding-box', 'Padding Box'],
                ['content-box', 'Content Box'], ['text', 'Text (Gradient Text)']
            ]},
            { label: 'Background Origin', property: 'background-origin', type: 'select', dependsOn: 'background-image', dependsValue: '!', options: [
                ['', 'Default'], ['border-box', 'Border Box'], ['padding-box', 'Padding Box'],
                ['content-box', 'Content Box']
            ]},
            { label: 'Background Blend Mode', property: 'background-blend-mode', type: 'select', dependsOn: 'background-image', dependsValue: '!', options: [
                ['', 'Default'], ['normal', 'Normal'], ['multiply', 'Multiply'], ['screen', 'Screen'],
                ['overlay', 'Overlay'], ['darken', 'Darken'], ['lighten', 'Lighten'],
                ['color-dodge', 'Color Dodge'], ['color-burn', 'Color Burn'],
                ['difference', 'Difference'], ['exclusion', 'Exclusion'],
                ['hue', 'Hue'], ['saturation', 'Saturation'], ['color', 'Color'], ['luminosity', 'Luminosity']
            ]},
            { label: 'Clip Path', property: 'clip-path', type: 'text', placeholder: 'circle(50%), polygon(0 0, 100% 0, 100% 100%)' }
        ]
    },

    // ─── SIZING ──────────────────────────────────────────────────
    {
        title: 'Sizing',
        controls: [
            { label: 'Width', property: 'width', type: 'text', placeholder: '100px, 50%, auto' },
            { label: 'Height', property: 'height', type: 'text', placeholder: '100px, 50%, auto' },
            { label: 'Min Width', property: 'min-width', type: 'text', placeholder: '100px, 50%' },
            { label: 'Max Width', property: 'max-width', type: 'text', placeholder: '1200px, 100%' },
            { label: 'Min Height', property: 'min-height', type: 'text', placeholder: '100px, 50vh' },
            { label: 'Max Height', property: 'max-height', type: 'text', placeholder: '800px, 100vh' },
            { label: 'Box Sizing', property: 'box-sizing', type: 'select', options: [
                ['', 'Default'], ['content-box', 'Content Box'], ['border-box', 'Border Box']
            ]},
            { label: 'Aspect Ratio', property: 'aspect-ratio', type: 'text', placeholder: '16/9, 1/1, auto' }
        ]
    },

    // ─── LAYOUT ──────────────────────────────────────────────────
    {
        title: 'Layout',
        controls: [
            { label: 'Display', property: 'display', type: 'select', options: [
                ['', 'Default'], ['block', 'Block'], ['inline', 'Inline'], ['inline-block', 'Inline Block'],
                ['flex', 'Flex'], ['inline-flex', 'Inline Flex'], ['grid', 'Grid'],
                ['inline-grid', 'Inline Grid'], ['none', 'None']
            ]},
            { label: 'Position', property: 'position', type: 'select', options: [
                ['', 'Default'], ['static', 'Static'], ['relative', 'Relative'],
                ['absolute', 'Absolute'], ['fixed', 'Fixed'], ['sticky', 'Sticky']
            ]},
            { label: 'Top', property: 'top', type: 'text', dependsOn: 'position', dependsValue: '!static,!', placeholder: '10px, 1em, 50%' },
            { label: 'Right', property: 'right', type: 'text', dependsOn: 'position', dependsValue: '!static,!', placeholder: '10px, 1em, 50%' },
            { label: 'Bottom', property: 'bottom', type: 'text', dependsOn: 'position', dependsValue: '!static,!', placeholder: '10px, 1em, 50%' },
            { label: 'Left', property: 'left', type: 'text', dependsOn: 'position', dependsValue: '!static,!', placeholder: '10px, 1em, 50%' },
            { label: 'Float', property: 'float', type: 'select', dependsOn: 'display', dependsValue: '!flex,!inline-flex,!grid,!inline-grid', options: [
                ['', 'Default'], ['left', 'Left'], ['right', 'Right'], ['none', 'None']
            ]},
            { label: 'Clear', property: 'clear', type: 'select', dependsOn: 'display', dependsValue: '!flex,!inline-flex,!grid,!inline-grid', options: [
                ['', 'Default'], ['left', 'Left'], ['right', 'Right'], ['both', 'Both'], ['none', 'None']
            ]},
            { label: 'Overflow', property: 'overflow', type: 'select', options: [
                ['', 'Default'], ['visible', 'Visible'], ['hidden', 'Hidden'], ['scroll', 'Scroll'], ['auto', 'Auto']
            ]},
            { label: 'Overflow X', property: 'overflow-x', type: 'select', options: [
                ['', 'Default'], ['visible', 'Visible'], ['hidden', 'Hidden'], ['scroll', 'Scroll'], ['auto', 'Auto']
            ]},
            { label: 'Overflow Y', property: 'overflow-y', type: 'select', options: [
                ['', 'Default'], ['visible', 'Visible'], ['hidden', 'Hidden'], ['scroll', 'Scroll'], ['auto', 'Auto']
            ]},
            { label: 'Z-Index', property: 'z-index', type: 'text', dependsOn: 'position', dependsValue: '!static,!', placeholder: '1, 10, 999' },
            { label: 'Flex Direction', property: 'flex-direction', type: 'select', dependsOn: 'display', dependsValue: 'flex,inline-flex', options: [
                ['', 'Default'], ['row', 'Row'], ['row-reverse', 'Row Reverse'],
                ['column', 'Column'], ['column-reverse', 'Column Reverse']
            ]},
            { label: 'Flex Wrap', property: 'flex-wrap', type: 'select', dependsOn: 'display', dependsValue: 'flex,inline-flex', options: [
                ['', 'Default'], ['nowrap', 'No Wrap'], ['wrap', 'Wrap'], ['wrap-reverse', 'Wrap Reverse']
            ]},
            { label: 'Justify Content', property: 'justify-content', type: 'select', dependsOn: 'display', dependsValue: 'flex,inline-flex,grid,inline-grid', options: [
                ['', 'Default'], ['flex-start', 'Flex Start'], ['flex-end', 'Flex End'],
                ['center', 'Center'], ['space-between', 'Space Between'],
                ['space-around', 'Space Around'], ['space-evenly', 'Space Evenly']
            ]},
            { label: 'Align Items', property: 'align-items', type: 'select', dependsOn: 'display', dependsValue: 'flex,inline-flex,grid,inline-grid', options: [
                ['', 'Default'], ['flex-start', 'Flex Start'], ['flex-end', 'Flex End'],
                ['center', 'Center'], ['baseline', 'Baseline'], ['stretch', 'Stretch']
            ]},
            { label: 'Align Content', property: 'align-content', type: 'select', dependsOn: 'display', dependsValue: 'flex,inline-flex,grid,inline-grid', options: [
                ['', 'Default'], ['flex-start', 'Flex Start'], ['flex-end', 'Flex End'],
                ['center', 'Center'], ['space-between', 'Space Between'],
                ['space-around', 'Space Around'], ['stretch', 'Stretch']
            ]},
            { label: 'Align Self', property: 'align-self', type: 'select', dependsOn: 'display', dependsValue: 'flex,inline-flex,grid,inline-grid', options: [
                ['', 'Default'], ['auto', 'Auto'], ['flex-start', 'Flex Start'], ['flex-end', 'Flex End'],
                ['center', 'Center'], ['baseline', 'Baseline'], ['stretch', 'Stretch']
            ]},
            { label: 'Order', property: 'order', type: 'text', dependsOn: 'display', dependsValue: 'flex,inline-flex,grid,inline-grid', placeholder: '0, 1, -1' },
            { label: 'Flex Grow', property: 'flex-grow', type: 'text', dependsOn: 'display', dependsValue: 'flex,inline-flex', placeholder: '0, 1, 2' },
            { label: 'Flex Shrink', property: 'flex-shrink', type: 'text', dependsOn: 'display', dependsValue: 'flex,inline-flex', placeholder: '0, 1, 2' },
            { label: 'Flex Basis', property: 'flex-basis', type: 'text', dependsOn: 'display', dependsValue: 'flex,inline-flex', placeholder: 'auto, 100px, 50%' },
            { label: 'Grid Template Columns', property: 'grid-template-columns', type: 'text', dependsOn: 'display', dependsValue: 'grid,inline-grid', placeholder: '1fr 1fr 1fr, repeat(3, 1fr)' },
            { label: 'Grid Template Rows', property: 'grid-template-rows', type: 'text', dependsOn: 'display', dependsValue: 'grid,inline-grid', placeholder: '1fr 1fr 1fr, repeat(3, 1fr)' },
            { label: 'Grid Column', property: 'grid-column', type: 'text', dependsOn: 'display', dependsValue: 'grid,inline-grid', placeholder: '1 / 3, span 2' },
            { label: 'Grid Row', property: 'grid-row', type: 'text', dependsOn: 'display', dependsValue: 'grid,inline-grid', placeholder: '1 / 3, span 2' },
            { label: 'Gap', property: 'gap', type: 'text', dependsOn: 'display', dependsValue: 'flex,inline-flex,grid,inline-grid', placeholder: '10px, 1em' },
            { label: 'Row Gap', property: 'row-gap', type: 'text', dependsOn: 'display', dependsValue: 'flex,inline-flex,grid,inline-grid', placeholder: '10px, 1em' },
            { label: 'Column Gap', property: 'column-gap', type: 'text', dependsOn: 'display', dependsValue: 'flex,inline-flex,grid,inline-grid', placeholder: '10px, 1em' },
            { label: 'Grid Auto Flow', property: 'grid-auto-flow', type: 'select', dependsOn: 'display', dependsValue: 'grid,inline-grid', options: [
                ['', 'Default'], ['row', 'Row'], ['column', 'Column'], ['dense', 'Dense'],
                ['row dense', 'Row Dense'], ['column dense', 'Column Dense']
            ]},
            { label: 'Grid Auto Columns', property: 'grid-auto-columns', type: 'text', dependsOn: 'display', dependsValue: 'grid,inline-grid', placeholder: 'auto, 1fr, 100px' },
            { label: 'Grid Auto Rows', property: 'grid-auto-rows', type: 'text', dependsOn: 'display', dependsValue: 'grid,inline-grid', placeholder: 'auto, 1fr, 100px' },
            { label: 'Visibility', property: 'visibility', type: 'select', options: [
                ['', 'Default'], ['visible', 'Visible'], ['hidden', 'Hidden'], ['collapse', 'Collapse']
            ]},
            { label: 'Cursor', property: 'cursor', type: 'select', options: [
                ['', 'Default'], ['auto', 'Auto'], ['pointer', 'Pointer'], ['default', 'Default'],
                ['text', 'Text'], ['move', 'Move'], ['wait', 'Wait'], ['help', 'Help'],
                ['not-allowed', 'Not Allowed'], ['grab', 'Grab'], ['grabbing', 'Grabbing'],
                ['crosshair', 'Crosshair'], ['zoom-in', 'Zoom In'], ['zoom-out', 'Zoom Out']
            ]},
            { label: 'Object Fit', property: 'object-fit', type: 'select', options: [
                ['', 'Default'], ['fill', 'Fill'], ['contain', 'Contain'], ['cover', 'Cover'],
                ['none', 'None'], ['scale-down', 'Scale Down']
            ]},
            { label: 'Object Position', property: 'object-position', type: 'text', placeholder: 'center, 50% 50%' }
        ]
    },

    // ─── LISTS ───────────────────────────────────────────────────
    {
        title: 'Lists',
        controls: [
            { label: 'List Style Type', property: 'list-style-type', type: 'select', options: [
                ['', 'Default'], ['none', 'None'], ['disc', 'Disc'], ['circle', 'Circle'],
                ['square', 'Square'], ['decimal', 'Decimal'], ['decimal-leading-zero', 'Decimal Leading Zero'],
                ['lower-roman', 'Lower Roman'], ['upper-roman', 'Upper Roman'],
                ['lower-greek', 'Lower Greek'], ['lower-latin', 'Lower Latin'],
                ['upper-latin', 'Upper Latin'], ['armenian', 'Armenian'], ['georgian', 'Georgian']
            ]},
            { label: 'List Style Position', property: 'list-style-position', type: 'select', options: [
                ['', 'Default'], ['inside', 'Inside'], ['outside', 'Outside']
            ]},
            { label: 'List Style Image', property: 'list-style-image', type: 'text', placeholder: "url('bullet.png')" }
        ]
    },

    // ─── TRANSITIONS & ANIMATIONS ────────────────────────────────
    {
        title: 'Transitions & Animations',
        controls: [
            { label: 'Transition', property: 'transition', type: 'text', placeholder: 'all 0.3s ease' },
            { label: 'Transition Property', property: 'transition-property', type: 'text', placeholder: 'all, opacity, transform' },
            { label: 'Transition Duration', property: 'transition-duration', type: 'text', dependsOn: 'transition-property', dependsValue: '!', placeholder: '0.3s, 300ms' },
            { label: 'Transition Timing Function', property: 'transition-timing-function', type: 'select', dependsOn: 'transition-property', dependsValue: '!', options: [
                ['', 'Default'], ['ease', 'Ease'], ['linear', 'Linear'], ['ease-in', 'Ease In'],
                ['ease-out', 'Ease Out'], ['ease-in-out', 'Ease In Out'],
                ['step-start', 'Step Start'], ['step-end', 'Step End'],
                ['steps(4, end)', 'Steps (4, end)'], ['cubic-bezier(0.4, 0, 0.2, 1)', 'Cubic Bezier']
            ]},
            { label: 'Transition Delay', property: 'transition-delay', type: 'text', dependsOn: 'transition-property', dependsValue: '!', placeholder: '0s, 100ms' },
            { label: 'Animation', property: 'animation', type: 'text', placeholder: 'name 1s ease infinite' },
            { label: 'Animation Name', property: 'animation-name', type: 'text', placeholder: 'fadeIn, slideUp' },
            { label: 'Animation Duration', property: 'animation-duration', type: 'text', dependsOn: 'animation-name', dependsValue: '!', placeholder: '1s, 500ms' },
            { label: 'Animation Timing Function', property: 'animation-timing-function', type: 'select', dependsOn: 'animation-name', dependsValue: '!', options: [
                ['', 'Default'], ['ease', 'Ease'], ['linear', 'Linear'], ['ease-in', 'Ease In'],
                ['ease-out', 'Ease Out'], ['ease-in-out', 'Ease In Out'],
                ['step-start', 'Step Start'], ['step-end', 'Step End'],
                ['steps(4, end)', 'Steps (4, end)'], ['cubic-bezier(0.4, 0, 0.2, 1)', 'Cubic Bezier']
            ]},
            { label: 'Animation Delay', property: 'animation-delay', type: 'text', dependsOn: 'animation-name', dependsValue: '!', placeholder: '0s, 100ms' },
            { label: 'Animation Iteration Count', property: 'animation-iteration-count', type: 'text', dependsOn: 'animation-name', dependsValue: '!', placeholder: '1, infinite, 3' },
            { label: 'Animation Direction', property: 'animation-direction', type: 'select', dependsOn: 'animation-name', dependsValue: '!', options: [
                ['', 'Default'], ['normal', 'Normal'], ['reverse', 'Reverse'],
                ['alternate', 'Alternate'], ['alternate-reverse', 'Alternate Reverse']
            ]},
            { label: 'Animation Fill Mode', property: 'animation-fill-mode', type: 'select', dependsOn: 'animation-name', dependsValue: '!', options: [
                ['', 'Default'], ['none', 'None'], ['forwards', 'Forwards'],
                ['backwards', 'Backwards'], ['both', 'Both']
            ]},
            { label: 'Animation Play State', property: 'animation-play-state', type: 'select', dependsOn: 'animation-name', dependsValue: '!', options: [
                ['', 'Default'], ['running', 'Running'], ['paused', 'Paused']
            ]},
            { label: 'Will Change', property: 'will-change', type: 'select', options: [
                ['', 'Default'], ['auto', 'Auto'], ['transform', 'Transform'],
                ['opacity', 'Opacity'], ['scroll-position', 'Scroll Position'], ['contents', 'Contents']
            ]},
            { label: 'Contain', property: 'contain', type: 'select', options: [
                ['', 'Default'], ['none', 'None'], ['layout', 'Layout'], ['style', 'Style'],
                ['paint', 'Paint'], ['size', 'Size'], ['content', 'Content'], ['strict', 'Strict']
            ]}
        ]
    },

    // ─── FILTERS ─────────────────────────────────────────────────
    {
        title: 'Filters',
        controls: [
            { label: 'Filter', property: 'filter', type: 'text', placeholder: 'blur(5px), brightness(1.5)' },
            { label: 'Blur', property: 'blur', type: 'text', placeholder: '5px' },
            { label: 'Brightness', property: 'brightness', type: 'text', placeholder: '1.5, 150%' },
            { label: 'Contrast', property: 'contrast', type: 'text', placeholder: '1.5, 150%' },
            { label: 'Grayscale', property: 'grayscale', type: 'text', placeholder: '50%, 0.5' },
            { label: 'Hue Rotate', property: 'hue-rotate', type: 'text', placeholder: '90deg' },
            { label: 'Invert', property: 'invert', type: 'text', placeholder: '50%, 0.5' },
            { label: 'Opacity', property: 'opacity', type: 'text', placeholder: '50%, 0.5' },
            { label: 'Saturate', property: 'saturate', type: 'text', placeholder: '1.5, 150%' },
            { label: 'Sepia', property: 'sepia', type: 'text', placeholder: '50%, 0.5' },
            { label: 'Drop Shadow', property: 'drop-shadow', type: 'text', placeholder: '2px 2px 4px rgba(0,0,0,0.5)' },
            { label: 'Backdrop Filter', property: 'backdrop-filter', type: 'text', placeholder: 'blur(10px), brightness(1.5)' }
        ]
    },

    // ─── TRANSFORM ───────────────────────────────────────────────
    {
        title: 'Transform',
        controls: [
            { label: 'Transform', property: 'transform', type: 'text', placeholder: 'rotate(45deg), scale(1.2)' },
            { label: 'Transform Origin', property: 'transform-origin', type: 'text', dependsOn: 'transform', dependsValue: '!', placeholder: 'center, 10px 15px' },
            { label: 'Rotate', property: 'rotate', type: 'text', placeholder: '45deg, 1turn' },
            { label: 'Rotate X', property: 'rotateX', type: 'text', placeholder: '45deg' },
            { label: 'Rotate Y', property: 'rotateY', type: 'text', placeholder: '45deg' },
            { label: 'Rotate Z', property: 'rotateZ', type: 'text', placeholder: '45deg' },
            { label: 'Rotate 3D', property: 'rotate3d', type: 'text', placeholder: '1, 1, 0, 45deg' },
            { label: 'Perspective', property: 'perspective', type: 'text', placeholder: '1000px, 500px' },
            { label: 'Scale', property: 'scale', type: 'text', placeholder: '1.5, 1.2 1.5' },
            { label: 'Scale X', property: 'scaleX', type: 'text', placeholder: '1.5' },
            { label: 'Scale Y', property: 'scaleY', type: 'text', placeholder: '1.5' },
            { label: 'Translate', property: 'translate', type: 'text', placeholder: '10px 20px, 50% 25%' },
            { label: 'Translate X', property: 'translateX', type: 'text', placeholder: '10px, 50%' },
            { label: 'Translate Y', property: 'translateY', type: 'text', placeholder: '20px, 25%' },
            { label: 'Skew', property: 'skew', type: 'text', placeholder: '10deg 15deg' },
            { label: 'Skew X', property: 'skewX', type: 'text', placeholder: '10deg' },
            { label: 'Skew Y', property: 'skewY', type: 'text', placeholder: '15deg' }
        ]
    },

    // ─── SPACING ─────────────────────────────────────────────────
    {
        title: 'Spacing',
        controls: [
            { label: 'Padding', property: 'padding', type: 'text', placeholder: '10px, 1em' },
            { label: 'Padding Top', property: 'padding-top', type: 'text', placeholder: '10px, 1em' },
            { label: 'Padding Right', property: 'padding-right', type: 'text', placeholder: '10px, 1em' },
            { label: 'Padding Bottom', property: 'padding-bottom', type: 'text', placeholder: '10px, 1em' },
            { label: 'Padding Left', property: 'padding-left', type: 'text', placeholder: '10px, 1em' },
            { label: 'Margin', property: 'margin', type: 'text', placeholder: '10px, 1em, auto' },
            { label: 'Margin Top', property: 'margin-top', type: 'text', placeholder: '10px, 1em, auto' },
            { label: 'Margin Right', property: 'margin-right', type: 'text', placeholder: '10px, 1em, auto' },
            { label: 'Margin Bottom', property: 'margin-bottom', type: 'text', placeholder: '10px, 1em, auto' },
            { label: 'Margin Left', property: 'margin-left', type: 'text', placeholder: '10px, 1em, auto' }
        ]
    },

    // ─── RADIUS ──────────────────────────────────────────────────
    {
        title: 'Radius',
        controls: [
            { label: 'Border Radius', property: 'border-radius', type: 'text', placeholder: '5px, 50%' },
            { label: 'Top Left Radius', property: 'border-top-left-radius', type: 'text', placeholder: '5px, 50%' },
            { label: 'Top Right Radius', property: 'border-top-right-radius', type: 'text', placeholder: '5px, 50%' },
            { label: 'Bottom Right Radius', property: 'border-bottom-right-radius', type: 'text', placeholder: '5px, 50%' },
            { label: 'Bottom Left Radius', property: 'border-bottom-left-radius', type: 'text', placeholder: '5px, 50%' }
        ]
    },

    // ─── BORDERS ─────────────────────────────────────────────────
    {
        title: 'Borders',
        controls: [
            { label: 'Border Style', property: 'border-style', type: 'select', options: [
                ['', 'Default'], ['solid', 'Solid'], ['dashed', 'Dashed'], ['dotted', 'Dotted'],
                ['double', 'Double'], ['groove', 'Groove'], ['ridge', 'Ridge'],
                ['inset', 'Inset'], ['outset', 'Outset'], ['none', 'None']
            ]},
            { label: 'Border Color', property: 'border-color', type: 'color', dependsOn: 'border-style', dependsValue: '!none,!' },
            { label: 'Border Width', property: 'border-width', type: 'text', dependsOn: 'border-style', dependsValue: '!none,!', placeholder: '1px, 2px' },
            { label: 'Top Border Width', property: 'border-top-width', type: 'text', placeholder: '1px, 2px' },
            { label: 'Right Border Width', property: 'border-right-width', type: 'text', placeholder: '1px, 2px' },
            { label: 'Bottom Border Width', property: 'border-bottom-width', type: 'text', placeholder: '1px, 2px' },
            { label: 'Left Border Width', property: 'border-left-width', type: 'text', placeholder: '1px, 2px' },
            { label: 'Top Border Style', property: 'border-top-style', type: 'select', options: [
                ['', 'Default'], ['solid', 'Solid'], ['dashed', 'Dashed'], ['dotted', 'Dotted'],
                ['double', 'Double'], ['groove', 'Groove'], ['ridge', 'Ridge'],
                ['inset', 'Inset'], ['outset', 'Outset'], ['none', 'None']
            ]},
            { label: 'Right Border Style', property: 'border-right-style', type: 'select', options: [
                ['', 'Default'], ['solid', 'Solid'], ['dashed', 'Dashed'], ['dotted', 'Dotted'],
                ['double', 'Double'], ['groove', 'Groove'], ['ridge', 'Ridge'],
                ['inset', 'Inset'], ['outset', 'Outset'], ['none', 'None']
            ]},
            { label: 'Bottom Border Style', property: 'border-bottom-style', type: 'select', options: [
                ['', 'Default'], ['solid', 'Solid'], ['dashed', 'Dashed'], ['dotted', 'Dotted'],
                ['double', 'Double'], ['groove', 'Groove'], ['ridge', 'Ridge'],
                ['inset', 'Inset'], ['outset', 'Outset'], ['none', 'None']
            ]},
            { label: 'Left Border Style', property: 'border-left-style', type: 'select', options: [
                ['', 'Default'], ['solid', 'Solid'], ['dashed', 'Dashed'], ['dotted', 'Dotted'],
                ['double', 'Double'], ['groove', 'Groove'], ['ridge', 'Ridge'],
                ['inset', 'Inset'], ['outset', 'Outset'], ['none', 'None']
            ]},
            { label: 'Top Border Color', property: 'border-top-color', type: 'color' },
            { label: 'Right Border Color', property: 'border-right-color', type: 'color' },
            { label: 'Bottom Border Color', property: 'border-bottom-color', type: 'color' },
            { label: 'Left Border Color', property: 'border-left-color', type: 'color' },
            { label: 'Border Image', property: 'border-image', type: 'text', placeholder: "url('border.png') 30 round" },
            { label: 'Border Collapse', property: 'border-collapse', type: 'select', options: [
                ['', 'Default'], ['separate', 'Separate'], ['collapse', 'Collapse']
            ]}
        ]
    },

    // ─── OUTLINE ─────────────────────────────────────────────────
    {
        title: 'Outline',
        controls: [
            { label: 'Outline Style', property: 'outline-style', type: 'select', options: [
                ['', 'Default'], ['solid', 'Solid'], ['dashed', 'Dashed'], ['dotted', 'Dotted'],
                ['double', 'Double'], ['groove', 'Groove'], ['ridge', 'Ridge'],
                ['inset', 'Inset'], ['outset', 'Outset'], ['none', 'None']
            ]},
            { label: 'Outline Color', property: 'outline-color', type: 'color' },
            { label: 'Outline Width', property: 'outline-width', type: 'text', placeholder: '1px, 2px, thin, medium, thick' },
            { label: 'Outline Offset', property: 'outline-offset', type: 'text', placeholder: '2px, 5px' }
        ]
    },

    // ─── EFFECTS ─────────────────────────────────────────────────
    {
        title: 'Effects',
        controls: [
            { label: 'Box Shadow', property: 'box-shadow', type: 'text', placeholder: '2px 2px 4px rgba(0,0,0,0.2)' },
            { label: 'Opacity', property: 'opacity', type: 'range', min: 0, max: 1, step: 0.01 },
            { label: 'Mix Blend Mode', property: 'mix-blend-mode', type: 'select', options: [
                ['', 'Default'], ['normal', 'Normal'], ['multiply', 'Multiply'], ['screen', 'Screen'],
                ['overlay', 'Overlay'], ['darken', 'Darken'], ['lighten', 'Lighten'],
                ['color-dodge', 'Color Dodge'], ['color-burn', 'Color Burn'],
                ['difference', 'Difference'], ['exclusion', 'Exclusion'],
                ['hue', 'Hue'], ['saturation', 'Saturation'], ['color', 'Color'], ['luminosity', 'Luminosity']
            ]},
            { label: 'Backdrop Filter', property: 'backdrop-filter', type: 'text', placeholder: 'blur(10px), brightness(1.5)' }
        ]
    }
];

/* =========================================================================
   renderVisualEditor — Builds the entire visual tab from the schema
   ========================================================================= */

/**
 * Renders all accordion sections from the VISUAL_EDITOR_SCHEMA into
 * the given container element. Generates the exact same HTML structure
 * that the old hardcoded PHP template produced, so all existing JS
 * (event handlers, search, usage dots, dependencies) continues to work
 * without changes.
 *
 * @param {HTMLElement} container - The DOM element to render into
 */
function renderVisualEditor(container) {
    if (!container) return;
    container.innerHTML = ''; // Clear any placeholder

    VISUAL_EDITOR_SCHEMA.forEach(section => {
        // Create accordion item
        const accordionItem = document.createElement('div');
        accordionItem.className = 'accordion-item';

        // Header
        const header = document.createElement('div');
        header.className = 'accordion-header';
        header.textContent = section.title;
        accordionItem.appendChild(header);

        // Content
        const content = document.createElement('div');
        content.className = 'accordion-content';

        section.controls.forEach(ctrl => {
            const group = document.createElement('div');
            group.className = 'control-group';

            // Dependency attributes
            if (ctrl.dependsOn) {
                group.dataset.dependsOn = ctrl.dependsOn;
                group.dataset.dependsValue = ctrl.dependsValue || '';
            }

            // Label
            const label = document.createElement('label');
            label.className = 'control-label';
            label.textContent = ctrl.label;
            group.appendChild(label);

            // Input element
            let input;

            switch (ctrl.type) {
                case 'select': {
                    input = document.createElement('select');
                    input.className = 'control';
                    input.dataset.property = ctrl.property;
                    (ctrl.options || []).forEach(([value, text]) => {
                        const opt = document.createElement('option');
                        opt.value = value;
                        opt.textContent = text;
                        input.appendChild(opt);
                    });
                    break;
                }

                case 'color': {
                    input = document.createElement('input');
                    input.type = 'color';
                    input.className = 'control';
                    input.dataset.property = ctrl.property;
                    break;
                }

                case 'range': {
                    input = document.createElement('input');
                    input.type = 'range';
                    input.className = 'control';
                    input.dataset.property = ctrl.property;
                    if (ctrl.min !== undefined) input.min = ctrl.min;
                    if (ctrl.max !== undefined) input.max = ctrl.max;
                    if (ctrl.step !== undefined) input.step = ctrl.step;
                    break;
                }

                default: { // 'text'
                    input = document.createElement('input');
                    input.type = 'text';
                    input.className = 'control';
                    input.dataset.property = ctrl.property;
                    if (ctrl.placeholder) input.placeholder = ctrl.placeholder;
                    break;
                }
            }

            group.appendChild(input);
            content.appendChild(group);
        });

        accordionItem.appendChild(content);
        container.appendChild(accordionItem);
    });
}
