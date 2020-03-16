<template>
  <div v-if="this.success">
    <div class="alert alert-success" v-for="value in getSuccess">
      <span>{{ value[0] }}</span>
    </div>
  </div>
</template>
<script>
    export default {
        name: 'success-list',
        data() {
            return {}
        },
        props: ['success'],
        computed: {
            getSuccess() {
                return this.success;
            }
        }
    }
</script>