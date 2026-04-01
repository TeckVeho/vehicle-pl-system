export function hasRole(rolesCondition = [], roles = []) {
    const len = roles.length;
    let idx = 0;

    while (idx < len) {
        if (rolesCondition.includes(roles[idx])) {
            return true;
        }

        idx++;
    }

    return false;
}
