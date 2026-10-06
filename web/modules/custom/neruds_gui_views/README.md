# NERUDS GUI — Views & Academic Layouts

Professional, responsive display templates for NERUDS academic content using modern design patterns.

## Features

✅ **5 Custom Twig Templates**
- News cards with ODS badges
- Event timeline cards
- Project showcase cards
- Researcher profile cards
- Master research view with filters

✅ **Academic-First Design**
- Magazine-style news layout
- Timeline visualization for events
- Research project cards with ODS alignment
- Grid layouts that adapt to content
- Sidebar filters for discoverability

✅ **Responsive & Accessible**
- Mobile-first design (320px+)
- Dark mode support
- WCAG 2.1 AA compliance
- Keyboard navigation ready
- Touch-friendly interface

✅ **Performance Optimized**
- Minimal CSS/JS overhead
- Lazy-loading images
- CSS Grid & Flexbox
- Fast animations
- No external dependencies

## Installation

```bash
# The module is included in NERUDS theme
drush pm:enable neruds_gui_views
drush cache:rebuild
```

## Templates

### 1. News Card (`node-news-teaser.html.twig`)

Magazine-style news article display with:
- Featured image
- ODS badge strip
- Publication date & author
- Excerpt with "Read More" link
- Research categories
- Hover animations

**Usage:** Node type "News" teaser display

**Grid:** 3 columns (responsive)

### 2. Event Card (`node-event-teaser.html.twig`)

Timeline-based event display featuring:
- Visual timeline marker
- Date box (month/day/year)
- Event status badge (Upcoming/Soon/Past)
- Location information
- ODS alignment indicators
- Quick registration button

**Usage:** Node type "Event" teaser display

**Grid:** Timeline (vertical stacking)

### 3. Project Card (`node-project-teaser.html.twig`)

Research project showcase with:
- Gradient header (ODS colors)
- Project status badge (Active/Completed/Proposal)
- Lead researcher attribution
- Project summary
- ODS goals with color-coded tags
- Research categories
- "Explore Project" CTA

**Usage:** Node type "Project" teaser display

**Grid:** 2-3 columns responsive

### 4. Researcher Card (`node-person-teaser.html.twig`)

Academic profile card featuring:
- Profile photo
- Name & specialty
- ODS alignment indicators
- Short biography
- Link to full profile

**Usage:** Node type "Person" teaser display

**Grid:** 4-5 columns (adaptive)

### 5. Research View (`views-view--neruds-research.html.twig`)

Master search & browse interface with:
- Hero banner with advanced search bar
- Sidebar filters (ODS, categories, content type)
- Main content grid
- Results counter
- Pagination
- Empty state messaging

**Usage:** Search/browse view wrapper

**Path:** `/pesquisa`

## Customization

### Colors

Edit CSS variables in theme:

```css
--color-babasu: #2E4632          /* Primary green */
--color-barro: #A0522D          /* Secondary brown *)
--color-palha: #E1C16E          /* Accent gold */
```

### Grid Layout

Modify columns in each template's CSS:

**News Grid (default 3 columns):**
```css
.research-grid {
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
}
```

Change `minmax(300px, 1fr)` to adjust card width.

### Hover Effects

All cards include smooth hover animations. Disable with:

```css
.neruds-news-card:hover {
  /* Remove or override */
  transform: none;
}
```

## Display Modes

Use these templates for different view modes:

| Mode | Use Case |
|------|----------|
| **teaser** | Grid/list views |
| **full** | Single page display |
| **compact** | Sidebar widgets |

Configure in Drupal admin:
- `/admin/structure/types/manage/news/display`
- `/admin/structure/types/manage/event/display`
- etc.

## Mobile Responsiveness

Templates are fully responsive:

| Breakpoint | Changes |
|-----------|---------|
| **768px** | Grid 3 → 2 columns |
| **640px** | Grid 2 → 1 column |
| **480px** | Sidebar hidden, full-width |

## Accessibility Features

- ✅ Semantic HTML (article, nav, aside)
- ✅ ARIA labels on buttons
- ✅ Color contrast ≥ 4.5:1
- ✅ Keyboard navigation (Tab, Enter)
- ✅ Focus visible indicators
- ✅ Alt text for images
- ✅ Form labels associated

## Dark Mode

Automatic dark mode support via `prefers-color-scheme: dark`:

```css
@media (prefers-color-scheme: dark) {
  .neruds-news-card {
    background: var(--color-gray-900);
    color: var(--color-white);
  }
}
```

## Performance Tips

1. **Lazy Load Images**
   ```html
   <img loading="lazy" src="..." alt="...">
   ```

2. **Optimize Card Size**
   - News: 280-320px wide
   - Events: 100% wide (timeline)
   - Projects: 280-300px wide
   - Researchers: 200-250px wide

3. **Limit Items Per Page**
   - Default: 12 items
   - Recommended max: 20 items

4. **Use Pagination**
   - Avoid infinite scroll for academic content
   - Users prefer discrete "pages"

## SEO Optimization

Templates include structured data:

- Microdata (schema.org Article, Event, Organization)
- Open Graph meta tags (via Metatag module)
- Proper heading hierarchy (h1, h2, h3)
- Descriptive link text

## Browser Support

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ✅ Mobile Chrome/Safari

## Troubleshooting

### Cards not showing?

1. Check module is enabled:
   ```bash
   drush pm:list | grep neruds_gui_views
   ```

2. Clear cache:
   ```bash
   drush cache:rebuild
   ```

3. Verify display mode is set to teaser in admin UI

### Colors not matching ODS?

- Check `field_ods_color` is populated
- Verify color format is hex (#RRGGBB)
- Inspect CSS in browser (F12)

### Grid not responsive?

- Check viewport meta tag in `<head>`
- Verify CSS `@media` queries are loaded
- Test in Firefox DevTools (Ctrl+Shift+M)

## Contributing

To add new templates:

1. Create `node-[type]-[display].html.twig`
2. Include CSS styling in `<style>` block
3. Document in this README
4. Test responsive breakpoints

## Related Modules

- `neruds_mcp` — API for content management
- `neruds_gui` — Theme & design system
- `search_api`, `facets` — Advanced filtering

## Future Enhancements

- [ ] Infinite scroll variant
- [ ] Masonry layout for images
- [ ] View mode switcher (grid/list/card)
- [ ] Print-friendly templates
- [ ] Email digest templates

---

**Last Updated:** 2026-03-24
**Compatibility:** Drupal 11.2.5+
**License:** All Rights Reserved (NERUDS)
