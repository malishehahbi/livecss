# LiveCSS Class Manager

A simplified CSS Class Manager integrated with Gutenberg, inspired by the CSS Class Manager plugin but built specifically for LiveCSS.

## Features

✅ **Enhanced CSS Class Control** - Replaces the default "Additional CSS Class(es)" field with an advanced multi-select interface
✅ **Class Management** - Add, edit, and delete custom CSS classes with descriptions
✅ **Gutenberg Integration** - Seamlessly integrated with the block editor
✅ **Search & Filter** - Quickly find classes with search functionality
✅ **User Settings** - Customizable display options
✅ **REST API** - Full REST API support for class management
✅ **Admin Interface** - Simple admin page for class management

## How It Works

### In the Block Editor

1. **Enhanced CSS Classes Control**: When editing any block, you'll find an enhanced CSS classes control in the Advanced section (or in its own panel if configured)

2. **Search & Select**: Type to search for existing classes or scroll through the available options

3. **Multi-Select**: Check multiple classes to apply them to your block

4. **Manage Classes**: Click "Manage Classes" to open the class manager modal

### Class Manager Modal

- **Add New Classes**: Enter class name and description
- **View All Classes**: See all your custom classes in one place
- **Delete Classes**: Remove classes you no longer need

### Admin Page

Access via **Tools > CSS Class Manager** to see an overview of the system.

## Default Classes Included

The system comes with some default utility classes:

- `text-center` - Center align text
- `mb-4` - Margin bottom 4
- `p-3` - Padding 3
- `border-radius` - Rounded corners
- `shadow` - Drop shadow

## REST API Endpoints

- `GET /wp-json/livecss/v1/css-classes` - Get all CSS classes
- `POST /wp-json/livecss/v1/css-classes` - Create/update a CSS class
- `DELETE /wp-json/livecss/v1/css-classes/{id}` - Delete a CSS class
- `GET /wp-json/livecss/v1/user-settings` - Get user settings
- `POST /wp-json/livecss/v1/user-settings` - Update user settings

## User Settings

- `showInOwnPanel` - Display CSS classes control in its own panel instead of Advanced section
- `enableFuzzySearch` - Enable fuzzy search functionality (future feature)

## Technical Implementation

### Files Structure

```
includes/
  └── class-css-class-manager.php    # Main PHP class
assets/
  ├── js/
  │   ├── class-manager.js           # Gutenberg integration
  │   └── class-manager.asset.php    # Dependencies
  └── css/
      └── class-manager.css          # Styles
```

### Key Components

1. **LiveCSS_Class_Manager** - Main PHP class handling REST API and admin functionality
2. **CSSClassSelect** - React component for the enhanced class selector
3. **ClassManagerModal** - React component for managing classes
4. **Store** - Simple JavaScript store for state management

## Differences from Original Plugin

This implementation is **simplified** compared to the original CSS Class Manager:

### ✅ What's Included
- Enhanced CSS class control with search
- Class management (add/edit/delete)
- Gutenberg integration
- REST API
- Basic user settings
- Admin interface

### ❌ What's Simplified/Removed
- No theme.json integration
- No body/post class management
- No import/export functionality
- No advanced fuzzy search (Fuse.js)
- No complex CSS parsing
- Simplified UI/UX

## Usage Tips

1. **Adding Classes**: Use the "Manage Classes" button to add your custom CSS classes with descriptions
2. **Organizing**: Use descriptive names and detailed descriptions to keep classes organized
3. **Performance**: The system stores classes in WordPress options, so it's lightweight and fast

## Browser Support

Works with all modern browsers that support Gutenberg.

## Requirements

- WordPress 5.0+ (Gutenberg)
- PHP 7.4+
- LiveCSS Plugin

---

This CSS Class Manager provides a clean, simple way to manage CSS classes in Gutenberg without the complexity of the full-featured original plugin.