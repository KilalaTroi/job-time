<template>
    <modal id="itemDetail" v-on:reset-validation="$emit('reset-validation')">
        <template slot="title">Edit Department</template>
        <div v-if="currentItem">
            <div class="form-group">
                <label class>Name</label>
                <input v-model="currentItem.name" type="text" name="name" class="form-control" required/>
            </div>
            <div class="form-group">
                <label class>Name VI</label>
                <input v-model="currentItem.name_vi" type="text" name="name_vi" class="form-control"/>
            </div>
            <div class="form-group">
                <label class>Name JA</label>
                <input v-model="currentItem.name_ja" type="text" name="name_ja" class="form-control"/>
            </div>
            <error-item :errors="errors"></error-item>
            <success-item :success="success"></success-item>
            <hr/>
            <div class="form-group text-right">
                <button
                        @click="$emit('update-item', currentItem)"
                        type="button"
                        class="btn btn-primary"
                >Update
                </button>
            </div>
        </div>
    </modal>
</template>

<script>
    import ErrorItem from "../../components/Validations/Error";
    import SuccessItem from "../../components/Validations/Success";
    import Modal from "../../components/Modals/Modal";

    export default {
        name: "edit-item",
        components: {
            Modal,
            ErrorItem,
            SuccessItem,
        },
        props: ["currentItem", "errors", "success"]
    };
</script>