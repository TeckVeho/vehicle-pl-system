import router, { resetRouter } from '@/router';
import store from '@/store';
import { getToken } from '@/utils/handleToken';
import getPageTitle from '@/utils/getPageTitle';
import CONST_ROLE from '@/const/role';
import { hasRole } from '@/utils/hasRole';

const whiteList = [
    '/login',
    '/register',
    '/reset-password',
    '/notification',
    '/preview-pdf',
    '/auth-mobile',
    '/public-driver-recorder',
    '/public-play-recorder/:id/:index',
    '/public-playlist/:id',
    '/public-driver-recorder-detail/:id',
];

router.beforeEach(async(to, from, next) => {
    document.title = getPageTitle();

    const TOKEN = getToken();

    if (TOKEN) {
        if (to.path === '/login') {
            next({ path: '/' });
        } else {
            if (to.path === '/master-manager') {
                const ROLE = store.getters.profile.roles;

                if (hasRole([
                    CONST_ROLE.PERSONNEL_LABOR,
                    CONST_ROLE.HEADQUARTER,
                    CONST_ROLE.AM_SM,
                    CONST_ROLE.DIRECTOR,
                    CONST_ROLE.DX_USER,
                    CONST_ROLE.DX_MANAGER,
                ], ROLE)) {
                    next('/master-manager/user-master');
                }

                if (hasRole([
                    CONST_ROLE.CREW,
                    CONST_ROLE.CLERKS,
                    CONST_ROLE.TL,
                    CONST_ROLE.DEPARTMENT_OFFICE_STAFF,
                ], ROLE)) {
                    next('/master-manager/course-master');
                }

                if (hasRole([
                    CONST_ROLE.ACCOUNTING,
                    CONST_ROLE.ACCOUNTANT_DIRECTOR,
                ], ROLE)) {
                    next('/master-manager/route-master');
                }

                if (hasRole([
                    CONST_ROLE.GENERAL_AFFAIRS,
                ], ROLE)) {
                    next('/master-manager/customer-master');
                }
            }

            next();
        }
    } else {
        resetRouter();

        if (whiteList.indexOf(to.matched[0] ? to.matched[0].path : '') !== -1) {
            next();
        } else {
            next(`/login`);
        }
    }
});
