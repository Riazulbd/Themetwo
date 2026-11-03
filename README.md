# News Press — a universal WordPress news theme

A light, fast, classic (PHP) theme for news, magazine and blog sites in any language. No page builder, no jQuery, one small deferred script, CSS inlined in `<head>`. It is not tied to one site: every default is neutral, and everything a site owner needs to change lives in a dedicated **Theme Panel** in the admin menu (it does not use the Customizer).

**Why classic and not a block theme:** a news front page is built from queries (lead, latest, per-category sections, most read). PHP templates give exact control over markup and query count. Post content still uses the block editor.

## Install

1. Copy this folder to `wp-content/themes/news-press/` (or upload it as a zip under Appearance → Themes → Add New).
2. Activate it. Requires WordPress 6.3+ and PHP 7.4+.
3. Open **Theme Panel** in the admin sidebar and fill in the tabs.
4. Under **Appearance → Menus**, build a menu and assign it to **Category navigation (pills)**. The pill bar and the mobile drawer show that menu. Optionally assign **Footer column 1/2**. Without menus they fall back to categories.
5. Under Settings → Permalinks, choose "Post name".

## Theme Panel

A top-level admin screen (capability `edit_theme_options`) with one tab per group. Everything saves together into one option, `np_options`.

| Tab | Settings |
|---|---|
| General | Header and footer logo (the header falls back to Site Identity's logo, then a text wordmark), tagline. Bangla digits/dates/calendar: automatic, always or never. Top bar on/off. Font: automatic, bundled Bangla font or system font. |
| Colours | Accent, dark, top bar background and text, footer background and text. |
| Footer & contact | Footer description, copyright holder (blank = site name), editor label and name, published-from line, office address, and three contact blocks (heading, phone, email each). |
| Social | Facebook, YouTube, WhatsApp, X, Instagram, TikTok, Google News: each has a Show switch and a link. |
| Home page | Breaking ticker (on/off, label, headline count, tag). For Latest, Top stories, the featured section, columns 1–3 and the video band: pick a category and type the title shown on the site. |
| Sharing & ads | Facebook Page box (URL, title), Facebook App ID, newsletter form action URL, reserved ad boxes. |

"Reset to defaults" is at the bottom of the panel.

**Upgrading from an older version:** values saved in the Customizer are copied into the new option the first time an admin screen loads. The old values are left in place, so rolling back loses nothing. Defaults that used to point at a specific site (category slugs, emails, placeholder text) are gone; sections without a category chosen are simply hidden.

## Language

Source strings are English. A Bangla translation (`languages/bn_BD.po`/`.mo`) is included, and `languages/news-press.pot` is the template for other languages. Bangla digits, Bangla months and weekdays, and the Bangla calendar (Bangabda) switch on automatically when the site language is Bangla, or from the Theme Panel. Otherwise dates and times follow Settings → General.

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

## Rolling back

Switch to any other theme under Appearance → Themes. Theme Panel settings stay stored and come back if the theme is reactivated.
