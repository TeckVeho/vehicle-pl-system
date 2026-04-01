import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import PlayRecorder from '@/pages/PlayRecorder/index';

describe('TEST COMPONENT PLAY RECORDER', () => {
    test('Test if call api get video url when navigate to the driver recorder player screen', async() => {
        const initData = jest.fn();

        const localVue = createLocalVue();
        const wrapper = mount(PlayRecorder, {
            localVue,
            store,
            router,
            methods: {
                initData,
            },
            data() {
                return {
                    listRecorder: [
                        {
                            type: 'front',
                            url: 'https://www.youtube.com/watch?v=UlV_5sRyGxY',
                        },
                        {
                            type: 'inside',
                            url: 'https://www.youtube.com/watch?v=UlV_5sRyGxY',
                        },
                        {
                            type: 'behind',
                            url: 'https://www.youtube.com/watch?v=UlV_5sRyGxY',
                        },
                    ],
                };
            },
        });

        await wrapper.vm.initData();

        expect(initData).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Test component render page', () => {
        const localVue = createLocalVue();
        const wrapper = mount(PlayRecorder, {
            localVue,
            store,
            router,
        });

        const PAGE = wrapper.find('.play-recorder');
        expect(PAGE.exists()).toBe(true);

        wrapper.destroy();
    });

    test('Test if call funtion play video when click on play button', async() => {
        const onClickButtonStatus = jest.fn();

        const localVue = createLocalVue();
        const wrapper = mount(PlayRecorder, {
            localVue,
            store,
            router,
            methods: {
                onClickButtonStatus,
            },
            data() {
                return {
                    listRecorder: [
                        {
                            type: 'front',
                            url: 'https://www.youtube.com/watch?v=UlV_5sRyGxY',
                        },
                        {
                            type: 'inside',
                            url: 'https://www.youtube.com/watch?v=UlV_5sRyGxY',
                        },
                        {
                            type: 'behind',
                            url: 'https://www.youtube.com/watch?v=UlV_5sRyGxY',
                        },
                    ],
                };
            },
        });

        const BTN_PLAY = wrapper.find('.btn-play');
        await BTN_PLAY.trigger('click');

        expect(onClickButtonStatus).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Test if call funtion pause video when click on pause button', async() => {
        const onClickButtonStatus = jest.fn();

        const localVue = createLocalVue();
        const wrapper = mount(PlayRecorder, {
            localVue,
            store,
            router,
            methods: {
                onClickButtonStatus,
            },
            data() {
                return {
                    status: true,
                    listRecorder: [
                        {
                            type: 'front',
                            url: 'https://www.youtube.com/watch?v=UlV_5sRyGxY',
                        },
                        {
                            type: 'inside',
                            url: 'https://www.youtube.com/watch?v=UlV_5sRyGxY',
                        },
                        {
                            type: 'behind',
                            url: 'https://www.youtube.com/watch?v=UlV_5sRyGxY',
                        },
                    ],
                };
            },
        });

        const BTN_PAUSE = wrapper.find('.btn-play');
        await BTN_PAUSE.trigger('click');

        expect(onClickButtonStatus).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Test if call funtion rewind video when click on button rewind', async() => {
        const onClickBack = jest.fn();

        const localVue = createLocalVue();
        const wrapper = mount(PlayRecorder, {
            localVue,
            store,
            router,
            methods: {
                onClickBack,
            },
            data() {
                return {
                    status: true,
                    listRecorder: [
                        {
                            type: 'front',
                            url: 'https://www.youtube.com/watch?v=UlV_5sRyGxY',
                        },
                        {
                            type: 'inside',
                            url: 'https://www.youtube.com/watch?v=UlV_5sRyGxY',
                        },
                        {
                            type: 'behind',
                            url: 'https://www.youtube.com/watch?v=UlV_5sRyGxY',
                        },
                    ],
                };
            },
        });

        const BTN_BACK = wrapper.find('.btn-back i');
        await BTN_BACK.trigger('click');

        expect(onClickBack).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Check the video rewind call function when pressing the forward button', async() => {
        const onClickNext = jest.fn();

        const localVue = createLocalVue();
        const wrapper = mount(PlayRecorder, {
            localVue,
            store,
            router,
            methods: {
                onClickNext,
            },
            data() {
                return {
                    status: true,
                    listRecorder: [
                        {
                            type: 'front',
                            url: 'https://www.youtube.com/watch?v=UlV_5sRyGxY',
                        },
                        {
                            type: 'inside',
                            url: 'https://www.youtube.com/watch?v=UlV_5sRyGxY',
                        },
                        {
                            type: 'behind',
                            url: 'https://www.youtube.com/watch?v=UlV_5sRyGxY',
                        },
                    ],
                };
            },
        });

        const BTN_NEXT = wrapper.find('.btn-next i');
        await BTN_NEXT.trigger('click');

        expect(onClickNext).toHaveBeenCalled();

        wrapper.destroy();
    });
});
