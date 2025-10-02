<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LiveCSS Editor</title>
    <!-- <?php wp_head(); ?> -->
<style>
    
#wpadminbar {
        display: none !important;
    }
    body{
        margin: 0;
        padding: 0;
    }
    
editor{
      font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
    
    /* :root { */
        --background: 0 0% 100%;
        --foreground: 0 0% 3.9%;
        --card: 0 0% 100%;
        --card-foreground: 0 0% 3.9%;
        --popover: 0 0% 100%;
        --popover-foreground: 0 0% 3.9%;
        --primary: 0 0% 9%;
        --primary-foreground: 0 0% 98%;
        --secondary: 0 0% 96.1%;
        --secondary-foreground: 0 0% 9%;
        --muted: 0 0% 96.1%;
        --muted-foreground: 0 0% 45.1%;
        --accent: 0 0% 96.1%;
        --accent-foreground: 0 0% 9%;
        --destructive: 0 0% 50%;
        --destructive-foreground: 0 0% 98%;
        --border: 0 0% 89.8%;
        --input: 0 0% 89.8%;
        --ring: 0 0% 3.9%;
        --radius: 0.6rem;
        --spacing: 1.25rem; /* Larger base spacing for a more spacious UI */
        --zoom-mini: 0.75;
    /* } */

    * {
        box-sizing: border-box;
        border-color: hsl(var(--border));
    }

    body, html {
        margin: 0;
        padding: 0;
        height: 100vh;
        color: hsl(var(--foreground));
        background-color: hsl(var(--background));
      overflow: hidden;
    }
    .editor-container {
        display: flex;
        flex-direction: column;
        height: 100vh;
        background: hsl(var(--background));
    }

    .header {
        background: hsl(var(--background));
        border-bottom: 1px solid hsl(var(--border));
        padding: var(--spacing) calc(var(--spacing) * 1.5);
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 72px; /* Taller header for better touch targets */
        zoom: var(--zoom-mini);
    }

    .header h1 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
        color: hsl(var(--foreground));
    }

    .header-actions {
        display: flex;
        gap: var(--spacing);
    }

    /* Device toggle */
    .device-toggle {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-right: auto; /* push actions to the right */
        margin-left: 1rem;
    }
    .device-btn {
        padding: 0.5rem 0.75rem;
        border: 1px solid hsl(var(--border));
        background: hsl(var(--background));
        border-radius: calc(var(--radius) - 2px);
        cursor: pointer;
        font-size: 0.9rem;
        color: hsl(var(--foreground));
    }
    .device-btn:hover { background: hsl(var(--accent)); }
    .device-btn.active { background: hsl(var(--secondary)); }

    .main-content {
        display: flex;
        flex: 1;
        overflow: hidden;
        height: calc(100vh - 72px);
    }

    .editor-panel {
        width: 480px; /* Wider sidebar */
        min-width: 420px;
        max-width: 60vw; /* Prevent over-expansion */
        background: hsl(var(--card));
        border-right: 1px solid hsl(var(--border));
        display: flex;
        flex-direction: column;
        overflow: auto; /* Required for resize to work */
        height: 100%;
        /* We’ll use our own resizer handle for better control */
        transition: width 0.2s ease;
    }

    /* Collapsed sidebar state */
    .editor-panel.is-collapsed {
        width: 60px !important;
        min-width: 60px !important;
        overflow: hidden;
    }

    /* Vertical resizer between sidebar and preview */
    .sidebar-resizer {
        width: 10px;
        background: hsl(var(--muted));
        border-right: 1px solid hsl(var(--border));
        border-left: 1px solid hsl(var(--border));
        cursor: col-resize;
        position: relative;
        z-index: 4;
    }
    .sidebar-resizer::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 3px;
        height: 28px;
        border-radius: 2px;
        background: hsl(var(--input));
        box-shadow: -3px 0 0 hsl(var(--input)), 3px 0 0 hsl(var(--input));
        opacity: 0.9;
        pointer-events: none;
    }
    .sidebar-resizer:focus-visible {
        outline: 2px solid hsl(var(--ring));
        outline-offset: -2px;
    }

    .selector-section {
        background: hsl(var(--background));
        border-bottom: 1px solid hsl(var(--border));
        padding: var(--spacing);
        zoom: var(--zoom-mini);
    }

    .breadcrumb-section {
        padding: 0.75rem var(--spacing);
        background: hsl(var(--muted));
        border-bottom: 1px solid hsl(var(--border));
        font-size: 0.95rem;
        color: hsl(var(--muted-foreground));
        white-space: nowrap;
        overflow-x: auto;
    }

    .breadcrumb-part-group {
        display: inline-block;
    }

    .breadcrumb-item {
        cursor: pointer;
        color: hsl(var(--accent-foreground));
        transition: color 0.2s, background-color 0.2s;
        padding: 0.1rem 0.4rem;
        border-radius: 4px;
        margin: 0 1px;
        display: inline-block;
        border: 1px solid transparent;
    }

    .breadcrumb-item:hover {
        color: hsl(var(--foreground));
        background-color: hsl(var(--accent));
        border-color: hsl(var(--border));
        text-decoration: none;
    }

    .selector-input {
        width: 100%;
        padding: 0.75rem 0.9rem;
        border: 1px solid hsl(var(--input));
        border-radius: calc(var(--radius) - 2px);
        font-size: 1rem;
        margin-bottom: calc(var(--spacing) / 2);
        background: hsl(var(--background));
        transition: border-color 0.2s;
        color: black;
    }
    option{
        color: black;
    }
    .selector-input:focus {
        outline: none;
        border-color: hsl(var(--ring));
        box-shadow: 0 0 0 1px hsl(var(--ring));
    }

    .pseudo-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 0.375rem;
    }

    /* Selector suggestions */
    .selector-suggest {
        position: absolute;
        margin-top: 4px;
        width: calc(100% - 2 * var(--spacing));
        max-height: 280px;
        overflow: auto;
        background: hsl(var(--background));
        border: 1px solid hsl(var(--border));
        border-radius: var(--radius);
        box-shadow: 0 12px 24px rgba(0,0,0,.12);
        z-index: 10;
    }
    .selector-suggest.hidden { display: none; }
    .selector-suggest-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        font-size: 0.95rem;
        color: hsl(var(--foreground));
    }
    .selector-suggest-item:hover,
    .selector-suggest-item.active {
        background: hsl(var(--accent));
    }
    .selector-suggest-type {
        font-size: 0.75rem;
        color: hsl(var(--muted-foreground));
        margin-right: 0.5rem;
        min-width: 44px;
        text-transform: uppercase;
    }
    .selector-suggest-text {
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    }
    .selector-suggest-count {
        font-size: 0.75rem;
        color: hsl(var(--muted-foreground));
        margin-left: 0.5rem;
    }

    .pseudo-button {
        padding: 0.35rem 0.6rem;
        background: hsl(var(--secondary));
        color: hsl(var(--secondary-foreground));
        border: 1px solid hsl(var(--border));
        border-radius: calc(var(--radius) - 2px);
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.2s;
    }

    .pseudo-button:hover {
        background: hsl(var(--accent));
        color: hsl(var(--accent-foreground));
    }

    .tabs {
        display: flex;
        background: hsl(var(--muted));
        border-bottom: 1px solid hsl(var(--border));
        position: sticky; /* Keep tabs visible while scrolling controls */
        top: 0;
        z-index: 3;
        zoom: var(--zoom-mini);
    }

    .tab {
        padding: 1rem 1.25rem;
        cursor: pointer;
        border: none;
        background: none;
        font-weight: 500;
        font-size: 1rem;
        color: hsl(var(--muted-foreground));
        transition: all 0.2s;
        position: relative;
    }

    .tab:hover {
        color: hsl(var(--foreground));
        background: hsl(var(--accent));
    }

    .tab.active {
        color: hsl(var(--foreground));
        background: hsl(var(--background));
    }

    .tab.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: hsl(var(--foreground));
    }

    .tab-content {
        flex: 1;
        overflow-y: auto;
        padding: calc(var(--spacing) * 1.25);
        height: 100%;
        zoom: var(--zoom-mini);
    }

    .tab-content.hidden {
        display: none;
    }

    .accordion-item {
        background: hsl(var(--card));
        border: 1px solid hsl(var(--border));
        border-radius: var(--radius);
        margin-bottom: var(--spacing);
        overflow: hidden;
    }

    .accordion-header {
        background: hsl(var(--background));
        padding: 1rem 1.25rem;
        cursor: pointer;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background-color 0.2s;
        user-select: none;
        font-size: 1rem;
        position: relative; /* for usage-dot positioning */
    }

    .accordion-header:hover {
        background: hsl(var(--accent));
    }

    .accordion-header::after {
        /* content: '+'; */
        font-size: 1.125rem;
        transition: transform 0.2s;
    }

    .accordion-header.active::after {
        /* content: '−'; */
    }

    .accordion-content {
        padding: 1.1rem 1.25rem;
        border-top: 1px solid hsl(var(--border));
        display: none;
    }

    .accordion-content.active {
        display: block;
    }

    .control-group {
        margin-bottom: 1rem;
    }

    .control-label {
        display: flex; /* allow dot to align to the right */
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        font-weight: 500;
        font-size: 1rem;
        color: hsl(var(--foreground));
    }

    .control {
        width: 100%;
        padding: 0.75rem 0.9rem;
        border: 1px solid hsl(var(--input));
        border-radius: calc(var(--radius) - 2px);
        font-size: 1rem;
        background: hsl(var(--background));
        transition: border-color 0.2s;
        color:black;
    }

    .control:focus {
        outline: none;
        border-color: hsl(var(--ring));
        box-shadow: 0 0 0 2px hsl(var(--ring) / 0.25);
    }

    .control[type="color"] {
        padding: 0.125rem;
        height: 3rem;
        cursor: pointer;
    }

    .button {
        padding: 0.75rem 1.5rem;
        border: 1px solid transparent;
        border-radius: var(--radius);
        cursor: pointer;
        font-size: 1rem;
        font-weight: 500;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .button-primary {
        background: hsl(var(--primary));
        color: hsl(var(--primary-foreground));
        border-color: hsl(var(--primary));
    }

    .button-primary:hover {
        background: hsl(var(--primary) / 0.9);
        border-color: hsl(var(--primary) / 0.9);
    }

    .button-danger {
        background: hsl(var(--destructive));
        color: hsl(var(--destructive-foreground));
        border-color: hsl(var(--destructive));
    }

    .button-danger:hover {
        background: hsl(var(--destructive) / 0.9);
        border-color: hsl(var(--destructive) / 0.9);
    }

    .preview-area {
        flex: 1;
        position: relative;
        background: hsl(var(--background));
    }

    .preview-iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    /* Center iframe when a fixed width is applied for tablet/mobile preview */
    .preview-area { display: flex; align-items: stretch; justify-content: center; }
    .preview-iframe { flex: 0 0 auto; }

    /* Usage dots */
    .usage-dot {
        
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 14px;
        height: 14px;
        min-width: 14px;
        border-radius: 50%;
        background: #0ea5e9; /* blue-500 */
        color: transparent; /* text hidden by default */
        font-size: 11px;
        font-weight: 700;
        line-height: 1;
        box-shadow: 0 0 0 2px hsl(var(--background));
        cursor: pointer;
        margin-left: 0.5rem;
        position: relative;
        transition: background-color .15s ease, color .15s ease, transform .1s ease;
    }
    .usage-dot--section {
        margin-left: 0.75rem;
    }
    .usage-dot:hover,
    .usage-dot:focus-visible {
        background: #ef4444; /* red-500 */
        color: #fff; /* show X */
        outline: none;
        transform: scale(1.05);
    }
    .usage-dot::before {
        content: '';
    }
    .usage-dot:hover::before,
    .usage-dot:focus-visible::before {
        content: '×';
    }

    .code-editor {
        height: 100%;
        min-height: 450px; /* Bigger code area for easier editing */
        border: 1px solid hsl(var(--border));
        border-radius: var(--radius);
        overflow: hidden;
        position: relative;
    }
    
    /* CodeMirror specific styles */
    .CodeMirror {
        height: 100% !important;
        min-height: 450px;
        font-size: 1rem; /* Make code text easier to read */
    }
    
    .CodeMirror-scroll {
        min-height: 450px;
    }

    .element-highlight {
        outline: 2px solid hsl(var(--primary)) !important;
        outline-offset: -2px;
    }

    @media (max-width: 768px) {
        .main-content {
            flex-direction: column;
        }

        .editor-panel {
            width: 100%;
            height: 60%;
            border-right: none;
            border-bottom: 1px solid hsl(var(--border));
        }
    }

    .status-message {
        position: fixed;
        top: 70px;
        right: 20px;
        padding: 0.75rem 1rem;
        border-radius: var(--radius);
        color: hsl(var(--primary-foreground));
        z-index: 1000;
        opacity: 0;
        transform: translateY(-1rem);
        transition: all 0.2s;
        font-size: 1rem;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border: 1px solid hsl(var(--border));
    }


    /* Nicer scrollbars (where supported) */
    .editor-panel::-webkit-scrollbar, .tab-content::-webkit-scrollbar {
        width: 10px;
    }
    .editor-panel::-webkit-scrollbar-track, .tab-content::-webkit-scrollbar-track {
        background: hsl(var(--muted));
    }
    .editor-panel::-webkit-scrollbar-thumb, .tab-content::-webkit-scrollbar-thumb {
        background: hsl(var(--input));
        border-radius: 10px;
        border: 2px solid hsl(var(--muted));
    }

    .status-message.show {
        opacity: 1;
        transform: translateY(0);
    }

    .status-message.success {
        background: hsl(var(--background));
        color: hsl(var(--foreground));
        border-color: hsl(var(--border));
    }

    .status-message.error {
        background: hsl(var(--destructive));
        color: hsl(var(--destructive-foreground));
        border-color: hsl(var(--destructive));
    }

    /* Confirmation Popup */
    .popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s;
    }

    .popup-overlay.visible {
        opacity: 1;
        visibility: visible;
    }

    .popup-content {
        background: hsl(var(--background));
        padding: 1.5rem 2rem;
        border-radius: var(--radius);
        width: 90%;
        max-width: 450px;
        text-align: center;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        transform: scale(0.95);
        transition: all 0.2s;
    }

    .popup-overlay.visible .popup-content {
        transform: scale(1);
    }

    .popup-content p {
        margin-top: 0;
        margin-bottom: 1.5rem;
        font-size: 1rem;
        color: hsl(var(--foreground));
    }

    .popup-actions {
        display: flex;
        justify-content: center;
        gap: 1rem;
    }}
</style>
</head>
<body>