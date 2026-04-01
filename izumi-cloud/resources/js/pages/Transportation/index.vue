<template>
	<div>
		<b-overlay
			:show="overlay.show"
			:variant="overlay.variant"
			:opacity="overlay.opacity"
			:blur="overlay.blur"
			:rounded="overlay.sm"
		>
			<template #overlay>
				<div class="text-center">
					<b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
					<p style="margin-top: 10px">{{ $t('PLEASE_WAIT') }}</p>
				</div>
			</template>

			<!-- ROW 1 CARD -->
			<div v-if="deviceMode === 'desktop'" class="main-content d-flex flex-row justify-content-center align-items-center">
				<div class="card text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.timeSheet)">
					<div class="icon-holder">
						<i class="far fa-calendar-alt" />
					</div>
				</div>

				<div v-if="![3].includes(role)" class="card text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.payslip)">
					<div class="icon-holder">
						<i class="fad fa-credit-card" />
					</div>
				</div>

				<div class="card text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.maintenanceSystem)">
					<div class="icon-holder">
						<i class="fas fa-wrench" />
					</div>
				</div>

				<div v-if="![1, 2, 3, 12].includes(role)" class="card text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.izumiWebApp)">
					<div class="icon-holder">
						<i class="fad fa-browser" />
					</div>
				</div>

				<div v-if="[3].includes(role)" class="card text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.plSystem)">
					<div class="icon-holder">
						<i class="fas fa-file-invoice-dollar" />
					</div>
				</div>

				<div v-if="[1, 2, 3, 12].includes(role)" class="card text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.eLearning)">
					<div class="icon-holder">
						<i class="fas fa-graduation-cap" />
					</div>
				</div>
			</div>

			<!-- ROW 1 TITLE -->
			<div v-if="deviceMode === 'desktop'" class="sub-content d-flex flex-row justify-content-center align-items-center">
				<div class="card align-items-center mt-3">
					<span>タイムシート</span>
				</div>

				<div v-if="![3].includes(role)" class="card align-items-center mt-3">
					<span>給与明細クラウド</span>
				</div>

				<div class="card align-items-center mt-3">
					<span>整備システム</span>
				</div>

				<div v-if="![1, 2, 3, 12].includes(role)" class="card align-items-center mt-3">
					<span>Web App</span>
				</div>

				<div v-if="[3].includes(role)" class="card align-items-center mt-3">
					<span>PLシステム</span>
				</div>

				<div v-if="[1, 2, 3, 12].includes(role)" class="card align-items-center mt-3">
					<span>E-ラーニング</span>
				</div>
			</div>

			<!-- ROW 2 CARD -->
			<div v-if="deviceMode === 'desktop'" class="main-content d-flex flex-row justify-content-center align-items-center">
				<template v-if="![1, 2, 3, 12].includes(role)">
					<div class="card text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.workshift)">
						<div class="icon-holder">
							<i class="fas fa-truck" />
						</div>
					</div>
				</template>

				<template v-if="[1, 2].includes(role)">
					<div class="card text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.workshift)">
						<div class="icon-holder">
							<i class="fas fa-truck" />
						</div>
					</div>

					<div class="empty-card text-center align-items-center justify-content-center" />

					<div class="empty-card text-center align-items-center justify-content-center" />
					<div class="empty-card text-center align-items-center justify-content-center" />
				</template>

				<template v-else>
					<template v-if="role === 4">
						<div class="card text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.plSystem)">
							<div class="icon-holder">
								<i class="fas fa-file-invoice-dollar" />
							</div>
						</div>

						<div class="card text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.izumiWorks)">
							<div class="icon-holder">
								<i class="fas fa-id-badge" />
							</div>
						</div>

						<div class="card text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.smartApproval)">
							<div class="icon-holder">
								<i class="fas fa-file-signature" />
							</div>
						</div>
					</template>

					<template v-else-if="role === 3">
						<div class="card text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.izumiWorks)">
							<div class="icon-holder">
								<i class="fas fa-id-badge" />
							</div>
						</div>

						<div class="card text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.smartApproval)">
							<div class="icon-holder">
								<i class="fas fa-file-signature" />
							</div>
						</div>

						<div class="card text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.workshift)">
							<div class="icon-holder">
								<i class="fas fa-truck" />
							</div>
						</div>

						<div class="empty-card text-center align-items-center justify-content-center" />
					</template>

					<template v-else>
						<div v-if="![12].includes(role)" class="card text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.izumiWorks)">
							<div class="icon-holder">
								<i class="fas fa-id-badge" />
							</div>
						</div>

						<div class="card text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.plSystem)">
							<div class="icon-holder">
								<i class="fas fa-file-invoice-dollar" />
							</div>
						</div>

						<div class="card text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.smartApproval)">
							<div class="icon-holder">
								<i class="fas fa-file-signature" />
							</div>
						</div>

						<template v-if="[12].includes(role)">
							<div class="card text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.workshift)">
								<div class="icon-holder">
									<i class="fas fa-truck" />
								</div>
							</div>

							<div class="empty-card text-center align-items-center justify-content-center" />
						</template>
					</template>
				</template>
			</div>

			<!-- ROW 2 TITLE -->
			<div v-if="deviceMode === 'desktop'" class="sub-content d-flex flex-row justify-content-center align-items-center">
				<template v-if="![1, 2, 3, 12].includes(role)">
					<div class="card align-items-center mt-3">
						<span>AIシフト</span>
					</div>
				</template>

				<template v-if="[1, 2].includes(role)">
					<div class="card align-items-center mt-3">
						<span>AIシフト</span>
					</div>

					<div class="card align-items-center mt-3" />

					<div class="card align-items-center mt-3" />
					<div class="card align-items-center mt-3" />
				</template>

				<template v-else>
					<template v-if="role === 4">
						<div class="card align-items-center mt-3">
							<span>PLシステム</span>
						</div>

						<div class="card align-items-center mt-3">
							<span>イズミワークス</span>
						</div>

						<div class="card align-items-center mt-3">
							<span>Smart稟議</span>
						</div>
					</template>

					<template v-else-if="role === 3">
						<div class="card align-items-center mt-3">
							<span>イズミワークス</span>
						</div>

						<div class="card align-items-center mt-3">
							<span>Smart稟議</span>
						</div>

						<div class="card align-items-center mt-3">
							<span>AIシフト</span>
						</div>

						<div class="card align-items-center mt-3" />
					</template>

					<template v-else>
						<div v-if="![12].includes(role)" class="card align-items-center mt-3">
							<span>イズミワークス</span>
						</div>

						<div class="card align-items-center mt-3">
							<span>PLシステム</span>
						</div>

						<div class="card align-items-center mt-3">
							<span>Smart稟議</span>
						</div>

						<template v-if="[12].includes(role)">
							<div class="card align-items-center mt-3">
								<span>AIシフト</span>
							</div>

							<div class="card align-items-center mt-3" />
						</template>
					</template>
				</template>
			</div>

			<!-- ROW 3 CARD -->
			<div v-if="deviceMode === 'desktop'" class="main-content d-flex flex-row justify-content-center align-items-center">
				<div v-if="![1, 2, 3, 12].includes(role)" class="card text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.eLearning)">
					<div class="icon-holder">
						<i class="fas fa-graduation-cap" />
					</div>
				</div>

				<template v-if="[9, 10].includes(role)">
					<div class="card text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.eLearningAdmin)">
						<div class="icon-holder">
							<i class="fas fa-graduation-cap" style="color: #BF092F !important;" />
						</div>
					</div>
				</template>

				<template v-if="[3, 4, 5, 6, 7, 8, 9, 10, 11, 12].includes(role)">
					<div class="card text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.aiQuotation)">
						<div class="icon-holder">
							<img :src="AI" class="w-100" alt="">
							<!-- <i class="fas fa-caravan" /> -->
						</div>
					</div>
				</template>

				<template v-if="![9, 10].includes(role)">
					<div class="empty-card text-center align-items-center justify-content-center" />
				</template>

				<div class="empty-card text-center align-items-center justify-content-center" />
			</div>

			<!-- ROW 3 TITLE -->
			<div v-if="deviceMode === 'desktop'" class="sub-content d-flex flex-row justify-content-center align-items-center">
				<div v-if="![1, 2, 3, 12].includes(role)" class="card align-items-center mt-3">
					<span>E-ラーニング</span>
				</div>

				<template v-if="[9, 10].includes(role)">
					<div class="card align-items-center mt-3">
						<span style="color: #BF092F !important;">E-ラーニング（管理）</span>
					</div>
				</template>

				<template v-if="[3, 4, 5, 6, 7, 8, 9, 10, 11, 12].includes(role)">
					<div class="card align-items-center mt-3">
						<span>AI見積り</span>
					</div>
				</template>

				<template v-if="![9, 10].includes(role)">
					<div class="card align-items-center mt-3" />
				</template>

				<!-- <div class="card align-items-center mt-3" /> -->
				<div class="card align-items-center mt-3" />
			</div>

			<!-- MOBILE -->
			<b-row v-if="deviceMode === 'mobile'" class="general-card">
				<b-col cols="6" class="mobile-holder">
					<div class="card-mobile text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.timeSheet)">
						<div class="icon-holder">
							<i class="far fa-calendar-alt" />
						</div>
					</div>

					<span class="mobile-holder-span">タイムシート</span>
				</b-col>

				<b-col v-if="![3].includes(role)" cols="6" class="mobile-holder">
					<div class="card-mobile text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.payslip)">
						<div class="icon-holder">
							<i class="fad fa-credit-card" />
						</div>
					</div>

					<span class="mobile-holder-span">給与明細クラウド</span>
				</b-col>

				<b-col cols="6" class="mobile-holder">
					<div class="card-mobile text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.maintenanceSystem)">
						<div class="icon-holder">
							<i class="fas fa-wrench" />
						</div>
					</div>

					<span class="mobile-holder-span">整備システム</span>
				</b-col>

				<b-col v-if="![1, 2, 3, 12].includes(role)" cols="6" class="mobile-holder">
					<div class="card-mobile text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.izumiWebApp)">
						<div class="icon-holder">
							<i class="fad fa-browser" />
						</div>
					</div>

					<span class="mobile-holder-span">Web App</span>
				</b-col>

				<b-col v-if="![1, 2].includes(role)" cols="6" class="mobile-holder">
					<div class="card-mobile text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.plSystem)">
						<div class="icon-holder">
							<i class="fas fa-file-invoice-dollar" />
						</div>
					</div>

					<span class="mobile-holder-span">PLシステム</span>
				</b-col>

				<b-col v-if="![1, 2, 12].includes(role)" cols="6" class="mobile-holder">
					<div class="card-mobile text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.izumiWorks)">
						<div class="icon-holder">
							<i class="fas fa-id-badge" />
						</div>
					</div>

					<span class="mobile-holder-span">イズミワークス</span>
				</b-col>

				<b-col v-if="![1, 2].includes(role)" cols="6" class="mobile-holder">
					<div class="card-mobile text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.smartApproval)">
						<div class="icon-holder">
							<i class="fas fa-file-signature" />
						</div>
					</div>

					<span class="mobile-holder-span">Smart稟議</span>
				</b-col>

				<b-col v-if="[9, 10].includes(role)" cols="6" class="mobile-holder">
					<div class="card-mobile text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.eLearningAdmin)">
						<div class="icon-holder">
							<i class="fas fa-graduation-cap" style="color: #BF092F !important;" />
						</div>
					</div>

					<span class="mobile-holder-span" style="color: #BF092F !important;">E-ラーニング（管理）</span>
				</b-col>

				<b-col cols="6" class="mobile-holder">
					<div class="card-mobile text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.workshift)">
						<div class="icon-holder">
							<i class="fas fa-truck" />
						</div>
					</div>

					<span class="mobile-holder-span">AIシフト</span>
				</b-col>

				<b-col cols="6" class="mobile-holder">
					<div class="card-mobile text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.eLearning)">
						<div class="icon-holder">
							<i class="fas fa-graduation-cap" />
						</div>
					</div>

					<span class="mobile-holder-span">E-ラーニング</span>
				</b-col>

				<b-col v-if="[9, 10].includes(role)" cols="6" class="mobile-holder">
					<div class="card-mobile text-center align-items-center justify-content-center" @click="handleNavigate(systemLinks.eLearningAdmin)">
						<div class="icon-holder">
							<i class="fas fa-graduation-cap" style="color: #BF092F !important;" />
						</div>
					</div>

					<span class="mobile-holder-span" style="color: #BF092F !important;">E-ラーニング（管理）</span>
				</b-col>
			</b-row>
		</b-overlay>
	</div>
</template>

<script>
import AI from '@/assets/images/img-ai.png';

export default {
    name: 'TransportationIndex',
    data() {
        return {
            overlay: {
                show: false,
                variant: 'light',
                opacity: 1,
                blur: '1rem',
                rounded: 'sm',
            },

            systemLinks: {
                timeSheet: '',
                payslip: '',
                maintenanceSystem: '',
                izumiWebApp: '',
                plSystem: '',
                eLearning: '',
                izumiWorks: '',
                smartApproval: '',
                eLearningAdmin: '',
                workshift: '',
                aiQuotation: '',
            },
            AI: AI,
        };
    },
    computed: {
        enviroment() {
            return process.env.MIX_APP_ENV;
        },
        deviceMode() {
            return this.$store.getters.deviceMode;
        },
        role() {
            return this.$store.getters.profile['role'];
        },
    },
    created() {
        if (this.enviroment === 'local' || this.enviroment === 'dev') {
            this.systemLinks.timeSheet = 'https://izumi-v2.vw-dev.com/login';

            if (this.role === 10 || this.role === 6) {
                this.systemLinks.payslip = 'https://izumi-dx.vw-dev.com/admin';
            } else {
                this.systemLinks.payslip = 'https://izumi-dx.vw-dev.com/login';
            }

            this.systemLinks.maintenanceSystem = 'https://izumi-maintenance.vw-dev.com/login';
            this.systemLinks.izumiWebApp = 'https://izumi-web-app.vw-dev.com/login';
            this.systemLinks.plSystem = 'https://izumi-pl.vw-dev.com/login';
            this.systemLinks.eLearning = 'https://izumi-e-learning.vw-dev.com/login';
            this.systemLinks.izumiWorks = 'https://izumi-works.vw-dev.com/';
            this.systemLinks.smartApproval = 'https://izumi-smart-approval.vw-dev.com/login';

            this.systemLinks.eLearningAdmin = 'https://izumi-e-learning.vw-dev.com/adminlogin';
            this.systemLinks.workshift = 'https://izumi-ai-shift.vw-dev.com/login';
            this.systemLinks.aiQuotation = 'https://izumi-ai-quotation.vw-dev.com';
        } else if (this.enviroment === 'staging') {
            this.systemLinks.timeSheet = 'https://izumi-v2-stage.izumilogi.com/login';

            if (this.role === 10 || this.role === 6) {
                this.systemLinks.payslip = 'https://payslip-stage.izumilogi.com/admin';
            } else {
                this.systemLinks.payslip = 'https://payslip-stage.izumilogi.com/login';
            }

            this.systemLinks.maintenanceSystem = 'https://maint-stage.izumilogi.com/login';
            this.systemLinks.izumiWebApp = 'https://izumi-web-app-stage.izumilogi.com/login';
            this.systemLinks.plSystem = 'https://pl-stage.izumilogi.com/login';
            this.systemLinks.eLearning = 'https://e-learning-stage.izumilogi.com/login';
            this.systemLinks.izumiWorks = 'https://iw-stage.izumilogi.com/';
            this.systemLinks.smartApproval = 'https://sa-stage.izumilogi.com/login';

            this.systemLinks.eLearningAdmin = 'https://e-learning-stage.izumilogi.com/adminlogin';
            this.systemLinks.workshift = 'https://ws-stage.izumilogi.com/login';
            this.systemLinks.aiQuotation = 'https://aq-stage.izumilogi.com';
        } else if (this.enviroment === 'production') {
            this.systemLinks.timeSheet = 'https://izumi-v2.izumilogi.com/login';

            if (this.role === 10 || this.role === 6) {
                this.systemLinks.payslip = 'https://payslip.izumilogi.com/admin';
            } else {
                this.systemLinks.payslip = 'https://payslip.izumilogi.com/login';
            }

            this.systemLinks.maintenanceSystem = 'https://maint.izumilogi.com/login';
            this.systemLinks.izumiWebApp = 'https://izumi-web-app.izumilogi.com/login';
            this.systemLinks.plSystem = 'https://pl.izumilogi.com/login';
            this.systemLinks.eLearning = 'https://ie.izumilogi.com/login';
            this.systemLinks.izumiWorks = 'https://iw.izumilogi.com/';
            this.systemLinks.smartApproval = 'https://sa.izumilogi.com/login';

            this.systemLinks.eLearningAdmin = 'https://ie.izumilogi.com/adminlogin';
            this.systemLinks.workshift = 'https://ws.izumilogi.com/login';
        }

        if (this.handleCheckDevice()) {
            this.$store.dispatch('app/setDeviceMode', 'mobile');
        } else {
            this.$store.dispatch('app/setDeviceMode', 'desktop');
        }
    },
    methods: {
        handleNavigate(url) {
            window.open(url, '_blank');
        },
        handleCheckDevice() {
            let check = false;
            (function(a) {
                if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))) {
                    check = true;
                }
            })(navigator.userAgent || navigator.vendor || window.opera);
            return check;
        },
    },
};
</script>

<style lang="scss" scoped>
.izumi-works-img {
  width: 130px;
  height: 130px;
}

@media only screen and (max-width: 768px) {
  .izumi-works-img {
    width: 62px;
    height: 65px;
  }
}

.main-content {
  padding: 40px 15px 0 15px;

  .empty-card {
    margin: 0px 40px 0 40px;
    width: 120px;
    padding: 15px;
    height: 120px;
    border-radius: 12px;
  }

  .card {
    margin: 0px 40px 0 40px;
    width: 120px;
    padding: 15px;
    height: 120px;
    border-radius: 12px;
    border: 1px solid #DDDDDD;
    box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;

    &:hover {
      cursor: pointer;
      transform: scale(1.05);
      transition: all 0.3s ease-in-out;
      box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
    }

    span:hover {
      cursor: pointer;
    }

    .icon-holder {
      &:hover {
        cursor: pointer;
      }

      i {
        color: #0F0448;
        font-size: 60px;

        &:hover {
          cursor: pointer;
        }
      }
    }
  }
}

.mobile-holder {
  text-align: center;
  padding: 120px 40px 0 40px;

  span {
    margin-top: 10px;
    display: inline-flex;
    font-size: 12px;
  }
}

.general-card {
  width: 100%;
  height: 100%;
  padding-bottom: 200px;
  overflow-y: auto;
  margin: 0 auto;
}

.card-mobile {
  height: 110px;
  padding-top: 20px;
  border-radius: 16px;
  border: 1px solid #DDDDDD;
  box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;

  &:hover {
    cursor: pointer;
    transform: scale(1.05);
    transition: all 0.3s ease-in-out;
    box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
  }

  span:hover {
    cursor: pointer;
  }

  .icon-holder {
    &:hover {
      cursor: pointer;
    }

    i {
      color: #0F0448;
      font-size: 60px;

      &:hover {
        cursor: pointer;
      }
    }
  }
}

.sub-content {
  padding: 0px 30px 0px 30px;

  .card {
    margin: 0px 40px 0 40px;
    width: 120px;
    border: none;
    white-space: nowrap;

    span {
      font-size: 14px;
    }
  }
}
</style>
