/**
 * Efeito de Digitação Avançado (Sincronizado com o ACF)
 */
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('typing-text');
    if (!container) return;

    // 1. Resgata o texto dinâmico configurado no WordPress (ACF)
    const rawText = container.getAttribute('data-typing');
    if (!rawText) return;

    // 2. Mini "Parser" para aplicar as cores do Figma automaticamente no texto do banco
    const codeChunks = [];
    
    // Regex inteligente para identificar: Strings ('' ou ""), Palavras-chave, Números e Restante
    const tokenRegex = /('[^']*'|"[^"]*")|\b(const|let|var|function|return|await|async|class|if|else|true|false)\b|\b(\d+)\b|([^'"0-9a-zA-Z_]+|[a-zA-Z_]+)/g;
    
    let match;
    while ((match = tokenRegex.exec(rawText)) !== null) {
        if (match[1]) {
            codeChunks.push({ text: match[1], cls: "token-string" }); // Textos em aspas
        } else if (match[2]) {
            codeChunks.push({ text: match[2], cls: "token-keyword" }); // Comandos JS
        } else if (match[3]) {
            codeChunks.push({ text: match[3], cls: "token-number" }); // Números
        } else if (match[4]) {
            // Agrupa textos neutros para não criar excesso de tags HTML
            const last = codeChunks[codeChunks.length - 1];
            if (last && !last.cls) {
                last.text += match[4];
            } else {
                codeChunks.push({ text: match[4], cls: "" });
            }
        }
    }

    // 3. Motor de Digitação (Inalterado)
    let chunkIndex = 0;
    let charIndex = 0;
    let currentSpan = null;

    function typeCode() {
        if (chunkIndex < codeChunks.length) {
            const chunk = codeChunks[chunkIndex];
            
            if (charIndex === 0) {
                currentSpan = document.createElement('span');
                if (chunk.cls) {
                    currentSpan.className = chunk.cls;
                }
                container.appendChild(currentSpan);
            }

            currentSpan.textContent += chunk.text.charAt(charIndex);
            charIndex++;

            if (charIndex >= chunk.text.length) {
                chunkIndex++;
                charIndex = 0;
            }

            // Velocidade da digitação (em milissegundos)
            setTimeout(typeCode, 40); 
        }
    }

    typeCode();
});