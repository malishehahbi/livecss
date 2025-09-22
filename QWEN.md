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

### Technologies Used
- PHP (WordPress plugin development)
- JavaScript (ES6 classes, AJAX)
- CSS3
- CodeMirror (for code editing)
- WordPress APIs (admin bar, AJAX, options)

## Project Structure

```
plugin/
├── livecss.php          # Main plugin file with core functionality
├── templates/
│   ├── editor.php       # Main editor template that includes all parts
│   ├── editor-header.php # HTML head, styles, and initial structure
│   ├── editor-content.php # Editor panel with all accordion sections
│   └── editor-js.php    # JavaScript functionality (LiveCSSEditor class)
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

### Editor Interface (`templates/editor.php`)
A standalone HTML page that provides:
- Visual CSS editor with accordion-based property controls
- CodeMirror-based CSS code editor
- Live preview iframe
- Element selection functionality
- Save and exit controls

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

### CSS Editor Features
1. **Visual Editor Tab**:
   - Accordion sections for different CSS property groups
   - Form controls for common CSS properties
   - Real-time preview updates

2. **Code Editor Tab**:
   - CodeMirror editor for direct CSS editing
   - Syntax highlighting
   - Real-time preview updates

3. **Element Selection**:
   - Click elements in preview to select them
   - Automatic CSS selector generation
   - Visual highlighting of selected elements

4. **Pseudo-class Support**:
   - Quick-add buttons for common pseudo-classes (:hover, :focus, etc.)

5. **Advanced Visual Controls**:
   - **Background**: Controls for background-color, background-image, background-size, background-position, background-repeat, background-attachment, background-clip, and background-origin
   - **Typography**: Controls for font-family, font-size, font-weight, font-style, color, line-height, text-align, text-decoration, text-transform, letter-spacing, and word-spacing
   - **Borders**: Controls for border-style, border-color, border-width, and side-specific properties (top, right, bottom, left) for width, style, and color
   - **Radius**: Controls for border-radius and side-specific radius properties (top-left, top-right, bottom-right, bottom-left)
   - **Spacing**: Controls for padding and margin, including side-specific properties (top, right, bottom, left)
   - **Transform**: Controls for transform properties like rotate, scale, translate, and skew, including individual properties like scaleX, scaleY, translateX, translateY, skewX, and skewY
   - **Filters**: Controls for CSS filter properties like blur, brightness, contrast, drop-shadow, grayscale, hue-rotate, invert, opacity, saturate, and sepia
   - **Lists**: Controls for styling lists, like list-style-type, list-style-position, and list-style-image
   - **Layout**: Controls for key layout properties like display, position, float, clear, overflow, z-index, flexbox properties, grid properties, and gap properties

## Development Workflow

### Accessing the Editor
1. Navigate to any page on the WordPress site
2. Click the "Edit CSS" button in the WordPress admin bar
3. The plugin uses the `?csseditor=run` URL parameter to load the editor interface
4. Use the visual editor or code editor to modify styles
5. Click "Save Changes" to persist CSS
6. Click "Exit Editor" to return to the normal site view

### Saving CSS
- CSS is saved to the WordPress options table with the key `livecss_custom_css`
- Saved CSS is injected into the site frontend via the `wp_head` action

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

### Browser Compatibility
- Modern JavaScript (ES6 classes, arrow functions)
- CSS3 features
- CodeMirror library dependency

## Customization Points

### Adding New CSS Properties
1. Add new control elements in the appropriate accordion section in `editor.php`
2. Add corresponding event listeners in the `LiveCSSEditor` JavaScript class
3. Ensure property names match valid CSS properties

### Extending Visual Controls
1. Add new accordion sections in the HTML
2. Add JavaScript handlers for new control types
3. Update the CSS generation logic as needed

### Modifying Editor Behavior
The `LiveCSSEditor` JavaScript class in `editor.php` contains all editor functionality:
- `updateCSSProperty()`: Handles CSS property updates
- `generateCSS()`: Generates CSS from current rules
- `parseCSS()`: Parses existing CSS into the editor
- `updatePreview()`: Updates the live preview
- `isTransformProperty()` / `isFilterProperty()`: Check if a property is a special combined property
- `buildTransformValue()` / `buildFilterValue()`: Build combined CSS values for transform and filter properties
- `parseTransformValue()` / `parseFilterValue()`: Parse combined CSS values into individual properties

## Testing

Since this is a WordPress plugin, testing should be done in a WordPress environment:
1. Install the plugin in a WordPress instance
2. Activate the plugin
3. Test the editor functionality on various pages
4. Verify CSS is saved and applied correctly
5. Test across different browsers and devices

## Deployment

1. Ensure all dependencies are properly referenced (CodeMirror CDN links)
2. Test in a staging environment that mirrors production
3. Activate the plugin in WordPress admin
4. Verify functionality before making changes on live site