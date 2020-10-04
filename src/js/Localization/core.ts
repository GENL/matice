const assert = require("assert");

export interface TranslationOptions {
  args: { [key: string]: any },
  pluralize: boolean
}

export default class Localization {
 private get translations()  {
   // Matice is added with the directive "@translation"
   // @ts-ignore
   return Matice.translations
 }

  /**
   * Translate the given key.
   */
  public trans(key: string, options: TranslationOptions = {args: {}, pluralize: false}) {
   const args = options.args;

   let sentence = this.findSentence(key, this.translations)

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
   * @param translations
   * @returns {string}
   * @private
   */
  private findSentence(key: string, translations: {[key: string]: any }): string {
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
        throw `Translation key not found : "${key}" -> Exactly "${_key}" not found`;
      }
    });

    return link.toString();
  }
}
