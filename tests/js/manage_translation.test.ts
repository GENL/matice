import {trans} from "../../src/js";

// @ts-ignore
global.Matice = {
  "translations": {
    "en": {
      "greet": {
        "me": "Hello!"
      }
    },
  },
  "locale": "en",
  "fallbackLocale": "en",
}

// const translation = require("../../src/js")

test('Retrieves simple sentence.', () => {
  let sentence = trans('greet.me')

  expect(sentence).toEqual("Hello!")
});
