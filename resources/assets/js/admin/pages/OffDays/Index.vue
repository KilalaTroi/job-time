<template>
    <div class="content">
        <div class="container-fluid max-width-1170">
            <off-days></off-days>   
            <all-off-days></all-off-days>   
        </div>
    </div>
</template>
<script>
import OffDays from './OffDays.vue'
import AllOffDays from './AllOffDays.vue'

export default {
    components: {
        OffDays,
        AllOffDays
    }
}
</script>
<style>
</style>