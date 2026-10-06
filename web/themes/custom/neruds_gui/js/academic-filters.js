(function (Drupal, once) {
  'use strict';
  Drupal.behaviors.nerudsAcademicFilters = {
    attach(context) {
      once('neruds-academic-filter', '.view-view-publicacoes-taxonomia select[multiple]', context).forEach(select => {
        const label = select.labels[0];
        if (!label || select.disabled) return;
        const details = document.createElement('details');
        details.className = 'neruds-filter';
        const summary = document.createElement('summary');
        summary.id = select.id + '-summary';
        const options = document.createElement('div');
        options.className = 'neruds-filter__options';
        const update = () => {
          summary.textContent = label.textContent.trim() + ': ' + (select.selectedOptions.length ? Drupal.t('@count selecionados', {'@count': select.selectedOptions.length}) : Drupal.t('Todos'));
          options.querySelectorAll('input').forEach((input, index) => { input.checked = select.options[index].selected; });
        };
        Array.from(select.options).forEach(option => {
          const row = document.createElement('label');
          const input = document.createElement('input');
          input.type = 'checkbox';
          input.disabled = option.disabled;
          input.checked = option.selected;
          row.append(input, document.createTextNode(option.textContent));
          options.append(row);
          // The original select remains the single submitted source of truth.
          input.addEventListener('change', event => {
            event.stopPropagation();
            option.selected = input.checked;
            select.dispatchEvent(new Event('change', {bubbles: true}));
            update();
          });
        });
        details.append(summary, options);
        select.after(details);
        select.hidden = true;
        label.hidden = true;
        select.addEventListener('change', update);
        select.form?.addEventListener('reset', () => setTimeout(update, 0));
        details.addEventListener('keydown', event => {
          if (event.key === 'Escape') { details.open = false; summary.focus(); event.stopPropagation(); }
        });
        update();
      });
    }
  };
})(Drupal, once);
