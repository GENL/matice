import MaticeLocalizationConfig from "./MaticeLocalizationConfig";

const assert = require("assert");

export interface TranslationOptions {
  args: { [key: string]: any },
  pluralize: boolean
}

class Localization {
  /**
   * Get the translations of [locale].
   * @param locale
   */
  private translations(locale: string = MaticeLocalizationConfig.locale) {
    // Matice is added with the directive "@translation"
    // @ts-ignore
    let translations = Matice.translations

    if (translations === undefined) {
      console.warn('Matice Translation not found. For Matice-js to work, make sure to add @translations' +
        ' blade directive in your view. Usually insert he directive in app.layout.');
      translations = [];
    } else {
      translations = translations[locale];

      if (translations === undefined) {
        throw `Locale [${locale}] does not exist.`
      }
    }

    return translations
  }

  /**
   * Translate the given key.
   */
  public trans(key: string, silentNotFoundError: boolean, options: TranslationOptions = {args: {}, pluralize: false}) {
    const args = options.args;

    let sentence = this.findSentence(key, silentNotFoundError)

    if (options.pluralize) {
      assert(typeof args.count === 'number',
        'On pluralization, the argument `count` must be a number and non-null.')
      sentence = this.pluralize(sentence, args.count)
    }

    // Replace the variables in sentence.
    Object.keys(args).forEach((key) => {
      sentence = sentence.replace(new RegExp(':' + key, 'g'), args[key])
    });

    return sentence
  }


  // noinspection JSMethodCanBeStatic
  /**
   * Manage sentence pluralization the sentence. Return the good sentence depending of the `count` argument.
   */
  private pluralize(sentence: string, count: number): string {
    let parts = sentence.split('|');

    if (sentence)

    // Make sure the pieces are always three in length for ease of calculation.
    // We fill the empty indexes with a direct preceding index.
    // We fill the empty parts by the last part.
      if (parts.length < 3) {
        if (parts.length === 2) parts = [parts[0], parts[1], parts[1]]
        else parts = [parts[0], parts[0], parts[0]]
      }

    if (count > 1) sentence = parts[2]
    else if (count === 1) sentence = parts[1]
    else sentence = parts[0]

    return sentence;
  }

  /**
   * Find the sentence using associated with the [key].
   * @param key
   * @param silentNotFoundError
   * @param locale
   * @returns {string}
   * @private
   */
  private findSentence(key: string, silentNotFoundError: boolean, locale: string = MaticeLocalizationConfig.locale): string {
    let translations: { [key: string]: any } = this.translations(locale);

    // At first [link] is a [Map<String, dynamic>] but at the end, it can be a [String],
    // the sentences.
    let link = translations;

    key.split('.').forEach((_key) => {
      // Get the new json until we fall on the last key of
      // the array which should point to a String.
      try {
        // Make sure the _key exist.
        // If not this throws an error that is handled by the "catch" block
        // @ts-ignore
        assert(link[_key]);
        // @ts-ignore
        link = link[_key];
      } catch (e) {
        // If sentence not found, try with the fallback locale.
        if (locale !== MaticeLocalizationConfig.fallbackLocale) {
          return this.findSentence(key, silentNotFoundError, MaticeLocalizationConfig.fallbackLocale);
        }
        throw `Translation key not found : "${key}" -> Exactly "${_key}" not found`;
      }
    });

    return link.toString();
  }
}




const localization = new Localization();


/**
 * Translate the given message.
 * @param key
 * @param options
 */
export function trans(key: string, options: TranslationOptions = {args: {}, pluralize: false}) {
  return localization.trans(key, false, options);
}

/**
 * Translate the given message with the particularity to return the key if
 * the sentence was not found, instead of throwing an exception.
 * @param key
 * @param options
 * @private
 */
export function __(key: string, options: TranslationOptions = {args: {}, pluralize: false}) {
  return localization.trans(key, true, options);
}
