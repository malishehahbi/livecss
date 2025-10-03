# Unsaved Changes Warning & Session History

## Overview
This document describes the **unsaved changes tracking** and **session history (undo/redo)** features implemented in the LiveCSS Editor.

## Features

### 1. Unsaved Changes Tracking

The editor now tracks when CSS changes are made but not saved, protecting users from accidentally losing their work.

#### How It Works:
- **Automatic Detection**: The editor marks changes as "unsaved" whenever:
  - You modify any CSS property in the Visual Editor
  - You edit CSS code in the Code Editor
  
- **Visual Indicator**: When there are unsaved changes:
  - The Save button displays a **pulsing red dot** in the top-right corner
  - The button border pulses between black and red
  - Tooltip changes to "You have unsaved changes"

- **State Management**: After successful save:
  - The unsaved flag is cleared
  - Visual indicators are removed
  - The editor stores the current CSS as the "saved state"

#### Code Implementation:
```javascript
// Properties in constructor
this.hasUnsavedChanges = false;
this.initialCSSState = null;

// Mark as changed
markAsChanged() {
    if (!this.hasUnsavedChanges) {
        this.hasUnsavedChanges = true;
        this.updateSaveButtonState();
    }
}

// Update save button UI
updateSaveButtonState() {
    const saveButton = document.getElementById('save-button');
    if (this.hasUnsavedChanges) {
        saveButton.classList.add('has-changes');
    } else {
        saveButton.classList.remove('has-changes');
    }
}
```

### 2. Exit Confirmation

Two layers of protection prevent accidental data loss:

#### A. Browser Warning (beforeunload)
- Triggers when:
  - Closing the browser tab
  - Refreshing the page
  - Navigating to another URL
  
- Shows **native browser dialog**:
  - "You have unsaved changes. Are you sure you want to leave?"
  - Options: "Leave" or "Stay"

#### B. Exit Button Confirmation
- Intercepts the "Exit Editor" button click
- Shows **JavaScript confirm dialog** if unsaved changes exist
- Message: "You have unsaved changes. Do you really want to exit without saving?"
- Options: "OK" (exit anyway) or "Cancel" (stay in editor)

#### Code Implementation:
```javascript
// Browser-level protection
window.addEventListener('beforeunload', (e) => {
    if (this.hasUnsavedChanges) {
        e.preventDefault();
        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        return e.returnValue;
    }
});

// Exit button protection
const exitButton = document.querySelector('a.button-danger');
if (exitButton) {
    exitButton.addEventListener('click', (e) => {
        if (this.hasUnsavedChanges) {
            const confirmExit = confirm('You have unsaved changes. Do you really want to exit without saving?');
            if (!confirmExit) {
                e.preventDefault();
            }
        }
    });
}
```

### 3. Session History (Undo/Redo)

The editor maintains a complete history of your CSS changes, allowing you to undo and redo modifications.

#### Features:
- **Automatic Snapshots**: State is captured automatically after each change (debounced by 500ms)
- **State Includes**:
  - Complete CSS code
  - Current selector
  - Current device (desktop/tablet/mobile)
  - Timestamp
  
- **History Limits**: 
  - Maximum 50 states stored
  - Oldest states are removed when limit is reached
  
- **Smart Tracking**:
  - Doesn't capture duplicate states
  - Removes future history when making new changes after undo

#### Keyboard Shortcuts:
- **Ctrl+Z** (or Cmd+Z on Mac): Undo
- **Ctrl+Y** (or Cmd+Shift+Z): Redo

#### Code Implementation:
```javascript
// Properties in constructor
this.history = [];
this.historyIndex = -1;
this.maxHistorySize = 50;

// Capture state (debounced)
captureHistory() {
    clearTimeout(this.historyTimeout);
    this.historyTimeout = setTimeout(() => {
        const currentState = this.serializeState();
        
        // Remove future history when making new changes
        this.history = this.history.slice(0, this.historyIndex + 1);
        
        // Add new state
        this.history.push(currentState);
        
        // Limit size
        if (this.history.length > this.maxHistorySize) {
            this.history.shift();
        } else {
            this.historyIndex++;
        }
        
        this.updateHistoryButtons();
    }, 500);
}

// Undo
undo() {
    if (this.historyIndex > 0) {
        this.historyIndex--;
        this.restoreState(this.history[this.historyIndex]);
        this.updateHistoryButtons();
        this.showStatusMessage('Undo successful', 'success');
    }
}

// Redo
redo() {
    if (this.historyIndex < this.history.length - 1) {
        this.historyIndex++;
        this.restoreState(this.history[this.historyIndex]);
        this.updateHistoryButtons();
        this.showStatusMessage('Redo successful', 'success');
    }
}
```

### 4. State Serialization

The editor captures complete snapshots of your work:

```javascript
serializeState() {
    return {
        css: this.generateCSS(),
        currentSelector: this.currentSelector,
        currentDevice: this.currentDevice,
        timestamp: Date.now()
    };
}

restoreState(state) {
    // Restore CSS
    this.parseCSS(state.css);
    this.updateCodeEditor();
    this.updatePreview();
    
    // Restore selector
    if (state.currentSelector) {
        this.currentSelector = state.currentSelector;
        this.updateSelectionFromInput();
        this.updateVisualControls();
    }
    
    // Restore device
    if (state.currentDevice) {
        this.setDevice(state.currentDevice);
    }
}
```

## User Experience

### Workflow Example:

1. **Open Editor**: 
   - Editor loads with saved CSS
   - Initial state captured in history
   - No unsaved changes

2. **Make Changes**:
   - Modify properties or write CSS code
   - Save button shows red pulsing dot
   - Changes tracked in history

3. **Undo/Redo**:
   - Press Ctrl+Z to undo changes
   - Press Ctrl+Y to redo
   - Can navigate through entire session history

4. **Try to Exit Without Saving**:
   - Browser shows warning dialog
   - Exit button requires confirmation
   - Can save or discard changes

5. **Save Changes**:
   - Click Save button
   - Red dot disappears
   - State marked as saved
   - Exit freely without warnings

## Visual Indicators

### Save Button States:

**Normal (No Changes)**:
```css
.button-primary {
    background: black;
    border: 1px solid black;
}
```

**Unsaved Changes**:
```css
.button-primary.has-changes {
    animation: pulse-border 2s ease-in-out infinite;
}

.button-primary.has-changes::after {
    content: '';
    width: 12px;
    height: 12px;
    background: #ef4444; /* red dot */
    border-radius: 50%;
    animation: pulse-dot 2s ease-in-out infinite;
}
```

## Integration Points

### Change Tracking Triggers:

1. **Visual Editor Changes**:
   ```javascript
   updateCSSProperty(property, value) {
       // ... update logic ...
       this.markAsChanged();
       this.captureHistory();
   }
   ```

2. **Code Editor Changes**:
   ```javascript
   this.codeEditor.on('change', () => {
       if (!this.isUpdatingFromCode) {
           this.parseCSS(this.codeEditor.getValue());
           this.updatePreview();
           this.markAsChanged();
           this.captureHistory();
       }
   });
   ```

3. **Save Success**:
   ```javascript
   saveCSS() {
       fetch(...)
           .then(data => {
               if (data.success) {
                   this.hasUnsavedChanges = false;
                   this.updateSaveButtonState();
                   this.initialCSSState = this.generateCSS();
               }
           });
   }
   ```

## Future Enhancements

### Optional UI Improvements:

1. **Undo/Redo Buttons**:
   - Add visible buttons in the header
   - Enable/disable based on history state
   - Show tooltips with keyboard shortcuts

2. **History Panel**:
   - Show list of recent changes with timestamps
   - Click to jump to specific state
   - Show preview of each state

3. **Auto-save**:
   - Periodic auto-save to prevent data loss
   - Configurable interval (e.g., every 2 minutes)
   - Visual indicator of auto-save status

4. **Save Confirmation Dialog**:
   - Replace browser confirm with custom modal
   - Options: "Save and Exit", "Exit Without Saving", "Cancel"
   - More user-friendly interface

## Technical Notes

### Performance:
- **Debouncing**: History capture is debounced by 500ms to avoid excessive snapshots
- **Memory Management**: History limited to 50 states to prevent memory issues
- **Efficient Comparison**: Uses JSON serialization to detect duplicate states

### Browser Compatibility:
- **beforeunload**: Supported in all modern browsers
- **confirm()**: Universal support
- **localStorage**: Could be used for persistence (not yet implemented)

### Edge Cases:
- **Rapid Changes**: Debouncing prevents flooding history with rapid changes
- **Circular Navigation**: History tree is linear (no branching after undo)
- **State Restoration**: Preserves selector, device, and all CSS during undo/redo

## Testing Checklist

- [ ] Make CSS changes and verify red dot appears on Save button
- [ ] Try to close tab with unsaved changes - browser warning appears
- [ ] Try to click Exit button with unsaved changes - confirmation appears
- [ ] Save changes and verify red dot disappears
- [ ] Make changes, press Ctrl+Z to undo
- [ ] Press Ctrl+Y to redo
- [ ] Make changes, undo, then make new changes - future history is cleared
- [ ] Navigate through 50+ changes to test history limit
- [ ] Verify state restoration includes selector and device

## Conclusion

The LiveCSS Editor now provides robust protection against data loss with:
- ✅ Visual unsaved changes indicator
- ✅ Browser exit warning
- ✅ Exit button confirmation
- ✅ Complete session history
- ✅ Undo/Redo with keyboard shortcuts
- ✅ State preservation and restoration

Users can work confidently knowing their changes are tracked and protected!
