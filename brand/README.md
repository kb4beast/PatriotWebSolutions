# Site icon

The favicon is a single, bold P on the site's navy ground, with a coral step. It replaces the detailed PW badge at tab size; it is an original vector mark, drawn for Patriot Web Solutions. The five PNG sizes are rendered from `favicon.svg` by `node scripts/Build-Favicon.mjs` so the 16-pixel and WordPress versions share the same geometry.

The live WordPress site uses **Appearance → Customize → Site Identity → Site Icon**, with `favicon-512.png` uploaded as the Site Icon. WordPress generates its own icon sizes from that media attachment. This setting overrides the theme's old fallback `logo.png` without rerunning the content/theme installer or affecting GiveWP and WooCommerce. Keep the 512-pixel image in the media library when updating the theme.
