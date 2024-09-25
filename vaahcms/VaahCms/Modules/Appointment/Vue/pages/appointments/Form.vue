<script setup>
import {onMounted, ref, watch} from "vue";
import { useAppointmentStore } from '../../stores/store-appointments'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useAppointmentStore();
const route = useRoute();

onMounted(async () => {
    /**
     * Fetch the record from the database
     */
    if((!store.item || Object.keys(store.item).length < 1)
            && route.params && route.params.id)
    {
        await store.getItem(route.params.id);
    }

    await store.getFormMenu();
});

//--------form_menu
const form_menu = ref();
const toggleFormMenu = (event) => {
    form_menu.value.toggle(event);
};
//--------/form_menu

</script>
<template>

    <div class="col-6" >

        <Panel class="is-small">

            <template class="p-1" #header>


                <div class="flex flex-row">
                    <div class="p-panel-title">
                        <span v-if="store.item && store.item.id">
                            Update
                        </span>
                        <span v-else>
                            Create
                        </span>
                    </div>

                </div>


            </template>

            <template #icons>


                <div class="p-inputgroup">

                    <Button class="p-button-sm"
                            v-tooltip.left="'View'"
                            v-if="store.item && store.item.id"
                            data-testid="appointments-view_item"
                            @click="store.toView(store.item)"
                            icon="pi pi-eye"/>

                    <Button label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="appointments-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="appointments-create-and-new"
                            icon="pi pi-save"/>


                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        data-testid="appointments-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="appointments-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item" class="mt-2">

                <Message severity="error"
                         class="p-container-message mb-3"
                         :closable="false"
                         icon="pi pi-trash"
                         v-if="store.item.deleted_at">

                    <div class="flex align-items-center justify-content-between">

                        <div class="">
                            Deleted {{store.item.deleted_at}}
                        </div>

                        <div class="ml-3">
                            <Button label="Restore"
                                    class="p-button-sm"
                                    data-testid="articles-item-restore"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>

                    </div>

                </Message>

                <VhField label="Doctor">
                    <div class="p-inputgroup">
                        <Dropdown  v-model="store.item.doctor_id"
                                   :options="store.assets.doctor"
                                   option-label="name"
                                   option-value="id"
                                   name="doctor_name"
                                   data-testid="doctor_name"
                                   required/>
                        <div class="required-field hidden"></div>
                    </div>
                </VhField>

                <VhField label="Patient">
                    <div class="p-inputgroup">
                        <Dropdown  v-model="store.item.patient_id"
                                   :options="store.assets.patient"
                                   option-label="name"
                                   option-value="id"
                                   name="patient_name"
                                   data-testid="patient_name"
                                   required/>
                        <div class="required-field hidden"></div>
                    </div>
                </VhField>

                <VhField label="Appointment Date">
                    <div class="p-inputgroup">
                        <Calendar v-model="store.item.appointment_date"
                                  placeholder="Select Date" name="appointment_date">
                        </Calendar>
                    </div>
                </VhField>

                <VhField label="Appointment Time">
                    <div class="p-inputgroup">
                        <Calendar v-model="store.item.appointment_time" timeOnly hourFormat="24" showIcon
                                  placeholder="Select time" name="appointment_time" >
                        </Calendar>
                    </div>
                </VhField>

                <VhField label="Medical Concern">
                    <div class="p-inputgroup">
                        <InputText class="w-full"
                                   placeholder="Share a little about your medical concern.."
                                   name="reason_for_visit"
                                   data-testid="reason_for_visit"
                                   v-model="store.item.reason_for_visit" required/>
                        <div class="required-field hidden"></div>
                    </div>
                </VhField>



                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 class="p-inputswitch-sm"
                                 name="appointments-active"
                                 data-testid="appointments-active"
                                 v-model="store.item.is_active"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>
