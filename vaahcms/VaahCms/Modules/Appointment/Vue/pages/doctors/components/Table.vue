<script setup>
import {vaah} from '../../../vaahvue/pinia/vaah'
import {usedoctorStore} from '../../../stores/store-doctors'

const store = usedoctorStore();
const useVaah = vaah();

// Sidebar script is store-doctor.js------
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

            <Column field="name" header="Name"
                    class="overflow-wrap-anywhere"
                    :sortable="true">

                <template #body="prop">

                    <span @click="store.fetchDoctorAppointments(prop.data.id)" class="doctors_name">
                        {{ prop.data.name }}
                        <sup><Badge value="4" size="" severity="info">{{ prop.data.appointments_count }}</Badge></sup>
                    </span>

                </template>

            </Column>

            <Column field="email" header="Email"
                    class="overflow-wrap-anywhere"
                    :sortable="true">

                <template #body="prop">
                    <Badge v-if="prop.data.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    {{ prop.data.email }}
                </template>

            </Column>

            <Column field="phone_number" header="Phone Number"
                    class="overflow-wrap-anywhere"
                    :sortable="true">

                <template #body="prop">
                    <Badge v-if="prop.data.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    {{ prop.data.phone_number }}
                </template>

            </Column>

            <Column field="specialization" header="Specialization"
                    class="overflow-wrap-anywhere"
                    :sortable="true">

                <template #body="prop">
                    <Badge v-if="prop.data.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    {{ prop.data.specialization }}
                </template>

            </Column>

            <Column field="charges" header="Charges"
                    class="overflow-wrap-anywhere"
                    :sortable="true">

                <template #body="prop">
                    <Badge v-if="prop.data.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    ₹{{ prop.data.charges }}/-
                </template>

            </Column>

            <Column field="" header="Working hours"
                    class="overflow-wrap-anywhere"
                    :sortable="true">

                <template #body="prop">
                    <Badge v-if="prop.data.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    {{ prop.data.working_hours_start }} - {{ prop.data.working_hours_end }}
                </template>

            </Column>

            <Column field="updated_at" header="Updated"
                    v-if="store.isViewLarge()"
                    style="width:150px;"
                    :sortable="true">

                <template #body="prop">
                    {{ useVaah.strToSlug(prop.data.updated_at) }}
                </template>

            </Column>

            <Column field="is_active" v-if="store.isViewLarge()"
                    :sortable="true"
                    style="width:100px;"
                    header="Is Active">

                <template #body="prop">
                    <InputSwitch v-model.bool="prop.data.is_active"
                                 data-testid="doctors-table-is-active"
                                 v-bind:false-value="0" v-bind:true-value="1"
                                 class="p-inputswitch-sm"
                                 @input="store.toggleIsActive(prop.data)">
                    </InputSwitch>
                </template>

            </Column>

            <Column field="actions" style="width:150px;"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup ">

                        <Button class="p-button-tiny p-button-text"
                                data-testid="doctors-table-to-view"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye"/>

                        <Button class="p-button-tiny p-button-text"
                                data-testid="doctors-table-to-edit"
                                v-tooltip.top="'Update'"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil"/>

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="doctors-table-action-trash"
                                v-if="store.isViewLarge() && !prop.data.deleted_at"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash"/>


                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="doctors-table-action-restore"
                                v-if="store.isViewLarge() && prop.data.deleted_at"
                                @click="store.itemAction('restore', prop.data)"
                                v-tooltip.top="'Restore'"
                                icon="pi pi-replay"/>


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

    <div class="card flex justify-content-center">
        <Sidebar v-model:visible="store.visible" header="Appointments" position="right" class="w-full md:w-50rem lg:w-30rem">

            <!-- Display Doctor's Name -->
            <Tag severity="info" v-if="store.appointments && store.appointments.data && store.appointments.data.length > 0 && store.appointments.data[0].doctor_name">
                 Dr. {{ store.appointments.data[0].doctor_name }}
            </Tag>
            <div class="card" >
                <TabView>
                    <TabPanel header="Booked">

                        <div class="card" v-if="store.appointments && store.appointments.data && store.appointments.data.length > 0">

                            <DataTable :value="store.appointments.data.filter(a => a.status === 'confirmed')"
                                       dataKey="id"
                                       :rowClass="store.setRowClass"
                                       class="p-datatable-sm p-datatable-hoverable-rows"
                                       :nullSortOrder="-1"
                                       stripedRows
                                       responsiveLayout="scroll">

                                <Column field="id" header="ID" :style="{width: '80px'}" :sortable="true">
                                </Column>

                                <Column field="patient_name" header="Patient name" class="overflow-wrap-anywhere"
                                        :sortable="true">
                                    <template #body="prop">
                                        {{ prop.data.patient_name}}
                                    </template>
                                </Column>

                                <Column field="appointment_date" header="Appointment Date and time"
                                        class="overflow-wrap-anywhere"
                                        :sortable="true">
                                    <template #body="prop">
                                        {{ prop.data.appointment_date}} - {{ prop.data.appointment_time}}
                                    </template>
                                </Column>

                                <Column field="charges" header="Appointment Charges"
                                        class="overflow-wrap-anywhere"
                                        :sortable="true">
                                    <template #body="prop">
                                        ₹{{ prop.data.charges}}/-
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
                                             ? '<b style=color:red> Cancelled</b>'
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

                            </DataTable>
                        </div>
                        <div class="card" v-else>
                            <h4 style="color: #b32b23; font-size: 15px">No Booked Appointments found !</h4>
                        </div>
                    </TabPanel>


                    <TabPanel header="Cancelled">
                            <div class="card" v-if="store.appointments && store.appointments.data && store.appointments.data.length > 0">

                                <DataTable :value="store.appointments.data.filter(a => a.status === 'cancelled')"
                                           dataKey="id"
                                           :rowClass="store.setRowClass"
                                           class="p-datatable-sm p-datatable-hoverable-rows"
                                           :nullSortOrder="-1"
                                           stripedRows
                                           responsiveLayout="scroll">

                                    <Column field="s_no" header="S.no." class="overflow-wrap-anywhere" :sortable="false">
                                        <template #body="prop">
                                            {{ prop.data.id}}
                                        </template>
                                    </Column>

                                    <Column field="patient_name" header="Patient name" class="overflow-wrap-anywhere"
                                            :sortable="true">
                                        <template #body="prop">
                                            {{ prop.data.patient_name}}
                                        </template>
                                    </Column>

                                    <Column field="appointment_date" header="Appointment Date and time"
                                            class="overflow-wrap-anywhere"
                                            :sortable="true">
                                        <template #body="prop">
                                            {{ prop.data.appointment_date}} - {{ prop.data.appointment_time}}
                                        </template>
                                    </Column>

                                    <Column field="charges" header="Appointment Charges"
                                            class="overflow-wrap-anywhere"
                                            :sortable="true">
                                        <template #body="prop">
                                            ₹{{ prop.data.charges}}/-
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
                                             ? '<b style=color:red> Cancelled</b>'
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

                                </DataTable>
                            </div>
                            <div class="card" v-else>
                                <h4 style="color: #b32b23; font-size: 15px">No Cancelled Appointments found !</h4>
                            </div>
                    </TabPanel>

                    <TabPanel header="Reschedule Pending">
                        <div class="card" v-if="store.appointments && store.appointments.data && store.appointments.data.length > 0">

                            <DataTable :value="store.appointments.data.filter(a => a.status === 'pending')"
                                       dataKey="id"
                                       :rowClass="store.setRowClass"
                                       class="p-datatable-sm p-datatable-hoverable-rows"
                                       :nullSortOrder="-1"
                                       stripedRows
                                       responsiveLayout="scroll">

                                <Column field="s_no" header="S.no." class="overflow-wrap-anywhere" :sortable="false">
                                    <template #body="prop">
                                        {{ prop.data.id}}
                                    </template>
                                </Column>

                                <Column field="patient_name" header="Patient name" class="overflow-wrap-anywhere"
                                        :sortable="true">
                                    <template #body="prop">
                                        {{ prop.data.patient_name}}
                                    </template>
                                </Column>

                                <Column field="appointment_date" header="Appointment Date and time"
                                        class="overflow-wrap-anywhere"
                                        :sortable="true">
                                    <template #body="prop">
                                        {{ prop.data.appointment_date}} - {{ prop.data.appointment_time}}
                                    </template>
                                </Column>

                                <Column field="charges" header="Appointment Charges"
                                        class="overflow-wrap-anywhere"
                                        :sortable="true">
                                    <template #body="prop">
                                        ₹{{ prop.data.charges}}/-
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
                                             ? '<b style=color:red> Cancelled</b>'
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

                            </DataTable>
                        </div>
                        <div class="card" v-else>
                            <h4 style="color: #b32b23; font-size: 15px">No "Reschedule Pending" Appointments found !</h4>
                        </div>
                    </TabPanel>
                </TabView>
            </div>


        </Sidebar>
    </div>

</template>

<style scoped>
.doctors_name {
    color: cornflowerblue;
    font-weight: 600;
    cursor: pointer;
}

</style>
