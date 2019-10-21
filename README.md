# WordPress Platform.sh Start State

Composer Based WordPress Start State to use with Platform.sh.

This starter state includes:

<ins>Theming / Site Building</ins>
- [Elementor](https://wordpress.org/plugins/elementor/)
- [Contact Form 7](https://wordpress.org/plugins/contact-form-7/)
- [Classic Editor](https://wordpress.org/plugins/classic-editor/)

<ins>Config Management / Development</ins>
* [Debug Bar](https://wordpress.org/plugins/debug-bar/)
* [WP-CFM](https://wordpress.org/plugins/wp-cfm/)

<ins>SEO</ins>
* [Yoast SEO](https://wordpress.org/plugins/wordpress-seo/)
* [WP SEO Structured Data Schema](https://wordpress.org/plugins/wp-seo-structured-data-schema/)
* [Google Analytics Dashboard for WP (GADWP)](https://wordpress.org/plugins/google-analytics-dashboard-for-wp/)
* [Head, Footer and Post Injections](https://wordpress.org/plugins/header-footer/)
* [Safe Redirect Manager](https://wordpress.org/plugins/safe-redirect-manager/)

<ins>Performance</ins>
* [WP Redis Object Cache Dropin](https://github.com/devgeniem/wp-redis-object-cache-dropin)
* [Autoptimize](https://wordpress.org/plugins/autoptimize/)

## Setup

1. Clone the repo down
2. Add a .env file to the root of the project and add the following:

```yaml
PLATFORMSH_CLI_TOKEN=YOUR_CLI_TOKEN
```

## Recommended Plugins

- [Advanced Custom Fields PRO](https://www.advancedcustomfields.com/pro/)
  - Use this to install ACF PRO: [ACF PRO Installer](https://github.com/PhilippBaschke/acf-pro-installer)
  - You will need to add your key to the lando .env file 
  - The only way this plugin works on platform.sh is if you add your env var to the [application itself](https://docs.platform.sh/development/variables.html#application-provided-variables) unfortunately.
