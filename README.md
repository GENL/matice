# Use your Laravel translations in JavaScript

[![Latest Version on Packagist](https://img.shields.io/packagist/v/genl/matice.svg?style=flat-square)](https://packagist.org/packages/genl/matice)
[![Latest Version on NPM](https://img.shields.io/npm/v/matice.svg?style=flat)](https://npmjs.com/package/matice)
[![GitHub Actions Status](https://img.shields.io/github/workflow/status/genl/matice/Tests?label=tests&style=flat)](https://github.com/genl/matice/actions?query=workflow:Tests+branch:master)
[![Total Downloads on packagist](https://img.shields.io/packagist/dt/genl/matice.svg?style=flat-square)](https://www.npmjs.com/package/matice)
[![Downloads on NPM](https://img.shields.io/npm/dt/matice.svg?style=flat)](https://www.npmjs.com/package/matice)

Matice creates a Blade directive that you can include in your views. 
It will export a JavaScript object of your application's translations,
keyed by their names (aliases, lang, filenames, folders name), 
as well as globals trans(), __() and transChoice() helper
functions which you can use to access your translations in your JavaScript.


- [Installation](#installation)
- [Usage](#usage)
    - [Examples](#examples)
    - [trans](#trans)
    - [Update locale](#update-locale)
    - [Pluralization](#pluralization)
    - [Trans Choice](#trans-choice)
    - [underscore function](#underscore-function)
    - [Default Values](#default-values)
    - [Retrieve the current locale](#retrieve-the-current-locale)
- [Artisan Command](#artisan-command)
- [Using with Vue Components](#using-with-vue-components)
- [Dive Deeper](#dive-deeper)

## Installation

You can install the package via composer:

```bash
composer require genl/matice
```

1. ##### Include our Blade directive (`@translations`) somewhere in your template before your main application JavaScript is loaded—likely in the header somewhere.
1. ##### Publish the vendor if you want to customize config: 
```php
php artisan vendor:publish --provider=Genl\Matice\MaticeServiceProvider
```

Matice is available as a NPM package `matice`
that exposes the `trans()` function for use in frontend apps that 
are not using Blade. 
You can install the NPM package with:
```bash
// With yarn
yarn add matice

With npm
npm install matice
```

or load it from a CDN:
```html
<!-- Load the Matice translation object first -->
<script defer src="https://unpkg.com/matice@1.1.x/dist/matice.min.js"></script>
```

* Note that you still have to generate your translations file and make 
it available to your frontend app by using `@translations`.

**TypeScript support**

Matice is fully written in TypeScript so, it's compatible with TypeScript projects.



## Usage

* ##### Core concepts

Matice comes with almost the same localization concepts as Laravel does. 
Read more about [Laravel localization](https://laravel.com/docs/localization)

This package uses the `@translations` directive to inject a JavaScript object containing all of your application's translations, keyed by their names. This collection is available at `Matice.translations`.

The package also creates an optional `trans()` JavaScript helper that functions like Laravel's PHP `trans()` helper, which can be used to retrieve translation sentences.


#### Examples

import the `trans()` function like follow:
 ```javascript
import { trans } from "matice";
```

Let's assume we have this translations:

```php
// resources/lang/en/custom.php

return [
    'greet' => [
        'me': 'Hello!',
        'someone' => 'Hello :name!',
        'me_more' => 'Hello Ekcel Henrich!',
        'people' =>'Hello Ekcel!|Hello everyone!',
    ],
    balance => "{0} You're broke|[1000, 5000] a middle man|[1000000,*] You are awesome :name; :count Million Dollars"
];
```

```php
// resources/lang/fr/custom.php

return [
    'greet' => [
        me => 'Bonjour!'
    ]
];
```

#### trans

Retrieve a text:
```javascript
let sentence = ''

sentence = trans('greet.me') // Hello!

// With parameters
sentence = trans('greet.someone', {args: {name: "Ekcel"}}) // Hello Ekcel!
```

#### Update locale

Matice expose `setLocale` function to uchange the locale that is used by the `trans` function.
```typescript
import { setLocale } from "matice"

// update the locale
setLocale('fr')
sentence = trans('greet.me') // Bonjour!
```

#### Pluralization 

Pluralization depends on the `count` argument. 

```javascript
// Pluralization
sentence = trans('greet.people', {args: {count: 0}, pluralize: true}) // Hello Ekcel!
sentence = trans('greet.people', {args: {count: 2}, pluralize: true}) // Hello everyone!

// Advanced pluralization with range
sentence = trans('balance', {args: {count: 0}, pluralize: true}) // You're broke
sentence = trans('balance', {args: {count: 3000}, pluralize: true}) // a middle man
```

#### Trans Choice

Matice provides a helper function for pluralization

```javascript
import { transChoice } from "matice"

let sentence = transChoice('balance', 17433085, {name: 'Ekcel'}) // You are awesome Ekcel; 17433085 Million Dollars
```


#### Underscore function
* As well of the `trans()` function, Matice provide `__()` that does the same as the `trans()` function but with this particularity
not to throw an error when the key is not found but returns an the key itself.

`transChoice()` always throws an error if the key is not found. To change this behaviour, use `__(key, {pluralize: true})`

```js
sentence = trans('greet.unknown') // -> throws an error with a message.
sentence = __('greet.unknown') // greet.unknown
```

#### Default values

Matice uses your current app locale `app()->getLocale()` a the default locale when generating the translation object with the blade directive `@translations`.
When generating with command line, it use the one in your `config.app`

When Matice does not find a key, he fallback to the default locale and search again. The fallback is the
same you define in your config.

```php
// config/app.php

'locale' => 'fr',
'fallback_locale' => 'en',
```

#### Retrieve the current locale
To locales information, matice exposes the `MaticeLocalizationConfig` class :
```js
import { MaticeLocalizationConfig } from "matice"

const locale = MaticeLocalizationConfig.locale // 'en'

const fallbackLocale = MaticeLocalizationConfig.fallbackLocale // 'en'

const locales = MaticeLocalizationConfig.locales // ['en', 'fr']
```

Matice also provides helpers to deal with locales information:
```js
import { setLocale, getLocale, locales } from "matice"

// Update the locale
setLocale('fr') //

const locale = getLocale() // 'en'

const locales = locales() // ['en', 'fr']
``` 

## Artisan Command
Matice registers an Artisan console command to generate a `matice_translations.js` translations file, which can be used as part of an asset pipeline such as [Laravel Mix](https://laravel.com/docs/mix).

You can run `php artisan matice:generate` in your project to generate a static translations file in `resources/assets/js/matice_translations.js`. You can optionally include a second parameter to override the path and file name (you must pass a complete path, including the file name):

```sh
php artisan matice:generate "resources/foo.js"
```

Example `matice_translations.js`, where auth translations exist in `resources/lang/en/auth.php`:

```php
// resources/lang/en/auth.php

return [
    'failed' => 'These credentials do not match our records.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
]
```

```js
// matice_translations.js

const Matice = {
    translations: {
      en: {
        auth: {
          failed: 'These credentials do not match our records.',
          throttle: 'Too many login attempts. Please try again in :seconds seconds.'
        }
      }
    },
    locale: 'en',
    fallbackLocale: 'en'
};

export { Matice };
```

At this point you can use javascript this translations file like usual, paste in your html as well.

## Using with Vue Components

If you want to use the `route()` helper in a Vue component, add this to your `app.js` file:

```typescript
// app.js

import {__, trans, setLocale, getLocale, transChoice, MaticeLocalizationConfig, locales} from "matice"

Vue.mixin({
    methods: {
        $trans: trans,
        $__: __,
        $transChoice: transChoice,
        $setLocale: (locale: string) => {
          setLocale(locale);
          app.$forceUpdate() // Refresh the vue instance after locale change.
        },
        // The current locale
        $locale() {
            return getLocale()
        },
        // A listing of the available locales
        $locales() {
            return locales()
        }
    },
})
```

Then you can use the methods in your Vue components like so:

```html
<p>{{ $trans('greet.me') }}</p>
```

## Dive Deeper

Matice extends the Laravel `Translator` Class. Use `Translator::list()` to returns
an array representation of all of your app translations.

If you want to load only translations of a specfic locale, use the matice facade:
```php
use GENL\Matice\Facades\Matice;

// Or load translations for a specific locale.
$translations = Matice::translations($locale)

// Or all the translations
$translations = Matice::translations()
```


**Environment-based loading of minified matice helper file**

When using the `@translations` Blade directive, Matice will detect the current environment and minify the output if `APP_ENV` is `production`. 
In this case matice will run `php artisan matice:generate` to generate translations file (if not exists) and use it — otherwise, translations will always be re-loaded.


######**Note: Matice works with json translation files as well as with php files.**, 


### Testing

``` bash
// --  laravel test --
composer test

// -- js test --

// With yarn
yarn test

// With npm
npm run test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email bigcodole@gmail.com instead of using the issue tracker.

## Credits

- [GENL](https://github.com/GENL/matice)
- [All Contributors](../../contributors)
- This package was largely inspired by [Ziggy](https://github.com/tighten/ziggy)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
