<?php
/**
 * Agent Discovery & AI Readiness Module
 * 
 * Based on the Agent-Ready Blueprint (WebMCP, AI Discovery, Link Relations & Content Negotiation)
 */

if (!defined('ABSPATH')) exit;

/**
 * 1. HTTP Link Headers & Content-Signals (RFC 8288)
 */
add_action('send_headers', function() {
    if (is_admin()) return;

    $link_headers = array(
        '</.well-known/api-catalog>; rel="api-catalog"',
        '</.well-known/mcp/server-card.json>; rel="mcp-server"',
        '</.well-known/agent-skills/index.json>; rel="agent-skills"',
        '</auth.md>; rel="authorizing-agents"',
        '</llms.txt>; rel="service-doc"'
    );
    header('Link: ' . implode(', ', $link_headers), false);
    header('Content-Signal: ai-train=no, search=yes, ai-input=no');
});

/**
 * 2. HTML <head> Tags (Link Relations & WebMCP Inline Fallback)
 */
add_action('wp_head', function() {
    if (is_admin()) return;
    ?>
    <!-- AI Agent Discovery Link Relations -->
    <link rel="api-catalog" href="<?php echo esc_url(home_url('/.well-known/api-catalog')); ?>" type="application/linkset+json">
    <link rel="mcp-server" href="<?php echo esc_url(home_url('/.well-known/mcp/server-card.json')); ?>" type="application/json">
    <link rel="agent-skills" href="<?php echo esc_url(home_url('/.well-known/agent-skills/index.json')); ?>" type="application/json">
    <link rel="authorizing-agents" href="<?php echo esc_url(home_url('/auth.md')); ?>" type="text/markdown">
    <link rel="llms" href="<?php echo esc_url(home_url('/llms.txt')); ?>" type="text/plain">

    <!-- WebMCP Target Registration (Navegadores & Agentes de IA) -->
    <script>
    (function() {
        if (typeof window === 'undefined') return;

        const searchTool = {
            name: 'search_portfolio',
            description: 'Pesquisa projetos, cases de engenharia WordPress e artigos no site.',
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
                window.location.href = '/projetos/?search=' + encodeURIComponent(q);
                return { message: 'Redirecionando para busca de projetos: ' + q };
            }
        };

        const contactTool = {
            name: 'contact_developer',
            description: 'Redireciona para o formulário de contato com o arquiteto WordPress Allyson Belo.',
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
                window.location.href = '/contato/';
                return { message: 'Navegado para a página de contato.' };
            }
        };

        const tools = [searchTool, contactTool];

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
 * 3. Content Negotiation: Responde em Markdown (Accept: text/markdown)
 */
function abc_tech_handle_markdown_negotiation() {
    if (is_admin()) return;

    $accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
    $is_md = (strpos($accept, 'text/markdown') !== false) || isset($_GET['markdown']);

    if (!$is_md) return;

    // Directives to bypass server cache
    if (!defined('LSCACHE_NO_CACHE')) define('LSCACHE_NO_CACHE', true);
    header('X-LiteSpeed-Cache-Control: no-cache');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Vary: Accept');
    header('Content-Type: text/markdown; charset=utf-8');
    header('Access-Control-Allow-Origin: *');

    $site_name = get_bloginfo('name');
    $site_desc = get_bloginfo('description');
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'allysonbelo.com';

    $md  = "# {$site_name} - Portfolio & Arquitetura WordPress\n\n";
    $md .= "> {$site_desc}\n\n";
    $md .= "## Visão Geral\n";
    $md .= "Allyson Belo é Arquiteto WordPress e Especialista em SEO Técnico, focado no desenvolvimento de temas sob medida de alta performance, arquitetura limpa em PHP e otimização para Core Web Vitals.\n\n";
    $md .= "## Páginas Principais\n";
    $md .= "- **Início**: https://{$host}/\n";
    $md .= "- **Projetos**: https://{$host}/projetos/\n";
    $md .= "- **Sobre**: https://{$host}/sobre/\n";
    $md .= "- **Contato**: https://{$host}/contato/\n\n";
    $md .= "## Discovery & Ferramentas\n";
    $md .= "- **LLMs Info**: https://{$host}/llms.txt\n";
    $md .= "- **API Catalog**: https://{$host}/.well-known/api-catalog\n";
    $md .= "- **MCP Server**: https://{$host}/.well-known/mcp/server-card.json\n";
    $md .= "- **Agent Skills**: https://{$host}/.well-known/agent-skills/index.json\n";
    $md .= "- **REST API**: https://{$host}/wp-json/wp/v2/\n";

    header('x-markdown-tokens: ' . str_word_count($md));
    echo $md;
    exit;
}
add_action('init', 'abc_tech_handle_markdown_negotiation', 0);

/**
 * 4. Custom Robots.txt Filter (Content-Signals)
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
 * 5. Programmatic Router for /.well-known & auth.md & llms.txt Fallback
 */
add_action('init', function() {
    $req_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $path = parse_url($req_uri, PHP_URL_PATH);

    if ($path === '/llms.txt') {
        header('Content-Type: text/plain; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        echo "# Allyson Belo - WordPress Architect\n\n";
        echo "> Desenvolvedor especializado em arquitetura de temas customizados, performance e SEO técnico.\n\n";
        echo "## Links Principais\n";
        echo "- [Portfolio](https://allysonbelo.com/projetos/)\n";
        echo "- [Sobre](https://allysonbelo.com/sobre/)\n";
        echo "- [Contato](https://allysonbelo.com/contato/)\n\n";
        echo "## Descoberta de Agentes\n";
        echo "- API Catalog: /.well-known/api-catalog\n";
        echo "- MCP Server: /.well-known/mcp/server-card.json\n";
        echo "- Agent Skills: /.well-known/agent-skills/index.json\n";
        exit;
    }

    if ($path === '/auth.md') {
        header('Content-Type: text/markdown; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        echo "# Auth.md\n\n";
        echo "## Regras de Acesso\n";
        echo "1. **Scraping / Leitura**: Permitido para indexação de busca e resposta a usuários.\n";
        echo "2. **Treinamento de Modelos**: Não permitido (`Content-Signal: ai-train=no`).\n";
        echo "3. **Limites de Taxa**: Máximo de 60 requisições por minuto por IP.\n\n";
        echo "## Contato\n";
        echo "Para integração via MCP ou contato direto: `contato@allysonbelo.com`.\n";
        exit;
    }
}, 0);
