/**
 * @file
 * JavaScript customizado do tema NERUDS/UFT
 */

(function (Drupal, $) {
  'use strict';

  /**
   * Comportamentos do tema NERUDS
   */
  Drupal.behaviors.nerudsTheme = {
    attach: function (context, settings) {
      
      // Inicializar tooltips Bootstrap
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });

      // Inicializar popovers Bootstrap
      var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
      var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
      });

      // Smooth scroll para âncoras
      $('a[href^="#"]', context).on('click', function (event) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
          event.preventDefault();
          $('html, body').stop().animate({
            scrollTop: target.offset().top - 70
          }, 1000);
        }
      });

      // Filtros AJAX para Views
      once('nerudsFilters', '.neruds-view-filters', context).forEach(function (element) {
        var $filters = $(element);
        $filters.find('select, input[type="checkbox"]').on('change', function () {
          // Disparar atualização da view via AJAX
          var $form = $filters.closest('form');
          if ($form.length) {
            // Trigger AJAX se disponível
            if (typeof Drupal.ajax !== 'undefined') {
              var $submit = $form.find('input[type="submit"]');
              if ($submit.length) {
                $submit.trigger('click');
              }
            }
          }
        });
      });

      // Carrossel de projetos
      once('nerudsCarousel', '.neruds-projects-carousel', context).forEach(function (element) {
        // Inicializar carrossel Bootstrap se disponível
        if (typeof bootstrap !== 'undefined' && bootstrap.Carousel) {
          new bootstrap.Carousel(element);
        }
      });

      // Menu mobile
      once('nerudsMobileNav', '.navbar-toggler', context).forEach(function (element) {
        $(element).on('click', function () {
          $(this).toggleClass('active');
        });
      });

      // Badges de taxonomia
      once('nerudsBadges', '.taxonomy-badge', context).forEach(function (element) {
        $(element).on('click', function (e) {
          // Adicionar efeito visual ao clicar
          $(this).addClass('pulse');
          setTimeout(function() {
            $(this).removeClass('pulse');
          }.bind(this), 300);
        });
      });

    }
  };

  /**
   * Função para atualizar conteúdo relacionado via AJAX
   */
  Drupal.nerudsUpdateRelatedContent = function (nodeId, taxonomies) {
    $.ajax({
      url: '/neruds/ajax/related-content',
      method: 'POST',
      data: {
        node_id: nodeId,
        taxonomies: taxonomies
      },
      success: function (response) {
        $('.neruds-related-content').html(response.content);
      }
    });
  };

  /**
   * Função para filtrar conteúdo por taxonomia
   */
  Drupal.nerudsFilterByTaxonomy = function (taxonomyId, taxonomyType) {
    var url = '';
    switch (taxonomyType) {
      case 'ods':
        url = '/artigos-por-ods?ods=' + taxonomyId;
        break;
      case 'eixo':
        url = '/projetos-eixo-tematico?eixo=' + taxonomyId;
        break;
      case 'microrregiao':
        url = '/projetos-por-microrregiao?microrregiao=' + taxonomyId;
        break;
      default:
        return;
    }
    if (url) {
      window.location.href = url;
    }
  };

})(Drupal, jQuery);

