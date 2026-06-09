/**
 * Typewriter Effect para os Cards de Projetos
 * Lê o atributo data-words (JSON) e faz o loop de digitação.
 */
document.addEventListener('DOMContentLoaded', () => {
    const typingElements = document.querySelectorAll('.typing-text');

    typingElements.forEach(el => {
        // Pega o array de palavras do PHP
        const words = JSON.parse(el.getAttribute('data-words'));
        if (!words || words.length === 0) return;

        let wordIndex = 0;
        let charIndex = 0;
        let isDeleting = false;

        function type() {
            const currentWord = words[wordIndex];
            
            // Lógica de apagar ou escrever caractere por caractere
            if (isDeleting) {
                el.textContent = currentWord.substring(0, charIndex - 1);
                charIndex--;
            } else {
                el.textContent = currentWord.substring(0, charIndex + 1);
                charIndex++;
            }

            // Velocidade: Digitar é mais lento que apagar
            let typeSpeed = isDeleting ? 40 : 80;

            if (!isDeleting && charIndex === currentWord.length) {
                // Terminou de escrever a palavra: Pausa para o usuário ler
                typeSpeed = 2500; 
                isDeleting = true;
            } else if (isDeleting && charIndex === 0) {
                // Terminou de apagar: Pula para a próxima métrica
                isDeleting = false;
                wordIndex = (wordIndex + 1) % words.length;
                typeSpeed = 400; // Pausa rápida antes de começar a nova palavra
            }

            setTimeout(type, typeSpeed);
        }

        // Inicia a animação com um pequeno atraso aleatório para 
        // dessincronizar os cards e dar um efeito orgânico e independente
        setTimeout(type, Math.random() * 1500);
    });
});