<?php
/**
 * Agent Discovery: Link Response Headers (RFC 8288 & RFC 9727)
 * 
 * Exposes RFC 8288 Link headers and HTML <head> relation links for automated AI Agent discovery.
 */

if (!defined('ABSPATH')) exit;

/**
 * Remove WP Core default unquoted shortlink header to prevent RFC 8288 syntax errors
 */
remove_action('template_redirect', 'wp_shortlink_header', 11);

/**
 * 1. HTTP Response Link Headers (RFC 8288 & RFC 9727)
 */
function abc_tech_send_link_headers() {
    if (is_admin()) return;

    $api_catalog_url = esc_url(home_url('/.well-known/api-catalog'));
    $skills_url      = esc_url(home_url('/.well-known/agent-skills/index.json'));
    $mcp_url         = esc_url(home_url('/.well-known/mcp/server-card.json'));
    $oauth_url       = esc_url(home_url('/.well-known/oauth-protected-resource'));
    $auth_md_url     = esc_url(home_url('/auth.md'));
    $llms_url        = esc_url(home_url('/llms.txt'));

    $link_headers = array(
        '<' . $api_catalog_url . '>; rel="api-catalog"; type="application/linkset+json"',
        '<' . $skills_url . '>; rel="agent-skills"; type="application/json"',
        '<' . $mcp_url . '>; rel="mcp-server"; type="application/json"',
        '<' . $oauth_url . '>; rel="oauth-protected-resource"; type="application/json"',
        '<' . $auth_md_url . '>; rel="authorizing-agents"; type="text/markdown"',
        '<' . $llms_url . '>; rel="service-doc"; type="text/plain"'
    );

    header('Link: ' . implode(', ', $link_headers), false);
}
add_action('send_headers', 'abc_tech_send_link_headers');

/**
 * 2. HTML <head> Link Relations
 */
function abc_tech_render_head_link_relations() {
    if (is_admin()) return;
    ?>
    <!-- AI Agent Discovery Link Relations (RFC 8288 & RFC 9727) -->
    <link rel="api-catalog" href="<?php echo esc_url(home_url('/.well-known/api-catalog')); ?>" type="application/linkset+json">
    <link rel="mcp-server" href="<?php echo esc_url(home_url('/.well-known/mcp/server-card.json')); ?>" type="application/json">
    <link rel="agent-skills" href="<?php echo esc_url(home_url('/.well-known/agent-skills/index.json')); ?>" type="application/json">
    <link rel="authorizing-agents" href="<?php echo esc_url(home_url('/auth.md')); ?>" type="text/markdown">
    <link rel="service-doc" href="<?php echo esc_url(home_url('/llms.txt')); ?>" type="text/plain">
    <link rel="llms" href="<?php echo esc_url(home_url('/llms.txt')); ?>" type="text/plain">
    <?php
}
add_action('wp_head', 'abc_tech_render_head_link_relations', 1);
