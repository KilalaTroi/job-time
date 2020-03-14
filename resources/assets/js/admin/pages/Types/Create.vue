<template>
    <modal id="itemCreate" :sizeClasses="modalLg" v-on:reset-validation="$emit('reset-validation')">
        <template slot="title">{{$ml.with('VueJS').get('txtCreateType')}}</template>
        <form @submit="emitCreateItem">
            <div class="form-group">
                <label class="">{{$ml.with('VueJS').get('txtSlug')}}</label>
                <input v-model="slug" type="text" name="slug" class="form-control" required>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="">{{$ml.with('VueJS').get('txtNameVi')}}</label>
                        <input v-model="slug_vi" type="text" name="slug_vi" class="form-control">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="">{{$ml.with('VueJS').get('txtNameJa')}}</label>
                        <input v-model="slug_ja" type="text" name="slug_ja" class="form-control">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="">{{$ml.with('VueJS').get('txtDescVi')}}</label>
                        <textarea v-model="description_vi" name="description_vi" class="form-control"></textarea>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="">{{$ml.with('VueJS').get('txtDescJa')}}</label>
                        <textarea v-model="description_ja" name="description_ja" class="form-control"></textarea>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="">{{$ml.with('VueJS').get('txtColor')}}</label>
                <color-picker :color="value" v-model="value"></color-picker>
            </div>
            <error-item :errors="errors"></error-item>
            <success-item :success="success"></success-item>
            <hr>
            <div class="form-group text-right">
                <button type="submit" class="btn btn-primary">{{$ml.with('VueJS').get('txtCreate')}}</button>
            </div>
        </form>
    </modal>
</template>

<script>
    import ErrorItem from '../../components/Validations/Error'
    import SuccessItem from '../../components/Validations/Success'
    import Modal from '../../components/Modals/Modal'
    import ColorPicker from '../../components/ColorPicker/ColorPicker'

    export default {
        name: 'create-item',
        components: {
            Modal,
            ErrorItem,
            SuccessItem,
            ColorPicker
        },
        props: ['errors', 'success'],
        data() {
            return {
                slug: '',
                slug_vi: '',
                slug_ja: '',
                description_vi: '',
                description_ja: '',
                value: '#000000',
                modalLg: 'modal-lg',
            }
        },
        methods: {
            emitCreateItem(e) {
                e.preventDefault()
                const newItem = {
                    slug: this.slug,
                    slug_vi: this.slug_vi,
                    slug_ja: this.slug_ja,
                    description_vi: this.description_vi,
                    description_ja: this.description_ja,
                    value: this.value
                };
                this.$emit('create-item', newItem);
            },
            resetData(data) {
                // Reset
                if (data.length) {
                    this.slug = '';
                    this.slug_vi = '';
                    this.slug_ja = '';
                    this.description_vi = '';
                    this.description_ja = '';
                    this.value = '#000000';
                }
            }
        },
        watch: {
            success: [{
                handler: 'resetData'
            }]
        }
    }
</script>