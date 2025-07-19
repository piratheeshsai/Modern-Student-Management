

<template>
  <MainLayout>
    <div class="row">
      <div class="col-12">
        <div class="card my-4">
          <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
            <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
              <h6 class="text-white text-capitalize ps-3">Dashboard</h6>
            </div>
          </div>
          <div class="card-body px-0 pb-2">
            <div class="p-3">
              <h5>Welcome to Dashboard</h5>
              <p>This is your dashboard content.</p>

            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '@/Layouts/MainLayout.vue'

export default {
  name: 'Dashboard',
  components: {
    MainLayout
  },
  props: {
    user: Object,
    // other props from controller
  }
}
</script>
