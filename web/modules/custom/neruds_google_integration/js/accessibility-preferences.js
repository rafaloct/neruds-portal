(function (Drupal, drupalSettings, once) {
  const storageKey = 'nerudsAccessibilityPreferences';
  const preferences = [
    ['highContrast', 'neruds-a11y-high-contrast', 'Alto contraste'],
    ['grayscale', 'neruds-a11y-grayscale', 'Escala de cinza'],
    ['readable', 'neruds-a11y-readable', 'Leitura ampliada'],
    ['largeCursor', 'neruds-a11y-large-cursor', 'Cursor ampliado'],
  ];

  function readPreferences() {
    try {
      return JSON.parse(window.localStorage.getItem(storageKey)) || {};
    }
    catch (error) {
      return {};
    }
  }

  function writePreferences(values) {
    try {
      window.localStorage.setItem(storageKey, JSON.stringify(values));
    }
    catch (error) {
      // Preference storage is progressive enhancement.
    }
  }

  function applyPreferences(values) {
    preferences.forEach(([key, className]) => {
      document.body.classList.toggle(className, Boolean(values[key]));
    });
  }

  function ensureSkipLink() {
    if (document.querySelector('.neruds-a11y-skip')) {
      return;
    }
    const target = document.querySelector('main, #main, [role="main"]');
    if (!target) {
      return;
    }
    if (!target.id) {
      target.id = 'neruds-main-content';
    }
    const link = document.createElement('a');
    link.className = 'neruds-a11y-skip';
    link.href = `#${target.id}`;
    link.textContent = 'Pular para o conteudo principal';
    document.body.prepend(link);
  }

  function buildToolbar(values) {
    if (document.querySelector('[data-neruds-a11y-toolbar]')) {
      return;
    }

    const toolbar = document.createElement('aside');
    toolbar.className = 'neruds-a11y-toolbar';
    toolbar.dataset.nerudsA11yToolbar = 'true';
    toolbar.setAttribute('aria-label', 'Preferencias de acessibilidade');

    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'neruds-a11y-toolbar__toggle';
    toggle.setAttribute('aria-expanded', 'false');
    toggle.textContent = 'Acessibilidade';

    const panel = document.createElement('div');
    panel.className = 'neruds-a11y-toolbar__panel';
    panel.hidden = true;

    const title = document.createElement('p');
    title.className = 'neruds-a11y-toolbar__title';
    title.textContent = 'Preferencias';
    panel.append(title);

    preferences.forEach(([key, , label]) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'neruds-a11y-toolbar__button';
      button.dataset.preference = key;
      button.setAttribute('aria-pressed', values[key] ? 'true' : 'false');
      button.textContent = label;
      panel.append(button);
    });

    const reset = document.createElement('button');
    reset.type = 'button';
    reset.className = 'neruds-a11y-toolbar__button';
    reset.dataset.preferenceReset = 'true';
    reset.textContent = 'Restaurar padrao';
    panel.append(reset);

    toolbar.append(toggle, panel);
    document.body.append(toolbar);

    toggle.addEventListener('click', () => {
      panel.hidden = !panel.hidden;
      toggle.setAttribute('aria-expanded', panel.hidden ? 'false' : 'true');
    });

    panel.addEventListener('click', (event) => {
      const button = event.target.closest('button');
      if (!button) {
        return;
      }
      if (button.dataset.preferenceReset) {
        preferences.forEach(([key]) => {
          values[key] = false;
        });
      }
      else if (button.dataset.preference) {
        values[button.dataset.preference] = !values[button.dataset.preference];
      }
      applyPreferences(values);
      writePreferences(values);
      panel.querySelectorAll('[data-preference]').forEach((control) => {
        control.setAttribute('aria-pressed', values[control.dataset.preference] ? 'true' : 'false');
      });
    });
  }

  function ensureVlibras() {
    if (!drupalSettings.nerudsAccessibility?.vlibrasEnabled || document.querySelector('[vw]')) {
      return;
    }

    const container = document.createElement('div');
    container.setAttribute('vw', '');
    container.className = 'enabled';
    container.innerHTML = '<div vw-access-button class="active"></div><div vw-plugin-wrapper><div class="vw-plugin-top-wrapper"></div></div>';
    document.body.append(container);

    const script = document.createElement('script');
    script.src = 'https://vlibras.gov.br/app/vlibras-plugin.js';
    script.onload = () => {
      if (window.VLibras?.Widget) {
        new window.VLibras.Widget('https://vlibras.gov.br/app');
      }
    };
    document.body.append(script);
  }

  Drupal.behaviors.nerudsAccessibilityPreferences = {
    attach(context) {
      once('neruds-accessibility-preferences', 'body', context).forEach(() => {
        const values = readPreferences();
        applyPreferences(values);
        ensureSkipLink();
        buildToolbar(values);
        ensureVlibras();
      });
    },
  };
})(Drupal, drupalSettings, once);
