<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useAppointmentStore } from '../../../stores/store-appointments'

const store = useAppointmentStore();
const useVaah = vaah();

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
                     {{ prop.data.doctor?.name ?? 'NA' }}
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
                    {{ prop.data.appointment_date}} - {{ prop.data.appointment_time}}
                 </template>
             </Column>

             <Column field="status" header="Booking Status"
                     class="overflow-wrap-anywhere"
                     :sortable="true">
                 <template #body="prop">
                      <span v-html="
                                prop.data.status === 'pending'
                                ? '<b style=color:#3b82f6;\n>Reschedule Pending</b>'
                                : prop.data.status === 'cancelled'
                                ? (prop.data.doctor?.name
                                    ? '<b style=color:red> Cancelled</b>'
                                    : '<b style=color:#3b82f6>Doctor removed</b>')
                                : prop.data.status === 'confirmed'
                                ? '<b style=color:green>Confirmed</b>'
                                : prop.data.status
                            "></span>
                 </template>
             </Column>

             <Column field="reason_for_visit" header="Medical Concern"
                     class="overflow-wrap-anywhere"
                     :sortable="true">
                 <template #body="prop">
                     {{prop.data.reason_for_visit}}
                 </template>
             </Column>

             <Column  header="Appointment Actions"
                     class="overflow-wrap-anywhere"
                     :sortable="true">
                 <template #body="prop">


                     <!-- Below btn used for Doctor -->
                     <div class="button-group"
                          v-if="store.hasPermission(store.assets.permissions, 'appointment-has-access-of-doctors-section')">

                         <!-- Below btn will work if Status set as pending to reschedule-->
                         <Button label="Request to reschedule" severity="info" rounded
                                 v-if="prop.data.status == 'confirmed'"
                                 @click="store.itemAction('req_to_reschedule', prop.data)"
                                 v-tooltip.top="'Reschedule'"/> &nbsp
                     </div>


                     <div class="button-group"> <!-- Below all btn used for patient -->

                         <!-- Below btn will work if Status is set as pending to reschedule - it is at top right of form-->

                         <Button label="Reschedule" severity="info" rounded
                                       v-if="prop.data.status == 'pending' && store.hasPermission(store.assets.permissions, 'appointment-has-access-of-patient-section')"
                                       @click="store.toEdit(prop.data)"
                                       v-tooltip.top="'Reschedule'"/> &nbsp


                         <!--  Below btn will work If the : v-if="prop.data.status !== 'cancelled'" and has mentioned permission-->

                         <Button label="Cancel" severity="danger" rounded
                                 v-if="prop.data.status !== 'cancelled' && store.hasPermission(store.assets.permissions, 'appointment-has-access-of-patient-section')"
                                 @click="store.itemAction('cancel', prop.data)"
                                 v-tooltip.top="'Cancel'"/>


                         <!--  Below btn will work If the : v-if="prop.data.status === 'cancelled'"-->

                         <Button label="Cancel" severity="secondary" rounded
                             disabled v-if="prop.data.status === 'cancelled' && store.hasPermission(store.assets.permissions, 'appointment-has-access-of-patient-section')"
                             v-tooltip.top="'Cancel'"/> &nbsp
                    </div>

                 </template>
             </Column>



            <Column field="actions" style="width:150px;"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup ">


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

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="doctors-table-action-trash"
                                v-if="store.isViewLarge() && !prop.data.deleted_at
                                && !store.hasPermission(store.assets.permissions, 'appointment-has-access-of-patient-section')
                                && !store.hasPermission(store.assets.permissions, 'appointment-has-access-of-doctors-section')"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


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


<style scoped>
.button-group {
    display: flex;
    align-items: center;
}

button{
    margin: 1px;
}
</style>
