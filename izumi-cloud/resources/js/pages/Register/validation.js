import { MakeToast } from '@/utils/MakeToast';
import i18n from '@/lang';

export function validateRegister(DATA = {}) {
    let isPassValidation = false;

    if (DATA) {
        if (DATA.password.length === 0) {
            isPassValidation = false;

            MakeToast({
                variant: 'warning',
                title: i18n.t('WARNING'),
                content: i18n.t('REGISTER.PASSWORD_REQUIRE_VALIDATION'),
            });
        } else if (DATA.password.length < 8 || DATA.password.length > 16) {
            isPassValidation = false;

            MakeToast({
                variant: 'warning',
                title: i18n.t('WARNING'),
                content: '新しいパスワード・パスワード（確認）は8文字以上16文字以内で入力してください',
            });
        } else if (DATA.confirm_password.length === 0) {
            isPassValidation = false;

            MakeToast({
                variant: 'warning',
                title: i18n.t('WARNING'),
                content: i18n.t('REGISTER.CONFIRM_PASSWORD_REQUIRE_VALIDATION'),
            });
        } else if (DATA.confirm_password.length < 8 || DATA.confirm_password.length > 16) {
            isPassValidation = false;

            MakeToast({
                variant: 'warning',
                title: i18n.t('WARNING'),
                content: '新しいパスワード・パスワード（確認）は8文字以上16文字以内で入力してください',
            });
        } else if (DATA.password !== DATA.confirm_password) {
            isPassValidation = false;

            MakeToast({
                variant: 'warning',
                title: i18n.t('WARNING'),
                content: i18n.t('REGISTER.NOT_MATCH_PASSWORD'),
            });
        } else {
            isPassValidation = true;
        }
    }

    return isPassValidation;
}
