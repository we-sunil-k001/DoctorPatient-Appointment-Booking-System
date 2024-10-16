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

import { useConfirm } from "primevue/useconfirm";
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

//Dialog ----------------
const visible = ref(false);

</script>
<template>

    <div class="grid" v-if="store.assets">

        <div :class="'col-'+(store.show_filters?9:store.list_view_width)">
            <Panel class="is-small">

                <template class="p-1" #header>

                    <div class="flex flex-row">
                        <div >
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

                    <Button @click="visible = true" >
                        <i class="pi pi-upload mr-1"></i>
                        Import
                    </Button>

                    <Button @click="store.exportAppointments()" >
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
                          :popup="true" />

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
            <Button label="Show" @click="visible = true" />
            <Dialog v-model:visible="visible" header="Upload File" :style="{ width: '25rem' }">
                <div class="flex align-items-center gap-3 mb-3">
                    <FileUpload class="flex-auto w-full"
                        mode="basic"
                        name="demo[]"
                        url="/api/upload"
                        accept=".csv, .xlsx, .xls"
                        :maxFileSize="1000000"
                        @upload="onUpload"
                        :auto="false"
                        chooseLabel="Browse"
                    />
                </div>
                <div class="flex justify-content-center  gap-2">
                    <Button type="button" severity="success" label="Save" @click="visible = false" class="w-full"></Button>
                </div>

                <div class="text-align-center">
                  <br>
                    <label class="font-semibold">Download a sample CSV with format</label>
                </div>

            </Dialog>
        </div>

</template>
