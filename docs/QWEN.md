# LiveCSS WordPress Plugin - Development Context

## Project Overview

This is a WordPress plugin called **LiveCSS** that provides a visual CSS editor with real-time preview capabilities. The plugin allows WordPress administrators to edit CSS styles directly on their site with a live preview, without needing to manually edit theme files.

### Key Features
- Visual CSS editor interface with real-time preview
- CodeMirror-based code editor for direct CSS editing
- Visual controls for common CSS properties (typography, background, layout, borders, effects)
- Element selector with click-to-select functionality in the preview
- AJAX-based saving of custom CSS
- Admin bar integration for easy access
- CSS Class Manager for advanced CSS class management
- Preview mode for full-screen website preview
- Smart loading system with feature library detection
- Property dependencies system for conditional property display

### Technologies Used
- PHP (WordPress plugin development)
- JavaScript (ES6 classes, AJAX, async/await)
- CSS3
- CodeMirror (for code editing)
- WordPress APIs (admin bar, AJAX, options)
- Gutenberg API (for CSS Class Manager)
- REST API (for CSS Class Manager)

## Project Structure

```
plugin/
├── livecss.php          # Main plugin file with core functionality
├── templates/
│   ├── editor.php       # Main editor template that includes all parts
│   ├── editor-header.php # HTML head, styles, and initial structure
│   ├── editor-content.php # Editor panel with all accordion sections
│   └── editor-js.php    # JavaScript functionality (LiveCSSEditor class)
├── includes/
│   └── class-css-class-manager.php # CSS Class Manager implementation
├── assets/
│   ├── js/
│   │   ├── class-manager.js # Gutenberg integration for CSS classes
│   │   ├── class-manager.asset.php # Dependencies
│   │   ├── property-dependencies.js # Property dependencies system
│   │   ├── spotlight-mode.js # Spotlight mode for code editor
│   │   └── search-functionality.js # Search functionality
│   └── css/
│       └── class-manager.css # Styles for class manager
├── docs/
│   ├── AUTO-SPOTLIGHT-V2.md
│   ├── CSS-CLASS-MANAGER.md
│   ├── LOADING-SYSTEM.md
│   ├── PREVIEW-MODE.md
│   ├── PROPERTY-DEPENDENCIES.md
│   ├── PSEUDO-BUTTONS-FIX.md
│   ├── SEARCH-FUNCTIONALITY.md
│   └── UNSAVED-CHANGES-AND-HISTORY.md
└── QWEN.md              # This file
```

## Plugin Architecture

### Main Plugin File (`livecss.php`)
This file contains the main `LiveCSS` class that handles:
- Plugin initialization and hooks
- Admin bar integration
- Loading the editor interface
- Frontend CSS injection
- AJAX handling for saving CSS
- CSS file management in uploads directory
- Body class support
- Activation/deactivation hooks

### CSS Class Manager (`includes/class-css-class-manager.php`)
A specialized class for managing CSS classes with Gutenberg integration:
- REST API endpoints for managing CSS classes
- Gutenberg sidebar integration
- Class management interface
- User settings for display preferences
- Default utility classes

### Editor Interface (`templates/editor.php`)
A standalone HTML page that provides:
- Visual CSS editor with accordion-based property controls
- CodeMirror-based CSS code editor
- Live preview iframe
- Element selection functionality
- Save and exit controls
- Preview mode functionality
- Device toggling for responsive design

The editor interface is now broken into three logical parts for better maintainability:
- `editor-header.php`: Contains the HTML head, CSS styles, and initial page structure
- `editor-content.php`: Contains the main editor panel with all accordion sections
- `editor-js.php`: Contains the JavaScript functionality (LiveCSSEditor class and initialization)

## Key Functionality

### Plugin Hooks
- `plugins_loaded`: Initialize the plugin
- `admin_bar_menu`: Add "Edit CSS" button to admin bar
- `template_redirect`: Load CSS editor when requested
- `wp_head`: Inject saved custom CSS on frontend
- `wp_ajax_livecss_save`: Handle AJAX CSS saving
- `wp_ajax_livecss_recreate_file`: Handle CSS file recreation
- `wp_ajax_livecss_save_body_classes`: Handle AJAX body class saving
- `register_activation_hook`: Create CSS file directory on activation
- `enqueue_block_editor_assets`: Load CSS class manager in Gutenberg

### CSS Editor Features

1. **Visual Editor Tab**:
   - Accordion sections for different CSS property groups
   - Form controls for common CSS properties
   - Real-time preview updates
   - Usage dots to reset properties
   - Property dependencies system

2. **Code Editor Tab**:
   - CodeMirror editor for direct CSS editing
   - Syntax highlighting
   - Real-time preview updates
   - Auto-complete functionality
   - Spotlight mode for focused editing

3. **Element Selection**:
   - Click elements in preview to select them
   - Automatic CSS selector generation
   - Visual highlighting of selected elements
   - Breadcrumb navigation
   - Selector suggestions

4. **Pseudo-class Support**:
   - Quick-add buttons for common pseudo-classes (:hover, :focus, etc.)
   - Automatic pseudo-selector addition/removal

5. **Device Preview**:
   - Desktop, tablet, and mobile preview options
   - Fixed iframe sizes for consistent previews
   - Responsive device toggling

6. **Preview Mode**:
   - Full-screen preview without editor panels
   - Floating exit button for returning to editor
   - ESC key support to exit preview

7. **Advanced Visual Controls**:
   - **Background**: Controls for background-color, background-image, background-size, background-position, background-repeat, background-attachment, background-clip, and background-origin
   - **Typography**: Controls for font-family, font-size, font-weight, font-style, color, line-height, text-align, text-decoration, text-transform, letter-spacing, and word-spacing
   - **Borders**: Controls for border-style, border-color, border-width, and side-specific properties (top, right, bottom, left) for width, style, and color
   - **Radius**: Controls for border-radius and side-specific radius properties (top-left, top-right, bottom-right, bottom-left)
   - **Spacing**: Controls for padding and margin, including side-specific properties (top, right, bottom, left)
   - **Transform**: Controls for transform properties like rotate, scale, translate, and skew, including individual properties like scaleX, scaleY, translateX, translateY, skewX, and skewY
   - **Filters**: Controls for CSS filter properties like blur, brightness, contrast, drop-shadow, grayscale, hue-rotate, invert, opacity, saturate, and sepia
   - **Lists**: Controls for styling lists, like list-style-type, list-style-position, and list-style-image
   - **Layout**: Controls for key layout properties like display, position, float, clear, overflow, z-index, flexbox properties, grid properties, and gap properties
   - **Transitions & Animations**: Controls for creating CSS transitions and animations
   - **Effects**: Controls for box-shadow, opacity, mix-blend-mode, and backdrop-filter

8. **CSS Class Manager**:
   - Integrated with Gutenberg Block Editor
   - Create, edit, and delete custom CSS classes
   - Search and filter functionality
   - User settings for display preferences
   - REST API endpoints for class management

9. **Search Functionality**:
   - Dual search (Visual + Code) with tab-specific results
   - Property highlighting in visual editor
   - Code search with navigation

10. **Change Tracking & History**:
    - Unsaved changes indicator
    - Undo/redo functionality
    - History management with debounced snapshots

## Development Workflow

### Accessing the Editor
1. Navigate to any page on the WordPress site
2. Click the "Edit CSS" button in the WordPress admin bar
3. The plugin uses the `?csseditor=run` URL parameter to load the editor interface
4. Use the visual editor or code editor to modify styles
5. Click "Save Changes" to persist CSS
6. Click "Exit Editor" to return to the normal site view

### Saving CSS
- CSS is saved to a file in the WordPress uploads directory (`wp-content/uploads/livecss/main.css`)
- Saved CSS is injected into the site frontend via the `wp_head` action
- CSS is minified before saving for performance
- The old database option approach has been replaced with file-based storage

### CSS Class Manager Usage
1. Access via the Gutenberg Block Editor
2. The CSS Class Manager appears in the Advanced panel of block settings
3. Add, edit, or delete custom CSS classes
4. Apply classes to blocks using the multi-select interface
5. Use the "Manage Classes" button to open the class manager modal

## Development Considerations

### Coding Standards
- Follow WordPress PHP coding standards
- Use WordPress hooks and APIs appropriately
- Proper nonce verification for AJAX requests
- Sanitize and validate all user inputs

### Security
- Nonce verification for AJAX endpoints
- User capability checks (restricted to administrators)
- CSS sanitization before output
- File permission checks for CSS management

### Browser Compatibility
- Modern JavaScript (ES6 classes, arrow functions, async/await)
- CSS3 features
- CodeMirror library dependency
- Gutenberg integration for CSS Class Manager

### Performance Optimizations
- CSS minification before saving
- Smart loading system with progress tracking
- Debounced history capture
- Asynchronous initialization

## Customization Points

### Adding New CSS Properties
1. Add new control elements in the appropriate accordion section in `editor-content.php`
2. Add corresponding event listeners in the `LiveCSSEditor` JavaScript class
3. Ensure property names match valid CSS properties
4. Update the CSS generation and parsing logic as needed

### Extending Visual Controls
1. Add new accordion sections in the HTML
2. Add JavaScript handlers for new control types
3. Update the CSS generation logic as needed
4. Consider property dependencies for conditional display

### Modifying Editor Behavior
The `LiveCSSEditor` JavaScript class in `editor-js.php` contains all editor functionality:
- `updateCSSProperty()`: Handles CSS property updates
- `generateCSS()`: Generates CSS from current rules
- `parseCSS()`: Parses existing CSS into the editor
- `updatePreview()`: Updates the live preview
- `isTransformProperty()` / `isFilterProperty()`: Check if a property is a special combined property
- `buildTransformValue()` / `buildFilterValue()`: Build combined CSS values for transform and filter properties
- `parseTransformValue()` / `parseFilterValue()`: Parse combined CSS values into individual properties
- `togglePreviewMode()`: Handles preview mode functionality
- `setupElementSelector()`: Handles element selection in preview
- `collectSelectorsFromDom()`: Collects selectors for suggestions

## Testing

Since this is a WordPress plugin, testing should be done in a WordPress environment:
1. Install the plugin in a WordPress instance
2. Activate the plugin
3. Test the editor functionality on various pages
4. Verify CSS is saved and applied correctly
5. Test CSS Class Manager in Gutenberg
6. Test responsive design with device toggles
7. Test preview mode functionality
8. Test across different browsers and devices

## Deployment

1. Ensure all dependencies are properly referenced (CodeMirror CDN links)
2. Test in a staging environment that mirrors production
3. Activate the plugin in WordPress admin
4. Verify functionality before making changes on live site
5. Check file permissions for the uploads/livecss directory

## Recent Updates

### v2.0.0 New Features
- **CSS Class Manager**: Integrated CSS class management with Gutenberg
- **Preview Mode**: Full-screen preview with floating exit button
- **Smart Loading System**: 7-step initialization with progress tracking
- **Search Functionality**: Dual search for visual and code editor tabs
- **Property Dependencies**: Conditional property display based on other properties
- **Spotlight Mode**: Focused editing for specific CSS rules
- **Unsaved Changes Tracking**: Visual indicator and history for changes
- **Enhanced UI**: Improved styling with Tailwind-inspired design
- **File-based CSS Storage**: Moved from database to file system for better performance

### Code Improvements
- Better error handling and retry mechanisms
- Improved iframe loading and admin bar hiding
- Enhanced selector suggestions with preview
- Better responsive preview with fixed device sizes
- Improved accessibility with proper ARIA labels
- Better keyboard navigation support
- Sidebar resizing with localStorage persistence

## Troubleshooting

### Common Issues
1. **Admin bar showing in preview**: The system has specific code to hide the WordPress admin bar in the preview iframe
2. **CSS not applying**: Check file permissions on the wp-content/uploads/livecss directory
3. **CodeMirror not loading**: Verify CDN links are accessible
4. **Selector highlighting not working**: Check if the iframe content is loading properly

### Debugging Tools
1. Use `debugLiveCSS()` in browser console to get editor status
2. Use `refreshLiveCSSPreview()` to refresh preview CSS
3. Use `hideAdminBar()` to manually hide admin bar in iframe
4. Check browser console for initialization errors
5. Use the loading system information to verify feature library loading

### Error Recovery
- The system has retry mechanisms for initialization failures
- File recreation functionality for missing CSS files
- Graceful degradation when feature libraries fail to load
- Backup mechanisms for iframe loading failures