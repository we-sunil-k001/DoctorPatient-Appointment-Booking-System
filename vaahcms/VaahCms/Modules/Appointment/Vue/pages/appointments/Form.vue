<script setup>
import {onMounted, ref, watch} from "vue";
import { useAppointmentStore } from '../../stores/store-appointments'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';
import axios from 'axios'; // Make sure to import axios


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

// Reactive variable to hold doctor details
const doctor_details = ref(null);

// Function to fetch doctor details on dropdown selection
const fetchDoctorDetails = async (event) => {
    const selectedDoctorId = event.value; // Get the selected doctor ID
    if (selectedDoctorId) {
        try {
            // Fetch doctor details from your API or data source
            const response = await axios.get(`backend/appointment/doctors/${selectedDoctorId}`);
            doctor_details.value = response.data; // Store the fetched data
            console.log(doctor_details.value);
        } catch (error) {
            console.error('Error fetching doctor details:', error);
            doctor_details.value = null; // Reset if there's an error
        }
    } else {
        doctor_details.value = null; // Reset if no doctor is selected
    }
};



// TO calculate and display 30min dropdown
const time_slots = ref([]); // Reactive variable to hold the time slots

// Helper function to convert "HH:MM AM/PM" to a Date object
const parseTime = (timeStr) => {
    const [time, modifier] = timeStr.split(' ');
    let [hours, minutes] = time.split(':');
    hours = parseInt(hours);
    if (modifier === 'PM' && hours < 12) {
        hours += 12; // Convert PM hour to 24-hour format
    } else if (modifier === 'AM' && hours === 12) {
        hours = 0; // Convert 12 AM to 0 hours
    }
    return new Date(1970, 0, 1, hours, minutes); // Use a fixed date for time calculations
};

// Function to generate time slots every 30 minutes
const generateTimeSlots = () => {
    if (doctor_details.value) {
        const startTime = doctor_details.value.data.working_hours_start;
        const endTime = doctor_details.value.data.working_hours_end;

        const start = parseTime(startTime);
        const end = parseTime(endTime);

        time_slots.value = [];

        while (start < end) {
            const formattedTime = start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
            const utcTime = new Date(start).toISOString(); // Convert to UTC format
            time_slots.value.push({ name: formattedTime, value: utcTime });
            start.setMinutes(start.getMinutes() + 30); // Increment by 30 minutes
        }
    }
};
// Watch for changes in doctor details to regenerate time slots
watch(doctor_details, (newVal) => {
    if (newVal && newVal.data) {
        generateTimeSlots();
    }
});

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

                    <Button label="Reschedule"
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
                <div class="new_details"
                     v-if="store.item.status !== 'pending'" >
                    <VhField label="Doctor">
                        <div class="p-inputgroup">
                            <Dropdown  v-model="store.item.doctor_id"
                                       :options="store.assets.doctor"
                                       option-label="name"
                                       option-value="id"
                                       filter
                                       name="doctor_name"
                                       data-testid="doctor_name"
                                       @change="fetchDoctorDetails"
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
                                       filter
                                       data-testid="patient_name"
                                       required/>
                            <div class="required-field hidden"></div>
                        </div>
                    </VhField>
                </div>
                <div class="existing_details"
                     v-if="store.item.status == 'pending'" >
                    <VhField label="Patient Name: ">
                        <div class="p-inputgroup">
                            <strong> {{ store.assets.patient.find(p => p.id === store.item.patient_id)?.name || 'Unknown Patient' }}</strong>
                        </div>
                    </VhField>

                    <VhField label="Doctor Name: ">
                        <div class="p-inputgroup">
                            <strong>  {{ store.assets.doctor.find(p => p.id === store.item.doctor_id)?.name || 'Unknown Doctor' }} </strong>
                        </div>
                    </VhField>

                    <VhField label="Appointment Scheduled: ">
                        <div class="p-inputgroup">
                            <strong>{{ store.item.appointment_date}} - {{store.item.appointment_time}}</strong>
                        </div>
                    </VhField>

                    <VhField label="Current Status:">
                        <div class="p-inputgroup">
                            <Button icon="pi pi-undo" label="Reschedule Pending" severity="secondary" rounded />
                        </div>
                    </VhField>
                </div>

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

                <VhField label="Appointment Date">
                    <div class="p-inputgroup">
                        <Calendar v-model="store.item.appointment_date"
                                  placeholder="Select Date" name="appointment_date">
                        </Calendar>
                    </div>
                </VhField>

<!--                <VhField label="Appointment Time">-->
<!--                    <div class="p-inputgroup">-->
<!--                        <Calendar v-model="store.item.appointment_time" timeOnly hourFormat="12" showIcon-->
<!--                                  placeholder="Select time"-->
<!--                                  name="appointment_time"-->
<!--                                 >-->
<!--                        </Calendar>-->
<!--                    </div>-->
<!--                </VhField>-->

                <VhField label=" ">
                    <div class="p-inputgroup">
                        <!-- Display Doctor Details -->
                        <div v-if="doctor_details">
                            <h3>Doctor Details</h3>
                            <p><strong>Name:</strong> {{doctor_details.data.name}}</p>
                            <p><strong>Working hour start:</strong> {{doctor_details.data.working_hours_start}}</p>
                            <p><strong>Working hour end:</strong> {{doctor_details.data.working_hours_end}}</p>
                        </div>
                    </div>

                </VhField>

                <VhField label="Appointment Time">
                    <div class="p-inputgroup">
                        <Dropdown v-model="store.item.appointment_time"
                                  :options="time_slots"
                                  option-label="name"
                                  option-value="value"
                                  name="appointment_time"
                                  filter
                                  data-testid="appointment_time"
                                  required/>
                        <div class="required-field hidden"></div>
                    </div>
                </VhField>



            </div>
        </Panel>

    </div>

</template>
