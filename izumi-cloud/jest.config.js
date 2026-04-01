require('dotenv').config();

// CI / máy không có .env: Laravel Echo + Pusher báo lỗi nếu key rỗng khi import bootstrap.
if (!process.env.MIX_PUSHER_APP_KEY) {
    process.env.MIX_PUSHER_APP_KEY = 'jest-placeholder-key';
}
if (!process.env.MIX_PUSHER_APP_CLUSTER) {
    process.env.MIX_PUSHER_APP_CLUSTER = 'mt1';
}

const TEST_URL = process.env.MIX_LARAVEL_TEST_URL;

module.exports = {
    testRegex: 'resources/js/tests/.*.spec.js$',
    moduleNameMapper: {
        '^@/(.*)$': '<rootDir>/resources/js/$1',
        '^vue2-datepicker/index\\.css$': 'jest-transform-stub',
        '^vue2-dropzone/dist/vue2Dropzone\\.min\\.css$': 'jest-transform-stub',
        '^vue-pdf-embed/dist/vue2-pdf-embed\\.js$': '<rootDir>/resources/js/tests/mocks/vuePdfEmbed.stub.js',
        '^vue-month-picker$': '<rootDir>/resources/js/tests/mocks/vueMonthPicker.stub.js',
    },
    moduleFileExtensions: ['js', 'json', 'vue'],
    transform: {
        '^.+\\.js$': '<rootDir>/node_modules/babel-jest',
        '.*\\.(vue)$': '<rootDir>/node_modules/vue-jest',
        "^.+\\.(css|styl|less|sass|scss|png|jpg|ttf|woff|woff2)$": "jest-transform-stub"
    },
    snapshotSerializers: ['jest-serializer-vue'],
    collectCoverageFrom: [
        'resources/js/**/*.{js,jsx,ts,tsx,vue}',
    ],
    collectCoverage: false,
    coverageReporters: ['html', 'lcov', 'text-summary'],
    coverageDirectory: './coverage',
    testEnvironment: "jsdom",
    setupFiles: ['<rootDir>/resources/js/tests/setup.js'],
    testURL: TEST_URL,
    setupFilesAfterEnv: ['<rootDir>/resources/js/tests/setup.js'],
    testTimeout: 30000,
    verbose: true,
}
