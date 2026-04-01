/**
 * Function get Route Master Edit Pagination Data in Local Storage
 * @returns Return Object Profile
 */
export function getPagination() {
    let ROUTE_MASTER_EDIT_PAGINATION = window.localStorage.getItem('route_master_edit_pagination');

    if (ROUTE_MASTER_EDIT_PAGINATION) {
        return JSON.parse(ROUTE_MASTER_EDIT_PAGINATION);
    } else {
        ROUTE_MASTER_EDIT_PAGINATION = {
            current_page: 1,
            per_page: 10,
            total_rows: 0,
        };
    }

    return ROUTE_MASTER_EDIT_PAGINATION;
}
