<template>
  <div v-if="this.errors">
    <div class="alert alert-danger" v-for="value in getErrors">
      <span>{{ value[0] }}</span>
    </div>
  </div>
</template>
<script>
    export default {
        name: 'error-item',
        data() {
            return {}
        },
        props: ['errors'],
        computed: {
            getErrors() {
                return this.errors;
            }
        }
    }
</script>