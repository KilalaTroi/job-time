<template>
  <div v-if="this.success">
    <div class="alert alert-success">
      <span>{{ this.success }}</span>
    </div>
  </div>
</template>
<script>
    export default {
        name: 'success-item',
        data() {
            return {}
        },
        props: ['success']
    }
</script>