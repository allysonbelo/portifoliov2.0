<?php
/**
 * Agent Discovery: Well-Known & Metadata Routes
 * 
 * Programmatic fallbacks for auth.md and llms.txt endpoints.
 */

if (!defined('ABSPATH')) exit;

function abc_tech_handle_well_known_routes_module() {
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
}
add_action('init', 'abc_tech_handle_well_known_routes_module', 0);
