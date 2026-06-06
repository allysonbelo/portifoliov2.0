/**
 * Efeito de Digitação Avançado (Figma Sync)
 */

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('typing-text');
    if (!container) return;

    // Array ajustado para mapear perfeitamente o design
    const codeChunks = [
        { text: "const ", cls: "token-keyword" },
        { text: "developer ", cls: "token-variable" },
        { text: "= {\n  name: ", cls: "" },
        { text: "'Allyson Belo'", cls: "token-string" },
        { text: ",\n  role: ", cls: "" },
        { text: "'WP Architect'", cls: "token-string" },
        { text: ",\n  focus: [", cls: "" },
        { text: "'Performance'", cls: "token-string" },
        { text: ", ", cls: "" },
        { text: "'SEO'", cls: "token-string" },
        { text: "],\n  pagespeed: ", cls: "" },
        { text: "100", cls: "token-number" },
        { text: "\n};\n\n", cls: "" },
        { text: "await ", cls: "token-keyword" },
        { text: "developer.optimize();", cls: "" }
    ];

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

            setTimeout(typeCode, 40); 
        }
    }

    typeCode();
});