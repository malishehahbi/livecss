# Preview Mode - Full-Screen Website Preview

## 🎯 Overview

Preview Mode allows you to see your styling changes **full-screen** without the editor panels, giving you a complete view of how the website will look before saving. A floating exit button appears to return to the editor.

---

## ✨ Features

### 1. **Full-Screen Preview**
- 🖥️ Hides all editor panels (sidebar, header, tabs)
- 📺 Shows website at 100% viewport width
- 🎨 Preview your CSS changes in real context
- 💡 No editor clutter - pure website view

### 2. **Beautiful Floating Exit Button**
- 🎈 Animated floating button (top-right corner)
- 🌈 Gradient purple design with glow effect
- 🔄 Same position as Preview button
- ⌨️ Multiple exit methods (click, ESC key)

### 3. **Instant Toggle**
- ⚡ One-click enter/exit
- 🔄 Smooth transitions
- 💾 Changes persist during preview
- 🎯 Return exactly where you left off

---

## 🎮 How to Use

### Entering Preview Mode

**Method 1: Click Preview Button**
```
1. Look at header (top-right area)
2. Click "Preview" button with eye icon
3. Editor panels disappear
4. Website shows full-screen
5. Floating "Exit Preview" button appears
```

**Button Location:**
```
┌─────────────────────────────────────────────────┐
│ LiveCSS Editor  [Desktop][Tablet][Mobile]      │
│                   [Preview] [Save] [Exit]       │
│                        ↑                        │
│                   Click here                    │
└─────────────────────────────────────────────────┘
```

### Exiting Preview Mode

**Method 1: Click Exit Preview Button**
```
1. See floating button (top-right)
2. Click "Exit Preview"
3. Editor panels return
4. Preview button reappears
```

**Method 2: Press ESC Key**
```
1. Press Escape key
2. Instantly returns to editor
3. Same as clicking exit button
```

**Floating Button Position:**
```
┌─────────────────────────────────────────────────┐
│                          [✕ Exit Preview] ← Floating
│                                                 │
│                                                 │
│              Your Website Here                  │
│              (Full Screen)                      │
│                                                 │
│                                                 │
└─────────────────────────────────────────────────┘
```

---

## 🎨 Visual Design

### Preview Button (Header)

**Appearance:**
- **Icon**: Eye symbol (👁️)
- **Text**: "Preview"
- **Color**: Secondary color (light gray)
- **Style**: Rounded corners, subtle border
- **Hover**: Accent color highlight

**States:**
```css
Normal:  [👁️ Preview]     /* Gray background */
Hover:   [👁️ Preview]     /* Light blue highlight */
Hidden:  (not visible)     /* During preview mode */
```

### Exit Preview Button (Floating)

**Appearance:**
- **Icon**: X symbol (✕)
- **Text**: "Exit Preview"
- **Color**: Purple-blue gradient
- **Style**: Pill shape (rounded 50px)
- **Animation**: Gentle floating effect
- **Shadow**: Glowing purple shadow

**Visual Effects:**
```css
/* Gradient background */
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

/* Glow shadow */
box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);

/* Floating animation */
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-5px); }
}
```

**Hover Effect:**
- Scales up 5%
- Moves up 2px
- Shadow intensifies
- Gradient reverses direction

---

## 🔧 Technical Implementation

### HTML Structure

**Header Buttons:**
```html
<div class="header-actions">
    <!-- Preview button (visible by default) -->
    <button id="preview-button" class="button button-preview">
        <svg><!-- Eye icon --></svg>
        <span>Preview</span>
    </button>
    
    <button id="save-button">Save Changes</button>
    <a href="..." class="button-danger">Exit Editor</a>
</div>
```

**Floating Exit Button:**
```html
<!-- Positioned fixed, initially hidden -->
<button id="exit-preview-button" class="exit-preview-button hidden">
    <svg><!-- X icon --></svg>
    <span>Exit Preview</span>
</button>
```

### CSS Classes

**`.preview-mode` (Applied to `.editor-container`)**
```css
.editor-container.preview-mode .editor-panel {
    display: none !important;  /* Hide sidebar */
}

.editor-container.preview-mode .sidebar-resizer {
    display: none !important;  /* Hide resizer */
}

.editor-container.preview-mode .header {
    display: none !important;  /* Hide header */
}

.editor-container.preview-mode .preview-wrapper {
    width: 100% !important;    /* Full width */
    height: 100vh !important;  /* Full height */
    flex: 1 !important;
}

.editor-container.preview-mode .main-content {
    padding: 0 !important;     /* Remove padding */
}
```

**`.exit-preview-button`**
```css
.exit-preview-button {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;             /* Above everything */
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 50px;       /* Pill shape */
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
    animation: float 3s ease-in-out infinite;
}

.exit-preview-button:hover {
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 12px 32px rgba(102, 126, 234, 0.5);
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
}
```

### JavaScript Methods

**`togglePreviewMode(enable)`**
```javascript
togglePreviewMode(enable) {
    const editorContainer = document.querySelector('.editor-container');
    const exitPreviewButton = document.getElementById('exit-preview-button');
    const previewButton = document.getElementById('preview-button');

    if (enable) {
        // Enter preview mode
        editorContainer?.classList.add('preview-mode');
        exitPreviewButton?.classList.remove('hidden');
        previewButton?.classList.add('hidden');
        
        console.log('[LiveCSSEditor] Preview mode enabled');
    } else {
        // Exit preview mode
        editorContainer?.classList.remove('preview-mode');
        exitPreviewButton?.classList.add('hidden');
        previewButton?.classList.remove('hidden');
        
        console.log('[LiveCSSEditor] Preview mode disabled');
    }
}
```

**Event Listeners:**
```javascript
// Preview button click
previewButton.addEventListener('click', () => {
    this.togglePreviewMode(true);
});

// Exit preview button click
exitPreviewButton.addEventListener('click', () => {
    this.togglePreviewMode(false);
});

// ESC key to exit preview
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const editorContainer = document.querySelector('.editor-container');
        if (editorContainer?.classList.contains('preview-mode')) {
            this.togglePreviewMode(false);
        }
    }
});
```

---

## 📊 State Management

### Preview States

```javascript
{
    isPreviewMode: false,  // Tracked via CSS class
    
    // Elements
    editorContainer: HTMLElement,
    previewButton: HTMLElement,
    exitPreviewButton: HTMLElement,
}
```

### State Flow

```
Initial State (Editor Mode):
- .preview-mode class: NOT present
- previewButton: visible
- exitPreviewButton: hidden
- All editor panels: visible

User Clicks Preview:
- .preview-mode class: ADDED
- previewButton: hidden
- exitPreviewButton: visible
- All editor panels: hidden (display: none)

User Clicks Exit or ESC:
- .preview-mode class: REMOVED
- previewButton: visible
- exitPreviewButton: hidden
- All editor panels: visible again
```

---

## 🎯 Use Cases

### Use Case 1: Checking Responsive Layout
```
1. User editing button styles
2. Wants to see full mobile layout
3. Clicks Preview button
4. Editor disappears
5. Sees full mobile view without distractions
6. Checks if button fits properly
7. Presses ESC to return
8. Continues editing
```

### Use Case 2: Verifying Color Scheme
```
1. User changed multiple colors
2. Wants to see overall look
3. Clicks Preview
4. Full-screen website displayed
5. Evaluates color harmony
6. Decides if changes look good
7. Clicks Exit Preview
8. Either saves or adjusts more
```

### Use Case 3: Client Presentation
```
1. User makes styling changes
2. Client watching via screen share
3. Clicks Preview
4. Shows clean full-screen view
5. Client sees website without editor
6. Client approves design
7. User exits preview
8. Clicks Save Changes
```

### Use Case 4: Comparing Before/After
```
1. User made significant changes
2. Wants clean comparison
3. Clicks Preview (see current changes)
4. Observes new design
5. Clicks Exit Preview
6. Removes some CSS (revert)
7. Clicks Preview again
8. Compares to previous
```

---

## ⌨️ Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `ESC` | Exit preview mode (if active) |
| Click anywhere | No effect (isolated to button) |

---

## ✅ Benefits

### For Users
- 🎯 **Clear View** - No editor clutter
- 📐 **True Layout** - See full responsive design
- 🎨 **Context** - Evaluate styling in situ
- ⚡ **Quick Toggle** - One-click in/out
- 💾 **Safe** - Changes preserved
- 🔄 **Reversible** - Easy to return

### For Workflow
- 🚀 **Faster Decisions** - Quick full-screen check
- 👀 **Better QA** - Spot issues easier
- 📊 **Client Ready** - Clean presentation mode
- 🎬 **Demo Friendly** - Show without UI

### For Design
- 🖼️ **Full Canvas** - 100% viewport usage
- 📱 **True Responsive** - See actual breakpoints
- 🎭 **Real Context** - Content with styling
- 🌐 **Browser Native** - True rendering

---

## 🐛 Edge Cases Handled

### Case 1: Preview During Search
- ✅ Search closes automatically
- ✅ Returns to normal editor state
- ✅ No floating search bars in preview

### Case 2: Preview with Spotlight Active
- ✅ Spotlight deactivates (if in code tab)
- ✅ Full CSS visible in iframe
- ✅ Reactivates on exit if selector selected

### Case 3: Unsaved Changes
- ✅ Changes visible in preview
- ✅ Not committed until Save clicked
- ✅ Can exit preview without saving

### Case 4: Multiple Rapid Toggles
- ✅ Button states update correctly
- ✅ No transition glitches
- ✅ Clean enter/exit each time

---

## 🎨 Animation Details

### Floating Button Animation

**Float Effect:**
```css
@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-5px);
    }
}

/* Applied to button */
animation: float 3s ease-in-out infinite;
```

**Hover Transform:**
```css
.exit-preview-button:hover {
    transform: translateY(-2px) scale(1.05);
}
```

**Click Transform:**
```css
.exit-preview-button:active {
    transform: translateY(0) scale(1);
}
```

**Timing:**
- Float cycle: 3 seconds
- Hover transition: 0.3s cubic-bezier
- Shadow transition: 0.3s
- All smooth and natural

---

## 📏 Layout Calculations

### Preview Mode Layout

**Before (Editor Mode):**
```
┌─────────────────────────────────────────┐
│ Header (72px)                           │
├──────────┬──────────────────────────────┤
│ Sidebar  │ Preview                      │
│ (480px)  │ (calc(100% - 480px))         │
│          │                              │
│ Editor   │ iframe                       │
│ Panels   │ Website                      │
│          │                              │
└──────────┴──────────────────────────────┘
```

**After (Preview Mode):**
```
┌─────────────────────────────────────────┐
│              [Exit Preview] ← Floating  │
│                                         │
│                                         │
│              Preview (100%)             │
│              Website Full Screen        │
│                                         │
│                                         │
└─────────────────────────────────────────┘
```

**Changes:**
- Sidebar: `display: none`
- Header: `display: none`
- Preview: `width: 100%, height: 100vh`
- Main: `padding: 0`

---

## 🧪 Testing Checklist

- [x] Preview button appears in header
- [x] Click Preview → Editor hides
- [x] Click Preview → Floating button appears
- [x] Click Exit → Editor returns
- [x] Press ESC → Editor returns
- [x] Floating button animates (floats)
- [x] Floating button hover effect works
- [x] Website shows full-screen (100%)
- [x] CSS changes visible in preview
- [x] Can enter/exit multiple times
- [x] No layout glitches
- [x] All panels hide properly
- [x] All panels return properly

---

## 📝 Summary

**What It Does:**
- Hides all editor UI
- Shows website at 100% viewport
- Floating exit button appears
- One-click return to editor

**Why It's Useful:**
- See changes without distractions
- True full-screen preview
- Quick comparison tool
- Client presentation ready
- Easy QA workflow

**How It Works:**
- Click "Preview" button
- `.preview-mode` class added
- CSS hides editor panels
- Floating button shows
- ESC or click to exit

**Result:** 🎉 **Beautiful full-screen preview with elegant floating exit!**
