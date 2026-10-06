/**
 * @file
 * JavaScript para menu otimizado e barra de pesquisa NERUDS
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.nerudsMenuSearch = {
    attach: function (context, settings) {
      
      // Auto-focus na busca quando pressionar Ctrl+K ou Cmd+K
      $(document, context).once('nerudsSearchShortcut').on('keydown', function(e) {
        // Ctrl+K ou Cmd+K
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
          e.preventDefault();
          const searchInput = $('.neruds-search-input', context);
          if (searchInput.length) {
            searchInput.focus();
            searchInput.select();
          }
        }
        
        // ESC para limpar busca
        if (e.key === 'Escape') {
          const searchInput = $('.neruds-search-input:focus', context);
          if (searchInput.length) {
            searchInput.blur();
            searchInput.val('');
          }
        }
      });

      // Melhorar UX do formulário de busca
      $('.neruds-search-form', context).once('nerudsSearchForm').on('submit', function(e) {
        const searchInput = $(this).find('.neruds-search-input');
        const searchValue = searchInput.val().trim();
        
        if (!searchValue) {
          e.preventDefault();
          searchInput.focus();
          return false;
        }
        
        // Adiciona feedback visual
        const submitBtn = $(this).find('.neruds-search-btn');
        submitBtn.addClass('searching');
        setTimeout(function() {
          submitBtn.removeClass('searching');
        }, 1000);
      });

      // Sugestões de busca com autocomplete
      const searchInput = $('.neruds-search-input', context);
      const suggestionsContainer = $('#neruds-search-suggestions', context);
      
      let searchTimeout;
      let currentSuggestionIndex = -1;
      
      searchInput.once('nerudsSearchInput').on('input', function() {
        const value = $(this).val().trim();
        const wrapper = $(this).closest('.neruds-search-wrapper');
        
        // Feedback visual
        if (value.length > 0) {
          $(this).addClass('has-value');
          $(this).attr('aria-expanded', 'true');
        } else {
          $(this).removeClass('has-value');
          $(this).attr('aria-expanded', 'false');
          suggestionsContainer.removeClass('show');
          suggestionsContainer.empty();
          currentSuggestionIndex = -1;
          return;
        }
        
        // Debounce para evitar muitas requisições
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
          buscarSugestoes(value, suggestionsContainer, searchInput);
        }, 300);
      }).on('keydown', function(e) {
        const suggestions = suggestionsContainer.find('.suggestion-item');
        
        // Navegação por teclado nas sugestões
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          currentSuggestionIndex = Math.min(currentSuggestionIndex + 1, suggestions.length - 1);
          suggestions.removeClass('active').eq(currentSuggestionIndex).addClass('active');
          suggestionsContainer.attr('aria-activedescendant', suggestions.eq(currentSuggestionIndex).attr('id'));
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          currentSuggestionIndex = Math.max(currentSuggestionIndex - 1, -1);
          if (currentSuggestionIndex >= 0) {
            suggestions.removeClass('active').eq(currentSuggestionIndex).addClass('active');
            suggestionsContainer.attr('aria-activedescendant', suggestions.eq(currentSuggestionIndex).attr('id'));
          } else {
            suggestions.removeClass('active');
            suggestionsContainer.removeAttr('aria-activedescendant');
          }
        } else if (e.key === 'Enter' && currentSuggestionIndex >= 0) {
          e.preventDefault();
          suggestions.eq(currentSuggestionIndex).trigger('click');
        }
      });
      
      // Fecha sugestões ao clicar fora
      $(document).once('nerudsSearchOutside').on('click', function(e) {
        if (!$(e.target).closest('.neruds-search-wrapper').length) {
          suggestionsContainer.removeClass('show');
          searchInput.attr('aria-expanded', 'false');
        }
      });
      
      // Função para buscar sugestões
      function buscarSugestoes(termo, container, input) {
        if (termo.length < 2) {
          container.removeClass('show');
          return;
        }
        
        // Tenta usar Search API Autocomplete primeiro
        if (typeof Drupal !== 'undefined' && Drupal.ajax) {
          $.ajax({
            url: '/search-api-autocomplete/neruds_content_index',
            method: 'GET',
            data: {
              q: termo,
            },
            success: function(data) {
              if (data && data.length) {
                const suggestions = data.map(function(item) {
                  return {
                    title: item.label || item.value,
                    type: item.type || 'Conteúdo',
                    url: item.url || '#'
                  };
                });
                mostrarSugestoes(suggestions, container, input);
              } else {
                buscarSugestoesFallback(termo, container, input);
              }
            },
            error: function() {
              // Fallback: busca simples via Drupal Search
              buscarSugestoesFallback(termo, container, input);
            }
          });
        } else {
          // Fallback direto
          buscarSugestoesFallback(termo, container, input);
        }
      }
      
      // Função para mostrar sugestões
      function mostrarSugestoes(data, container, input) {
        container.empty();
        currentSuggestionIndex = -1;
        
        if (!data || !data.length) {
          container.removeClass('show');
          return;
        }
        
        // Limita a 8 sugestões
        const suggestions = data.slice(0, 8);
        
        suggestions.forEach(function(item, index) {
          const suggestionItem = $('<div>')
            .addClass('suggestion-item')
            .attr('id', 'suggestion-' + index)
            .attr('role', 'option')
            .html(
              '<div class="suggestion-item-title">' + item.title + '</div>' +
              (item.type ? '<div class="suggestion-item-type">' + item.type + '</div>' : '')
            )
            .on('click', function() {
              input.val(item.title);
              input.closest('form').submit();
            })
            .on('mouseenter', function() {
              container.find('.suggestion-item').removeClass('active');
              $(this).addClass('active');
              currentSuggestionIndex = index;
              container.attr('aria-activedescendant', $(this).attr('id'));
            });
          
          container.append(suggestionItem);
        });
        
        container.addClass('show');
        input.attr('aria-expanded', 'true');
      }
      
      // Fallback: busca simples via Drupal Search API Views ou Core Search
      function buscarSugestoesFallback(termo, container, input) {
        // Busca via Search API View (se disponível)
        $.ajax({
          url: '/api/search',
          method: 'GET',
          data: {
            q: termo,
            format: 'json',
            limit: 5
          },
          success: function(data) {
            if (data && data.results && data.results.length) {
              const suggestions = data.results.map(function(item) {
                return {
                  title: item.title || item.label,
                  type: item.type || 'Conteúdo',
                  url: item.url || '#'
                };
              });
              mostrarSugestoes(suggestions, container, input);
            } else {
              // Busca simples em títulos via Views
              buscarSugestoesSimples(termo, container, input);
            }
          },
          error: function() {
            // Último fallback: busca simples
            buscarSugestoesSimples(termo, container, input);
          }
        });
      }
      
      // Busca simples em títulos (último fallback)
      function buscarSugestoesSimples(termo, container, input) {
        // Busca via endpoint REST do Drupal (se disponível)
        $.ajax({
          url: '/api/node',
          method: 'GET',
          data: {
            'filter[title][value]': termo,
            'filter[title][operator]': 'CONTAINS',
            'page[limit]': 5,
            'fields[node--perfil-pesquisador]': 'title,path',
            'fields[node--projeto-pesquisa-extensao]': 'title,path',
            'fields[node--publicacao-cientifica]': 'title,path',
          },
          success: function(data) {
            if (data && data.data && data.data.length) {
              const suggestions = data.data.slice(0, 5).map(function(item) {
                return {
                  title: item.attributes.title,
                  type: item.type.replace('node--', '').replace(/-/g, ' '),
                  url: item.attributes.path ? item.attributes.path.alias : '/node/' + item.id
                };
              });
              mostrarSugestoes(suggestions, container, input);
            } else {
              container.removeClass('show');
            }
          },
          error: function() {
            // Se não houver API, apenas mostra loading e depois esconde
            container.removeClass('show');
          }
        });
      }

      // Melhorar acessibilidade do dropdown
      $('.dropdown-toggle', context).once('nerudsDropdown').on('click', function(e) {
        const $this = $(this);
        const expanded = $this.attr('aria-expanded') === 'true';
        
        // Fecha outros dropdowns abertos
        $('.dropdown-toggle').not($this).attr('aria-expanded', 'false');
      });

      // Fecha dropdowns ao clicar fora
      $(document).once('nerudsDropdownOutside').on('click', function(e) {
        if (!$(e.target).closest('.dropdown').length) {
          $('.dropdown-toggle[aria-expanded="true"]').each(function() {
            $(this).attr('aria-expanded', 'false');
            $(this).next('.dropdown-menu').removeClass('show');
          });
        }
      });

      // Animações suaves para dropdowns
      $('.dropdown-menu', context).once('nerudsDropdownAnimation').on('show.bs.dropdown', function() {
        $(this).css({
          'opacity': '0',
          'transform': 'translateY(-10px)'
        });
      }).on('shown.bs.dropdown', function() {
        $(this).css({
          'transition': 'opacity 0.3s ease, transform 0.3s ease',
          'opacity': '1',
          'transform': 'translateY(0)'
        });
      });
    }
  };

})(jQuery, Drupal);

