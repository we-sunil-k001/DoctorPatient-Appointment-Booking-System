<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useAppointmentStore } from '../../../stores/store-appointments'

const store = useAppointmentStore();
const useVaah = vaah();

// Function to convert UTC time to Asia/Kolkata
function convertUTCtoKolkata(date,time) {
    const date_time_string = `${date} ${time} UTC`;
    const appointment_date_time = new Date(date_time_string);

    // Adjust to the correct date in IST
    appointment_date_time.setUTCDate(appointment_date_time.getUTCDate() + 1);

    const formattedDate = appointment_date_time.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: '2-digit'
    });

    const formattedTime = appointment_date_time.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });

    return `${formattedDate}, ${formattedTime}`;
}


</script>

<template>

    <div v-if="store.list">
        <!--table-->
         <DataTable :value="store.list.data"
                   dataKey="id"
                   :rowClass="store.setRowClass"
                   class="p-datatable-sm p-datatable-hoverable-rows"
                   :nullSortOrder="-1"
                   v-model:selection="store.action.items"
                   stripedRows
                   responsiveLayout="scroll">

            <Column selectionMode="multiple"
                    v-if="store.isViewLarge()"
                    headerStyle="width: 3em">
            </Column>

            <Column field="id" header="ID" :style="{width: '80px'}" :sortable="true">
            </Column>

             <Column field="name" header="Doctor name" class="overflow-wrap-anywhere" :sortable="true">
                 <template  #body="prop">
                     {{ prop.data.doctor.name }}
                 </template>
             </Column>

             <Column field="Patient name" header="Patient name" class="overflow-wrap-anywhere" :sortable="true">
                 <template  #body="prop">
                     {{ prop.data.patient.name }}
                 </template>
             </Column>

             <Column field="appointment_date" header="Appointment Date and time"
                     class="overflow-wrap-anywhere"
                     :sortable="true">
                 <template #body="prop">
                    {{ convertUTCtoKolkata(prop.data.appointment_date, prop.data.appointment_time)}}
                 </template>
             </Column>

             <Column field="status" header="Booking Status"
                     class="overflow-wrap-anywhere"
                     :sortable="true">
                 <template #body="prop">
                     {{prop.data.status}}
                 </template>
             </Column>

             <Column field="reason_for_visit" header="Medical Concern"
                     class="overflow-wrap-anywhere"
                     :sortable="true">
                 <template #body="prop">
                     {{prop.data.reason_for_visit}}
                 </template>
             </Column>



            <Column field="actions" style="width:150px;"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup ">

                        <!--  Below btn will work If the : v-if="prop.data.status !== 'cancelled'"-->
                        <Button label="Cancel" severity="danger" rounded
                                v-if="prop.data.status !== 'cancelled'"
                                @click="store.itemAction('cancel', prop.data)"
                                v-tooltip.top="'Cancel'"/>

                        <!--  Below btn will work If the : v-if="prop.data.status === 'cancelled'"-->
                        <Button label="Cancel" severity="secondary" rounded
                                disabled v-else
                                v-tooltip.top="'Cancel'"/>

                        <Button class="p-button-tiny p-button-text"
                                data-testid="appointments-table-to-view"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />


                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="appointments-table-action-restore"
                                v-if="store.isViewLarge() && prop.data.deleted_at"
                                @click="store.itemAction('restore', prop.data)"
                                v-tooltip.top="'Restore'"
                                icon="pi pi-replay" />

                        <Button class="p-button-tiny p-button-text"
                                v-if="prop.data.status !== 'cancelled'"
                                data-testid="appointments-table-to-edit"
                                v-tooltip.top="'Update'"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <!--  Below btn will work If the : v-if="prop.data.status === 'cancelled'"-->
                        <Button class="p-button-tiny p-button-text"
                                disabled v-else
                                icon="pi pi-pencil"/>

                    </div>

                </template>


            </Column>

             <template #empty>
                 <div class="text-center py-3">
                     No records found.
                 </div>
             </template>

        </DataTable>
        <!--/table-->

        <!--paginator-->
        <Paginator v-if="store.query.rows"
                   v-model:rows="store.query.rows"
                   :totalRecords="store.list.total"
                   :first="((store.query.page??1)-1)*store.query.rows"
                   @page="store.paginate($event)"
                   :rowsPerPageOptions="store.rows_per_page"
                   class="bg-white-alpha-0 pt-2">
        </Paginator>
        <!--/paginator-->

    </div>

</template>
