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


</template>



<style scoped>
.button-group {
    display: flex;
    align-items: center;
}

button{
    margin: 1px;
}


@media screen and (max-width: 960px) {
    ::v-deep(.p-datatable-tbody > tr > td .p-column-title) {
        display: inline-block;
        font-weight: bold;
        margin-right: 0.5rem;
    }

    .p-datatable.p-datatable-sm .p-datatable-thead > tr > th {
        display: none !important;
    }

    .p-datatable.p-datatable-sm .p-datatable-tbody > tr > td {
        text-align: left;
        display: block;
        width: 100%;
        float: left;
        clear: left;
        border: 0 none;
    }

    .p-datatable.p-datatable-sm .p-datatable-tbody > tr > td:last-child {
        border-bottom: 1px solid var(--surface-d);
    }

    .p-datatable.p-datatable-sm .p-datatable-tbody > tr > td .p-column-title {
        padding: 0.4rem;
        min-width: 30%;
        display: inline-block;
        margin: -0.4em 1em -0.4em -0.4rem;
        font-weight: bold;
    }

    .p-datatable.p-datatable-sm .p-datatable-tbody > tr {
        border-bottom: 1px solid var(--surface-d);
    }

    .button-group {
        justify-content: flex-start;
    }
    .col-9{
        width: 100%;
    }

    .col-6{
        width: 100%;
    }

    .col-6:first-child {
        order: 2; /* Move the first .col-6 to the second position */
    }

    .col-6:last-child {
        order: 1; /* Move the last .col-6 to the first position */
    }


    .col-9:first-child {
        order: 2; /* Move the first .col-6 to the second position */
    }

    .col-3:last-child {
        order: 1; /* Move the last .col-6 to the first position */
    }

    .overflow-wrap-anywhere{
        display: flex;
        justify-content: space-between;
    }
}

</style>
