export default {
    USER_NOT_FOUND: {
        code: 404,
        message: 'user not found',
        data: '',
    },
    TOKEN_EXPIRE: {
        code: 401,
        data_error: null,
        message: '',
        message_content: 'token expire',
        message_internal: null,
    },
    PROFILE: {
        id: '',
        uuid: '',
        name: '',
        email: '',
        supervisor_email: '',
        department_code: '',
        department: '',
        role: '',
        roles: [],
        expToken: '',
    },
};
