<template>
    <div class="content">
        <div class="container-fluid">
            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-auto">
                        <button-create>
                            <template slot="title">{{$ml.with('VueJS').get('txtCreateType')}}</template>
                        </button-create>
                    </div>
                </div>
            </div>

            <card class="strpied-tabled-with-hover">
                <template slot="header">
                    <h4 class="card-title">{{$ml.with('VueJS').get('txtJobTypeList')}}</h4>
                </template>
                <div class="table-responsive">
                    <table-type class="table-hover table-striped" />
                </div>
            </card>
            <create-item />
            <edit-item />
        </div>
    </div>
</template>
<script>
import TableType from "../../components/TableType";
import Card from "../../components/Cards/Card";
import CreateItem from "./Create";
import EditItem from "./Edit";
import ButtonCreate from "../../components/Buttons/Create";
import { mapGetters, mapActions } from "vuex";

export default {
  components: {
    TableType,
    Card,
    CreateItem,
    EditItem,
    ButtonCreate,
  }
};
</script>
<style lang="scss">
    .type-color {
        width: 60px;
        height: 20px;
        display: inline-block;
        vertical-align: middle;
    }
</style>
