<?php
/**
 * Agent Discovery & AI Readiness Master Loader
 * 
 * Includes modular agent discovery features:
 * - Link Response Headers (RFC 8288 & RFC 9727)
 * - WebMCP API Integration
 * - Markdown Negotiation for LLMs
 * - Robots.txt & Content Signals
 * - Well-Known Fallback Routes
 */

if (!defined('ABSPATH')) exit;

$dir = get_template_directory() . '/inc/agent-discovery/';

require_once $dir . 'link-headers.php';
require_once $dir . 'webmcp.php';
require_once $dir . 'markdown-negotiation.php';
require_once $dir . 'robots-signals.php';
require_once $dir . 'well-known-routes.php';
