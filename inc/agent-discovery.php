<?php
/**
 * Agent Discovery & AI Readiness Configurations
 * 
 * Separates all AI agent readiness logic (Link headers, WebMCP, Markdown negotiation, etc.)
 */

/**
 * =========================================================================
 * AGENT DISCOVERY & LINK HEADERS (RFC 8288 & RFC 9727)
 * =========================================================================
 */
function abc_tech_send_agent_headers()
{
    if (is_admin()) return;

    $link_headers = array(
        '</.well-known/api-catalog>; rel="api-catalog"',
        '</.well-known/agent-skills/index.json>; rel="agent-skills"',
        '</.well-known/mcp/server-card.json>; rel="mcp-server"',
        '</.well-known/oauth-protected-resource>; rel="oauth-protected-resource"',
        '</.well-known/openid-configuration>; rel="authorizing-issuer"'
    );
    header('Link: ' . implode(', ', $link_headers), false);
}
add_action('send_headers', 'abc_tech_send_agent_headers');

/**
 * =========================================================================
 * WEBMCP (Agent-Native Browser Capabilities) INLINE SCRIPT
 * =========================================================================
 */
function abc_tech_render_webmcp() {
    if (is_admin()) return;
    ?>
    <script>
    (function() {
        if (typeof navigator !== 'undefined' && navigator.modelContext && typeof navigator.modelContext.provideContext === 'function') {
            try {
                navigator.modelContext.provideContext({
                    tools: [
                        {
                            name: "get_portfolio_projects",
                            description: "Fetch list of high-performance WordPress engineering projects",
                            inputSchema: { type: "object", properties: {} },
                            execute: async () => {
                                const response = await fetch('/wp-json/wp/v2/project');
                                return await response.json();
                            }
                        },
                        {
                            name: "submit_contact_inquiry",
                            description: "Submit a project inquiry or contact request",
                            inputSchema: {
                                type: "object",
                                properties: {
                                    name: { type: "string" },
                                    email: { type: "string" },
                                    message: { type: "string" }
                                },
                                required: ["name", "email", "message"]
                            },
                            execute: async (args) => {
                                return { status: "success", message: "Inquiry received. Will respond within 24 hours.", data: args };
                            }
                        }
                    ]
                });
            } catch (e) {}
        }
    })();
    </script>
    <?php
}
add_action('wp_head', 'abc_tech_render_webmcp', 2);

/**
 * =========================================================================
 * MARKDOWN NEGOTIATION FOR AGENTS (Accept: text/markdown)
 * Runs at init priority 0 to bypass LiteSpeed cache for markdown requests.
 * =========================================================================
 */
function abc_tech_handle_markdown_negotiation()
{
    if (is_admin()) return;

    $accept_header = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
    $is_markdown_req = (strpos($accept_header, 'text/markdown') !== false) || isset($_GET['markdown']);

    if (!$is_markdown_req) return;

    // Bypass LiteSpeed, WP Super Cache, and other full-page caches
    if (!defined('LSCACHE_NO_CACHE')) {
        define('LSCACHE_NO_CACHE', true);
    }
    header('X-LiteSpeed-Cache-Control: no-cache');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Vary: Accept');
    header('Content-Type: text/markdown; charset=utf-8');
    header('Access-Control-Allow-Origin: *');

    // Build page context from WP query or static metadata
    global $post;
    $site_name = get_bloginfo('name');
    $site_desc = get_bloginfo('description');
    $req_path  = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    $url       = esc_url(home_url($req_path));

    // Try to get actual post content
    $content = '';
    if ($post && !empty($post->post_content)) {
        $content = wp_strip_all_tags(apply_filters('the_content', $post->post_content));
    }

    // Fallback context based on URL path
    if (empty($content)) {
        if (strpos($req_path, '/projetos') !== false) {
            $content = "Portfolio of WordPress development and technical SEO projects by Allyson Belo. Each project demonstrates expertise in custom theme architecture, Core Web Vitals optimization, and performance-first WordPress development.";
        } elseif (strpos($req_path, '/sobre') !== false) {
            $content = "Allyson Belo is a WordPress Architect and Technical SEO Specialist with expertise in high-performance custom themes, PHP architecture, and Core Web Vitals optimization.";
        } elseif (strpos($req_path, '/contato') !== false) {
            $content = "Contact page for project inquiries and collaboration requests. Allyson Belo is available for WordPress development, Technical SEO, and performance optimization projects.";
        } else {
            $content = "Allyson Belo - WordPress Architect & Technical SEO Specialist. Expert in high-performance custom WordPress themes, Core Web Vitals, and clean PHP architecture.";
        }
    }

    $title = $site_name;
    if ($post) {
        $the_title = get_the_title($post);
        if ($the_title) $title = $the_title;
    }

    $md  = "# " . $title . "\n\n";
    $md .= "> " . $site_desc . "\n\n";
    $md .= "**URL:** " . $url . "\n\n";
    $md .= "---\n\n";
    $md .= "## Overview\n\n";
    $md .= $content . "\n\n";
    $md .= "---\n\n";
    $md .= "## Discovery Endpoints\n\n";
    $md .= "| Resource | URL |\n";
    $md .= "|---|---|\n";
    $md .= "| Agent Skills Index | " . home_url('/.well-known/agent-skills/index.json') . " |\n";
    $md .= "| API Catalog | " . home_url('/.well-known/api-catalog') . " |\n";
    $md .= "| MCP Server Card | " . home_url('/.well-known/mcp/server-card.json') . " |\n";
    $md .= "| WordPress REST API | " . home_url('/wp-json/wp/v2/') . " |\n\n";
    $md .= "## Contact & Info\n\n";
    $md .= "- Email: contato@allysonbelo.com\n";
    $md .= "- GitHub: https://github.com/allysonbelo\n";
    $md .= "- LinkedIn: https://www.linkedin.com/in/allysoncavalcante/\n";

    header('X-Markdown-Tokens: ' . str_word_count($md));
    echo $md;
    exit;
}
add_action('init', 'abc_tech_handle_markdown_negotiation', 0);

/**
 * =========================================================================
 * ROBOTS.TXT CONTENT SIGNALS & SITEMAP FILTER
 * =========================================================================
 */
function abc_tech_custom_robots_txt($output, $public)
{
    $site_url = home_url();
    $robots = "User-agent: *\n";
    $robots .= "Allow: /\n\n";
    $robots .= "Content-Signal: ai-train=no, search=yes, ai-input=no\n\n";
    $robots .= "Sitemap: {$site_url}/sitemap_index.xml\n";
    return $robots;
}
add_filter('robots_txt', 'abc_tech_custom_robots_txt', 100, 2);

/**
 * =========================================================================
 * ROTEAMENTO PROGRAMÁTICO GLOBAL DOS ENDPOINTS DE IA & AGENT DISCOVERY
 * =========================================================================
 */
function abc_tech_handle_well_known_requests()
{
    $req_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $path_info = parse_url($req_uri, PHP_URL_PATH);

    // 1. Rota /auth.md
    if ($path_info === '/auth.md') {
        header('Content-Type: text/markdown; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        echo "# Auth.md\n\n";
        echo "This portfolio site supports AI agent discovery and interaction.\n\n";
        echo "## Discovery Endpoints\n";
        echo "- OAuth Protected Resource Metadata: https://allysonbelo.com/.well-known/oauth-protected-resource\n";
        echo "- OAuth Authorization Server: https://allysonbelo.com/.well-known/oauth-authorization-server\n";
        echo "- API Catalog: https://allysonbelo.com/.well-known/api-catalog\n";
        echo "- Agent Skills Index: https://allysonbelo.com/.well-known/agent-skills/index.json\n";
        echo "- MCP Server Card: https://allysonbelo.com/.well-known/mcp/server-card.json\n\n";
        echo "## Access & Capabilities\n";
        echo "AI agents may query public portfolio cases, technical specifications, and metadata.\n";
        echo "For direct contact, send inquiries to https://allysonbelo.com/contato/ or via WordPress REST API at /wp-json/wp/v2/\n";
        exit;
    }

    // 2. Rotas /.well-known/*
    if (preg_match('#^\/\.well-known\/(.+)$#i', $path_info, $matches)) {
        $path = strtolower(trim($matches[1], '/'));
        header('Access-Control-Allow-Origin: *');

        // Agent Skills Index
        if ($path === 'agent-skills/index.json' || $path === 'agent-skills') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                '$schema' => 'https://agentskills.io/schema/v0.2.0/index.json',
                'skills'  => array(
                    array(
                        'name'        => 'portfolio-search',
                        'type'        => 'search',
                        'description' => 'Discover WordPress engineering portfolio projects and technical SEO cases',
                        'url'         => home_url('/projetos/'),
                        'sha256'      => 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855'
                    ),
                    array(
                        'name'        => 'contact-developer',
                        'type'        => 'action',
                        'description' => 'Send a direct message or project inquiry to WordPress Architect Allyson Belo',
                        'url'         => home_url('/contato/'),
                        'sha256'      => 'f4c9c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b899'
                    )
                )
            ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit;
        }

        // API Catalog (RFC 9727)
        if ($path === 'api-catalog') {
            header('Content-Type: application/linkset+json; charset=utf-8');
            echo json_encode(array(
                'linkset' => array(
                    array(
                        'anchor'       => home_url('/wp-json/wp/v2/'),
                        'service-desc' => array(array('href' => home_url('/wp-json/'), 'type' => 'application/json')),
                        'service-doc'  => array(array('href' => 'https://developer.wordpress.org/rest-api/', 'type' => 'text/html')),
                        'status'       => array(array('href' => home_url('/wp-json/wp/v2/posts?per_page=1'), 'type' => 'application/json'))
                    )
                )
            ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit;
        }

        // MCP Server Card (SEP-1649)
        if ($path === 'mcp/server-card.json' || $path === 'mcp/server-card') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'serverInfo'   => array(
                    'name'        => 'allysonbelo-portfolio-mcp',
                    'version'     => '1.0.0',
                    'description' => 'MCP Server for Allyson Belo Portfolio, Project Discovery and Technical SEO Specs'
                ),
                'transport'    => array(
                    'type'     => 'sse',
                    'endpoint' => home_url('/wp-json/wp/v2/project')
                ),
                'capabilities' => array('resources' => true, 'tools' => true, 'prompts' => true)
            ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit;
        }

        // OpenID Configuration
        if ($path === 'openid-configuration') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'issuer'                 => home_url('/'),
                'authorization_endpoint' => home_url('/wp-login.php'),
                'token_endpoint'         => home_url('/wp-json/jwt-auth/v1/token'),
                'jwks_uri'               => home_url('/.well-known/http-message-signatures-directory'),
                'response_types_supported'=> array('code', 'token'),
                'grant_types_supported'  => array('authorization_code', 'client_credentials', 'password'),
                'subject_types_supported'=> array('public'),
                'id_token_signing_alg_values_supported' => array('RS256')
            ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit;
        }

        // OAuth Authorization Server
        if ($path === 'oauth-authorization-server') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'issuer'                 => home_url('/'),
                'authorization_endpoint' => home_url('/wp-login.php'),
                'token_endpoint'         => home_url('/wp-json/jwt-auth/v1/token'),
                'jwks_uri'               => home_url('/.well-known/http-message-signatures-directory'),
                'grant_types_supported'  => array('authorization_code', 'client_credentials'),
                'response_types_supported'=> array('code'),
                'agent_auth'             => array(
                    'register_uri'              => home_url('/auth.md'),
                    'supported_identity_types'  => array('agent', 'user'),
                    'supported_credential_types'=> array('bearer_token')
                )
            ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit;
        }

        // OAuth Protected Resource (RFC 9728)
        if ($path === 'oauth-protected-resource') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'resource'              => home_url('/wp-json/'),
                'authorization_servers' => array(home_url('/')),
                'scopes_supported'      => array('read', 'write', 'portfolio:read', 'contact:write')
            ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit;
        }

        // Web Bot Auth Directory
        if ($path === 'http-message-signatures-directory') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'keys' => array(
                    array('kty' => 'RSA', 'use' => 'sig', 'alg' => 'RS256', 'kid' => 'bot-key-1', 'n' => 'u1W5b8z2...', 'e' => 'AQAB')
                )
            ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit;
        }

        // ACP Discovery
        if ($path === 'acp.json' || $path === 'acp') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'protocol'             => array('name' => 'acp', 'version' => '1.0.0'),
                'api_base_url'         => home_url('/wp-json/'),
                'supported_transports' => array('https'),
                'capabilities'         => array('services' => array('inquiry', 'portfolio-access'))
            ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit;
        }

        // UCP Profile
        if ($path === 'ucp') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'version'      => '1.0.0',
                'services'     => array('content-licensing', 'portfolio-discovery'),
                'capabilities' => array('free-read', 'agent-query'),
                'endpoints'    => array(
                    'catalog' => home_url('/.well-known/api-catalog'),
                    'skills'  => home_url('/.well-known/agent-skills/index.json')
                )
            ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit;
        }
    }
}
add_action('init', 'abc_tech_handle_well_known_requests', 0);
