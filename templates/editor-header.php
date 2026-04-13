<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LiveCSS Editor</title>
    <!-- <?php wp_head(); ?> -->
    <link rel="stylesheet" href="<?php echo plugins_url('assets/css/editor.css', dirname(__FILE__)); ?>?v=<?php echo defined('LIVECSS_VERSION') ? LIVECSS_VERSION : '2.0.0'; ?>">
</head>
<body>