# Quick Testing Guide - Property Dependencies

## How to Test

### Quick Start
1. Open your WordPress site with the LiveCSS plugin active
2. Open the LiveCSS Editor
3. Check the browser console - you should see:
   ```
   [PropertyDependencies] Initializing...
   [PropertyDependencies] Found XX dependent elements
   [PropertyDependencies] Initialization complete
   [PropertyDependencies] Stats: {parentProperties: 9, totalDependencies: 47, cachedProperties: 9}
   ```

### Test #1: Position Dependencies ⭐ CRITICAL
**What to test**: Top/Right/Bottom/Left/Z-Index should only show when position is NOT static

1. Go to **Layout** section
2. Observe that Top, Right, Bottom, Left, Z-Index are **HIDDEN** (position is empty/default)
3. Change Position to **"Relative"**
   - ✅ Top/Right/Bottom/Left/Z-Index should **appear**
4. Change Position to **"Static"**
   - ✅ Those properties should **hide again**
5. Try **"Absolute"**, **"Fixed"**, **"Sticky"**
   - ✅ Properties should stay **visible**

**Expected behavior**: Positioning properties only show when position allows positioning.

---

### Test #2: Flexbox Dependencies ⭐ CRITICAL
**What to test**: Flex properties only show when display is flex

1. Go to **Layout** section
2. Observe that Flex Direction, Flex Wrap, Flex Grow/Shrink/Basis are **HIDDEN**
3. Change Display to **"Flex"**
   - ✅ Flex Direction, Flex Wrap should **appear**
   - ✅ Justify Content, Align Items, Align Content should **appear**
   - ✅ Float and Clear should **HIDE** (they don't work with flex)
4. Scroll down to find Flex Grow, Flex Shrink, Flex Basis
   - ✅ These should also be **visible**
5. Change Display back to **"Block"**
   - ✅ All flex properties should **hide**
   - ✅ Float and Clear should **appear again**

**Expected behavior**: Flex properties only show in flex context. Float/clear hide in flex.

---

### Test #3: Grid Dependencies ⭐ CRITICAL
**What to test**: Grid properties only show when display is grid

1. Go to **Layout** section
2. Observe that Grid Template Columns/Rows, Grid Column/Row are **HIDDEN**
3. Change Display to **"Grid"**
   - ✅ Grid Template Columns/Rows should **appear**
   - ✅ Grid Column, Grid Row should **appear**
   - ✅ Grid Auto Flow, Grid Auto Columns/Rows should **appear**
   - ✅ Gap, Row Gap, Column Gap should **appear**
   - ✅ Float and Clear should **HIDE**
4. Change Display back to **"Block"**
   - ✅ All grid properties should **hide**

**Expected behavior**: Grid properties only show in grid context.

---

### Test #4: Background Dependencies
**What to test**: Background properties only show when background-image is set

1. Go to **Background** section
2. Find Background Size, Background Position, Background Repeat, etc.
3. These should be **HIDDEN** initially
4. In **Background Image** field, type: `url('test.jpg')`
   - ✅ Background Size, Position, Repeat, Attachment, Clip, Origin, Blend Mode should all **appear**
5. Clear the Background Image field
   - ✅ Those properties should **hide again**
6. Try **Background Gradient** field: `linear-gradient(to right, red, blue)`
   - ✅ Background properties should **appear** (same background-image property)

**Expected behavior**: Background manipulation properties only show when there's an image/gradient.

---

### Test #5: Animation Dependencies
**What to test**: Animation timing properties only show when animation-name is set

1. Go to **Transitions & Animations** section
2. Find Animation Duration, Timing Function, Delay, etc.
3. These should be **HIDDEN** initially
4. In **Animation Name** field, type: `fadeIn`
   - ✅ All animation properties should **appear**:
     - Animation Duration
     - Animation Timing Function
     - Animation Delay
     - Animation Iteration Count
     - Animation Direction
     - Animation Fill Mode
     - Animation Play State
5. Clear Animation Name
   - ✅ All those properties should **hide**

**Expected behavior**: Animation configuration only shows when animation is named.

---

### Test #6: Transition Dependencies
**What to test**: Transition timing only shows when transition-property is set

1. Go to **Transitions & Animations** section
2. Find Transition Duration, Timing Function, Delay
3. These should be **HIDDEN** initially
4. In **Transition Property** field, type: `all`
   - ✅ Transition Duration, Timing Function, Delay should **appear**
5. Clear Transition Property
   - ✅ Those properties should **hide**

**Expected behavior**: Transition timing only relevant when property to transition is specified.

---

### Test #7: Transform Dependencies
**What to test**: Transform-origin only shows when transform is set

1. Go to **Transform** section
2. Transform Origin should be **HIDDEN** initially
3. In **Transform** field, type: `rotate(45deg)`
   - ✅ Transform Origin should **appear**
4. Clear Transform field
   - ✅ Transform Origin should **hide**

**Expected behavior**: Transform origin only matters when transform is applied.

---

### Test #8: Border Dependencies
**What to test**: Border color/width only show when border-style is not none

1. Go to **Borders** section
2. Border Color and Border Width should be **HIDDEN** initially (no border style)
3. Change **Border Style** to **"Solid"**
   - ✅ Border Color and Border Width should **appear**
4. Change Border Style to **"None"**
   - ✅ Border Color and Width should **hide**
5. Try other styles (Dashed, Dotted, Double)
   - ✅ Color and Width should stay **visible**

**Expected behavior**: Border appearance properties only show when border style is set.

---

## Visual Indicators

### What to Look For
- **Hidden properties**: `display: none;` and class `property-hidden`
- **Visible properties**: Normal display and class `property-visible`
- **Smooth transitions**: Properties appear/disappear instantly (no animation by design)

### Console Debugging
Open browser DevTools console and type:
```javascript
// Get current stats
window.getPropertyDependencyStats()

// Manually refresh all dependencies
window.refreshPropertyDependencies()

// Access the manager
window.propertyDependencyManager
```

---

## Common Issues & Solutions

### Issue: Properties not hiding/showing
**Solution**: 
1. Check console for "[PropertyDependencies]" messages
2. Verify initialization completed
3. Check that parent property has `data-property` attribute
4. Verify dependent property has `data-depends-on` and `data-depends-value` attributes

### Issue: Script not loading
**Solution**:
1. Check browser console for 404 errors
2. Verify `property-dependencies.js` exists in `assets/js/` folder
3. Check that `editor-js.php` includes the script tag correctly

### Issue: Dependencies not evaluating
**Solution**:
1. Open console and run: `window.refreshPropertyDependencies()`
2. Check that you're changing the right parent property
3. Verify the data-depends-value syntax is correct

---

## Expected Console Output

### On Page Load
```
[PropertyDependencies] Initializing...
[PropertyDependencies] Found 47 dependent elements
[PropertyDependencies] Initialization complete
[PropertyDependencies] Stats: { parentProperties: 9, totalDependencies: 47, cachedProperties: 9 }
```

### On Property Change
(Silent - no console output for performance)

### On Manual Refresh
```javascript
window.refreshPropertyDependencies()
// Output: [PropertyDependencies] Manual refresh triggered
```

---

## Success Criteria

✅ **All tests pass**: All 8 test scenarios work as expected  
✅ **No console errors**: No JavaScript errors in browser console  
✅ **Performance**: Property changes feel instant (<50ms)  
✅ **UX**: Editor feels more intuitive with less clutter  
✅ **Stats correct**: `getPropertyDependencyStats()` shows expected numbers

---

## Rollback Plan (if needed)

If issues occur, you can temporarily disable the system:

1. **Quick disable**: Comment out the script include in `editor-js.php`:
   ```php
   <!-- Property Dependencies Manager -->
   <!-- <script src="<?php echo plugins_url('assets/js/property-dependencies.js', dirname(__FILE__)); ?>"></script> -->
   ```

2. **Full rollback**: Remove all `data-depends-on` and `data-depends-value` attributes from `editor-content.php`

---

**Testing Time**: ~10-15 minutes for all tests  
**Priority**: Tests #1-3 are critical (position, flex, grid)  
**Status**: Ready for testing ✅
