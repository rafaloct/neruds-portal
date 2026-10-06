(function ($, Drupal, drupalSettings) {
  'use strict';

  function cssEscape(value) {
    if (window.CSS && window.CSS.escape) {
      return window.CSS.escape(value);
    }
    return String(value).replace(/([ #;?%&,.+*~\':"!^$[\]()=>|/@])/g, '\\$1');
  }

  function findAltInput(button) {
    const id = button.attr('id') || '';
    if (id) {
      const inferredId = id.replace(/-ai-alt-text-generation-\d+$/, '-alt');
      const inferred = $('#' + cssEscape(inferredId));
      if (inferred.length) {
        return inferred.first();
      }
    }

    const containers = [
      '.form-managed-file',
      '.js-form-managed-file',
      '[data-drupal-selector$="-widget"]',
      '.field--type-image',
      '.form-wrapper',
      'details',
      'form',
    ];
    for (const selector of containers) {
      const container = button.closest(selector);
      const input = container.find('input[name$="[alt]"], textarea[name$="[alt]"]').first();
      if (input.length) {
        return input;
      }
    }

    const form = button.closest('form');
    return form.find('input[name$="[alt]"], textarea[name$="[alt]"]').first();
  }

  function setBusy(button, busy) {
    const wrapper = button.closest('.ai-alt-text-generation-wrapper');
    if (busy) {
      if (!wrapper.find('.ajax-progress').length) {
        const message = Drupal.t('Generating alt text...');
        wrapper.append(
          '<div class="ajax-progress ajax-progress--throbber">' +
          '<div class="ajax-progress__throbber">&nbsp;</div>' +
          `<div class="ajax-progress__message">${message}</div>` +
          '</div>'
        );
      }
      button.hide().attr('aria-busy', 'true');
    }
    else {
      wrapper.find('.ajax-progress').remove();
      button.removeAttr('aria-busy');
      if (!drupalSettings.ai_image_alt_text.hide_button) {
        button.show();
      }
    }
  }

  function showMessage(text, type) {
    const messenger = new Drupal.Message();
    messenger.add(text, { type: type || 'status' });
  }

  Drupal.behaviors.nerudsAiAltTextFix = {
    attach(context) {
      $('.ai-alt-text-generation', context).each(function () {
        const button = $(this);
        if (button.data('neruds-ai-alt-text-fix')) {
          return;
        }
        button.data('neruds-ai-alt-text-fix', true);
        button.off('click').on('click', function (event) {
          event.preventDefault();
          event.stopImmediatePropagation();

          const activeButton = $(this);
          const altInput = findAltInput(activeButton);
          const fileId = activeButton.data('file-id');
          const lang = drupalSettings.ai_image_alt_text.lang || drupalSettings.path.currentLanguage || 'pt-br';

          setBusy(activeButton, true);
          altInput.prop('disabled', true);

          $.ajax({
            url: `${drupalSettings.path.baseUrl}admin/config/ai/ai_image_alt_text/generate/${fileId}/${lang}`,
            type: 'GET',
            timeout: 60000,
          })
            .done(function (response) {
              if (response && response.alt_text && altInput.length) {
                altInput.val(response.alt_text).trigger('input').trigger('change');
                showMessage(Drupal.t('AI suggested alternative text. Review it before saving.'), 'status');
              }
              else if (response && response.alt_text) {
                showMessage(Drupal.t('AI returned alternative text, but the target alt field was not found.'), 'warning');
              }
              else {
                showMessage(Drupal.t('AI did not return alternative text.'), 'warning');
              }
            })
            .fail(function (response) {
              const error = response.responseJSON && response.responseJSON.error
                ? response.responseJSON.error
                : Drupal.t('We could not create an Alt Text, please try again later.');
              showMessage(Drupal.t('Error: @error', { '@error': error }), 'warning');
            })
            .always(function () {
              altInput.prop('disabled', false);
              setBusy(activeButton, false);
            });
        });
      });
    },
  };
})(jQuery, Drupal, drupalSettings);
