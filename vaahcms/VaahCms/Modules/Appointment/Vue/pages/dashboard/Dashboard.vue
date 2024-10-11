<script setup>
import { onMounted, ref, watch } from "vue";
import { vaah } from '../../vaahvue/pinia/vaah';
import { useAppointmentStore } from '../../stores/store-appointments';
import {useRoute} from 'vue-router';

const store = useAppointmentStore();
const route = useRoute();
const useVaah = vaah();

onMounted(async () => {
    await store.getDashboardStats();
});
console.log(store.getDashboardStats());


</script>


<template>
  <div style="margin-top: 8px;">
    <h1 class="text-4xl">Dashboard</h1>
  </div>
    <br>

  <div style="background-color: #ffffff; padding: 10px">

      <div class="container">
          <div class="row" style="display: inline-flex;gap: 10px">
              <div class="card1">
                  <Card>
                      <template #header>
                          <h4 v-if="store.item">
                              {{ store.item.data.total_appointments}}
                          </h4>
                      </template>
                      <template #title><h5>Appointments Booked</h5></template>
                      <template #icons> <i class="pi pi-refresh"></i> </template>
                      <template #content>
                          <p class="m-0">
                              Lorem ipsum dolor sit amet, consectetur adipisicing elit. Inventore sed consequuntur
                          </p>
                      </template>
                  </Card>
              </div>
              <div class="card2">
                  <Card>
                      <template #header>
                          <h4 v-if="store.item">
                              {{ store.item.data.total_doctors}}
                          </h4>
                      </template>
                      <template #title><h5>Total Doctors</h5></template>
                      <template #content>
                          <p class="m-0">
                              Lorem ipsum dolor sit amet, consectetur adipisicing elit. Inventore sed consequuntur
                          </p>
                      </template>
                  </Card>
              </div>
              <div class="card2">
                  <Card>
                      <template #header>
                          <h4 v-if="store.item">
                              {{ store.item.data.total_patients}}
                          </h4>
                      </template>
                      <template #title><h5>Total Patients</h5></template>
                      <template #content>
                          <p class="m-0">
                              Lorem ipsum dolor sit amet, consectetur adipisicing elit. Inventore sed consequuntur
                          </p>
                      </template>
                  </Card>
              </div>
              <div class="card2">
                  <Card>
                      <template #header>
                          <h4 v-if="store.item">
                              {{ store.item.data.cancelled_appointments}}
                          </h4>
                      </template>
                      <template #title><h5>Appointments Cancelled</h5></template>
                      <template #content>
                          <p class="m-0">
                              Lorem ipsum dolor sit amet, consectetur adipisicing elit. Inventore sed consequuntur
                          </p>
                      </template>
                  </Card>
              </div>
          </div>
      </div>

      <div class="container">

      </div>

  </div>


</template>

<style>
.card1 Card{
    background-color: var(--highlight-bg);
    color: var(--highlight-text-color);
    border-radius: var(--border-radius);
    padding: 3rem;
}

h4
{
    font-size: 40px;
    color: green;
    background-color: #c4ffc4;
    width: fit-content;
    padding: 20px;
    border-radius: 50px;
}

h5{
    color: #ef4444;
}
</style>
