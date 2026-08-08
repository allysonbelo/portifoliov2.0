<?php
/**
 * Agent Discovery & AI Readiness Module (Monolithic WordPress Theme Edition)
 * 
 * Fully adapted for native WordPress architecture (PHP, WP REST API, Template Hooks & Dynamic Queries).
 */

if (!defined('ABSPATH')) exit;

/**
 * 1. HTTP Link Headers & Content-Signals (RFC 8288)
 */
add_action('send_headers', function() {
    if (is_admin()) return;

    $link_headers = array(
        '<' . esc_url(home_url('/.well-known/api-catalog')) . '>; rel="api-catalog"',
        '<' . esc_url(home_url('/.well-known/mcp/server-card.json')) . '>; rel="mcp-server"',
        '<' . esc_url(home_url('/.well-known/agent-skills/index.json')) . '>; rel="agent-skills"',
        '<' . esc_url(home_url('/auth.md')) . '>; rel="authorizing-agents"',
        '<' . esc_url(home_url('/llms.txt')) . '>; rel="service-doc"'
    );
    header('Link: ' . implode(', ', $link_headers), false);
    header('Content-Signal: ai-train=no, search=yes, ai-input=no');
});

/**
 * 2. HTML <head> Tags (Link Relations & Native WebMCP Integration)
 */
add_action('wp_head', function() {
    if (is_admin()) return;
    $home = esc_url(home_url());
    $search_url = esc_url(home_url('/?s='));
    $contact_url = esc_url(home_url('/contato/'));
    $projects_url = esc_url(home_url('/projetos/'));
    $rest_api_url = esc_url(rest_url('wp/v2/project'));
    ?>
    <!-- AI Agent Discovery Link Relations -->
    <link rel="api-catalog" href="<?php echo esc_url(home_url('/.well-known/api-catalog')); ?>" type="application/linkset+json">
    <link rel="mcp-server" href="<?php echo esc_url(home_url('/.well-known/mcp/server-card.json')); ?>" type="application/json">
    <link rel="agent-skills" href="<?php echo esc_url(home_url('/.well-known/agent-skills/index.json')); ?>" type="application/json">
    <link rel="authorizing-agents" href="<?php echo esc_url(home_url('/auth.md')); ?>" type="text/markdown">
    <link rel="llms" href="<?php echo esc_url(home_url('/llms.txt')); ?>" type="text/plain">

    <!-- Native WebMCP Target Registration for WordPress Monolith -->
    <script>
    (function() {
        if (typeof window === 'undefined') return;

        const searchTool = {
            name: 'search_portfolio',
            description: 'Pesquisa projetos de engenharia WordPress e artigos no site.',
            inputSchema: {
                type: 'object',
                properties: { query: { type: 'string', description: 'Termo de busca' } },
                required: ['query']
            },
            parameters: {
                type: 'object',
                properties: { query: { type: 'string', description: 'Termo de busca' } },
                required: ['query']
            },
            execute: async function(args) {
                var q = (args && args.query) || '';
                window.location.href = '<?php echo $search_url; ?>' + encodeURIComponent(q);
                return { message: 'Navegado para a pesquisa WordPress por: ' + q };
            }
        };

        const getProjectsTool = {
            name: 'get_projects_api',
            description: 'Obtém a lista completa de projetos em formato JSON via WordPress REST API.',
            inputSchema: { type: 'object', properties: {} },
            execute: async function() {
                const res = await fetch('<?php echo $rest_api_url; ?>');
                const data = await res.json();
                return { projects: data };
            }
        };

        const contactTool = {
            name: 'contact_developer',
            description: 'Redireciona para o formulário de contato do arquiteto WordPress Allyson Belo.',
            inputSchema: {
                type: 'object',
                properties: {
                    name: { type: 'string', description: 'Nome do solicitante' },
                    email: { type: 'string', description: 'Email de contato' },
                    message: { type: 'string', description: 'Mensagem sobre o projeto' }
                },
                required: ['name', 'email', 'message']
            },
            parameters: {
                type: 'object',
                properties: {
                    name: { type: 'string', description: 'Nome do solicitante' },
                    email: { type: 'string', description: 'Email de contato' },
                    message: { type: 'string', description: 'Mensagem sobre o projeto' }
                },
                required: ['name', 'email', 'message']
            },
            execute: async function(args) {
                window.location.href = '<?php echo $contact_url; ?>';
                return { message: 'Redirecionando para a página de contato.' };
            }
        };

        const tools = [searchTool, getProjectsTool, contactTool];

        const targets = [
            typeof navigator !== 'undefined' ? navigator.modelContext : null,
            typeof document !== 'undefined' ? document.modelContext : null,
            window.modelContext
        ].filter(Boolean);

        targets.forEach(function(mc) {
            if (typeof mc.provideContext === 'function') {
                try { mc.provideContext({ tools: tools }); } catch(e) {}
            }
            if (typeof mc.registerTool === 'function') {
                tools.forEach(function(t) {
                    try { mc.registerTool(t); } catch(e) {}
                });
            }
        });
    })();
    </script>
    <?php
}, 1);

/**
 * 3. Dynamic WordPress Markdown Negotiation (Accept: text/markdown)
 * Dynamically converts current WP Post/Page/Archive into Markdown!
 */
add_action('template_redirect', function() {
    if (is_admin()) return;

    $accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
    $is_md = (strpos($accept, 'text/markdown') !== false) || isset($_GET['markdown']);

    if (!$is_md) return;

    // Cache Bypass for LiteSpeed & Caching Plugins
    if (!defined('LSCACHE_NO_CACHE')) define('LSCACHE_NO_CACHE', true);
    header('X-LiteSpeed-Cache-Control: no-cache');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Vary: Accept');
    header('Content-Type: text/markdown; charset=utf-8');
    header('Access-Control-Allow-Origin: *');

    $site_name = get_bloginfo('name');
    $site_desc = get_bloginfo('description');
    $current_url = esc_url(home_url(add_query_arg(array(), $GLOBALS['wp']->request)));

    $md  = "# {$site_name}\n\n";
    $md .= "> {$site_desc}\n\n";
    $md .= "**URL Canonical:** {$current_url}\n\n";
    $md .= "---\n\n";

    if (is_singular()) {
        global $post;
        $title = get_the_title($post);
        $content = wp_strip_all_tags(apply_filters('the_content', $post->post_content));
        
        $md .= "## {$title}\n\n";
        if (has_excerpt($post)) {
            $md .= "> " . wp_strip_all_tags(get_the_excerpt($post)) . "\n\n";
        }
        $md .= "{$content}\n\n";
    } elseif (is_search()) {
        $query = get_search_query();
        $md .= "## Resultados da Pesquisa por: {$query}\n\n";
        if (have_posts()) {
            while (have_posts()) {
                the_post();
                $md .= "### [" . get_the_title() . "](" . get_permalink() . ")\n";
                $md .= wp_strip_all_tags(get_the_excerpt()) . "\n\n";
            }
        } else {
            $md .= "Nenhum resultado encontrado.\n\n";
        }
    } else {
        $md .= "## Visão Geral do Site\n\n";
        $md .= "Allyson Belo - Arquiteto WordPress & Especialista em SEO Técnico.\n";
        $md .= "Desenvolvimento de temas sob medida de alta performance, arquitetura limpa PHP e otimização Core Web Vitals.\n\n";
        
        $md .= "### Últimos Projetos\n\n";
        $projects = get_posts(array('post_type' => 'project', 'posts_per_page' => 5));
        if (!empty($projects)) {
            foreach ($projects as $proj) {
                $md .= "- **[" . get_the_title($proj) . "](" . get_permalink($proj) . ")**: " . wp_strip_all_tags(get_the_excerpt($proj)) . "\n";
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
});

/**
 * 4. Custom WordPress Robots.txt Filter (Content-Signals)
 */
add_filter('robots_txt', function($output, $public) {
    $site_url = home_url();
    $robots  = "User-agent: *\n";
    $robots .= "Allow: /\n\n";
    $robots .= "Content-Signal: ai-train=no, search=yes, ai-input=no\n\n";
    $robots .= "Sitemap: {$site_url}/sitemap_index.xml\n";
    return $robots;
}, 100, 2);

/**
 * 5. Programmatic Router for /.well-known, auth.md & llms.txt Fallbacks
 */
add_action('init', function() {
    $req_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $path = parse_url($req_uri, PHP_URL_PATH);

    if ($path === '/llms.txt') {
        header('Content-Type: text/plain; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        echo "# " . get_bloginfo('name') . " - WordPress Architect\n\n";
        echo "> " . get_bloginfo('description') . "\n\n";
        echo "## Recursos e Páginas Principais\n";
        echo "- [Página Inicial](" . home_url('/') . ")\n";
        echo "- [Projetos](" . home_url('/projetos/') . ")\n";
        echo "- [Sobre](" . home_url('/sobre/') . ")\n";
        echo "- [Contato](" . home_url('/contato/') . ")\n\n";
        echo "## Descoberta e REST API\n";
        echo "- API Catalog: " . home_url('/.well-known/api-catalog') . "\n";
        echo "- MCP Server: " . home_url('/.well-known/mcp/server-card.json') . "\n";
        echo "- Agent Skills: " . home_url('/.well-known/agent-skills/index.json') . "\n";
        echo "- WP REST API: " . rest_url('wp/v2/') . "\n";
        exit;
    }

    if ($path === '/auth.md') {
        header('Content-Type: text/markdown; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        echo "# Auth.md\n\n";
        echo "## Regras de Acesso\n";
        echo "1. **Scraping / Leitura**: Permitido para indexação de busca e resposta aos usuários.\n";
        echo "2. **Treinamento de Modelos**: Não permitido (`Content-Signal: ai-train=no`).\n";
        echo "3. **Limites de Taxa**: Máximo de 60 requisições por minuto por IP.\n\n";
        echo "## Contato\n";
        echo "Para suporte de integração de IA: `contato@allysonbelo.com`.\n";
        exit;
    }
}, 0);
