<template>
	<div class="container">
		<h2 class="message">{{ message }}</h2>
	</div>
</template>

<script>

import store from '@/store';
import { parseToken } from '@/utils/handleToken';

const axios = require('axios').default;

const LOADING = 'LOADING';
const urlAPI = '/api/profile';

export default {
    name: 'AuthMobile',
    data() {
        return {
            message: LOADING,
        };
    },
    computed: {
        token() {
            return this.$route.query.token;
        },
    },
    created() {
        this.handleVerifiedToken(this.token);
    },
    methods: {
        async handleVerifiedToken(token = '') {
            if (token) {
                const URL = urlAPI;

                const HEADERS = {
                    'Authorization': token,
                    'Content-Type': 'application/json',
                };

                try {
                    const response = await axios.get(URL, { headers: HEADERS });

                    if (response.status === 200) {
                        const DATA = response.data.data.profile;

                        const ROLES = response.data.data.roles;

                        const TOKEN = this.token;

                        const EXP_TOKEN = parseToken(TOKEN);

                        const USER = {
                            id: DATA.id || '',
                            uuid: DATA.uuid || '',
                            name: DATA.name || '',
                            email: DATA.email || '',
                            supervisor_email: DATA.supervisor_email || '',
                            department_code: DATA.department_code || '',
                            department: DATA.department || '',
                            role: DATA.role || '',
                            roles: ROLES || [],
                            expToken: EXP_TOKEN.exp || '',
                        };

                        await store.dispatch('user/setAutoLogin', { USER, TOKEN })
                            .then(() => {
                                this.$store.dispatch('role/generateRoutes', { roles: ROLES, permissions: [] })
                                    .then((routes) => {
                                        for (let route = 0; route < routes.length; route++) {
                                            this.$router.addRoute(routes[route]);
                                        }

                                        this.$toast.success({
                                            content: this.$t('LOGIN_SUCCESS'),
                                        });
                                    });
                            });

                        this.message = 'SUCCESSFULLY LOGGED IN.';

                        const urlName = 'transportation';

                        await setTimeout(() => {
                            this.$router.push({ path: urlName });
                        }, 2000);
                    } else {
                        this.message = 'FAILED TO LOGIN.';
                    }
                } catch (error) {
                    console.log(error);
                    this.message = 'FAILED TO LOGIN.';
                }
            } else {
                this.message = 'NO TOKEN PROVIDED.';
            }
        },
    },
};
</script>

<style lang="scss" scoped>
.container {
  display: flex;
  justify-content: center;
  align-items: center;

  margin-top: 100px;
}

.message {
  text-transform: uppercase;
  text-align: center;
}
</style>
