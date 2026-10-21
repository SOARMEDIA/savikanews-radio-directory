# SAVIKA NEWS RADIO DIRECTORY
## Full Project Requirements, Technical Plan and Implementation Roadmap

**Project:** Savika News Radio Directory  
**Website:** https://savikanews.com/  
**Platform:** WordPress  
**Page Builder:** Elementor  
**Streaming Provider:** Zeno.fm  
**Broadcasting Software:** BUTT (Broadcast Using This Tool)  
**Initial Business Model:** Free station listings  
**Approval Model:** Application followed by administrator review  
**Document Version:** 1.0  
**Project Status:** Ready for Testing & Deployment

---

## 1. Structure Created in Plugin
- `savika-news-radio-directory.php`: Plugin entry point, registration, CPT initialization.
- `includes/class-snrd-post-type.php`: Custom post type (`snrd_station`) and genres taxonomy (`snrd_genre`).
- `includes/class-snrd-admin.php`: Admin edit screens, stream preview player, applicant meta, custom list columns.
- `includes/class-snrd-application-form.php`: Broadcaster submission form handler, logo upload, server-side validation, nonce, honeypot anti-spam, admin email notification.
- `includes/class-snrd-shortcodes.php`: Shortcodes `[savika_radio_directory]` and `[savika_radio_apply]`.
- `includes/class-snrd-templates.php`: Single station template loader (`/radio-station/station-name/`).
- `templates/directory-grid.php`: Directory search, genre chips, cards, HTML5 stream audio players, CTA banner.
- `templates/application-form.php`: Application form interface.
- `templates/single-snrd_station.php`: Single station dedicated page template.
- `assets/css/snrd-public.css` & `assets/css/snrd-admin.css`.
- `assets/js/snrd-public.js` & `assets/js/snrd-admin.js`.

---

## 2. Elementor Setup Guide
1. Create a page at `/radio/` in WordPress Admin -> Pages.
2. Edit with Elementor -> Add Shortcode Widget -> `[savika_radio_directory]`.
3. Create a page at `/radio/apply/` in WordPress Admin -> Pages.
4. Edit with Elementor -> Add Shortcode Widget -> `[savika_radio_apply]`.

