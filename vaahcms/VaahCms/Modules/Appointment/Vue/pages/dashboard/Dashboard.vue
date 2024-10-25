<script setup>
import {onMounted, ref, watch} from "vue";
import {vaah} from '../../vaahvue/pinia/vaah';
import {useAppointmentStore} from '../../stores/store-appointments';
import {useRoute} from 'vue-router';

const store = useAppointmentStore();
const route = useRoute();
const useVaah = vaah();

const total_appointments = ref(null);
const total_doctors = ref(null);
const total_patients = ref(null);
const cancelled_appointments = ref(null);
const reschedule_pending = ref(null);
const confirm_appointments = ref(null);

const chart_data = ref();
const chart_options = ref();

const pie_data = ref();
const pie_options = ref();

onMounted(async () => {
    await store.getDashboardStats();

    // Ensure store.item exists and has data before accessing it
    if (store.item && store.item.data) {
        total_appointments.value = store.item.data.total_appointments || 0;
        total_doctors.value = store.item.data.total_doctors || 0;
        total_patients.value = store.item.data.total_patients || 0;
        cancelled_appointments.value = store.item.data.cancelled_appointments || 0;
        reschedule_pending.value = store.item.data.reschedule_pending || 0;
        confirm_appointments.value = store.item.data.confirm_appointments || 0;

        // Set chart data and options only after data is available
        chart_data.value = setChartData();
        chart_options.value = setChartOptions();

        // Set Pie data and options only after data is available
        pie_data.value = setpieData();
        pie_options.value = setpieOptions();
    } else {
        console.warn("store.item or store.item.data is null/undefined");
    }
});


// Chart --------------------------------------------

const setChartData = () => {
    return {
        labels: ['Total Doctors', 'Total Patients'],
        datasets: [
            {
                label: 'Total Doctors | Total Patients',
                data: [
                    total_doctors.value,             // Total doctors
                    total_patients.value             // Total patients
                ],
                backgroundColor: ['rgba(249, 115, 22, 0.2)', 'rgba(6, 182, 212, 0.2)', 'rgb(107, 114, 128, 0.2)', 'rgba(139, 92, 246 0.2)'],
                borderColor: ['rgb(249, 115, 22)', 'rgb(6, 182, 212)', 'rgb(107, 114, 128)', 'rgb(139, 92, 246)'],
                borderWidth: 1
            }
        ]
    };
};
const setChartOptions = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = documentStyle.getPropertyValue('--text-color');
    const textColorSecondary = documentStyle.getPropertyValue('--text-color-secondary');
    const surfaceBorder = documentStyle.getPropertyValue('--surface-border');

    return {
        plugins: {
            legend: {
                labels: {
                    color: textColor
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: textColorSecondary
                },
                grid: {
                    color: surfaceBorder
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    color: textColorSecondary
                },
                grid: {
                    color: surfaceBorder
                }
            }
        }
    };
}

// Pie chart scrip below ---------------------
const setpieData = () => {
    const documentStyle = getComputedStyle(document.body);

    return {
        labels: ['Appointments Confirm', 'Appointments Cancelled', 'Appointments Pending for Reschedule'],
        datasets: [
            {
                data: [
                    confirm_appointments.value,        // confirm appointments
                    cancelled_appointments.value,    // Cancelled appointments
                    reschedule_pending.value,    // Cancelled appointments

                ],
                backgroundColor: [documentStyle.getPropertyValue('--cyan-500'), documentStyle.getPropertyValue('--orange-500'), documentStyle.getPropertyValue('--gray-500')],
                hoverBackgroundColor: [documentStyle.getPropertyValue('--cyan-400'), documentStyle.getPropertyValue('--orange-400'), documentStyle.getPropertyValue('--gray-400')]
            }
        ]
    };
};

const setpieOptions = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = documentStyle.getPropertyValue('--text-color');

    return {
        plugins: {
            legend: {
                labels: {
                    usePointStyle: true,
                    color: textColor
                }
            }
        }
    };
};
</script>


<template>
    <div class="container p-3 bg-white">
        <div class="grid">
            <div class="col-12 md:col-12 lg:col-12">
                <div class=" border-round-sm  font-bold">
                    <h1>Dashboard</h1>
                </div>
            </div>
            <div class="col-12 md:col-6 lg:col-3">
                <div class="p-4 py-5 border-round-sm shadow-2 bg-white font-bold">
                    <h4 class="text-6xl pt-2" v-if="store.item">
                        {{ store.item.data.total_doctors }}
                    </h4>
                    <h5 class="pt-5 text-xl font-normal">Associated Doctors</h5>
                </div>
            </div>
            <div class="col-12 md:col-6 lg:col-3">
                <div class="p-4 py-5 border-round-sm shadow-2 bg-white font-bold">

                    <h4 class="text-6xl pt-2" v-if="store.item">
                        {{ store.item.data.total_patients }}
                    </h4>

                    <h5 class="pt-5 text-xl font-normal">Registered Patients</h5>
                </div>
            </div>
            <div class="col-12 md:col-6 lg:col-3">
                <div class="p-4 py-5 border-round-sm shadow-2 bg-white font-bold">
                    <h4 class="text-6xl pt-2" v-if="store.item">
                        {{ store.item.data.cancelled_appointments }} / {{ store.item.data.total_appointments }}
                    </h4>
                    <h5 class="pt-5 text-xl font-normal">Appointments Cancelled</h5>
                </div>
            </div>
            <div class="col-12 md:col-6 lg:col-3">
                <div class="p-4 py-5 border-round-sm shadow-2 bg-white font-bold">
                    <h4 class="text-6xl pt-2" v-if="store.item">
                        {{ store.item.data.reschedule_pending }} / {{ store.item.data.total_appointments }}
                    </h4>
                    <h5 class="pt-5 text-xl font-normal">Appointments Reschedule-Pending</h5>
                </div>
            </div>
        </div>


        <div class="container mt-5">
            <div class="grid">
                <div class="col-12 md:col-6 lg:col-6">
                        <Chart type="bar" :data="chart_data" :options="chart_options"/>
                    <h3 class="font-normal text-center pt-5">Associated Doctors and Register Patients</h3>
                </div>

                <div class="col-12 md:col-6 lg:col-6 d-flex ">
                    <div class="text-center">
                        <Chart type="doughnut" :data="pie_data" :options="pie_options" class="pie w-full md:w-30rem" />
                        <h3 class="font-normal pt-5">Appointments Count with Status</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<style scopod>
.pie{
    /*height: 400px;*/
    /*width: 400px;*/
}
</style>
