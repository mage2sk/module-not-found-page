# Panth NotFoundPage

[![Magento 2.4.4 - 2.4.8](https://img.shields.io/badge/Magento-2.4.4%20--%202.4.8-orange)]()
[![PHP 8.1 - 8.4](https://img.shields.io/badge/PHP-8.1%20--%208.4-blue)]()
[![License](https://img.shields.io/badge/License-Proprietary-red)]()

**Custom 404 Not Found Page** for Magento 2. Replaces the default CMS
404 page with a modern, fully configurable design featuring dynamic
category links, an integrated search bar, and admin-controlled content.

---

## Features

- **Modern 404 design** — clean, centered layout with gradient 404
  code, configurable heading, subheading, and action buttons.
- **Integrated search bar** — lets visitors search products directly
  from the 404 page without navigating away.
- **Dynamic category links** — automatically pulls top-level store
  categories so visitors can browse popular sections.
- **Admin configuration** — toggle search bar, popular links, contact
  info, and customize heading/subheading text per store view.
- **Contact information** — optional email display so visitors can
  reach out for help.
- **Fully responsive** — optimized for desktop and mobile with
  icon-only buttons on small screens.
- **Hyva + Luma compatible** — self-contained CSS with no JavaScript
  dependencies.

---

## Installation

### Composer (recommended)

```bash
composer require mage2kishan/module-not-found-page
bin/magento module:enable Panth_NotFoundPage
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

### Manual zip

1. Download the extension package zip
2. Extract to `app/code/Panth/NotFoundPage`
3. Run the same `module:enable ... cache:flush` commands above

---

## Requirements

| | Required |
|---|---|
| Magento | 2.4.4 - 2.4.8 (Open Source / Commerce / Cloud) |
| PHP | 8.1 / 8.2 / 8.3 / 8.4 |
| Panth_Core | ^1.0 (installed automatically via Composer) |

---

## Configuration

Navigate to **Stores > Configuration > Panth Extensions > 404 Not Found Page**.

### General

| Setting | Default | Description |
|---|---|---|
| Enable Custom 404 Page | Yes | Replace the default Magento 404 page with the Panth design |

### Page Content

| Setting | Default | Description |
|---|---|---|
| Heading | Page Not Found | Main heading text |
| Subheading Message | Oops! The page you're looking for... | Descriptive message below the heading |
| Show Search Bar | Yes | Display the integrated search form |
| Show Popular Links | Yes | Display top-level store categories |
| Show Contact Information | Yes | Display the contact email |
| Contact Email | support@example.com | Email shown on the 404 page |

---

## How it works

The module overrides the `cms_noroute_index` layout handle. When
enabled, it removes the default CMS page content block and injects
a custom block (`Panth\NotFoundPage\Block\NotFound`) into the page
wrapper. The block dynamically fetches top-level categories from the
store's root category and renders a self-contained template with
inline CSS — no external assets required.

---

## Support

| Channel | Contact |
|---|---|
| Email | kishansavaliyakb@gmail.com |
| Website | https://kishansavaliya.com |
| WhatsApp | +91 84012 70422 |

---

## License

Proprietary — see `LICENSE.txt`. Distribution is restricted to the
Adobe Commerce Marketplace.

---

## About the developer

Built and maintained by **Kishan Savaliya** — https://kishansavaliya.com.
Builds high-quality, security-focused Magento 2 extensions and themes
for both Hyva and Luma storefronts.
