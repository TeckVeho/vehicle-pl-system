import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import DataImportIndex from '@/pages/DataImport';

describe('TEST COMPONENT SIDEBAR', () => {
    test('Test component render Sidebar', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DataImportIndex, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const DataImport = wrapper.find('.data-import');
        expect(DataImport.exists()).toBe(true);

        const DataImportHeader = wrapper.find('.data-import__title-header');
        expect(DataImportHeader.exists()).toBe(true);
        expect(DataImportHeader.text()).toEqual('PAGE_TITLE.DATA_IMPORT');

        const ImportZone = wrapper.find('.data-import-cvs-zone');
        expect(ImportZone.exists()).toBe(true);

        const DataImportContentHeader = ImportZone.find('.data-import-cvs-zone__header');
        expect(DataImportContentHeader.text()).toEqual('DATA_IMPORT.CVS');

        const DataImportContent = ImportZone.find('.data-import-cvs-zone__main-content');
        expect(DataImportContent.exists()).toBe(true);

        const LabelList = DataImportContent.findAll('.label');
        expect(LabelList.length).toEqual(3);
        expect(LabelList.at(0).text()).toEqual('DATA_IMPORT.SELECT_DATA:');
        expect(LabelList.at(1).text()).toEqual('年月選択：');
        expect(LabelList.at(2).text()).toEqual('DATA_IMPORT.IMPORT_DATA_CSV:');

        const SelectDataButton = DataImportContent.find('.btn-select-data');
        expect(SelectDataButton.exists()).toBe(true);
        expect(SelectDataButton.text()).toEqual('DATA_IMPORT.SELECT_DATA');

        const SelectFileButton = DataImportContent.find('.btn-select-file');
        expect(SelectFileButton.exists()).toBe(true);
        expect(SelectFileButton.text()).not.toBe(null);

        const ButtonImport = wrapper.find('.v-button-import-data');
        expect(ButtonImport.exists()).toBe(true);
        expect(ButtonImport.text()).toEqual('DATA_IMPORT.IMPORT');

        const doImportData = jest.spyOn(wrapper.vm, 'doImportData');
        await ButtonImport.trigger('click');
        expect(doImportData).toHaveBeenCalled();

        wrapper.destroy();
    });
});
