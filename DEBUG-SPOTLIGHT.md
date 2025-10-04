# Spotlight Media Query Debugging Guide

## Enhanced Logging Added

I've added comprehensive logging to help diagnose the spotlight media query issue. Here's what to look for in the browser console.

## How to Debug

### 1. Open Browser Console
- Press **F12** or **Ctrl+Shift+I**
- Go to **Console** tab
- Clear console (click 🚫 icon)

### 2. Perform These Steps

1. **Select a selector** (e.g., `.header` in Visual Editor)
2. **Switch to Code Editor tab** (spotlight should auto-activate)
3. **Click Tablet button**
4. **Check console output**

## Expected Console Output

### When Spotlight Activates
```
[SpotlightMode] ✅ SpotlightMode library loaded
[SpotlightMode] Auto-spotlight initialized
[SpotlightMode] 🔍 Updating spotlight for: .header Device: desktop
[SpotlightMode] Found 3 matches, filtering by device: desktop
[SpotlightMode] Match at line 0, scope: desktop
[SpotlightMode] ✅ Found desktop match (root CSS)
[SpotlightMode] 🔧 expandRangeForMediaQuery called: {
    originalRange: "Line 0 to 2",
    mediaQueryLine: null,
    scope: "desktop"
}
[SpotlightMode] ℹ️ Not in @media query - returning original range
[SpotlightMode] 📍 Found range: {
    from: "Line 0",
    to: "Line 2",
    lines: 3
}
```

### When Clicking Tablet Button
```
[SpotlightMode] 📱 Device changed from desktop to tablet
[SpotlightMode] 🔄 Updating spotlight for new device with selector: .header
[SpotlightMode] 🔍 Updating spotlight for: .header Device: tablet
[SpotlightMode] Found 3 matches, filtering by device: tablet
[SpotlightMode] Match at line 0, scope: desktop
[SpotlightMode] Match at line 5, scope: tablet
[SpotlightMode] ✅ Found tablet match (@media max-width: 1024px)
[SpotlightMode] 🔧 expandRangeForMediaQuery called: {
    originalRange: "Line 5 to 7",
    mediaQueryLine: 4,
    scope: "tablet"
}
[SpotlightMode] 📦 Expanding range to include @media query at line 4
[SpotlightMode] @media line content: "@media (max-width: 1024px) {"
[SpotlightMode] ✅ Expanded range: {
    from: "Line 4",
    to: "Line 8",
    totalLines: 5
}
[SpotlightMode] 📍 Found range: {
    from: "Line 4",
    to: "Line 8",
    lines: 5
}
```

## What to Check

### ❌ Problem 1: Device Not Changing
**Console shows:**
```
[SpotlightMode] ⚠️ Spotlight not active, skipping update
```
**Solution:** Make sure you're in Code Editor tab, not Visual Editor

### ❌ Problem 2: No Selector
**Console shows:**
```
[SpotlightMode] ⚠️ No selector selected, skipping update
```
**Solution:** Select an element first in Visual Editor

### ❌ Problem 3: Selector Not Found
**Console shows:**
```
[SpotlightMode] ❌ Could not find selector: .header
```
**Solution:** Make sure the selector exists in your CSS

### ❌ Problem 4: Wrong Scope Detection
**Console shows:**
```
[SpotlightMode] Match at line 5, scope: desktop
```
**Expected:**
```
[SpotlightMode] Match at line 5, scope: tablet
```
**Issue:** The `getDeviceScope()` method is not detecting @media query correctly

### ❌ Problem 5: @media Line Not Found
**Console shows:**
```
[SpotlightMode] 🔧 expandRangeForMediaQuery called: {
    mediaQueryLine: null,
    scope: "tablet"
}
```
**Issue:** `findMediaQueryLine()` is not finding the @media line

### ❌ Problem 6: Can't Find @media End
**Console shows:**
```
[SpotlightMode] ⚠️ Could not find end of @media block
```
**Issue:** Brace matching failed - check for syntax errors in CSS

## Test CSS Structure

Use this test CSS to verify it's working:

```css
.header {
    font-size: 20px;
    color: blue;
}

@media (max-width: 1024px) {
    .header {
        font-size: 18px;
        color: green;
    }
}

@media (max-width: 640px) {
    .header {
        font-size: 16px;
        color: red;
    }
}
```

## Logging Key

- 🔍 = Searching/Finding
- 📱 = Device change
- 🔄 = Updating
- 📦 = Expanding range
- 📍 = Final range
- 🔧 = Processing
- ✅ = Success
- ❌ = Error
- ⚠️ = Warning
- ℹ️ = Info

## What to Send Me

If it's still not working, **copy and paste** the console output showing:

1. The initial spotlight activation
2. The device button click
3. Any errors or warnings

Example format:
```
[Copy entire console output here]
```

## Quick Checks

✅ **Is spotlight active?**
- Look for: `[SpotlightMode] 🔍 Updating spotlight`

✅ **Is device changing?**
- Look for: `[SpotlightMode] 📱 Device changed from X to Y`

✅ **Are matches being found?**
- Look for: `[SpotlightMode] Found 3 matches`

✅ **Is scope detected correctly?**
- Look for: `[SpotlightMode] Match at line X, scope: tablet`

✅ **Is range expanding?**
- Look for: `[SpotlightMode] 📦 Expanding range`

✅ **Is @media line found?**
- Look for: `@media line content: "@media (max-width: 1024px)"`

## Next Steps

1. **Clear console** (🚫 button)
2. **Select a selector** in Visual Editor
3. **Switch to Code Editor**
4. **Click Tablet/Mobile buttons**
5. **Copy console output**
6. **Share with me**

This will help me identify exactly where the issue is!

---

**Debug Version**: Enhanced logging active  
**File**: `assets/js/spotlight-mode.js`  
**Status**: Ready for testing
