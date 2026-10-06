/**
 * @file search-bar.js
 * NERUDS Search Bar — Versão Otimizada com Overlay Fix, Debounce e Contexto.
 */
(function ($, Drupal, once) {
  'use strict';

  function debounce(func, wait) {
    var timeout;
    return function () {
      var context = this, args = arguments;
      clearTimeout(timeout);
      timeout = setTimeout(function () {
        func.apply(context, args);
      }, wait);
    };
  }

  Drupal.behaviors.nerudsSearchBar = {
    attach: function (context, settings) {
      initSearchOverlays(context);
      initCategoryChips(context);
      initAutoSubmit(context);
      initProjectScope(context);
    }
  };

  function initProjectScope(context) {
    var scope = $('[data-project-scope]').attr('data-project-scope');
    if (scope) {
      $('[name=keys]').attr('placeholder', 'Buscar em: ' + scope);
      if ($('[name=project_scope]').length === 0) {
        $('[data-neruds-search] form').append('<input type="hidden" name="project_scope" value="' + scope + '">');
      }
    }
  }

  function initSearchOverlays(context) {
    $(once('searchOverlay', '[data-search-trigger]', context)).each(function () {
      var $trigger = $(this);
      var $wrapper = $trigger.closest('[data-neruds-search]');
      var $panel = $wrapper.find('.neruds-search__panel');
      var $input = $wrapper.find('[data-search-input]');

      $trigger.on('click', function (e) {
        e.preventDefault();
        openPanel($panel, $trigger, $input);
      });

      $wrapper.find('[data-search-close]').on('click', function () {
        closePanel($panel, $trigger);
      });

      $panel.on('click', function (e) {
        if (e.target === this) {
          closePanel($panel, $trigger);
        }
      });

      $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $panel.attr('data-state') === 'open') {
          closePanel($panel, $trigger);
        }
      });
    });
  }

  function openPanel($panel, $trigger, $input) {
    $panel.attr('data-state', 'open').removeAttr('hidden');
    $trigger.attr('aria-expanded', 'true');
    $('body').css('overflow', 'hidden');
    setTimeout(function () { $input.focus(); }, 150);
  }

  function closePanel($panel, $trigger) {
    $panel.attr('data-state', 'closed').attr('hidden', 'true');
    $trigger.attr('aria-expanded', 'false');
    $('body').css('overflow', '');
    $trigger.focus();
  }

  function initCategoryChips(context) {
    $(once('categoryChips', '[data-neruds-search]', context)).each(function () {
      var $block = $(this);
      var $chips = $block.find('[data-search-category]');
      var $hiddenInput = $block.find('[data-search-type]');
      var $form = $block.find('form');

      $chips.on('click', function () {
        var $chip = $(this);
        var category = $chip.attr('data-search-category');

        if (category === 'all') {
          $chips.removeClass('neruds-search__chip--active').attr('aria-checked', 'false');
          $chip.addClass('neruds-search__chip--active').attr('aria-checked', 'true');
        } else {
          $block.find('[data-search-category="all"]').removeClass('neruds-search__chip--active').attr('aria-checked', 'false');
          $chip.toggleClass('neruds-search__chip--active');
          $chip.attr('aria-checked', $chip.hasClass('neruds-search__chip--active') ? 'true' : 'false');

          if ($block.find('.neruds-search__chip--active').length === 0) {
            $block.find('[data-search-category="all"]').addClass('neruds-search__chip--active').attr('aria-checked', 'true');
          }
        }

        var activeCats = [];
        $block.find('.neruds-search__chip--active').each(function () {
          var val = $(this).attr('data-search-category');
          if (val !== 'all') activeCats.push(val);
        });

        if ($hiddenInput.length) {
          $hiddenInput.val(activeCats.join(','));
        }

        var $searchInput = $block.find('[data-search-input]');
        if ($form.length && $searchInput.val().trim() !== '') {
          triggerSearch($form);
        }
      });
    });
  }

  function initAutoSubmit(context) {
    $(once('autoSubmit', '[data-neruds-search]', context)).each(function () {
      var $block = $(this);
      var $input = $block.find('[data-search-input]');
      var $form = $block.find('form');
      
      if (!$input.length || !$form.length) return;

      var debouncedSubmit = debounce(function () {
        triggerSearch($form);
      }, 350);

      $input.on('input', function () {
        var isOverlayOpen = $block.find('.neruds-search__panel[data-state="open"]').length > 0;
        if (!isOverlayOpen && $input.val().length > 2) {
          debouncedSubmit();
        }
      });
    });
  }

  function triggerSearch($form) {
    var $submitBtn = $form.find('input[type="submit"], .form-submit');
    if ($submitBtn.length) {
      $submitBtn.click();
    } else {
      $form.submit();
    }
  }

  $(once('nerudsKeyboard', document.documentElement)).on('keydown', function (e) {
    if (e.key !== '/' || e.ctrlKey || e.metaKey || e.altKey) return;
    if (['INPUT', 'TEXTAREA', 'SELECT'].indexOf(document.activeElement.tagName) > -1) return;
    
    var $trigger = $('[data-search-trigger]');
    if ($trigger.length) {
      e.preventDefault();
      $trigger.first().click();
    }
  });

})(jQuery, Drupal, once);
