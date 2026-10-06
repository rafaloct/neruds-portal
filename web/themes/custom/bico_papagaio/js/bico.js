/**
 * @file bico.js
 * BICO DO PAPAGAIO — Interações JavaScript
 * Tema decolonial NERUDS/UFT
 */

(function (Drupal) {
  'use strict';

  // =========================================================
  // HEADER — adiciona sombra ao rolar
  // =========================================================
  Drupal.behaviors.bicoNavigation = {
    attach: function (context, settings) {
      // Executar apenas uma vez no document
      if (context !== document) return;

      var header = document.querySelector('.site-header');
      if (!header || header.dataset.bicoScrollBound) return;

      header.dataset.bicoScrollBound = '1';

      function handleScroll() {
        if (window.scrollY > 60) {
          header.classList.add('is-scrolled');
        } else {
          header.classList.remove('is-scrolled');
        }
      }

      window.addEventListener('scroll', handleScroll, { passive: true });
      handleScroll();
    }
  };

  // =========================================================
  // ANIMAÇÕES DE ENTRADA — Intersection Observer
  // =========================================================
  Drupal.behaviors.bicoAnimations = {
    attach: function (context, settings) {
      // Respeitar preferência de redução de movimento
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

      var selector = '.card-content, .card-pesquisador, .card-publicacao, .card-projeto, .stat-card';
      var elements = context.querySelectorAll(selector);
      if (!elements.length) return;

      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('bico-visible');
            observer.unobserve(entry.target);
          }
        });
      }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });

      elements.forEach(function (el, i) {
        if (el.dataset.bicoAnimated) return;
        el.dataset.bicoAnimated = '1';
        el.style.opacity = '0';
        el.style.transform = 'translateY(16px)';
        el.style.transition = 'opacity 0.45s ease ' + (i * 0.06) + 's, transform 0.45s ease ' + (i * 0.06) + 's';
        observer.observe(el);
      });

      // Injetar CSS de estado visível uma única vez
      if (!document.getElementById('bico-anim-css')) {
        var style = document.createElement('style');
        style.id = 'bico-anim-css';
        style.textContent = '.bico-visible { opacity: 1 !important; transform: translateY(0) !important; }';
        document.head.appendChild(style);
      }
    }
  };

  // =========================================================
  // CONTADOR DE ESTATÍSTICAS
  // =========================================================
  Drupal.behaviors.bicoCounters = {
    attach: function (context, settings) {
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

      var counters = context.querySelectorAll('.stat-card__number[data-target]');
      if (!counters.length) return;

      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          var el = entry.target;
          if (el.dataset.bicoCountDone) return;
          el.dataset.bicoCountDone = '1';

          var target = parseInt(el.dataset.target, 10);
          var duration = 1400;
          var step = Math.ceil(target / (duration / 16));
          var current = 0;

          var timer = setInterval(function () {
            current = Math.min(current + step, target);
            el.textContent = current.toLocaleString('pt-BR');
            if (current >= target) clearInterval(timer);
          }, 16);

          observer.unobserve(el);
        });
      }, { threshold: 0.5 });

      counters.forEach(function (el) { observer.observe(el); });
    }
  };

  // =========================================================
  // SMOOTH SCROLL para âncoras internas
  // =========================================================
  Drupal.behaviors.bicoSmoothScroll = {
    attach: function (context, settings) {
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

      var links = context.querySelectorAll('a[href^="#"]:not([href="#"])');
      links.forEach(function (link) {
        if (link.dataset.bicoScroll) return;
        link.dataset.bicoScroll = '1';

        link.addEventListener('click', function (e) {
          var target = document.querySelector(this.getAttribute('href'));
          if (!target) return;
          e.preventDefault();
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
      });
    }
  };

  // =========================================================
  // IMAGENS — fallback de erro
  // =========================================================
  Drupal.behaviors.bicoImageFallback = {
    attach: function (context, settings) {
      var imgs = context.querySelectorAll('.card-pesquisador__foto img');
      imgs.forEach(function (img) {
        if (img.dataset.bicoFallback) return;
        img.dataset.bicoFallback = '1';

        img.addEventListener('error', function () {
          var wrapper = this.closest('.card-pesquisador__foto');
          if (wrapper) {
            var nome = wrapper.dataset.nome || '';
            var inicial = nome.charAt(0).toUpperCase() || '?';
            this.style.display = 'none';
            wrapper.classList.add('card-pesquisador__foto--avatar');
            if (!wrapper.textContent.trim()) {
              wrapper.textContent = inicial;
            }
          }
        });
      });
    }
  };

})(Drupal);
