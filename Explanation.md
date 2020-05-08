# RGOU Sitemap - WP-Media

## Overview

This plugin should create and maintain a simple sitemap for the site's homepage, and a static version of it.

A settings page should be created under Dashboard -> Settings, allowing an admin to run the crawling immediately and schedule to run each hour after that.

## Deconstructing the problem

1. Create a new plugin.
1. Setup a settings page.
1. Create the crawler.
1. Save the current links on `wp_options`.
1. Dump the files (`sitemap.html` and `index.html`) to the site's root.
1. Display success/error messages for the user.
1. Display a list of links.

## Technical solutions used

### Plugin bootstrap

The plugin is based on [WP-Media package template](https://github.com/wp-media/package-template) and [wppb.me](https://wppb.me/), with some tweaks.

**First tweak**: Although [wppb.me](https://wppb.me/) uses classes, it doesn't use namespaces. To make it work, some changes were done:

- The folder structure was changed so `classes/Rgou/WPMedia` is the place for the `Rgou\WPMedia` namespace, and the PSR4 autoload was adjusted in `composer.json`:

```javascript
"autoload": {
    "psr-4": {
        "Rgou\\WPMedia\\": "classes/Rgou/WPMedia"
    }
},
"autoload-dev": {
    "psr-4": {
        "Rgou\\WPMedia\\Tests\\": "Tests/"
    }
},
```

The classes are loaded on the main plugin file (`rgou-wp-media.php`), as using the autoload directly can lead to conflicts with other plugins.

**Second tweak**: Tests were adjusted to use patterns from [imagify-plugin](https://github.com/wp-media/imagify-plugin). This allows to bootstrap integration tests separately from unit tests.

### Settings page

[wppb.me](https://wppb.me/) suggests a pattern for admin menus. This was adjusted for a settings page.

It also splits for clarity. [`Rgou\WPMedia\Plugin::define_admin_hooks()`](https://github.com/rafaelgou/rgou-wp-media/blob/master/classes/Rgou/WPMedia/Plugin.php#L115) defines the hook, and the function is placed on [`Rgou\WPMedia\Admin::settings_init()`](https://github.com/rafaelgou/rgou-wp-media/blob/master/classes/Rgou/WPMedia/Admin.php#L111). The output is configure in [`Rgou\WPMedia\Admin::admin_init()`](https://github.com/rafaelgou/rgou-wp-media/blob/master/classes/Rgou/WPMedia/Admin.php#L126) and the final content is rendered in separated file [`admin/partials/rgou-wp-media-admin-display.php`](https://github.com/rafaelgou/rgou-wp-media/tree/master/admin/partials).

### Frontend output

Originally the public class was prefixed with the plugin name (an old pattern to avoid name collision). After switching to namespaces, it was renamed to [`Rgou\WPMedia\PublicArea`](https://github.com/rafaelgou/rgou-wp-media/blob/master/classes/Rgou/WPMedia/PublicArea.php#L22). It uses a filter to add a footer when the sitemap is enabled.

### The crawler

To make it clear and easier to test, the crawler is a standalone class, without any Wordpress code. This means using `file_get_contents()` instead of `wp_get_remote()`. To be able to unit test many files from `wp-includes` should be added as Fixtures (like [WP_Error on imagify-plugin](https://github.com/wp-media/imagify-plugin/blob/d81c3c2078eceb5c139966e0402f286944d98912/Tests/Unit/TestCase.php#L68)). Or, as an option, use an integration test for that.

The crawler receives an URL, gets the content, extracts the links using [DomDocumnet](https://www.php.net/manual/en/class.domdocument.php), and generates the site map also using [DomDocumnet](https://www.php.net/manual/en/class.domdocument.php). HTML + inline PHP is avoided for consistency. As the last functionality, it also returns the original content.

The sitemap uses [lit](https://ajusa.github.io/lit/) for very basic styling.

### Saving links

A `wp_option` named `rgou_wp_media` is used to store current crawled links. It an array with 2 itens: `timestamp` (the last run) and `links` (array of urls). On a clean run, [it returns](https://github.com/rafaelgou/rgou-wp-media/blob/master/classes/Rgou/WPMedia/Admin.php#L158):

```php
[
    'timestamp' => false,
    'links'     => [],
]
```

allowing the frontend to display the content accordingly (hide `Disable` button, links for `sitemap.html` and `index.html`, and links' listing).

There are two actions: `submit` and `disable`.

- `submit` runs the method [`Rgou\WPMedia\Admin::crawler()`](https://github.com/rafaelgou/rgou-wp-media/blob/master/classes/Rgou/WPMedia/Admin.php#L126) that creates an instance of the crawler, get the links, saves the `wp_option`, and calls the [`Rgou\WPMedia\Admin::dump_files($crawler)`](https://github.com/rafaelgou/rgou-wp-media/blob/master/classes/Rgou/WPMedia/Admin.php#L271) to create/store the files. The last action is to [schedule the next run](https://github.com/rafaelgou/rgou-wp-media/blob/master/classes/Rgou/WPMedia/Admin.php#L231).

- `submit` runs the method [`Rgou\WPMedia\Admin::disable_crawler()`](https://github.com/rafaelgou/rgou-wp-media/blob/master/classes/Rgou/WPMedia/Admin.php#L205) that cleans the `wp_option`, unschedule the next run and calls [`Rgou\WPMedia\Admin::delete_files()`](https://github.com/rafaelgou/rgou-wp-media/blob/master/classes/Rgou/WPMedia/Admin.php#L291).

### Dumping files

A short version of [wp-rocket WP_Filesystem_Direct implementation](https://github.com/wp-media/wp-rocket/blob/adab7a846f85e1edbdeb7e6a63575789d0f0bf7b/inc/functions/files.php#L1142) was used, without changing any `wp-config.php` configuration to force direct access while using `WP_Filesystem`.

[`Rgou\WPMedia\Admin::dump_files($crawler)`](https://github.com/rafaelgou/rgou-wp-media/blob/master/classes/Rgou/WPMedia/Admin.php#L271) and [`Rgou\WPMedia\Admin::delete_files()`](https://github.com/rafaelgou/rgou-wp-media/blob/master/classes/Rgou/WPMedia/Admin.php#L291) do the tasks checking the return of `WP_Filesystem_Direct::put_contents()` and `WP_Filesystem_Direct::delete()` to throw exceptions (see next).

### Error handling

File permission issues while saving are the most common error. It's expected that the root folder to be writable by the webserver.

[`try...catch`](https://github.com/rafaelgou/rgou-wp-media/blob/master/classes/Rgou/WPMedia/Admin.php#L128) is used to intercept any error and display a reasonable message for the admin user. When an error occurs, the `wp_option` is removed.

To avoid looping errors, the remaining files are not removed at this point.

[`Exception`](https://www.php.net/manual/en/class.exception.php) is used instead of [`Throwable`](https://www.php.net/manual/en/class.throwable.php) to support PHP 5.6.

### Coding standards and PHPCS

To keep consistency, `phpcs.xml.dist` was changed to match the plugins' prefixes and i18n text domain:

```xml
    <rule ref="WordPress.WP.I18n">
        <properties>
            <property name="text_domain" type="array" value="rgou-wp-media" />
        </properties>
    </rule>
    <rule ref="WordPress.NamingConventions.PrefixAllGlobals">
        <properties>
            <property name="prefixes" type="array" value="Rgou\WPMedia,Rgou_Wp_Media,rgou_wp_media,RGOU_WP_MEDIA" />
        </properties>
    </rule>
```

`.vscode/settings.json` was also tweaked to help linting while developing, speeding up coding standards validation:

```javascript
{
    // PHPCS
    "phpcs.enable":   true,
    "phpcs.standard": "./phpcs.xml.dist",
    "editor.insertSpaces": false,
    "editor.detectIndentation": false,
    "prettier.useTabs": true,
    "phpcs.executablePath": "./wp-content/plugins/rgou-wp-media/vendor/bin/phpcs"
}
```

As the VSCode project includes the whole Wordpress installation, the `phpcs` path is set accordingly.

`index.php` files were ignored to pass the validation:

```php
<?php
// phpcs:disable
// Silence is golden
```

### Testing and Travis-CI

Unit and integration tests were implemented. A relaxed [TDD](https://en.wikipedia.org/wiki/Test-driven_development) was used while developing the [Crawler](https://github.com/rafaelgou/rgou-wp-media/blob/master/classes/Rgou/WPMedia/Crawler.php#L22) as very specific actions are easier to develop this way.

Travis-CI integration was pretty straight forward. The only change was the slack channel, pointing to a personal workspace. It was very handy to discover PHP 5.6 issues, in special the [DomNodeList](https://www.php.net/manual/en/class.domnodelist.php) behavior: starting on PHP 7.2 the [Countable interface](https://www.php.net/manual/en/class.countable.php) was implemented, so the item you can use `count($node)` function directly. But that wasn't the earlier behavior, so the [test code was changed](https://github.com/rafaelgou/rgou-wp-media/commit/8664eab31010c931c2aedc2e3e2205e40bf44176#diff-ae1c66b5e5b6f4d00c97a1b816e4561eL99) to cover all target versions.

#### Running

```bash
composer install
bin/install-wp-tests.sh <DB_NAME> <DB_USER> <DB_PASSWORD> localhost latest
composer install-codestandards
```

To run tests:

- Unit: `composer test-unit`
- Integration: `composer test-integration`
- Both: `composer run-tests`

To validate the code: `composer phpcs`.

## Extras

- Brazilian Portuguese translation was added.
- Unistall clean up.

## Clean up

Some unused features from [wppb.me](https://wppb.me/) were removed for the sake of clarity, like enqueueing scripts and styles.
