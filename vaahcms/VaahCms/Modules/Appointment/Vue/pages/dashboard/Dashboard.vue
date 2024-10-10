<script  setup>
document.title = 'Appointment';
</script>
<template  >

  <div style="margin-top: 8px;">
    <h1 class="text-4xl">Dashboard</h1>
  </div>
    <br>

  <div style="background-color: #ffffff; padding: 10px">
      <div class="row" style="display: inline-flex;gap: 10px">
          <div class="card1">
              <Card>
                  <template #header>
                     <h2>12</h2>
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


</template>

<style>
.card1 Card{
    background-color: var(--highlight-bg);
    color: var(--highlight-text-color);
    border-radius: var(--border-radius);
    padding: 3rem;
}
</style>
