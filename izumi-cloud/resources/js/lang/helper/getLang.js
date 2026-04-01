import Cookies from 'js-cookie';

/**
 * Function get Current Language in Cookies
 * @returns Current Language (String)
 */
export function getLanguage() {
    const chooseLanguage = Cookies.get('language');

    if (chooseLanguage) {
        return chooseLanguage;
    }

    return process.env.MIX_LARAVEL_LANG || 'en';
}
