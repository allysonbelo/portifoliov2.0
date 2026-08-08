<?php
/**
 * Agent Discovery: Robots.txt & Content Signals
 * 
 * Filter for outputting Content-Signal directives in robots.txt.
 */

if (!defined('ABSPATH')) exit;

function abc_tech_custom_robots_txt_module($output, $public) {
    $site_url = home_url();
    $robots  = "User-agent: *\n";
    $robots .= "Allow: /\n";
    $robots .= "Content-Signal: ai-train=no, search=yes, ai-input=no\n";
    $robots .= "Content-Signal: ai-train=no\n";
    $robots .= "Content-Signal: search=yes\n";
    $robots .= "Content-Signal: ai-input=no\n\n";
    $robots .= "Sitemap: {$site_url}/sitemap_index.xml\n";
    return $robots;
}
add_filter('robots_txt', 'abc_tech_custom_robots_txt_module', 100, 2);
