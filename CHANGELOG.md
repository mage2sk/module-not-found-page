# Changelog

All notable changes to this extension are documented here. The format
is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.0.14] - 2026-10-03

### Fixed
- The 404 page now renders inside a `main` landmark with the `#contentarea` skip-link target, so the theme "Skip to Content" link works and screen readers find the main region on Hyva and Luma.
- The search field has an accessible name, the form is marked as a search region, decorative icons and the large 404 numeral are hidden from assistive technology, and buttons, category links and the contact link show a visible keyboard focus outline.
- "Browse Categories" is now an h2 inside a labelled navigation region instead of an h3 that skipped a heading level.
- Touch targets are at least 44px: the Back to Homepage and Go Back buttons on every width and the category links on phones and tablets. The conflicting 640px button rule that padded the round mobile buttons was removed.
- Body text stays at 14px on phones (subheading and contact line), and the search placeholder colour now meets 4.5:1 contrast. The shorter placeholder no longer gets cut off on 375px screens.
- "Go Back" falls back to the home page when there is no browser history, and the button has an explicit type.
- The custom 404 page sends a `NOINDEX,FOLLOW` robots meta tag.
- README now states where the page title comes from; duplicate changelog heading removed.
