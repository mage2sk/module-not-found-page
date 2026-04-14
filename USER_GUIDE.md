# Panth NotFoundPage — User Guide

This guide walks store administrators through installing, configuring,
and customizing the Panth NotFoundPage extension for Magento 2.

---

## Table of contents

1. [Installation](#1-installation)
2. [Verifying the module is active](#2-verifying-the-module-is-active)
3. [Configuration](#3-configuration)
4. [Customization tips](#4-customization-tips)
5. [Troubleshooting](#5-troubleshooting)
6. [Uninstallation](#6-uninstallation)

---

## 1. Installation

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

1. Download the extension package zip from the Adobe Commerce Marketplace
2. Extract to `app/code/Panth/NotFoundPage`
3. Run the same `module:enable ... cache:flush` commands above

---

## 2. Verifying the module is active

```bash
bin/magento module:status Panth_NotFoundPage
# Module is enabled
```

Visit any non-existent URL on your storefront (e.g. `/this-page-does-not-exist`)
and you should see the custom 404 page instead of the default Magento CMS page.

---

## 3. Configuration

Navigate to **Stores > Configuration > Panth Extensions > 404 Not Found Page**.

### General group

| Setting | Default | Description |
|---|---|---|
| **Enable Custom 404 Page** | Yes | When set to No, Magento falls back to the default CMS 404 page |

### Page Content group

| Setting | Default | Description |
|---|---|---|
| **Heading** | Page Not Found | The large heading displayed on the 404 page |
| **Subheading Message** | Oops! The page you're looking for doesn't exist or has been moved. | Descriptive text below the heading |
| **Show Search Bar** | Yes | Toggle the integrated search form |
| **Show Popular Links** | Yes | Toggle the dynamic category links section |
| **Show Contact Information** | Yes | Toggle the contact email display |
| **Contact Email** | support@example.com | The email address shown when contact info is enabled |

All settings are scoped to the store-view level, so you can have
different 404 pages per store view in a multi-store setup.

---

## 4. Customization tips

### Changing the visual style

The template uses CSS custom properties (`--color-primary` and
`--color-primary-darker`) which inherit from your theme. If your Hyva
or Luma theme defines these properties, the 404 page will automatically
match your brand colors. Otherwise it defaults to teal (#0D9488).

### Limiting the number of categories

By default, the module displays up to 6 top-level categories that are
active and included in the navigation menu. The categories are sorted
by position.

### Overriding the template

To customize the HTML, copy the template to your theme:

```
app/design/frontend/YourVendor/YourTheme/Panth_NotFoundPage/templates/notfound.phtml
```

---

## 5. Troubleshooting

| Symptom | Cause | Fix |
|---|---|---|
| Default 404 page still shows | Module disabled or cache not flushed | Run `bin/magento module:status Panth_NotFoundPage` and `bin/magento cache:flush` |
| Categories not showing | No active categories with "Include in Menu" enabled | Check catalog categories in admin |
| Search form not visible | "Show Search Bar" set to No | Enable in admin configuration |
| Page looks unstyled | Browser caching old static files | Run `bin/magento setup:static-content:deploy -f` and clear browser cache |

---

## 6. Uninstallation

### Composer

```bash
bin/magento module:disable Panth_NotFoundPage
composer remove mage2kishan/module-not-found-page
bin/magento setup:upgrade
bin/magento cache:flush
```

### Manual

```bash
bin/magento module:disable Panth_NotFoundPage
rm -rf app/code/Panth/NotFoundPage
bin/magento setup:upgrade
bin/magento cache:flush
```

---

## Support

For all questions, bug reports, or feature requests:

- **Email:** kishansavaliyakb@gmail.com
- **Website:** https://kishansavaliya.com
- **WhatsApp:** +91 84012 70422

Free email support is provided on a best-effort basis.
