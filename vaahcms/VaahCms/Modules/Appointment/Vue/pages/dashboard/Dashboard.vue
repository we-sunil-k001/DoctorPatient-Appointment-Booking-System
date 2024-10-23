<script setup>
import { onMounted, ref, watch } from "vue";
import { vaah } from '../../vaahvue/pinia/vaah';
import { useAppointmentStore } from '../../stores/store-appointments';
import {useRoute} from 'vue-router';

const store = useAppointmentStore();
const route = useRoute();
const useVaah = vaah();

const total_appointments = ref(null);
const total_doctors = ref(null);
const total_patients = ref(null);
const cancelled_appointments = ref(null);
const reschedule_pending = ref(null);

onMounted(async () => {
    await store.getDashboardStats();

    // Ensure store.item exists and has data before accessing it
    if (store.item && store.item.data) {
        total_appointments.value = store.item.data.total_appointments || 0;
        total_doctors.value = store.item.data.total_doctors || 0;
        total_patients.value = store.item.data.total_patients || 0;
        cancelled_appointments.value = store.item.data.cancelled_appointments || 0;
        reschedule_pending.value = store.item.data.reschedule_pending || 0;

        // Set chart data and options only after data is available
        chartData.value = setChartData();
        chartOptions.value = setChartOptions();
    } else {
        console.warn("store.item or store.item.data is null/undefined");
    }
});



// Chart --------------------------------------------

const chartData = ref();
const chartOptions = ref();

const setChartData = () => {
    return {
        labels: ['Total Appointments', 'Appointments Cancelled', 'Reschedule Pending', 'Total Doctors', 'Total Patients'],
        datasets: [
            {
                label: 'Total Appointments | Appointments Cancelled | Reschedule Pending | Total Doctors | Total Patients',
                data: [
                    total_appointments.value,        // Total appointments
                    cancelled_appointments.value,    // Cancelled appointments
                    reschedule_pending.value,    // Cancelled appointments
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
</script>


<template>
  <div style="margin-top: 8px;">
    <h1 class="text-4xl">Dashboard</h1>
  </div>
    <br>

  <div style="background-color: #ffffff; padding: 10px;">

      <div class="container">
          <div class="row" >
              <div class="card">
                  <Card>
                      <template #header>
                          <h4 v-if="store.item">
                              {{ store.item.data.total_appointments}}
                          </h4>
                      </template>
                      <template #title><h5>Appointments Booked</h5></template>
                      <template #icons> <i class="pi pi-refresh"></i> </template>
                  </Card>
              </div>
              <div class="card">
                  <Card>
                      <template #header>
                          <h4 v-if="store.item">
                              {{ store.item.data.total_doctors}}
                          </h4>
                      </template>
                      <template #title><h5>Total Doctors</h5></template>

                  </Card>
              </div>
              <div class="card">
                  <Card>
                      <template #header>
                          <h4 v-if="store.item">
                              {{ store.item.data.total_patients}}
                          </h4>
                      </template>
                      <template #title><h5>Total Patients</h5></template>

                  </Card>
              </div>
              <div class="card">
                  <Card>
                      <template #header>
                          <h4 v-if="store.item">
                              {{ store.item.data.cancelled_appointments}}
                          </h4>
                      </template>
                      <template #title><h5>Appointments Cancelled</h5></template>

                  </Card>
              </div>
          </div>
      </div>
        <br>
      <div class="chart">
        <div class="row1">
              <div class="card5">
                  <Chart type="bar" :data="chartData" :options="chartOptions" />
              </div>
        </div>

      </div>

  </div>


</template>

<style>
.row{
    display: inline-flex;
    gap: 10px;
    width: 100%;
    margin-bottom: 20px;
}
.card{
    width: 25%;
}
.row .card .p-card-content{
    display: none;
}
h4{
    font-size: 40px;
    width: fit-content;
    padding: 20px;
    border-radius: 50px;
}

.chart .row1{
    display: flex;
    justify-content: center;
}

.chart .card5{
    width: 60%;
}
</style>
