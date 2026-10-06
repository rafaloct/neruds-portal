/**
 * NERUDS Ant Design JavaScript
 * Funcionalidades básicas para melhorar UX
 */

(function(Drupal) {
  'use strict';

  Drupal.behaviors.nerudsAntDesign = {
    attach: function(context, settings) {
      // Inicializar tooltips/hover effects
      initCardHovers(context);
      
      // Inicializar animações
      initAnimations(context);
      
      // Inicializar formulários
      initForms(context);
    }
  };

  function initCardHovers(context) {
    var cards = context.querySelectorAll('.stat-card, .card-neruds');
    
    cards.forEach(function(card) {
      card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-2px)';
      });
      
      card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
      });
    });
  }

  function initAnimations(context) {
    // Fade in para elementos com classe neruds-fade-in
    var fadeElements = context.querySelectorAll('.neruds-fade-in');
    
    if (fadeElements.length > 0 && 'IntersectionObserver' in window) {
      var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
          if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
            observer.unobserve(entry.target);
          }
        });
      }, {
        threshold: 0.1
      });
      
      fadeElements.forEach(function(el) {
        el.style.opacity = '0';
        el.style.transform = 'translateY(10px)';
        el.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
        observer.observe(el);
      });
    }
  }

  function initForms(context) {
    // Adicionar classe form-neruds a formulários
    var forms = context.querySelectorAll('form:not(.form-neruds)');
    
    forms.forEach(function(form) {
      form.classList.add('form-neruds');
    });
    
    // Adicionar classe table-neruds a tabelas
    var tables = context.querySelectorAll('table:not(.table-neruds)');
    
    tables.forEach(function(table) {
      table.classList.add('table-neruds');
      table.classList.add('table-neruds--striped');
    });
  }

})(Drupal);
