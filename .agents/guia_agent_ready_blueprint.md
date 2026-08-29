# Blueprint & Guia Completo: Implementação "Agent Ready" (WebMCP, AI Discovery & Markdown Negotiation)

Este documento reúne todas as técnicas, códigos e especificações de infraestrutura para tornar qualquer aplicação web (React/Node ou WordPress/PHP) 100% pronta para interagir com **Agentes de IA**, **Navegadores com IA (Chrome AI/WebMCP)** e **LLMs**.

---

## 📋 Sumário
1. [Frontend (React / TypeScript): WebMCP Hook](#1-frontend-react--typescript-webmcp-hook)
2. [HTML `<head>`: Metadados & Link Relations](#2-html-head-metadados--link-relations)
3. [Backend Node.js/Express: Headers & Content Negotiation](#3-backend-nodejs-express-headers--content-negotiation)
4. [Arquivos Estáticos de Descoberta (`public/`)](#4-arquivos-est%C3%A1ticos-de-descoberta-public)
   - `llms.txt`
   - `auth.md`
   - `.well-known/mcp/server-card.json`
   - `.well-known/agent-skills/index.json`
5. [Bônus: Snippet PHP / WordPress (Headless ou Monolítico)](#5-b%C3%B4nus-snippet-php--wordpress-headless-ou-monol%C3%ADtico)

---

## 1. Frontend (React / TypeScript): WebMCP Hook

Crie o hook em `src/hooks/useWebMCP.ts` para registrar imperativamente as ferramentas que os agentes do navegador podem invocar.

```typescript
import { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

export function useWebMCP() {
  const navigate = useNavigate();

  useEffect(() => {
    if (typeof window === 'undefined') return;

    const controller = new AbortController();

    try {
      // 1. Ferramenta de Busca
      const searchTool = {
        name: 'search_destinations',
        description: 'Pesquisa destinos, artigos e recursos no site.',
        inputSchema: {
          type: 'object',
          properties: {
            query: { type: 'string', description: 'Termo ou palavra-chave de busca' }
          },
          required: ['query']
        },
        parameters: {
          type: 'object',
          properties: {
            query: { type: 'string', description: 'Termo ou palavra-chave de busca' }
          },
          required: ['query']
        },
        execute: async (args: { query?: string }) => {
          const q = args?.query || '';
          navigate(`/?search=${encodeURIComponent(q)}`);
          return { message: `Navegado para a busca por: ${q}` };
        }
      };

      // 2. Ferramenta de Navegação / Guias
      const guideTool = {
        name: 'get_travel_guides',
        description: 'Obtém a lista de guias principais e categorias.',
        inputSchema: {
          type: 'object',
          properties: {
            category: { type: 'string', description: 'Categoria opcional' }
          }
        },
        execute: async () => {
          navigate('/guias');
          return { message: 'Navegado para a página de guias.' };
        }
      };

      // 3. Ferramenta de Formulário / Contato
      const contactTool = {
        name: 'contact_support',
        description: 'Envia mensagem ou redireciona para suporte.',
        inputSchema: {
          type: 'object',
          properties: {
            name: { type: 'string', description: 'Nome do usuário' },
            email: { type: 'string', description: 'E-mail do usuário' },
            message: { type: 'string', description: 'Mensagem' }
          },
          required: ['name', 'email', 'message']
        },
        execute: async (args: any) => {
          navigate('/contato');
          return { message: `Redirecionado para contato para o usuário ${args.name}.` };
        }
      };

      const tools = [searchTool, guideTool, contactTool];

      // Alvos de injeção da API WebMCP (especificação de navegadores AI)
      const targets = [
        typeof navigator !== 'undefined' ? (navigator as any).modelContext : null,
        typeof document !== 'undefined' ? (document as any).modelContext : null,
        (window as any).modelContext
      ].filter(Boolean);

      targets.forEach(mc => {
        if (typeof mc.provideContext === 'function') {
          try { mc.provideContext({ tools }); } catch (e) {}
        }
        if (typeof mc.registerTool === 'function') {
          tools.forEach(t => {
            try { mc.registerTool(t, { signal: controller.signal }); } catch (e) {}
          });
        }
      });
    } catch (e) {
      console.error('Falha ao registrar ferramentas WebMCP:', e);
    }

    return () => {
      controller.abort();
    };
  }, [navigate]);
}
```

### Inicialização no `App.tsx`:
```tsx
import { useWebMCP } from './hooks/useWebMCP';

export default function App() {
  useWebMCP(); // Registra as ferramentas assim que a aplicação é montada

  return (
    <div>
      {/* Restante da sua aplicação */}
    </div>
  );
}
```

---

## 2. HTML `<head>`: Metadados & Link Relations

Adicione as tags `<link rel="...">` no `<head>` do `index.html` para indicar onde os agentes encontram as definições do site:

```html
<!-- AI Agent Discovery Link Relations -->
<link rel="api-catalog" href="/.well-known/api-catalog" type="application/linkset+json">
<link rel="mcp-server" href="/.well-known/mcp/server-card.json" type="application/json">
<link rel="agent-skills" href="/.well-known/agent-skills/index.json" type="application/json">
<link rel="authorizing-agents" href="/auth.md" type="text/markdown">
<link rel="llms" href="/llms.txt" type="text/plain">

<!-- Fallback inline para WebMCP (Garante registro antes do React carregar) -->
<script>
  (function() {
    if (typeof window === 'undefined') return;

    const searchTool = {
      name: 'search_destinations',
      description: 'Pesquisa conteúdos no site.',
      inputSchema: {
        type: 'object',
        properties: { query: { type: 'string', description: 'Termo de busca' } },
        required: ['query']
      },
      execute: async function(args) {
        var q = (args && args.query) || '';
        window.location.href = '/?search=' + encodeURIComponent(q);
        return { message: 'Redirecionando para busca: ' + q };
      }
    };

    const targets = [
      typeof navigator !== 'undefined' ? navigator.modelContext : null,
      typeof document !== 'undefined' ? document.modelContext : null,
      window.modelContext
    ].filter(Boolean);

    targets.forEach(function(mc) {
      if (typeof mc.provideContext === 'function') {
        try { mc.provideContext({ tools: [searchTool] }); } catch(e) {}
      }
    });
  })();
</script>
```

---

## 3. Backend Node.js/Express: Headers & Content Negotiation

No servidor `server.ts` ou `app.ts`:

```typescript
import express from 'express';

const app = express();

// Middleware 1: Cabeçalhos HTTP para Agentes de IA (RFC 8288 Link Headers & Content-Signals)
app.use((req, res, next) => {
  res.setHeader(
    'Link',
    '</.well-known/api-catalog>; rel="api-catalog", </.well-known/mcp/server-card.json>; rel="mcp-server", </.well-known/agent-skills/index.json>; rel="agent-skills", </auth.md>; rel="authorizing-agents", </llms.txt>; rel="service-doc"'
  );
  // Define diretrizes para robôs e IAs (não treinar, permitir busca, sem entrada direta)
  res.setHeader('Content-Signal', 'ai-train=no, search=yes, ai-input=no');
  next();
});

// Middleware 2: Content Negotiation para Markdown (Accept: text/markdown)
app.use((req, res, next) => {
  const accept = req.headers.accept || '';

  // Se a requisição vier de um LLM ou cliente solicitando Markdown
  if (accept.includes('text/markdown') && !req.path.startsWith('/api/') && !req.path.startsWith('/.well-known/')) {
    res.setHeader('Content-Type', 'text/markdown; charset=utf-8');
    res.setHeader('x-markdown-tokens', '350');

    const mdResponse = `# Meu Site - Portal & Aplicação Web

> Breve resumo do site e seus serviços para consumo por inteligências artificiais.

## Páginas Principais
- **Início**: https://${req.headers.host}/
- **Pesquisa**: https://${req.headers.host}/?search={query}
- **Contato**: https://${req.headers.host}/contato

## Ferramentas MCP / WebMCP Suportadas
1. \`search_destinations\`: Pesquisa por palavras-chave.
2. \`get_travel_guides\`: Lista os guias principais.
3. \`contact_support\`: Direciona formulário de suporte.
`;
    return res.send(mdResponse);
  }

  next();
});
```

---

## 4. Arquivos Estáticos de Descoberta (`public/`)

### 📄 `public/llms.txt`
```text
# Nome do Seu Projeto

> Um resumo claro de 1 parágrafo descrevendo a empresa, o produto ou a aplicação.

## Recursos e Páginas Principais
- [Página Inicial](https://seudominio.com/): Descrição do produto.
- [Blog / Artigos](https://seudominio.com/blog): Coleção de artigos informativos.
- [Contato](https://seudominio.com/contato): Formulário de suporte.

## APIs e Ferramentas para Agentes
- [API Catalog](/.well-known/api-catalog): Lista de rotas da API.
- [MCP Server Card](/.well-known/mcp/server-card.json): Ficha do servidor MCP.
- [Agent Skills](/.well-known/agent-skills/index.json): Habilidades expostas.
```

### 📄 `public/auth.md`
```markdown
# Diretrizes e Termos para Agentes de IA

## Regras de Acesso
1. **Scraping / Leitura**: Permitido para indexação de busca e resposta aos usuários.
2. **Treinamento de Modelos**: Não permitido sem autorização prévia (`Content-Signal: ai-train=no`).
3. **Limites de Taxa (Rate Limits)**: Máximo de 60 requisições por minuto por IP para rotas de agente.

## Contato
Para suporte técnico de IA ou integração via MCP, envie um e-mail para: `suporte@seudominio.com`.
```

### 📄 `public/.well-known/mcp/server-card.json`
```json
{
  "name": "meu-site-mcp-server",
  "version": "1.0.0",
  "description": "Servidor MCP Web para integração de ferramentas do site com navegadores e assistentes de IA.",
  "capabilities": {
    "tools": true,
    "prompts": false,
    "resources": true
  },
  "endpoints": {
    "webMcp": true,
    "jsonLd": "/.well-known/api-catalog"
  }
}
```

### 📄 `public/.well-known/agent-skills/index.json`
```json
{
  "skills": [
    {
      "id": "search_destinations",
      "name": "Buscar no Site",
      "description": "Permite ao agente buscar artigos, produtos ou destinos no site.",
      "parameters": {
        "query": { "type": "string", "required": true }
      }
    },
    {
      "id": "contact_support",
      "name": "Contato e Suporte",
      "description": "Preenche ou direciona o usuário para o formulário de suporte.",
      "parameters": {
        "name": { "type": "string", "required": true },
        "email": { "type": "string", "required": true },
        "message": { "type": "string", "required": true }
      }
    }
  ]
}
```

---

## 5. Bônus: Snippet PHP / WordPress (Headless ou Monolítico)

Se o projeto futuro for em **WordPress**, adicione este snippet no `functions.php` do tema para injetar os headers, os metadados no `<head>` e o suporte a Markdown:

```php
<?php
// Injeta Headers HTTP para Agentes de IA
add_action('send_headers', function() {
    header('Link: </.well-known/api-catalog>; rel="api-catalog", </.well-known/mcp/server-card.json>; rel="mcp-server", </.well-known/agent-skills/index.json>; rel="agent-skills", </auth.md>; rel="authorizing-agents", </llms.txt>; rel="service-doc"');
    header('Content-Signal: ai-train=no, search=yes, ai-input=no');
});

// Content Negotiation: Responde em Markdown se o cliente enviar Accept: text/markdown
add_action('template_redirect', function() {
    $accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
    
    if (strpos($accept, 'text/markdown') !== false && !is_admin()) {
        header('Content-Type: text/markdown; charset=utf-8');
        header('x-markdown-tokens: 300');
        
        $title = get_bloginfo('name');
        $desc = get_bloginfo('description');
        
        echo "# {$title}\n\n";
        echo "> {$desc}\n\n";
        echo "## Conteúdo Principal\n";
        
        if (is_single() || is_page()) {
            global $post;
            echo "### " . get_the_title() . "\n\n";
            echo wp_strip_all_tags(get_the_content());
        } else {
            echo "Acesse https://" . $_SERVER['HTTP_HOST'] . "/ para navegar no conteúdo completo.";
        }
        exit;
    }
});

// Injeta as tags <link> de descoberta no <head>
add_action('wp_head', function() {
    ?>
    <link rel="api-catalog" href="/.well-known/api-catalog" type="application/linkset+json">
    <link rel="mcp-server" href="/.well-known/mcp/server-card.json" type="application/json">
    <link rel="agent-skills" href="/.well-known/agent-skills/index.json" type="application/json">
    <link rel="authorizing-agents" href="/auth.md" type="text/markdown">
    <link rel="llms" href="/llms.txt" type="text/plain">
    <?php
});
