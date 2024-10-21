<script setup>
import {onMounted, reactive, ref} from "vue";
import {useRoute} from 'vue-router';

import {useAppointmentStore} from '../../stores/store-appointments'
import {useRootStore} from '../../stores/root'

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";
import Filters from './components/Filters.vue'

const store = useAppointmentStore();
const root = useRootStore();
const route = useRoute();

import {useConfirm} from "primevue/useconfirm";

const confirm = useConfirm();


onMounted(async () => {
    document.title = 'Appointments - Appointment';
    store.item = null;
    /**
     * call onLoad action when List view loads
     */
    await store.onLoad(route);

    /**
     * watch routes to update view, column width
     * and get new item when routes get changed
     */
    await store.watchRoutes(route);

    /**
     * watch states like `query.filter` to
     * call specific actions if a state gets
     * changed
     */
    await store.watchStates();

    /**
     * fetch assets required for the crud
     * operation
     */
    await store.getAssets();

    /**
     * fetch list of records
     */
    await store.getList();

    await store.getListCreateMenu();

});

//--------form_menu
const create_menu = ref();
const toggleCreateMenu = (event) => {
    create_menu.value.toggle(event);
};
//--------/form_menu

</script>
<template>

    <div class="grid" v-if="store.assets">

        <div :class="'col-'+(store.show_filters?9:store.list_view_width)">
            <Panel class="is-small">

                <template class="p-1" #header>

                    <div class="flex flex-row">
                        <div>
                            <b class="mr-1">Appointments</b>
                            <Badge v-if="store.list && store.list.total > 0"
                                   :value="store.list.total">
                            </Badge>
                        </div>

                    </div>

                </template>

                <template #icons>

                    <div class="p-inputgroup">

                        <Button data-testid="appointments-list-create"
                                class="p-button-sm"
                                @click="store.toForm()">
                            <i class="pi pi-plus mr-1"></i>
                            Create
                        </Button>

                        <Button @click="store.visible = true">
                            <i class="pi pi-upload mr-1"></i>
                            Import
                        </Button>

                        <Button @click="store.exportAppointments()">
                            <i class="pi pi-download mr-1"></i>
                            Export
                        </Button>

                        <Button data-testid="appointments-list-reload"
                                class="p-button-sm"
                                @click="store.getList()">
                            <i class="pi pi-refresh mr-1"></i>
                        </Button>


                        <!--form_menu-->

                        <Button v-if="root.assets && root.assets.module
                                                && root.assets.module.is_dev"
                                type="button"
                                @click="toggleCreateMenu"
                                class="p-button-sm"
                                data-testid="appointments-create-menu"
                                icon="pi pi-angle-down"
                                aria-haspopup="true"/>

                        <Menu ref="create_menu"
                              :model="store.list_create_menu"
                              :popup="true"/>

                        <!--/form_menu-->

                    </div>

                </template>

                <Actions/>

                <Table/>

            </Panel>
        </div>

        <Filters/>

        <RouterView/>

    </div>

    <!--Dialog for import-->

    <div class="card flex justify-content-center">
        <Dialog v-model:visible="store.visible" modal header="Import Appointments" :style="{ width: '80vw' }"
                :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">

            <!-- Tab View -->
            <div class="card">
                <TabView :activeIndex="store.activeTabIndex" @tab-change="store.onTabChange">

                    <!-- Bulk Import Tab -->
                    <TabPanel :header="store.tabs[0].header" :disabled="store.tabs[0].disabled">
                        <div class="flex align-items-center gap-3 mb-3">
                            <FileUpload
                                class="flex-auto w-full"
                                mode="basic"
                                name="file"
                                accept=".csv, .xlsx, .xls"
                                :maxFileSize="1000000"
                                :customUpload="true"
                                @select="store.onFileSelect"
                                chooseLabel="Browse"
                            />
                        </div>
<!--                        <div class="flex justify-content-center  gap-2">-->
<!--                            <Button type="button" severity="success" label="Save" @click="store.uploadFile"  class="w-full"></Button>-->
<!--                        </div>-->

                        <div class="text-align-center flex justify-content-center">
                            <br>
                            <label class="font-semibold mt-3" @click="store.exportAppointments"
                                   style="cursor: pointer; color: dodgerblue"
                            >Download a sample CSV with format</label>

                        </div>

                        <div class="flex justify-content-end gap-2">
                            <Button type="button" severity="primary" label="Next" @click="store.uploadFile"
                                    class=""></Button>
                        </div>
                    </TabPanel>

                    <!-- Mapping Tab -->
                    <TabPanel :header="store.tabs[1].header" :disabled="store.tabs[1].disabled">
                        <div class="card p-fluid">
                            <div class="grid">

                                <div class="col-12 md:col-2 mb-2 flex align-items-center justify-content-start">
                                    <label for="dropdown1" class="font-bold">Patient Name</label>
                                </div>
                                <div class="col-12 md:col-4 mb-2">
                                    <Dropdown id="dropdown1"
                                              v-model="store.selectedPatientName"
                                              :options="store.csv_headers"
                                       
                                    placeholder="Select Header" class="w-full" />
                                </div>

                                <div class="col-12 md:col-2 mb-2 flex align-items-center justify-content-start">
                                    <label for="dropdown2" class="font-bold">Patient Email</label>
                                </div>
                                <div class="col-12 md:col-4 mb-2">
                                    <Dropdown id="dropdown2" v-model="store.selectedPatientEmail" :options="store.csv_headers" placeholder="Select Header" class="w-full" />
                                </div>

                                <div class="col-12 md:col-2 mb-2 flex align-items-center justify-content-start">
                                    <label for="dropdown3" class="font-bold">Doctor Name</label>
                                </div>
                                <div class="col-12 md:col-4 mb-2">
                                    <Dropdown id="dropdown3" v-model="store.selectedDoctorName" :options="store.csv_headers" placeholder="Select Header" class="w-full" />
                                </div>

                                <div class="col-12 md:col-2 mb-2 flex align-items-center justify-content-start">
                                    <label for="dropdown4" class="font-bold">Doctor Email</label>
                                </div>
                                <div class="col-12 md:col-4 mb-2">
                                    <Dropdown id="dropdown4" v-model="store.selectedDoctorEmail" :options="store.csv_headers" placeholder="Select Header" class="w-full" />
                                </div>

                                <div class="col-12 md:col-2 mb-2 flex align-items-center justify-content-start">
                                    <label for="dropdown5" class="font-bold">Appointment Date</label>
                                </div>
                                <div class="col-12 md:col-4 mb-2">
                                    <Dropdown id="dropdown5" v-model="store.selectedAppointmentDate" :options="store.csv_headers" placeholder="Select Header" class="w-full" />
                                </div>

<!--                                <div class="col-12 md:col-4">-->
<!--                                    <Button label="Submit" @click="submitData" />-->
<!--                                </div>-->
                            </div>
                        </div>

                        <div class="flex justify-content-between gap-2">
                            <Button type="button" severity="secondary" label="Back" @click="store.moveToUpload"
                                    class=""></Button>
                            <Button type="button" severity="primary" label="Next" @click="store.moveToSuccess"
                                    class=""></Button>
                        </div>
                    </TabPanel>

                    <!-- Success Tab -->
                    <TabPanel :header="store.tabs[2].header" :disabled="store.tabs[2].disabled">
                        <h3>Appointments Imported Successfully.</h3>
                        <br>
                        <div class="flex justify-content-between gap-2">
                            <Button type="button" severity="secondary" label="Back" @click=""
                                    class=""></Button>
                            <Button type="button" severity="danger" label="Close" @click="store.closeMoveToImport"
                                    class=""></Button>
                        </div>
                    </TabPanel>
                </TabView>
            </div>
            <!-- Tab View ends here -->

        </Dialog>
    </div>

</template>

<style>
.p-tabview-nav{
    display: flex;
    justify-content: space-evenly;
}
</style>
