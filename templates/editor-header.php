<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LiveCSS Editor</title>
    <?php wp_head(); ?>
<style>
    body{
        margin-top: -32px !important;
    }
    #wpadminbar {
        display: none !important;
    }
    :root {
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
        --radius: 0.5rem;
        --spacing: 1rem;
    }

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
        font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
        overflow: hidden;
    }
    input, select, textarea {
        color: black;}

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
        height: 60px;
    }

    .header h1 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: hsl(var(--foreground));
    }

    .header-actions {
        display: flex;
        gap: calc(var(--spacing) / 2);
    }

    .main-content {
        display: flex;
        flex: 1;
        overflow: hidden;
        height: calc(100vh - 60px);
    }

    .editor-panel {
        width: 400px;
        min-width: 350px;
        background: hsl(var(--card));
        border-right: 1px solid hsl(var(--border));
        display: flex;
        flex-direction: column;
        overflow: hidden;
        height: 100%;
    }

    .selector-section {
        background: hsl(var(--background));
        border-bottom: 1px solid hsl(var(--border));
        padding: var(--spacing);
    }

    .breadcrumb-section {
        padding: 0.75rem var(--spacing);
        background: hsl(var(--muted));
        border-bottom: 1px solid hsl(var(--border));
        font-size: 0.8rem;
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
        padding: 0.625rem 0.75rem;
        border: 1px solid hsl(var(--input));
        border-radius: calc(var(--radius) - 2px);
        font-size: 0.875rem;
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

    .pseudo-button {
        padding: 0.25rem 0.5rem;
        background: hsl(var(--secondary));
        color: hsl(var(--secondary-foreground));
        border: 1px solid hsl(var(--border));
        border-radius: calc(var(--radius) - 2px);
        cursor: pointer;
        font-size: 0.75rem;
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
    }

    .tab {
        padding: 0.75rem 1.25rem;
        cursor: pointer;
        border: none;
        background: none;
        font-weight: 500;
        font-size: 0.875rem;
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
        padding: var(--spacing);
        height: 100%;
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
        padding: 0.875rem 1rem;
        cursor: pointer;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background-color 0.2s;
        user-select: none;
        font-size: 0.875rem;
    }

    .accordion-header:hover {
        background: hsl(var(--accent));
    }

    .accordion-header::after {
        content: '+';
        font-size: 1.125rem;
        transition: transform 0.2s;
    }

    .accordion-header.active::after {
        content: '−';
    }

    .accordion-content {
        padding: 1rem;
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
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        font-size: 0.875rem;
        color: hsl(var(--foreground));
    }

    .control {
        width: 100%;
        padding: 0.625rem 0.75rem;
        border: 1px solid hsl(var(--input));
        border-radius: calc(var(--radius) - 2px);
        font-size: 0.875rem;
        background: hsl(var(--background));
        transition: border-color 0.2s;
    }

    .control:focus {
        outline: none;
        border-color: hsl(var(--ring));
        box-shadow: 0 0 0 1px hsl(var(--ring));
    }

    .control[type="color"] {
        padding: 0.125rem;
        height: 2.5rem;
        cursor: pointer;
    }

    .button {
        padding: 0.625rem 1.25rem;
        border: 1px solid transparent;
        border-radius: var(--radius);
        cursor: pointer;
        font-size: 0.875rem;
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

    .code-editor {
        height: 100%;
        min-height: 300px;
        border: 1px solid hsl(var(--border));
        border-radius: var(--radius);
        overflow: hidden;
        position: relative;
    }
    
    /* CodeMirror specific styles */
    .CodeMirror {
        height: 100% !important;
        min-height: 300px;
    }
    
    .CodeMirror-scroll {
        min-height: 300px;
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
            height: 50%;
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
        font-size: 0.875rem;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border: 1px solid hsl(var(--border));
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
</style>
</head>
<body>