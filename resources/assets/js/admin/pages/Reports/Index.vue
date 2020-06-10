<template>
    <div class="content">
        <div class="container-fluid">
            <add-new v-show="true">

            </add-new>
        </div>
    </div>
</template>
<script>
import AddNew from './AddNew';
export default {
    components: {
        AddNew
    },
    data() {
        return {

        }
    }
}
</script>