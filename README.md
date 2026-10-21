# Savika News Radio Directory (WordPress Plugin)

**Official Plugin for Savika News (https://savikanews.com/)**  
*Manage online radio stations, broadcaster applications, stream testing, and public HTML5 playback.*

---

## 📻 Features Overview

- **Dedicated Post Type (`snrd_station`)**: Structured storage for station name, description, logo, location, stream URL, Zeno.fm page, and applicant contact details.
- **Taxonomy (`snrd_genre`)**: Automatic pre-population of genres (*News, Talk, Gospel, Sports, Music, Community, Educational, Other*).
- **Public Directory (`[savika_radio_directory]`)**:
  - Live instant keyword search (station name, location).
  - Interactive genre filter pills.
  - Multi-column responsive cards (desktop, tablet, mobile).
  - Native HTML5 audio player for live streaming with smart single-active-player control (pausing others when one plays).
  - Direct links to dedicated station pages.
  - "Apply to Broadcast" call-to-action banner.
- **Broadcaster Application Form (`[savika_radio_apply]`)**:
  - Front-end submission form for radio broadcasters.
  - Logo upload via WordPress media library.
  - Honeypot anti-spam trap and WordPress nonce verification.
  - All new applications are saved with **Pending Review** status.
  - Automatic email notification sent to site administrator upon submission.
- **Dedicated Single Station Page (`/radio-station/station-name/`)**:
  - Dedicated SEO title formatting: `{Station Name} — Listen Live | Savika News Radio`.
  - Prominent HTML5 live stream player with fallback link to official Zeno.fm page.
- **Admin Workflow & Live Stream Testing**:
  - In-admin audio test player to verify Zeno.fm / Icecast stream before publishing.
  - Quick-view custom columns in WordPress Admin.
  - Broadcaster contact details kept private from public visitors.

---

## 🚀 Installation Instructions

### Method 1: Upload via WordPress Admin (Recommended)
1. Download the `savika-news-radio-directory.zip` file.
2. In your WordPress Admin panel, go to **Plugins → Add New**.
3. Click the **Upload Plugin** button at the top.
4. Choose `savika-news-radio-directory.zip` and click **Install Now**.
5. Click **Activate Plugin**.

### Method 2: Manual FTP / File Manager Upload
1. Extract the folder `savika-news-radio-directory`.
2. Upload it to your server at:
   `wp-content/plugins/savika-news-radio-directory/`
3. In WordPress Admin, navigate to **Plugins → Installed Plugins** and click **Activate**.

---

## 🎨 Elementor & Public Page Setup

### 1. Main Radio Directory Page
- Go to **Pages → Add New**.
- Title: **Radio** (Permalink: `/radio/`).
- Click **Edit with Elementor**.
- Drag a **Shortcode** widget into your layout section.
- Enter:
  ```text
  [savika_radio_directory]
  ```
- Publish the page.

### 2. Broadcaster Application Page
- Go to **Pages → Add New**.
- Title: **Apply to Broadcast** (Permalink: `/radio/apply/`).
- Click **Edit with Elementor**.
- Drag a **Shortcode** widget into your layout section.
- Enter:
  ```text
  [savika_radio_apply]
  ```
- Publish the page.

---

## 🎧 Broadcaster Setup Guide (Zeno.fm + BUTT)

1. **Broadcasting with BUTT:**
   - The broadcaster signs up at [Zeno.fm](https://zeno.fm) and creates their station.
   - Broadcaster configures **BUTT (Broadcast Using This Tool)** with their private Zeno.fm Icecast server address, port, mountpoint, and source password.
   - The broadcaster clicks **Play / Stream** in BUTT.
2. **Obtaining the Public Listener Link:**
   - On Zeno.fm, the broadcaster copies their **Public Stream URL** (e.g. `https://stream.zeno.fm/xxxxxxxxxx`).
   - Broadcasters must **never submit their BUTT source password** to Savika News.
3. **Submitting the Listing:**
   - Broadcaster visits `https://savikanews.com/radio/apply/`, enters their station name, location, genre, public stream URL, and logo, then clicks submit.
4. **Admin Approval:**
   - The Savika News administrator receives an email notification.
   - The admin opens **Radio Stations** in WordPress Admin, clicks **Edit**, and clicks **"Load & Test Stream URL"**.
   - If audio plays clearly, the admin changes status to **Publish**.
   - The station immediately appears live on `https://savikanews.com/radio/`.

---

## ⚙️ Shortcode Parameters

### `[savika_radio_directory]`
- `per_page`: Number of stations to query (Default: `24`).
- `genre`: Filter by a specific genre slug (e.g. `[savika_radio_directory genre="gospel"]`).
