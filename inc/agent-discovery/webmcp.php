<?php
/**
 * Agent Discovery: WebMCP Integration
 * 
 * Registers browser capabilities for AI agents (Chrome AI & WebMCP API).
 */

if (!defined('ABSPATH')) exit;

function abc_tech_render_webmcp_script() {
    if (is_admin()) return;
    $search_url = esc_url(home_url('/?s='));
    $contact_url = esc_url(home_url('/contato/'));
    $rest_api_url = esc_url(rest_url('wp/v2/project'));
    ?>
    <!-- WebMCP Target Registration for WordPress Monolith -->
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
}
add_action('wp_head', 'abc_tech_render_webmcp_script', 2);
