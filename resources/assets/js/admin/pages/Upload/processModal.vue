<template>
    <modal id="processModal" v-on:reset-validation="$emit('reset-validation')">
        <template slot="title">{{$ml.with('VueJS').get('txtEditDept')}}</template>
        <div v-if="currentProcess">
            <div class="form-group">
                <label class>{{$ml.with('VueJS').get('txtName')}}</label>
                <input v-model="currentProcess.job_type" type="text" name="name" class="form-control" required/>
            </div>
            <div class="form-group">
                <label class>{{$ml.with('VueJS').get('txtNameVi')}}</label>
                <input v-model="currentProcess.phase" type="text" name="name_vi" class="form-control"/>
            </div>
            <div class="form-group">
                <label class>{{$ml.with('VueJS').get('txtNameJa')}}</label>
                <input v-model="currentProcess.p_name" type="text" name="name_ja" class="form-control"/>
            </div>
            <error-item :errors="errors"></error-item>
            <success-item :success="success"></success-item>
            <hr/>
            <div class="form-group text-right">
                <button
                        @click="$emit('update-item', currentProcess)"
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
        name: "process-modal",
        components: {
            Modal,
            ErrorItem,
            SuccessItem,
        },
        props: ["currentProcess", "errors", "success"]
    };
</script>