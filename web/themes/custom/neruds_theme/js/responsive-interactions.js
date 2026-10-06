/**
 * @file
 * JavaScript para interações responsivas do tema NERUDS
 * Baseado em Bootstrap 5 com funcionalidades customizadas
 */

(function ($, Drupal, once) {
  'use strict';

  /**
   * Inicialização das funcionalidades responsivas
   */
  Drupal.behaviors.nerudsResponsive = {
    attach: function (context, settings) {
      
      // Inicializar tooltips Bootstrap
      once('neruds-tooltips', '[data-bs-toggle="tooltip"]', context).forEach(function (element) {
        new bootstrap.Tooltip(element);
      });

      // Inicializar popovers Bootstrap
      once('neruds-popovers', '[data-bs-toggle="popover"]', context).forEach(function (element) {
        new bootstrap.Popover(element);
      });

      // Animação de badges de taxonomia
      once('taxonomy-badges-animation', '.taxonomy-badge', context).forEach(function (badge) {
        badge.addEventListener('mouseenter', function() {
          this.classList.add('pulse');
        });
        
        badge.addEventListener('animationend', function() {
          this.classList.remove('pulse');
        });
      });

      // Lazy loading para imagens
      once('neruds-lazy-images', 'img[loading="lazy"]', context).forEach(function (img) {
        if ('IntersectionObserver' in window) {
          const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
              if (entry.isIntersecting) {
                const img = entry.target;
                img.classList.add('fade-in-up');
                observer.unobserve(img);
              }
            });
          });
          imageObserver.observe(img);
        }
      });

      // Smooth scroll para links internos
      once('neruds-smooth-scroll', 'a[href^="#"]', context).forEach(function (link) {
        link.addEventListener('click', function(e) {
          const targetId = this.getAttribute('href');
          const targetElement = document.querySelector(targetId);
          
          if (targetElement) {
            e.preventDefault();
            targetElement.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            });
          }
        });
      });

      // Funcionalidade de busca em tempo real para Views
      once('neruds-live-search', '.view-filters input[type="search"]', context).forEach(function (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
          clearTimeout(searchTimeout);
          const searchTerm = this.value.toLowerCase();
          
          searchTimeout = setTimeout(() => {
            const cards = document.querySelectorAll('.neruds-card');
            
            cards.forEach(card => {
              const title = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
              const content = card.querySelector('.card-text')?.textContent.toLowerCase() || '';
              
              if (title.includes(searchTerm) || content.includes(searchTerm) || searchTerm === '') {
                card.closest('.col-12, .col-sm-6, .col-lg-4, .col-xl-3')?.style.setProperty('display', 'block');
                card.classList.add('fade-in-up');
              } else {
                card.closest('.col-12, .col-sm-6, .col-lg-4, .col-xl-3')?.style.setProperty('display', 'none');
              }
            });
          }, 300);
        });
      });

      // Contador de caracteres para textareas
      once('neruds-char-counter', 'textarea[maxlength]', context).forEach(function (textarea) {
        const maxLength = textarea.getAttribute('maxlength');
        const counter = document.createElement('small');
        counter.className = 'form-text text-muted char-counter';
        
        const updateCounter = () => {
          const remaining = maxLength - textarea.value.length;
          counter.textContent = `${remaining} caracteres restantes`;
          
          if (remaining < 50) {
            counter.classList.add('text-warning');
          } else {
            counter.classList.remove('text-warning');
          }
          
          if (remaining < 10) {
            counter.classList.add('text-danger');
            counter.classList.remove('text-warning');
          } else {
            counter.classList.remove('text-danger');
          }
        };
        
        textarea.parentNode.appendChild(counter);
        textarea.addEventListener('input', updateCounter);
        updateCounter();
      });

      // Funcionalidade de favoritos (localStorage)
      once('neruds-favorites', '.btn-favorite', context).forEach(function (btn) {
        const nodeId = btn.dataset.nodeId;
        const favorites = JSON.parse(localStorage.getItem('neruds_favorites') || '[]');
        
        // Atualizar estado inicial
        if (favorites.includes(nodeId)) {
          btn.classList.add('active');
          btn.innerHTML = '<i class="bi bi-heart-fill"></i> Favoritado';
        }
        
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          
          let currentFavorites = JSON.parse(localStorage.getItem('neruds_favorites') || '[]');
          
          if (currentFavorites.includes(nodeId)) {
            // Remover dos favoritos
            currentFavorites = currentFavorites.filter(id => id !== nodeId);
            this.classList.remove('active');
            this.innerHTML = '<i class="bi bi-heart"></i> Favoritar';
          } else {
            // Adicionar aos favoritos
            currentFavorites.push(nodeId);
            this.classList.add('active');
            this.innerHTML = '<i class="bi bi-heart-fill"></i> Favoritado';
          }
          
          localStorage.setItem('neruds_favorites', JSON.stringify(currentFavorites));
          
          // Mostrar toast de confirmação
          showToast(currentFavorites.includes(nodeId) ? 'Adicionado aos favoritos!' : 'Removido dos favoritos!');
        });
      });

      // Funcionalidade de compartilhamento nativo
      once('neruds-share', '.btn-share', context).forEach(function (btn) {
        btn.addEventListener('click', async function(e) {
          e.preventDefault();
          
          const title = this.dataset.title || document.title;
          const url = this.dataset.url || window.location.href;
          const text = this.dataset.text || 'Confira este conteúdo do NERUDS/UFT';
          
          if (navigator.share) {
            try {
              await navigator.share({ title, text, url });
            } catch (err) {
              console.log('Erro ao compartilhar:', err);
              fallbackShare(title, url);
            }
          } else {
            fallbackShare(title, url);
          }
        });
      });

      // Filtros dinâmicos para taxonomias
      once('neruds-taxonomy-filters', '.taxonomy-filter', context).forEach(function (filter) {
        filter.addEventListener('change', function() {
          const filterType = this.dataset.filterType;
          const filterValue = this.value;
          const cards = document.querySelectorAll('.neruds-card');
          
          cards.forEach(card => {
            const taxonomyBadges = card.querySelectorAll(`.badge-${filterType}`);
            let shouldShow = filterValue === '' || filterValue === 'all';
            
            if (!shouldShow) {
              taxonomyBadges.forEach(badge => {
                if (badge.textContent.toLowerCase().includes(filterValue.toLowerCase())) {
                  shouldShow = true;
                }
              });
            }
            
            const cardContainer = card.closest('.col-12, .col-sm-6, .col-lg-4, .col-xl-3');
            if (cardContainer) {
              cardContainer.style.display = shouldShow ? 'block' : 'none';
            }
          });
          
          // Atualizar contador de resultados
          updateResultsCounter();
        });
      });

      // Modo escuro/claro
      once('neruds-theme-toggle', '.theme-toggle', context).forEach(function (toggle) {
        const currentTheme = localStorage.getItem('neruds_theme') || 'light';
        document.documentElement.setAttribute('data-theme', currentTheme);
        
        toggle.addEventListener('click', function() {
          const currentTheme = document.documentElement.getAttribute('data-theme');
          const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
          
          document.documentElement.setAttribute('data-theme', newTheme);
          localStorage.setItem('neruds_theme', newTheme);
          
          // Atualizar ícone do botão
          const icon = this.querySelector('i');
          if (icon) {
            icon.className = newTheme === 'dark' ? 'bi bi-sun' : 'bi bi-moon';
          }
        });
      });

      // Scroll infinito para Views
      once('neruds-infinite-scroll', '.view-content[data-infinite-scroll]', context).forEach(function (viewContent) {
        let loading = false;
        let page = 1;
        
        const loadMore = async () => {
          if (loading) return;
          loading = true;
          
          try {
            // Aqui você implementaria a lógica para carregar mais conteúdo via AJAX
            // Por enquanto, apenas um placeholder
            console.log('Carregando página', page + 1);
            page++;
          } catch (error) {
            console.error('Erro ao carregar mais conteúdo:', error);
          } finally {
            loading = false;
          }
        };
        
        // Detectar quando o usuário está próximo do final da página
        const observer = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            if (entry.isIntersecting && !loading) {
              loadMore();
            }
          });
        }, { threshold: 0.1 });
        
        // Observar o último elemento da view
        const lastCard = viewContent.querySelector('.neruds-card:last-child');
        if (lastCard) {
          observer.observe(lastCard);
        }
      });
    }
  };

  /**
   * Função auxiliar para mostrar toast
   */
  function showToast(message, type = 'success') {
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    `;
    
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remover o toast após ser ocultado
    toast.addEventListener('hidden.bs.toast', () => {
      toast.remove();
    });
  }

  /**
   * Criar container para toasts
   */
  function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    container.style.zIndex = '1055';
    document.body.appendChild(container);
    return container;
  }

  /**
   * Fallback para compartilhamento
   */
  function fallbackShare(title, url) {
    if (navigator.clipboard) {
      navigator.clipboard.writeText(url).then(() => {
        showToast('Link copiado para a área de transferência!');
      });
    } else {
      // Fallback mais antigo
      const textArea = document.createElement('textarea');
      textArea.value = url;
      document.body.appendChild(textArea);
      textArea.select();
      document.execCommand('copy');
      document.body.removeChild(textArea);
      showToast('Link copiado para a área de transferência!');
    }
  }

  /**
   * Atualizar contador de resultados
   */
  function updateResultsCounter() {
    const visibleCards = document.querySelectorAll('.neruds-card:not([style*="display: none"])').length;
    const counter = document.querySelector('.results-counter');
    
    if (counter) {
      counter.textContent = `${visibleCards} resultado${visibleCards !== 1 ? 's' : ''} encontrado${visibleCards !== 1 ? 's' : ''}`;
    }
  }

  /**
   * Inicialização quando o DOM estiver pronto
   */
  document.addEventListener('DOMContentLoaded', function() {
    // Adicionar classe para animações CSS
    document.body.classList.add('neruds-loaded');
    
    // Inicializar componentes Bootstrap que precisam de inicialização manual
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Smooth scroll para o topo
    const backToTopBtn = document.querySelector('.back-to-top');
    if (backToTopBtn) {
      window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
          backToTopBtn.style.display = 'block';
        } else {
          backToTopBtn.style.display = 'none';
        }
      });
      
      backToTopBtn.addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    }
  });

})(jQuery, Drupal, once);