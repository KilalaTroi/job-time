<template>
    <div class="content">
        <div class="container-fluid">
            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-auto">
                        <button-create>
                            <template slot="title">{{$ml.with('VueJS').get('txtCreateUser')}}</template>
                        </button-create>
                    </div>
                </div>
            </div>

            <card class="strpied-tabled-with-hover">
                <template slot="header">
                    <div class="d-flex justify-content-between">
                        <h4 class="card-title">{{$ml.with('VueJS').get('txtUserList')}}</h4>

                        <div class="form-group mb-0 d-flex justify-content-between" style="min-width: 100px;">
                            <multiselect
                            v-model="selectedTeam"
                            :options="currentTeamOption"
                            :clear-on-select="false"
                            :searchable="false"
                            :placeholder="$ml.with('VueJS').get('txtSelectOne')"
                            label="text"
                            track-by="text"
                            :name="'selectedTeam'"
                            :preselect-first="true"
                            :showLabels="false"
                            ></multiselect>
                        </div>
                    </div>
                </template>
                <div class="table-responsive">
                    <table-user class="table-hover table-striped"/>

                    <div v-if="!users.length" class="text-center mt-3">
                        <img src="https://i.imgur.com/JfPpwOA.gif">
                    </div>
                </div>
            </card>

            <create-item/>
            <edit-item/>
        </div>
    </div>
</template>
<script>
    import TableUser from "../../components/TableUser"
    import Card from "../../components/Cards/Card"
    import CreateItem from "./Create"
    import EditItem from "./Edit"
    import ButtonCreate from "../../components/Buttons/Create"
    import Multiselect from "vue-multiselect";
    import { mapGetters, mapActions } from 'vuex'

    export default {
        components: {
            TableUser,
            Card,
            CreateItem,
            EditItem,
            ButtonCreate,
            Multiselect
        },

        computed: {
            ...mapGetters({
                currentTeamOption: "currentTeamOption", 
			    currentTeam: "currentTeam",
                users: 'users/items',
                roles: 'users/roles'
            })
        },

        data() {
            return {
                selectedTeam: {}
            }
        },

        methods: {
            ...mapActions({
                getAllUser: 'users/getAllUser',
                getRoleOptions: 'users/getRoleOptions',
                setCurrentTeam: 'setCurrentTeam'
            })
        },

        mounted() {
            const _this = this
            _this.selectedTeam = Object.assign({}, _this.currentTeam)
            _this.getAllUser()
        },

        watch: {
            roles: [{
                handler: function(newValue, oldValue) {
                    if ( newValue.length != oldValue.length ) {
                        this.getRoleOptions()
                    }
                }
            }],
            selectedTeam: [{
                handler: async function(newValue, oldValue) {
                    if ( ! jQuery.isEmptyObject(oldValue) && ! jQuery.isEmptyObject(newValue) ) {
                        await this.setCurrentTeam(this.selectedTeam.id)
                        this.getAllUser()
                    }
                }
            }]
        }
    };
</script>

<style lang="scss">
@import "~vue-multiselect/dist/vue-multiselect.min.css";
</style>
