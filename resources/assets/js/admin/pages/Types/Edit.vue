<template>
    <modal id="itemDetail" v-on:reset-validation="$emit('reset-validation')">
        <template slot="title">Edit Type</template>
        <div v-if="currentItem">
            <div class="form-group">
                <label class>Slug</label>
                <input v-model="currentItem.slug" type="text" name="slug" class="form-control" required/>
            </div>
            <div class="form-group">
                <label class>Slug VI</label>
                <input v-model="currentItem.slug_vi" type="text" name="slug_vi" class="form-control"/>
            </div>
            <div class="form-group">
                <label class>Slug JA</label>
                <input v-model="currentItem.slug_ja" type="text" name="slug_ja" class="form-control"/>
            </div>
            <div class="form-group">
                <label class>Type Color</label>
                <color-picker :color="currentItem.value" v-model="currentItem.value"></color-picker>
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
    import ColorPicker from "../../components/ColorPicker/ColorPicker";

    export default {
        name: "edit-item",
        components: {
            Modal,
            ErrorItem,
            SuccessItem,
            ColorPicker
        },
        props: ["currentItem", "errors", "success"]
    };
</script>