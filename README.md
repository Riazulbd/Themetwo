# Narayanganj Press — WordPress theme

A light, fast, classic (PHP) theme for a Bangla news site. No page builder, no jQuery, one small deferred script, CSS inlined in `<head>`.

**Why classic and not a block theme:** a news front page is built from queries (lead, latest, per-category sections, most read). PHP templates give exact control over markup and query count, and they make it easy to keep the Customizer "Theme Panel" the newsroom asked for. Post content still uses the block editor.

## Install

1. Copy the `narayanganj-press` folder to `wp-content/themes/` (or upload it as a zip under Appearance → Themes → Add New).
2. Activate it. Requires WordPress 6.3+ and PHP 7.4+.
3. Go to **Theme Panel** in the admin menu (it opens Appearance → Customize → Theme Panel).
4. Under **Appearance → Menus**, build a menu and assign it to **Category navigation (pills)**. The pill bar and the mobile drawer show that menu. Optionally assign **Footer column 1/2**. Without menus they fall back to categories.
5. Under Settings → Permalinks, choose "Post name".

## Theme Panel (Customizer)

| Section | Settings |
|---|---|
| Logos & colours | Header logo and footer logo (separate; the header falls back to Site Identity's logo, then to a text wordmark). Logo tagline. Accent colour, dark colour, top bar background and text, footer background and text. |
| Footer: editor & contact | Footer description, editor label (e.g. সম্পাদক ও প্রকাশক), editor name, published-from line, office address, phones and emails for news / ads / general, copyright holder. |
| Social links | Facebook, YouTube, WhatsApp, X, Instagram, TikTok, Google News: each has a Show on/off switch and a link. |
| Home page sections | For সর্বশেষ, শীর্ষ সংবাদ, the local section, column 1–3 and the video band: pick a category (or "Hide this section") and type the title shown on the site. Blank title = default title or the category name. Most-read card: title. Ticker headline count. |
| Single post: Facebook Page box | Facebook Page URL and box title. Shown after "সর্বাধিক পঠিত" in the sidebar (single posts and archives, also on phones). Blank URL hides it. It is a lazy-loaded iframe, so nothing from Facebook loads until the reader scrolls near it. |
| Sharing, newsletter & ads | Facebook App ID (Messenger share on desktop), newsletter form action URL, reserved ad boxes on/off. |

## Widget areas

`ad-header`, `ad-home`, `ad-single-inline` (after the 3rd paragraph), `ad-sidebar`, `sidebar-extra`.

## Built-in behaviour

- SolaimanLipi self-hosted (`assets/fonts`), `font-display:swap`, preloaded, plus a size-matched local fallback to avoid layout shift.
- No author names, bylines or bios anywhere. Author archives redirect to home and the REST users endpoint is closed.
- Comments are fully disabled (front end, admin, feeds, REST, blocks).
- Share buttons: Facebook, WhatsApp, Messenger, copy link.
- Bangla digits, Bangla dates and the Bangla calendar date in the top bar.
- Responsive images (`srcset`/`sizes`), the lead image loads eagerly with high priority, everything else lazy. Sizes: `np-lead` 1200×675, `np-card` 640×384, `np-thumb` 240×160.
- Most-read uses a lightweight view counter (one ping per post per browser session).
- Light and dark mode follow the reader's system setting.
- Child-theme ready: templates and most helpers are wrapped in `function_exists`, so a child theme can override them.

## Filters

| Filter | Purpose |
|---|---|
| `np_customizer_defaults` | Change any Theme Panel default. |
| `np_home_sections` | Add/rename home sections or change their default category slugs. |
| `np_social_networks` | Add or reorder social networks. |
| `np_share_items` | Change the share buttons. |
| `np_inline_css` | `false` loads main.css as a cached file instead of inlining it. |
| `np_font_url` | Serve the font from somewhere else. |
| `np_inline_ad_after_paragraph` | Paragraph after which the in-article ad appears (default 3). |
| `np_breaking_tag` | Tag slug that feeds the breaking ticker (default `breaking`). |
| `np_enable_view_counter` | Turn the view counter off (e.g. if a stats plugin does it). |
| `np_disable_comments` | `false` re-enables comments. |
| `np_bn_date`, `np_bn_ago`, `np_bn_calendar_date` | Adjust date output. |
| `np_words_per_minute` | Reading-time speed. |
| `np_breadcrumb_items`, `np_schema_graph`, `np_seo_plugin_active` | Breadcrumbs and structured data (skipped automatically when an SEO plugin is active). |
| `np_content_width` | Content width. |

## Rolling back

Switch to any other theme under Appearance → Themes. Theme Panel settings stay stored and come back if the theme is reactivated.
