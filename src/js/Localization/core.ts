import MaticeLocalizationConfig from "./MaticeLocalizationConfig"

function assert(value: boolean, message: string) {
  if (! value) throw message
}

export interface TranslationOptions {
  args?: { [key: string]: any },
  pluralize?: boolean,
  locale?: string,
}

class Localization {
  private static _instance: Localization

  public static get instance(): Localization {
    if (Localization._instance === undefined) {
      Localization._instance = new Localization()
    }
    return Localization._instance
  }

  private constructor() {
    // @ts-ignore
    MaticeLocalizationConfig.locale = Matice.locale

    // @ts-ignore
    MaticeLocalizationConfig.fallbackLocale = Matice.fallbackLocale

    // @ts-ignore
    MaticeLocalizationConfig.locales = Object.keys(Matice.translations)
  }

  /**
   * Update the locale
   * @param locale
   */
  public setLocale(locale: string) {
    MaticeLocalizationConfig.locale = locale
  }

  /**
   * Retrieve the current locale
   */
  public getLocale() {
    return MaticeLocalizationConfig.locale
  }

  /**
   * Return a listing of the locales.
   */
  public locales() {
    return MaticeLocalizationConfig.locales
  }

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
        ' blade directive in your view. Usually insert the directive in app.layout.')
      translations = []
    } else {
      translations = translations[locale]

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
    const args = options.args || {}

    let sentence = this.findSentence(key, silentNotFoundError, options.locale)

    if (options.pluralize) {
      assert(typeof args.count === 'number',
        'On pluralization, the argument `count` must be a number and non-null.')
      sentence = this.pluralize(sentence, args.count)
    }

    // Replace the variables in sentence.
    Object.keys(args).forEach((key) => {
      sentence = sentence.replace(new RegExp(':' + key, 'g'), args[key])
    })

    return sentence
  }


  // noinspection JSMethodCanBeStatic
  /**
   * Manage sentence pluralization the sentence. Return the good sentence depending of the `count` argument.
   */
  private pluralize(sentence: string, count: number): string {
    let parts = sentence.split('|')

    // Make sure the pieces are always three in length for ease of calculation.
    // We fill the empty indexes with a direct preceding index.
    // We fill the empty parts by the last part.
    if (parts.length >= 3) parts = [parts[0], parts[1], parts[2]]
    else if (parts.length === 2) parts = [parts[0], parts[0], parts[1]]
    else parts = [parts[0], parts[0], parts[0]]

    // Manage multiple number range.
    let ranges: { min: number, max: number, part: string }[] = []
    const pattern = /^(\[(\s*\d+\s*)+,(\s*(\d+|\*)\s*)])|({\s*\d+\s*})/

    for (let i = 0; i < parts.length; i++) {
      let part = parts[i]
      let matched = part.match(pattern)

      if (matched === null) {
        // If range is found, use the part index as the range.
        parts[i] = `{${i}} ${parts[i]}`
        matched = [parts[i]]
      }

      // Remove unwanted characters: "[",  "]",  "{",  "}"
      const replaced = matched[0].replace(/[\[{\]}]/, '')
      // Split the matched to have an array of string number
      const rangeNumbers = replaced.split(',').map((m: string) => {
        const parsed = Number.parseInt(m.trim())
        // If parsed is a star(*) which mean infinity, just replace by count + 1
        return Number.isInteger(parsed) ? parsed : count + 1
      })

      // Lets make sure to remove the range symbols in the parts.
      parts[i] = part = part.replace(pattern, '')

      ranges.push(
        rangeNumbers.length == 1
          ? {min: rangeNumbers[0], max: rangeNumbers[0], part}
          : {min: rangeNumbers[0], max: rangeNumbers[1], part}
      )
    }

    let foundInRange = false
    // Compare the part with the range to choose the pluralization.
    // -------  ------
    // Return the first part if count is zero or negative
    if (count <= 0) {
      sentence = parts[0]
    } else {
      for (const range of ranges) {
        // If count is in the range, return the corresponding text part.
        if (count >= range.min && count <= range.max) {
          // count is in the range.
          sentence = range.part
          foundInRange = true
          break
        }
      }
      if (! foundInRange) {
        // If count is not in the range, we use the last part.
        sentence = parts[parts.length - 1]
      }
    }

    return sentence
  }

  /**
   * Find the sentence using associated with the [key].
   * @param key
   * @param silentNotFoundError
   * @param locale
   * @param splitKey
   * @returns {string}
   * @private
   */
  private findSentence(key: string, silentNotFoundError: boolean, locale: string = MaticeLocalizationConfig.locale, splitKey: boolean = false): string {
    const translations: { [key: string]: any } = this.translations(locale)

    // At first [link] is a [Map<String, dynamic>] but at the end, it can be a [String],
    // the sentences.
    let link = translations

    const parts = splitKey ? key.split('.') : [key]

    for (const part of parts) {
      // Get the new json until we fall on the last key of
      // the array which should point to a String.
      if (typeof link === 'object' && part in link) {
        // Make sure the key exist.
        link = link[part]
      } else {
        // If key not found, try to split it using dot.
        if (!splitKey) {
          return this.findSentence(key, silentNotFoundError, locale, true)
        }

        // If key not found, try with the fallback locale.
        if (locale !== MaticeLocalizationConfig.fallbackLocale) {
          return this.findSentence(key, silentNotFoundError, MaticeLocalizationConfig.fallbackLocale)
        }

        // If the key not found and the silent mode is on, return the key,
        if (silentNotFoundError) return key

        // If key not found and the silent mode is off, throw error,
        throw `Translation key not found : "${key}" -> Exactly "${part}" not found`
      }
    }

    return link.toString()
  }
}



/*
|
| ----------------------------------
| Exports
| ----------------------------------
|
*/


/**
 * Translate the given message.
 * @param key
 * @param options
 */
export function trans(key: string, options: TranslationOptions = {args: {}, pluralize: false}) {
  return Localization.instance.trans(key, false, options)
}

/**
 * Translate the given message with the particularity to return the key if
 * the sentence was not found, instead of throwing an exception.
 * @param key
 * @param options
 */
export function __(key: string, options: TranslationOptions = {args: {}, pluralize: false}) {
  return Localization.instance.trans(key, true, options)
}

/**
 * An helper to the trans function but with the pluralization mode activated by default.
 * @param key
 * @param count
 * @param args
 * @param locale
 */
export function transChoice(key: string, count: number, args: {} = {}, locale: string = MaticeLocalizationConfig.locale) {
  return trans(key, { args: {...args, count}, pluralize: true, locale })
}

/**
 * Update the locale
 * @param locale
 */
export function setLocale(locale: string) {
  Localization.instance.setLocale(locale)

}

/**
 * Retrieve the current locale
 */
export function getLocale() {
  return Localization.instance.getLocale()
}

/**
 * Return a listing of the locales.
 */
export function locales() {
  return Localization.instance.locales()
}
