=== ABC Engagement Toast (Popup Style) ===
Contributors: allysonbelo
Tags: toast, popup, engagement, notifications, conversion
Requires at least: 5.6
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: Proprietary

Notificações inteligentes tipo Toast e Popups com gatilhos de rolagem, atraso e intenção de saída, protegidas por chave de licença remota.

== Description ==

O **ABC Engagement Toast** é um plugin de alto padrão projetado para aumentar a conversão, cliques e engajamento dos visitantes através de toasts elegantes e popups interativos.

### Funcionalidades:
* Construtor visual integrado com suporte nativo ao Advanced Custom Fields (ACF).
* Pré-visualização em tempo real das notificações no painel administrativo.
* Múltiplos gatilhos avançados:
  * Ao carregar a página
  * Após X segundos (Delay)
  * Ao rolar a página (% de scroll)
  * Intenção de Saída (Exit-Intent / Mouse leaving screen) com efeito overlay escuro e desfoque (blur).
* Totalmente responsivo com posicionamento customizável (Inferior direito, esquerdo, superior, centro).
* **Sistema de Licenciamento Remoto**: Funciona mediante ativação de chave de licença gerenciada em allysonbelo.com.

== Installation ==

1. Faça o upload do arquivo `abc-engagement-toast.zip` através do painel do WordPress em **Plugins > Adicionar Novo > Enviar Plugin**.
2. Ative o plugin.
3. Acesse **Toasts (Popups) > 🔑 Licença** e insira sua chave de licença autorizada.
4. Comece a criar seus toasts e popups em **Toasts (Popups) > Adicionar Novo**.

== Headless Support ==

O plugin possui suporte nativo para sites Headless (Next.js, React, Astro, Vue, HTML):
1. No painel do WordPress, vá em **Toasts (Popups) > 🔑 Licença** e informe o domínio do seu frontend no campo **Domínio do Frontend** (ex: `roteirodeviagem.org`).
2. No seu frontend (Next.js/React/HTML), basta colar o loader universal no `<head>`:
   `<script src="https://SEU-WORDPRESS/wp-content/plugins/abc-engagement-toast/assets/js/headless-loader.js" async></script>`
3. Ou consuma diretamente a REST API protegida:
   `GET https://SEU-WORDPRESS/wp-json/abc-toast/v1/toasts`

== Changelog ==

= 1.1.0 =
* Adicionado suporte nativo a sites Headless (Next.js/React) com endpoint REST API protegido por licença e script universal `headless-loader.js`.
* Adicionado campo para configurar o Domínio do Frontend em sites desacoplados.

= 1.0.0 =
* Lançamento inicial como plugin independente com sistema de ativação e validação por chave de licença remota.

