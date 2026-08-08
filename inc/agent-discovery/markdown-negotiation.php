<?php
/**
 * Agent Discovery: Markdown Negotiation (Accept: text/markdown)
 * 
 * Dynamic Markdown response generator for agents requesting text/markdown content.
 */

if (!defined('ABSPATH')) exit;

function abc_tech_handle_markdown_negotiation_module() {
    if (is_admin()) return;

    $accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
    $is_md = (strpos(strtolower($accept), 'text/markdown') !== false) || isset($_GET['markdown']);

    if (!$is_md) return;

    // Cache Bypass for WP-Rocket, LiteSpeed, W3TC, AccelerateWP, etc.
    if (!defined('LSCACHE_NO_CACHE')) define('LSCACHE_NO_CACHE', true);
    if (!defined('DONOTCACHEPAGE')) define('DONOTCACHEPAGE', true);
    if (!defined('DONOTCACHEOBJECT')) define('DONOTCACHEOBJECT', true);
    if (!defined('DONOTCACHEDB')) define('DONOTCACHEDB', true);

    header('X-LiteSpeed-Cache-Control: no-cache');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Vary: Accept');
    header('Content-Type: text/markdown; charset=utf-8');
    header('Access-Control-Allow-Origin: *');

    $site_name = get_bloginfo('name');
    $site_desc = get_bloginfo('description');
    $req_uri   = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    $current_url = esc_url(home_url($req_uri));

    $md  = "# {$site_name}\n\n";
    $md .= "> {$site_desc}\n\n";
    $md .= "**URL Canonical:** {$current_url}\n\n";
    $md .= "---\n\n";

    // Try to resolve URL to post ID
    $post_id = url_to_postid($current_url);
    if ($post_id) {
        $post = get_post($post_id);
        if ($post) {
            $title = get_the_title($post);
            $content = wp_strip_all_tags(apply_filters('the_content', $post->post_content));
            $md .= "## {$title}\n\n";
            if (has_excerpt($post)) {
                $md .= "> " . wp_strip_all_tags(get_the_excerpt($post)) . "\n\n";
            }
            $md .= "{$content}\n\n";
        }
    } else {
        $md .= "## Visão Geral do Site\n\n";
        $md .= "Allyson Belo - Arquiteto WordPress & Especialista em SEO Técnico.\n";
        $md .= "Desenvolvimento de temas sob medida de alta performance, arquitetura limpa em PHP e otimização Core Web Vitals.\n\n";
        
        $md .= "### Últimos Projetos\n\n";
        $projects = get_posts(array('post_type' => 'project', 'posts_per_page' => 10));
        if (!empty($projects)) {
            foreach ($projects as $proj) {
                $excerpt = wp_strip_all_tags(get_the_excerpt($proj));
                $md .= "- **[" . get_the_title($proj) . "](" . get_permalink($proj) . ")**: {$excerpt}\n";
            }
            $md .= "\n";
        }
        
        $md .= "### Links Principais\n";
        $md .= "- [Início](" . home_url('/') . ")\n";
        $md .= "- [Projetos](" . home_url('/projetos/') . ")\n";
        $md .= "- [Sobre](" . home_url('/sobre/') . ")\n";
        $md .= "- [Contato](" . home_url('/contato/') . ")\n\n";
    }

    $md .= "---\n\n";
    $md .= "## Discovery & WP REST API Endpoints\n\n";
    $md .= "| Recurso | URL |\n";
    $md .= "|---|---|\n";
    $md .= "| API Catalog | " . home_url('/.well-known/api-catalog') . " |\n";
    $md .= "| MCP Server Card | " . home_url('/.well-known/mcp/server-card.json') . " |\n";
    $md .= "| Agent Skills Index | " . home_url('/.well-known/agent-skills/index.json') . " |\n";
    $md .= "| WP REST API Projects | " . rest_url('wp/v2/project') . " |\n";

    header('x-markdown-tokens: ' . str_word_count($md));
    echo $md;
    exit;
}
add_action('init', 'abc_tech_handle_markdown_negotiation_module', 0);
