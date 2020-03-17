<template>
    <modal id="itemDetail" v-on:reset-validation="$emit('reset-validation')">
        <template slot="title">{{$ml.with('VueJS').get('txtEditDept')}}</template>
        <div v-if="currentItem">
            <div class="form-group">
                <label class>{{$ml.with('VueJS').get('txtName')}}</label>
                <input v-model="currentItem.name" type="text" name="name" class="form-control" required/>
            </div>
            <div class="form-group">
                <label class>{{$ml.with('VueJS').get('txtNameVi')}}</label>
                <input v-model="currentItem.name_vi" type="text" name="name_vi" class="form-control"/>
            </div>
            <div class="form-group">
                <label class>{{$ml.with('VueJS').get('txtNameJa')}}</label>
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
                >{{$ml.with('VueJS').get('txtUpdate')}}
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