const getters = {
    language: state => state.app.language,
    userId: state => state.user.profile.id,
    name: state => state.user.profile.name,
    email: state => state.user.profile.email,
    token: state => state.user.access_token,
    profile: state => state.user.profile,
    expToken: state => state.user.profile.expToken,
    permissionRoutes: state => state.role.routes,
    addRoutes: state => state.role.addRoutes,
    deviceMode: state => state.app.deviceMode,

    // Filter
    filterEmployeeMaster: state => state.filter.employeeMaster,
    filterCourseMaster: state => state.filter.courseMaster,
    filterRouteMaster: state => state.filter.routeMaster,
    filterUserMaster: state => state.filter.userMaster,
    filterDataList: state => state.filter.dataList,
    filterDataConnect: state => state.filter.dataConnect,
    filterTypeDateChange: state => state.filter.typeDateChange,
    filterVehicleMaster: state => state.filter.vehicleMaster,
    filterStoreMaster: state => state.filter.storeMaster,
    filterUrgentContactMaster: state => state.filter.urgentContactMaster,

    // Driver Recorder
    yearMonthPicker: state => state.driverRecorder.yearMonthPicker,

    // Vehicle Master
    yearMonthPickerVehicleMaster: state => state.vehicleMaster.yearMonthPicker,

    // Pagination
    userManagementCurrentPage: state => state.user_management.current_page,
    userManagementPerPage: state => state.user_management.per_page,

    storeManagementCurrentPage: state => state.store_management.current_page,
    storeManagementPerPage: state => state.store_management.per_page,

    driverRecorderCurrentPage: state => state.driver_recorder.current_page,
    driverRecorderPerPage: state => state.driver_recorder.per_page,

    // PAGINATION NEW UPATE MON 14 AUG 2023
    employeeMasterCP: state => state.pagination.employee_master_cp,
    courseMasterCP: state => state.pagination.course_master_cp,
    routeMasterCP: state => state.pagination.route_master_cp,
    storeMasterCP: state => state.pagination.store_master_cp,
    customerMasterCP: state => state.pagination.customer_master_cp,
    vehicleMasterCP: state => state.pagination.vehicle_master_cp,
    userManagementCP: state => state.pagination.user_management_cp,

    // PER PAGE NEW UPDATE FRI 25 AUG 2023
    employee_master_per_page: state => state.pagination.employee_master_per_page,
    course_master_per_page: state => state.pagination.course_master_per_page,
    route_master_per_page: state => state.pagination.route_master_per_page,
    store_master_per_page: state => state.pagination.store_master_per_page,
    customer_master_per_page: state => state.pagination.customer_master_per_page,
    vehicle_master_per_page: state => state.pagination.vehicle_master_per_page,
    user_management_per_page: state => state.pagination.user_management_per_page,

    current_index_recorder: state => state.driver_recorder.current_index_recorder,
    is_autoplay: state => state.driver_recorder.is_autoplay,

    yearMonthPickerDriverRecorder: state => state.driver_recorder.yearMonth,
};

export default getters;
