/**
 * Function get user's role
 * @param {*} role
 * @returns String
 * Example: Crew, Manager, Driver...
 */
export function getUserRole(role) {
    if (role === 1) {
        return 'USER_MANAGEMENT.ROLE_MASTER';
    } else if (role === 2) {
        return 'USER_MANAGEMENT.ROLE_HR';
    } else if (role === 3) {
        return 'USER_MANAGEMENT.ROLE_ACCOUNTING';
    } else if (role === 4) {
        return 'USER_MANAGEMENT.ROLE_FACILITY';
    } else {
        return 'ERROR_!!!!!';
    }
}

