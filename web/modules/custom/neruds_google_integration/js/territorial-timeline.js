(function (Drupal, drupalSettings, once) {
  const colors = {
    Simposio: '#E74C3C',
    'Visita de campo': '#27AE60',
    Reuniao: '#3498DB',
    'Defesa de TCC/Tese': '#9B59B6',
    Extensao: '#F39C12',
    Evento: '#2C5530',
  };

  function googleCalendarUrl(event) {
    const start = event.start ? event.start.toISOString().replace(/[-:]/g, '').replace(/\.\d{3}Z$/, 'Z') : '';
    const endDate = event.end || new Date((event.start || new Date()).getTime() + 60 * 60 * 1000);
    const end = endDate.toISOString().replace(/[-:]/g, '').replace(/\.\d{3}Z$/, 'Z');
    const params = new URLSearchParams({
      action: 'TEMPLATE',
      text: event.title,
      dates: `${start}/${end}`,
      details: event.extendedProps?.description || event.url || '',
      location: event.extendedProps?.location || event.extendedProps?.territory || '',
    });
    return `https://calendar.google.com/calendar/render?${params.toString()}`;
  }

  function renderNextEvent(container, event) {
    const start = event.start ? new Date(event.start) : new Date();
    const tempEvent = {
      title: event.title,
      start,
      end: event.end ? new Date(event.end) : null,
      url: event.url,
      extendedProps: event.extendedProps || {},
    };
    const label = document.createElement('strong');
    label.textContent = 'Proximo evento: ';
    const link = document.createElement('a');
    link.href = event.url || '#';
    link.textContent = event.title;
    const google = document.createElement('a');
    google.className = 'neruds-territorial-timeline__google';
    google.href = googleCalendarUrl(tempEvent);
    google.target = '_blank';
    google.rel = 'noopener';
    google.textContent = 'Adicionar ao Google Calendar';
    container.replaceChildren(label, link, google);
  }

  Drupal.behaviors.nerudsTerritorialTimeline = {
    attach(context) {
      once('neruds-territorial-timeline', '[data-neruds-timeline]', context).forEach((element) => {
        if (!window.FullCalendar) {
          return;
        }
        const endpoint = drupalSettings.nerudsTimeline?.endpoint || '/neruds/api/calendar';
        const wrapper = element.closest('.neruds-territorial-timeline');
        const nextEvent = wrapper.querySelector('[data-neruds-next-event]');
        const calendar = new FullCalendar.Calendar(element, {
          initialView: window.innerWidth < 720 ? 'listMonth' : 'dayGridMonth',
          locale: 'pt-br',
          height: 'auto',
          events(fetchInfo, success, failure) {
            fetch(endpoint)
              .then((response) => response.json())
              .then((items) => {
                const events = items.map((item) => ({
                  ...item,
                  backgroundColor: colors[item.extendedProps?.type] || colors.Event,
                  borderColor: colors[item.extendedProps?.type] || colors.Event,
                }));
                if (events[0] && nextEvent) {
                  renderNextEvent(nextEvent, events[0]);
                }
                success(events);
              })
              .catch(failure);
          },
          eventClick(info) {
            if (typeof window.gtag === 'function') {
              window.gtag('event', 'neruds_calendar_event_click', {
                event_title: info.event.title,
              });
            }
            if (info.event.url) {
              window.open(info.event.url, '_blank', 'noopener');
              info.jsEvent.preventDefault();
            }
          },
        });
        calendar.render();
      });
    },
  };
})(Drupal, drupalSettings, once);
