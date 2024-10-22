<script setup>
import {vaah} from '../../../vaahvue/pinia/vaah'
import {useAppointmentStore} from '../../../stores/store-appointments'

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
                   @selection-change="store.action.items = $event"
                   stripedRows
                   responsiveLayout="scroll">

            <Column selectionMode="multiple"
                    v-if="store.isViewLarge()"
                    headerStyle="width: 3em">
            </Column>

            <Column field="id" header="ID" :style="{width: '80px'}" :sortable="true">
            </Column>

            <Column field="name" header="Doctor name" class="overflow-wrap-anywhere" :sortable="true">
                <template #body="prop">
                    {{ prop.data.doctor?.name ?? 'NA' }}
                </template>
            </Column>

            <Column field="Patient name" header="Patient name" class="overflow-wrap-anywhere" :sortable="true">
                <template #body="prop">
                    {{ prop.data.patient.name }}
                </template>
            </Column>

            <Column field="appointment_date" header="Appointment Date and time"
                    class="overflow-wrap-anywhere"
                    :sortable="true">
                <template #body="prop">
                    {{ prop.data.appointment_date }} - {{ prop.data.appointment_time }}
                </template>
            </Column>

            <Column field="charges" header="Appointment Charges"
                    class="overflow-wrap-anywhere"
                    :sortable="true">
                <template #body="prop">
                    ₹{{ prop.data.doctor?.charges ?? 'NA' }}/-
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
                    {{ prop.data.reason_for_visit }}
                </template>
            </Column>


            <Column  header="Appointment Actions"
                     class="overflow-wrap-anywhere"
                     :sortable="true">
                <template #body="prop">

                    <!-- Below btn will work if Status set as pending to reschedule-->
                    <div v-for="permission in store.assets.permission">
                        <Button v-if="permission == 'appointment-has-access-of-doctors-section' && prop.data.status == 'confirmed'"
                                label="Request to reschedule" severity="info" rounded
                                @click="store.itemAction('req_to_reschedule', prop.data)"
                                v-tooltip.top="'Request to reschedule'"/>
                    </div>

                    <div v-for="permission in store.assets.permission">
                        <Button v-if="permission == 'appointment-has-access-of-patient-section' && prop.data.status == 'pending'"
                                label="Reschedule" severity="info" rounded
                                @click="store.toEdit(prop.data)"
                                 v-tooltip.top="'Reschedule'"/>
                    </div>

                    <div v-for="permission in store.assets.permission">
                        <Button v-if="permission == 'appointment-has-access-of-patient-section' && prop.data.status !== 'cancelled'"
                                label="Cancel" severity="danger" rounded
                                @click="store.itemAction('cancel', prop.data)"
                                v-tooltip.top="'Cancel'"/>
                    </div>

                    <div v-for="permission in store.assets.permission">
                        <Button v-if="permission == 'appointment-has-access-of-patient-section' && prop.data.status == 'cancelled'"
                                label="Cancel" rounded
                                disabled
                                v-tooltip.top="'Cancel'"/>
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
                   :rows="store.query.rows"
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

button {
    margin: 1px;
}
</style>
