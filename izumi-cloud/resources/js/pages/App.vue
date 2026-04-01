<template>
	<div id="app">
		<router-view />
	</div>
</template>

<script>

const urlAPI = {
    urlRefreshToken: `/api/auth/refresh`,
};
import { getToken } from '@/utils/handleToken';
import axios from 'axios';
export default {
    name: 'App',

    data() {
        return {
            lastActive: null,
            lastParentActive: null,
            checkSubEq: false,
        };
    },
    created() {
        console.log(
            '%cIzumi Cloud',
            'font-size: 20px; padding: 5px 10px 5px 10px; border-radius: .25rem; color: #55AEEE; background-color: #0F0049; text-align: center;'
        );
    },
    mounted() {
        const ROLES = this.$store.getters.profile.roles;

        if (ROLES) {
            this.$store.dispatch('role/generateRoutes', { roles: ROLES, permissions: [] })
                .then((routes) => {
                    for (let route = 0; route < routes.length; route++) {
                        this.$router.addRoute(routes[route]);
                    }
                });
        }

        window.addEventListener('keydown', this.resetActivityTimer);
        window.addEventListener('click', this.resetActivityTimer);

        setInterval(() => {
            this.callApiRefreshToken();
        }, 60000);
    },

    methods: {
        async callApiRefreshToken() {
            const token = getToken();

            if (token && this.lastActive) {
                try {
                    const headers = {
                        'Authorization': token,
                    };
                    const response = await axios.post(urlAPI.urlRefreshToken, null, { headers });
                    if (response.status === 200) {
                        const newToken = response.data.data.access_token;
                        localStorage.setItem('refresh_token', `Bearer ${newToken}`);
                        await this.$store.dispatch('user/setRefreshToken', `Bearer ${newToken}`);
                        this.lastActive = false;
                    } else {
                        this.lastActive = false;
                        console.error('Không thể làm mới token');
                    }
                } catch (error) {
                    this.lastActive = false;
                    console.error('Lỗi khi làm mới token:', error);
                }
            }
        },

        resetActivityTimer() {
            this.lastActive = true;
        },
    },
};
</script>
