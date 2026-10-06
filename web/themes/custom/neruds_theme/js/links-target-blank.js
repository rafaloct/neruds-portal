/**
 * @file
 * Garante que todos os links em páginas abram em nova aba (target="_blank")
 */
(function (Drupal) {
  'use strict';

  Drupal.behaviors.nerudsThemeLinksTargetBlank = {
    attach: function (context, settings) {
      // Processar todos os links que não têm target definido
      const links = context.querySelectorAll('a:not([target])');
      
      links.forEach(function(link) {
        const href = link.getAttribute('href');
        
        // Se for um link externo (começa com http:// ou https://)
        // e não for um link interno do site, adicionar target="_blank"
        if (href && (href.startsWith('http://') || href.startsWith('https://'))) {
          // Verificar se não é um link interno do site
          const currentHost = window.location.hostname;
          try {
            const linkUrl = new URL(href);
            // Se o hostname for diferente, é um link externo
            if (linkUrl.hostname !== currentHost) {
              link.setAttribute('target', '_blank');
              link.setAttribute('rel', 'noopener noreferrer');
              
              // Adicionar ícone visual se não tiver
              if (!link.querySelector('.bi-box-arrow-up-right')) {
                const icon = document.createElement('i');
                icon.className = 'bi bi-box-arrow-up-right ms-1';
                icon.setAttribute('aria-hidden', 'true');
                link.appendChild(icon);
              }
            }
          } catch (e) {
            // Se não conseguir fazer parse da URL, assumir que é externo
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener noreferrer');
          }
        }
      });
      
      // Garantir que links com target="_blank" tenham rel="noopener noreferrer"
      const blankLinks = context.querySelectorAll('a[target="_blank"]');
      blankLinks.forEach(function(link) {
        if (!link.getAttribute('rel')) {
          link.setAttribute('rel', 'noopener noreferrer');
        } else if (!link.getAttribute('rel').includes('noopener')) {
          link.setAttribute('rel', link.getAttribute('rel') + ' noopener noreferrer');
        }
      });
    }
  };

})(Drupal);

