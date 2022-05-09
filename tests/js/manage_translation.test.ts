import {__, trans, setLocale, transChoice, locales} from "../../src/js/matice";

// @ts-ignore
global.Matice = {
  "translations": {
    "en": {
      "greet": {
        "me": "Hello!",
        "meMore": "Hello Ekcel Henrich!",
        "people": "Hello Ekcel!|Hello everyone!",
      },
      "balance": "{0} You're broke|[1000, 5000] a middle man|[1000000,*] You are awesome :name; :count Million Dollars",
      "pluralTwoSegments": "One user|Many users",
    },
    "fr": {
      "greet": {
        "me": "Bonjour!"
      },
      "Key with one dot. Should be OK": "Avec un point, c'est bien.",
      "Key with dots. Should be better...": "Avec plusieurs points, c'est mieux..."
    }
  },
  "locale": "en",
  "fallbackLocale": "en",
}

// const translation = require("../../src/js")

test('Retrieves simple sentence.', () => {
  expect(locales()).toEqual(['en', 'fr'])

  let sentence = trans('greet.me')
  expect(sentence).toEqual("Hello!")

  expect(() => trans('greet.unknown'))
    .toThrow(`Translation key not found : "greet.unknown" -> Exactly "unknown" not found`)

  sentence = __('greet.unknown')
  expect(sentence).toEqual('greet.unknown')

  // Test missing locale
  setLocale('non_Existing_Locale')
  expect(() => __('greet.unknown')).toThrow('Locale [non_Existing_Locale] does not exist.')

  // Change locale
  setLocale('fr')

  // Test locale change text
  sentence = trans('greet.me')
  expect(sentence).toEqual("Bonjour!")

  // test fallback locale text.
  // greet.meMore in french so fallback to english.
  sentence = __('greet.meMore')
  expect(sentence).toEqual('Hello Ekcel Henrich!')
    
  // Test translation key with one dot
  sentence = trans('Key with one dot. Should be OK')
  expect(sentence).toEqual("Avec un point, c'est bien.")
    
  // Test translation key with multiple dots
  sentence = trans('Key with dots. Should be better...')
  expect(sentence).toEqual("Avec plusieurs points, c'est mieux...")
});

test('Pluralize the sentence well', () => {
  let sentence: string;
  sentence = trans('greet.people', {args: {count: 0}, pluralize: true})
  expect(sentence).toEqual("Hello Ekcel!")

  sentence = trans('greet.people', {args: {count: 20}, pluralize: true})
  expect(sentence).toEqual("Hello everyone!")

  sentence = trans('balance', {args: {count: 0}, pluralize: true})
  expect(sentence).toEqual(" You're broke")

  sentence = trans('balance', {args: {count: 2853}, pluralize: true})
  expect(sentence).toEqual(" a middle man")

  sentence = trans('balance', {args: {count: 1000000}, pluralize: true})
  expect(sentence).toEqual(" You are awesome :name; 1000000 Million Dollars")

  sentence = transChoice('balance', 8578442, {name: 'Ekcel'})
  expect(sentence).toEqual(" You are awesome Ekcel; 8578442 Million Dollars")

  sentence = transChoice('pluralTwoSegments', 1)
  expect(sentence).toEqual("One user")

  sentence = transChoice('pluralTwoSegments', 2)
  expect(sentence).toEqual("Many users")
});

test('Test that the locale can be forced', () => {
  expect(trans('greet.me', { locale: 'fr' })).toEqual("Bonjour!")

  expect(__('greet.me', { locale: 'fr' })).toEqual("Bonjour!")

  expect(transChoice('greet.me', 1, undefined, 'fr')).toEqual("Bonjour!")
})