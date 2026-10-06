# NERUDS-GUI — Tema Premium Acadêmico

**Versão:** 2.0.0
**Compatibilidade:** Drupal 11, Drupal 12
**Base:** Olivero
**Tipo:** Tema Personalizado (Custom Theme)

---

## 🎨 Identidade Visual

### Cores — "Trama e Barro" (Decolonial)

| Cor | Hex | Uso |
|-----|-----|-----|
| **Verde Babaçu** | `#2E4632` | Primária, biodiversidade |
| **Terracota/Barro** | `#A0522D` | Secundária, território |
| **Palha/Ouro Velho** | `#E1C16E` | Destaque, luz |
| **Off-white** | `#FAF9F6` | Fundo suave |

### Tipografia

| Tipo | Fonte | Uso |
|------|-------|-----|
| **Títulos** | Zilla Slab (Serifada) | H1-H6, destaque |
| **Body** | Outfit (Sans-serif) | Texto, parágrafos |
| **Dados** | JetBrains Mono | Código, meta |

---

## 📁 Estrutura do Tema

```
neruds_gui/
├── neruds_gui.info.yml          # Configuração do tema
├── neruds_gui.libraries.yml     # Bibliotecas (CSS/JS)
├── neruds_gui.theme             # Hooks e funções PHP
├── README.md                     # Este arquivo
│
├── css/
│   ├── tokens/                   # Design System
│   │   ├── design-tokens.css    # Variáveis CSS (cores, spacing)
│   │   ├── colors.css           # Paleta de cores
│   │   ├── typography.css       # Escalas de fontes
│   │   ├── spacing.css          # Espaçamento (padding/margin)
│   │   └── effects.css          # Glassmorphism, sombras, transições
│   ├── base/
│   │   ├── reset.css            # Reset CSS normalizado
│   │   ├── html.css             # Estilos HTML
│   │   └── body.css             # Estilos BODY
│   ├── components/
│   │   ├── layout.css           # Grid, containers, header
│   │   ├── header.css           # Estilo do header
│   │   ├── hero.css             # Seção hero
│   │   ├── navigation.css       # Menu de navegação
│   │   ├── cards.css            # Componentes card
│   │   ├── buttons.css          # Botões
│   │   ├── forms.css            # Formulários
│   │   ├── footer.css           # Rodapé
│   │   ├── glass-cards.css      # Cards com glassmorphism
│   │   ├── ods-badges.css       # Badges de ODS
│   │   ├── search-filters.css   # Busca e filtros
│   │   └── ...
│   └── pages/
│       ├── home.css             # Página inicial
│       └── about.css            # Página sobre
│
├── js/
│   ├── global.js                # Script global (tema, acessibilidade)
│   ├── animations.js            # Micro-interações e animations
│   ├── search-filters.js        # Lógica de busca/filtros
│   └── content-types/           # Scripts por tipo de conteúdo
│       ├── article.js
│       └── perfil-pesquisador.js
│
├── templates/
│   ├── layout/
│   │   ├── page.html.twig       # Template principal de página
│   │   ├── html.html.twig       # Tag <html> (se customizado)
│   │   └── region.html.twig     # Regiões de bloco
│   ├── node/
│   │   ├── node.html.twig       # Template padrão de nó
│   │   ├── node--noticia--full.html.twig
│   │   ├── node--perfil-pesquisador--full.html.twig
│   │   ├── node--projeto--full.html.twig
│   │   └── ...
│   ├── block/
│   │   └── block.html.twig
│   ├── views/
│   │   └── views-view.html.twig
│   ├── field/
│   │   └── field.html.twig
│   └── region/
│       ├── region--header.html.twig
│       ├── region--hero.html.twig
│       └── ...
│
├── images/                       # Imagens do tema
├── config/                       # Configuração do tema
└── fonts/ (opcional)            # Fontes customizadas (se não via CDN)
```

---

## 🚀 Instalação

### 1. Ativar o Tema

```bash
drush theme:install neruds_gui
drush theme:set neruds_gui
```

Ou via Admin UI: **Appearance** → **Themes** → ativar "NERUDS-GUI"

### 2. Limpar Cache

```bash
drush cache:rebuild
```

---

## 📚 Usando o Design System

### Variáveis CSS

Todas as variáveis estão definidas em `css/tokens/design-tokens.css`:

```css
/* Cores */
--color-babasu: #2E4632          /* Verde primária */
--color-barro: #A0522D           /* Secundária */
--color-palha: #E1C16E           /* Destaque */

/* Tipografia */
--font-serif: 'Zilla Slab', Georgia, serif;
--font-sans: 'Outfit', -apple-system, sans-serif;
--font-mono: 'JetBrains Mono', monospace;

--font-size-base: 1rem;
--font-size-lg: 1.125rem;
--font-size-xl: 1.25rem;
/* ... etc */

/* Espaçamento (4px scale) */
--space-4: 1rem;    /* 16px */
--space-6: 1.5rem;  /* 24px */
--space-8: 2rem;    /* 32px */
/* ... etc */

/* Efeitos */
--glass-effect: blur(12px) saturate(140%);
--shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
--focus-ring: 0 0 0 3px rgba(46, 70, 50, 0.3);
```

### Classes Utilitárias

```html
<!-- Cores -->
<div class="bg-primary">Verde Babaçu</div>
<div class="bg-secondary">Barro</div>
<div class="color-accent">Palha</div>

<!-- Espaçamento -->
<div class="p-4">Padding 16px</div>
<div class="my-6">Margin Y 24px</div>
<div class="gap-4">Gap 16px (flexbox/grid)</div>

<!-- Efeitos -->
<div class="shadow-lg">Sombra grande</div>
<div class="glass">Efeito vidro fosco</div>
<div class="rounded-lg">Border radius 16px</div>

<!-- Tipografia -->
<h1 class="h1">Título grande</h1>
<p class="lead">Parágrafo destaque</p>
<small class="meta">Texto pequeno meta</small>
```

---

## 🎯 Componentes Principais

### Cards

```html
<div class="card card--publication">
  <img src="..." alt="..." class="card__image">
  <div class="card__body">
    <h3 class="card__title">Título do artigo</h3>
    <p class="card__excerpt">Resumo do conteúdo...</p>
    <div class="card__meta">
      <span class="card__meta-item">👤 Autor</span>
      <span class="card__meta-item">📅 2026-03-24</span>
    </div>
  </div>
  <div class="card__footer">
    <a href="#" class="btn btn--primary">Ler mais</a>
  </div>
</div>
```

### Grid Layout

```html
<div class="grid grid--cols-3">
  <div>Item 1</div>
  <div>Item 2</div>
  <div>Item 3</div>
</div>
```

### Glassmorphism

```html
<div class="glass p-6 rounded-xl">
  Conteúdo com efeito vidro
</div>
```

---

## ♿ Acessibilidade (WCAG 2.1 AA)

- ✅ **Skip Links:** Navegação rápida ao conteúdo principal
- ✅ **ARIA Labels:** Menu de navegação com `aria-label`
- ✅ **Focus States:** Anéis de foco visíveis
- ✅ **Contraste:** ≥ 4.5:1 para textos
- ✅ **Responsividade:** Mobile-first, testado em 640px-1536px+
- ✅ **Reduzir Movimento:** Respeita `prefers-reduced-motion`
- ✅ **Modo Escuro:** Suporta `prefers-color-scheme`

---

## 📱 Responsividade

| Breakpoint | Largura | Device |
|-----------|---------|--------|
| xs | 0px | Mobile |
| sm | 640px | Landscape mobile |
| md | 768px | Tablet |
| lg | 1024px | Desktop |
| xl | 1280px | Wide desktop |
| 2xl | 1536px | Ultra-wide |

---

## 🔗 Integração com Drupal

### Tipos de Conteúdo Suportados

- 📰 **Notícia** (`noticia`)
- 👨‍🔬 **Perfil de Pesquisador** (`perfil_pesquisador`)
- 📊 **Projeto de Pesquisa/Extensão** (`projeto_pesquisa_extensao`)
- 📄 **Publicação Científica** (`publicacao_cientifica`)
- 📅 **Evento Científico** (`evento_cientifico`)
- 📋 **Boletim Periódico** (`boletim_periodico`)

### Regiões de Bloco

```
header → primary_menu, secondary_menu
hero → destaques, manifesto
breadcrumb → navegação hierárquica
content_above → widgets acima do conteúdo
content → conteúdo principal (nós, views)
sidebar → barra lateral (opcional)
content_below → widgets abaixo
social_bar → redes sociais
footer_top → links, contato, informações
footer_bottom → copyright
```

---

## 🎬 Micro-Interações

### Fade In

```html
<div data-animate="fade-in">Conteúdo</div>
```

### Slide Up

```html
<div data-animate="slide-up" data-animate-delay="0.2">Conteúdo</div>
```

### Hover Lift

```html
<div class="hover-lift">Card que sobe ao hover</div>
```

---

## 🌙 Dark Mode

O tema suporta automaticamente dark mode baseado em:

1. **Preferência do sistema:** `prefers-color-scheme: dark`
2. **Toggle manual:** Armazenado em localStorage

```javascript
// Mudar tema via JavaScript
localStorage.setItem('neruds-theme', 'dark');
document.documentElement.setAttribute('data-theme', 'dark');
```

---

## 📖 Tipos de Conteúdo — Exemplos

### Artigo Científico

```twig
{% attach_library('neruds_gui/article') %}

<article class="node--article">
  <h1>{{ node.title }}</h1>
  <p class="meta">Por {{ author }} — {{ published_date }}</p>

  <div class="grid grid--cols-3">
    <div class="badge badge--primary">ODS 4</div>
    <div class="badge badge--success">Publicado</div>
  </div>

  {{ node.body }}
</article>
```

### Perfil de Pesquisador

```twig
{% attach_library('neruds_gui/perfil-pesquisador') %}

<article class="node--perfil-pesquisador">
  <div class="glass rounded-xl p-6">
    <div class="flex gap-4">
      <div class="avatar avatar--xl">{{ iniciais }}</div>
      <div>
        <h1>{{ node.title }}</h1>
        <p class="lead">{{ node.lattes_id }}</p>
      </div>
    </div>
  </div>
</article>
```

---

## 🧪 Testes e Performance

### PageSpeed Targets

- **LCP (Largest Contentful Paint):** < 2.5s
- **FID (First Input Delay):** < 100ms
- **CLS (Cumulative Layout Shift):** < 0.1

### Testes Recomendados

```bash
# Acessibilidade
drush axe

# Performance
drush lighthouse

# SEO
drush seocheck
```

---

## 🛠️ Customização

### Mudar Cores Primárias

Editar `css/tokens/design-tokens.css`:

```css
:root {
  --color-babasu: #YourColor;
  --color-barro: #YourColor;
  /* ... */
}
```

### Adicionar Fonte Customizada

Em `neruds_gui.libraries.yml`:

```yaml
fonts:
  css:
    theme:
      'https://fonts.googleapis.com/css2?family=YourFont:wght@400;700':
        type: external
```

---

## 📞 Suporte

- **Documentação:** `/web/themes/custom/neruds_gui/README.md`
- **Design System:** `/web/themes/custom/neruds_gui/css/tokens/`
- **Template Sample:** `/web/themes/custom/neruds_gui/templates/layout/page.html.twig`

---

## 📝 Changelog

### v2.0.0 (2026-03-24)
- ✨ Novo tema premium NERUDS-GUI
- ✨ Design system completo com tokens CSS
- ✨ Suporte para Drupal 11 & 12
- ✨ Acessibilidade WCAG 2.1 AA
- ✨ Dark mode automático
- ✨ Micro-interações e glassmorphism

---

**Feito com ❤️ para o NERUDS/UFT — Núcleo de Estudos Rurais, Desigualdades e Sistemas Socioecológicos**
