import TRANSLATIONS from './poncho-admin-translations.json'

export default class Translator {

    constructor(locale, fallbackLocale = 'en') {
        this.locale = locale;
        this.fallbackLocale = locale
    }


    trans(key, params = {}, locale = null) {

        if (null === locale) {
            locale = this.locale
        }

        // search for locale
        let translation = this._search(key, locale)

        // search for fallback locale
        if (locale !== this.fallbackLocale && false === translation) {
            translation = this._search(key, this.fallbackLocale)
        }

        // no translation found
        if (false === translation) {
            return key
        }

        // replace params
        for (const [k, v] of Object.entries(params)) {
            translation = translation.replace(k, v)
        }

        return translation;
    }

    _search(key, locale) {
        return TRANSLATIONS[locale] && TRANSLATIONS[locale][key] && typeof TRANSLATIONS[locale][key] === 'string'
            ? TRANSLATIONS[locale][key]
            : false
    }
}
