# KARAKO design system

Phase 2 establishes the shared visual language for the holding site and the tourism site. Each application keeps its own copy of the CSS, fonts, components, and photographs so it can still be deployed alone.

## Tokens

| Token | Value | Use |
| --- | --- | --- |
| Ink | `#141210` | Text on ivory |
| Charcoal | `#1c1916` | Header overlay, footer, primary buttons |
| Paper | `#faf7f2` | Page background |
| Ivory | `#f4efe6` | Text on charcoal |
| Gold | `#c6a36a` | Buttons, rules, current-language mark |
| Gold deep | `#7a5c32` | Small gold text on ivory (contrast) |
| Stone | `#5c564e` | Secondary text |
| Danger | `#8c3b32` | Validation errors |

Content width is `72rem`. Sections use a large vertical rhythm. Corners stay at 2px. Gold is an accent, not a body-text color on ivory.

## Type

Fonts are OFL families imported from `@fontsource` and bundled by Vite. Nothing is loaded from a font CDN.

| Script | Headings | Text |
| --- | --- | --- |
| Persian, Arabic | Vazirmatn 600 | Vazirmatn 400 |
| English, Russian | Literata 500 | Source Sans 3 |

Direction comes from `config/karako.php`: `fa` and `ar` are RTL, `en` and `ru` are LTR.

## Logo

The official mark is the gold CARACO lockup in `public/brand/logo.png` in each application. `resources/views/components/logo.blade.php` renders it. The header and the login page sit it on an ink plate so the gold stays visible on ivory. The footer, the empty homepage masthead, and the admin chrome sit it directly on charcoal. The browser icon is the triangle from the same file. Do not redraw the mark.

## Components

Blade components live in `resources/views/components/` in each app:

- `brand-mark`
- `language-switcher`
- `breadcrumbs`
- `hero` (`editorial` on the holding home, `immersive` on the tourism home)
- `section-heading`
- `photo`
- `gallery`
- `cta`
- `form-field`
- `alert`
- `empty-state`
- `badge`

Pagination is `resources/views/pagination/karako.blade.php`. The mobile menu is in `resources/js/navigation.js`. A `noscript` rule shows the links if JavaScript does not run.

## Sample pages

Holding: `/`, `/divisions`, `/contact`, `/foundations`, `/credits`, each under `fa`, `en`, `ru`, or `ar`.

Tourism: `/`, `/journey`, `/inquiry`, `/foundations`, `/credits`, with the same locale prefix.

Copy is placeholder and says so. The contact and inquiry forms validate and confirm on the page. They do not store or send the message.

Photographs are licensed reference images, credited on the page and in `public/images/CREDITS.txt`.
