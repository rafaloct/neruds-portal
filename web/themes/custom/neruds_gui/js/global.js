/**
 * @file global.js
 * Script global NERUDS-GUI
 * Inicialização, utils, interações básicas
 */

(function() {
  'use strict';

  // =====================================================
  // INICIALIZAÇÃO DO TEMA
  // =====================================================

  document.addEventListener('DOMContentLoaded', function() {
    initializeTheme();
    setupMobileNav();
    setupAccessibility();
    setupInteractions();
  });

  // =====================================================
  // MOBILE NAV — hamburger toggle
  // =====================================================

  function setupMobileNav() {
    var toggle = document.querySelector('.nav-toggle');
    var nav    = document.getElementById('primary-nav');
    if (!toggle || !nav) return;

    nav.querySelectorAll('[data-drupal-selector="primary-nav-submenu-toggle-button"]').forEach(function(button) {
      var item = button.parentElement;
      button.removeAttribute('aria-hidden');
      button.tabIndex = 0;
      button.type = 'button';
      item.dataset.submenuOpen = 'false';
      function closeSubmenu() {
        button.setAttribute('aria-expanded', 'false');
        item.dataset.submenuOpen = 'false';
      }
      button.addEventListener('click', function() {
        var open = button.getAttribute('aria-expanded') !== 'true';
        button.setAttribute('aria-expanded', String(open));
        item.dataset.submenuOpen = String(open);
      });
      item.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && item.dataset.submenuOpen === 'true') {
          event.stopPropagation();
          closeSubmenu();
          button.focus();
        }
      });
      item.addEventListener('focusout', function(event) {
        if (!item.contains(event.relatedTarget)) closeSubmenu();
      });
      document.addEventListener('click', function(event) {
        if (!item.contains(event.target)) closeSubmenu();
      });
    });

    toggle.addEventListener('click', function() {
      var isOpen = toggle.getAttribute('aria-expanded') === 'true';
      toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
      nav.setAttribute('data-state', isOpen ? 'closed' : 'open');
      // Update aria-label for accessibility
      toggle.setAttribute('aria-label', isOpen
        ? 'Abrir menu de navegação'
        : 'Fechar menu de navegação'
      );
    });

    // Close on outside click
    document.addEventListener('click', function(e) {
      if (!toggle.contains(e.target) && !nav.contains(e.target)) {
        toggle.setAttribute('aria-expanded', 'false');
        nav.setAttribute('data-state', 'closed');
      }
    });

    // Close on ESC
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && nav.getAttribute('data-state') === 'open') {
        toggle.setAttribute('aria-expanded', 'false');
        nav.setAttribute('data-state', 'closed');
        toggle.focus();
      }
    });

    // Close compact nav when resizing to the full horizontal navigation.
    var mq = window.matchMedia('(min-width: 1101px)');
    mq.addEventListener('change', function(e) {
      if (e.matches) {
        toggle.setAttribute('aria-expanded', 'false');
        nav.setAttribute('data-state', 'closed');
      }
    });
  }

  // =====================================================
  // TEMA (Light/Dark Mode)
  // =====================================================

  function initializeTheme() {
    const html = document.documentElement;
    const themeToggle = document.querySelector('[data-theme-toggle]');

    // Verificar preferência do sistema
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const savedTheme = localStorage.getItem('neruds-theme');
    const currentTheme = savedTheme || (prefersDark ? 'dark' : 'light');

    // Aplicar tema
    html.setAttribute('data-theme', currentTheme);

    // Toggle de tema
    if (themeToggle) {
      themeToggle.addEventListener('click', function() {
        const newTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('neruds-theme', newTheme);
      });
    }

    // Monitorar mudanças do sistema
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
      const newTheme = e.matches ? 'dark' : 'light';
      html.setAttribute('data-theme', newTheme);
      localStorage.setItem('neruds-theme', newTheme);
    });
  }

  // =====================================================
  // ACESSIBILIDADE
  // =====================================================

  function setupAccessibility() {
    // Skip link ao focar
    const skipLink = document.querySelector('.skip-link');
    if (skipLink) {
      skipLink.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector('#main-content');
        if (target) {
          target.focus();
          target.scrollIntoView({ behavior: 'smooth' });
        }
      });
    }

    // Fechar menu ao clicar fora
    const navToggle = document.querySelector('.navbar-toggler');
    const navCollapse = document.querySelector('.navbar-collapse');

    if (navToggle && navCollapse) {
      document.addEventListener('click', function(event) {
        if (!event.target.closest('.navbar')) {
          const bsCollapse = new (window.bootstrap?.Collapse || function() {})(navCollapse, { toggle: false });
          if (bsCollapse.hide) bsCollapse.hide();
        }
      });
    }

    // Anunciar mudanças dinâmicas
    const liveRegions = document.querySelectorAll('[aria-live]');
    liveRegions.forEach(function(region) {
      region.setAttribute('role', 'status');
    });
  }

  // =====================================================
  // INTERAÇÕES
  // =====================================================

  function setupInteractions() {
    // Smooth scroll para âncoras
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
      anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (!href || href === '#' || this.classList.contains('skip-link')) return;
        const target = document.getElementById(decodeURIComponent(href.slice(1)));

        if (target && href !== '#') {
          e.preventDefault();
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });

          // Atualizar URL sem scroll padrão
          history.pushState(null, null, href);
        }
      });
    });

    // Hover lift em cards
    const cards = document.querySelectorAll('.card');
    cards.forEach(function(card) {
      card.addEventListener('mouseenter', function() {
        this.classList.add('hover-lift');
      });
      card.addEventListener('mouseleave', function() {
        this.classList.remove('hover-lift');
      });
    });

    // Lazy load de imagens
    if ('IntersectionObserver' in window) {
      const images = document.querySelectorAll('img[data-src]');
      const imageObserver = new IntersectionObserver(function(entries, observer) {
        entries.forEach(function(entry) {
          if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.getAttribute('data-src');
            img.removeAttribute('data-src');
            observer.unobserve(img);
          }
        });
      });

      images.forEach(function(img) {
        imageObserver.observe(img);
      });
    }
  }

  // =====================================================
  // UTILITÁRIOS
  // =====================================================

  window.NERUDS = window.NERUDS || {};

  /**
   * Announce message to screen readers
   */
  NERUDS.announce = function(message, priority) {
    const ariaLive = document.querySelector('[aria-live="' + (priority || 'polite') + '"]');
    if (ariaLive) {
      ariaLive.textContent = message;
    }
  };

  /**
   * Toggle active state
   */
  NERUDS.toggleActive = function(element, className) {
    className = className || 'active';
    if (element) {
      element.classList.toggle(className);
    }
  };

  /**
   * Detectar redimensionamento de viewport
   */
  NERUDS.onBreakpoint = function(callback) {
    const breakpoints = {
      xs: '(max-width: 639px)',
      sm: '(min-width: 640px)',
      md: '(min-width: 768px)',
      lg: '(min-width: 1024px)',
      xl: '(min-width: 1280px)',
      '2xl': '(min-width: 1536px)'
    };

    for (let bp in breakpoints) {
      const mq = window.matchMedia(breakpoints[bp]);
      mq.addEventListener('change', function() {
        callback(bp, mq.matches);
      });
    }
  };

})();
