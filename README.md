# libfavorites

`libfavorites` is a PHP library with Joomla namespace support and a Composer PSR-4 autoloader for use in non-Joomla applications.

## Joomla

Install the Joomla library package. Joomla reads the namespace mapping from `libfavorites.xml`:

```xml
<namespace path="src">JLTRY\LibFavorites</namespace>
```

Once installed, class references can use imports such as `use JLTRY\LibFavorites\HtmlFavorites;` without manually including each class file.

## Non-Joomla PHP applications

Install with Composer from the repository root:

```bash
composer require jltry/libfavorites
```

Or add the package to your `composer.json` and run `composer install`. Composer maps `JLTRY\LibFavorites\` to `src/`. The source files do not require `_JEXEC`, so they can be used outside Joomla.

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use JLTRY\LibFavorites\HtmlFavorites;
use JLTRY\LibFavorites\JsonFavorites;

$root = HtmlFavorites::converthtmltoroot('/path/to/bookmarks.html');
// Or: $root = JsonFavorites::convertjsontoroot('/path/to/bookmarks.json');
```

`readsync()` is a namespaced function rather than a class, so Composer loads it from `src/readsync.body.php` using the `autoload.files` setting. Call it as `JLTRY\LibFavorites\readsync($params, $result)`.

The HTML import path needs PHP's DOM extension; URL checking needs cURL.
