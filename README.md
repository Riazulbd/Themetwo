# Khobor Press — a universal WordPress news theme

A light, fast, classic (PHP) theme for news, magazine and blog sites in any language. No page builder, no jQuery, one small deferred script, CSS inlined in `<head>`. It is not tied to one site: every default is neutral, and everything a site owner needs to change lives in a dedicated **Theme Panel** in the admin menu (it does not use the Customizer).

**Why classic and not a block theme:** a news front page is built from queries (lead, latest, per-category sections, most read). PHP templates give exact control over markup and query count. Post content still uses the block editor.

## Install

1. Copy this folder to `wp-content/themes/khobor-press/` (the folder name must be unique: do not name it after a theme from wordpress.org) (or upload it as a zip under Appearance → Themes → Add New).
2. Activate it. Requires WordPress 6.3+ and PHP 7.4+.
3. Open **Theme Panel** in the admin sidebar and fill in the tabs.
4. Under **Appearance → Menus**, build a menu and assign it to **Category navigation (pills)**. The pill bar and the mobile drawer show that menu. Optionally assign **Footer column 1/2**. Without menus they fall back to categories.
5. Under Settings → Permalinks, choose "Post name".

## Theme Panel

A top-level admin screen (capability `edit_theme_options`) with one tab per group. Everything saves together into one option, `np_options`.

| Tab | Settings |
|---|---|
| General | Header and footer logo (the header falls back to Site Identity's logo, then a text wordmark), tagline. Interface language (Bengali by default, or follow the site language). Bangla digits/dates/calendar. Light/dark mode default, switch times and the header switch. Top bar on/off. Font: automatic, bundled Bangla font or system font. |
| Colours | **Header** background and text, separately for light and dark mode (so a logo with a solid background never clashes). Accent, dark, top bar and footer colours. |
| Footer & contact | Footer description, copyright holder (blank = site name), editor label and name, published-from line, office address, and three contact blocks (heading, phone, email each). |
| Social | Facebook, YouTube, WhatsApp, X, Instagram, TikTok, Google News: each has a Show switch and a link. |
| Home page | Scrolling breaking ticker (on/off, label, headline count, tag). For Latest, Top stories, the featured section, columns 1–3 and the video band: pick a category and a title. Sections left on **Automatic** pick busy categories by themselves, so a fresh install needs no mapping. |
| Login page | Branded `wp-login.php`: logo, background colour (button uses the accent colour), optional welcome line, on/off. |
| Sharing & ads | Facebook Page box (URL, title), Facebook App ID, newsletter form action URL, edge cache time, reserved ad boxes. |

"Reset to defaults" is at the bottom of the panel.

**Upgrading from an older version:** values saved in the Customizer are copied into the new option the first time an admin screen loads. The old values are left in place, so rolling back loses nothing. Defaults that used to point at a specific site (category slugs, emails, placeholder text) are gone; sections without a category chosen are simply hidden.

## Light and dark mode

The page follows the time of day by default (dark from 18:00, light from 06:00, in the visitor's own time) and never flashes the wrong colours, because a tiny inline script sets the mode before the first paint. The round button in the header switches it; the choice is remembered on that device. Choosing the mode that automatic would pick anyway returns the visitor to automatic.

## Speed (and Cloudflare)

- CSS is inlined, one deferred script, no jQuery, system font by default.
- The main image is preloaded and eager; everything else is lazy.
- **Early Hints:** the theme sends `Link: rel=preload` headers for the font and the main image. Turn on Cloudflare → Speed → Optimization → **Early Hints** and the browser starts those downloads before WordPress has finished building the page.
- **Edge caching:** logged-out pages are sent with `s-maxage` (Theme Panel → Sharing & ads, default 300 s). To use it, add a Cloudflare Cache Rule that caches HTML ("Eligible for cache", respect origin TTL), and bypass the cache for cookies starting `wordpress_logged_in` and for `/wp-admin`.
- **Instant clicks:** Chromium browsers prerender the next article when a link is hovered or pressed (Speculation Rules). Views are only counted once the page is actually shown.
- **View counter:** the beacon is answered right after the theme loads, before WordPress builds the page, and uses atomic increments. On a very busy site set Theme Panel → Sharing & ads → "Count 1 in every N views" to 5 or 10.
- **Fewer queries:** featured images are loaded in one query per list, title-only lists skip term and meta loading, and the "Most read" list refreshes without a stampede (stale-while-revalidate).
- Serve images through Cloudflare Polish / WebP or AVIF for the smallest files.

## Language

The visitor-facing site is Bengali by default, whatever language WordPress itself is set to (the admin keeps its own language). Set **Site interface language** to "Follow the site language" to use another language. Source strings are English; the Bangla translation is `languages/bn_BD.po`/`.mo` and `languages/khobor-press.pot` is the template for other languages. Bangla digits, months, weekdays and the Bangla calendar (Bangabda) are used whenever the interface is Bengali, or can be forced from the Theme Panel. Otherwise dates follow Settings → General.

## Widget areas

`ad-header`, `ad-home`, `ad-single-inline` (after the 3rd paragraph), `ad-sidebar`, `sidebar-extra`.

## Built-in behaviour

- Optional SolaimanLipi (Bangla) self-hosted in `assets/fonts`, `font-display:swap`, preloaded only when used; otherwise the system font stack with nothing to download.
- No author names, bylines or bios anywhere. Author archives redirect to home and the REST users endpoint is closed.
- Comments are fully disabled (front end, admin, feeds, REST, blocks).
- Share buttons: Facebook, WhatsApp, Messenger, copy link.
- Responsive images (`srcset`/`sizes`), the lead image loads eagerly with high priority, everything else lazy. Sizes: `np-lead` 1200×675, `np-card` 640×384, `np-thumb` 240×160.
- Most-read uses a lightweight view counter (one ping per post per browser session).
- Light and dark mode follow the reader's system setting.
- Child-theme ready: templates and most helpers are wrapped in `function_exists`, so a child theme can override them.

## Filters

| Filter | Purpose |
|---|---|
| `np_panel_tabs` | Add, remove or change Theme Panel tabs and fields. |
| `np_option_defaults` | Change any option default. |
| `np_home_sections` | Add/rename home sections or set default category slugs. |
| `np_social_networks` | Add or reorder social networks. |
| `np_share_items` | Change the share buttons. |
| `np_inline_css` | `false` loads main.css as a cached file instead of inlining it. |
| `np_font_url` | Serve the font from somewhere else. |
| `np_inline_ad_after_paragraph` | Paragraph after which the in-article ad appears (default 3). |
| `np_breaking_tag` | Tag slug that feeds the breaking ticker (default: the Theme Panel value). |
| `np_enable_view_counter` | Turn the view counter off (e.g. if a stats plugin does it). |
| `np_disable_comments` | `false` re-enables comments. |
| `np_bn_date`, `np_bn_ago`, `np_bn_calendar_date` | Adjust date output. |
| `np_words_per_minute` | Reading-time speed. |
| `np_breadcrumb_items`, `np_schema_graph`, `np_seo_plugin_active` | Breadcrumbs and structured data (skipped automatically when an SEO plugin is active). |
| `np_content_width` | Content width. |

Read an option in code with `np_opt( 'key' )`.

## Updates

The theme never asks wordpress.org for updates (`Update URI: false`, and it is removed from the update-check request), so WordPress can't offer to replace it with an unrelated theme of the same name. Update it by uploading the new zip.

## Rolling back

Switch to any other theme under Appearance → Themes. Theme Panel settings stay stored and come back if the theme is reactivated.
