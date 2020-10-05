<template>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <router-link :to="{path: '/'}" class="py-1 d-block d-sm-none">
                <div class="logo-img">
                    <img width="50" src="/images/logo.png" :alt="$ml.with('VueJS').get('siteName')">
                </div>
            </router-link>
            <p class="navbar-brand d-none d-sm-block">{{$ml.with('VueJS').get('txtWelcome')}} {{ loginUser.name }}</p>
            <div class="languages nav-item d-block d-sm-none ml-auto mr-3">
                <button
                    v-for="lang in $ml.list"
                    :key="lang"
                    @click="$ml.change(lang)"
                    :class="activeLanguage(lang)"
                    v-text="lang"
                />
            </div>
            <button type="button" class="navbar-toggler navbar-toggler-right" :class="{toggled: $sidebar.showSidebar}" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation" @click="toggleSidebar">
                <span class="navbar-toggler-bar burger-lines"></span>
                <span class="navbar-toggler-bar burger-lines"></span>
                <span class="navbar-toggler-bar burger-lines"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end">
                <div v-if="currentTeamOption" class="switch-team ml-auto mr-3">
                    <select-2 :options="currentTeamOption" v-model="selectTeam" class="select2" @input="setCurrentTeam(selectTeam)"></select-2>
                </div>
                <ul class="navbar-nav">
                    <li class="languages nav-item mr-3">
                        <button
                            v-for="lang in $ml.list"
                            :key="lang"
                            @click="$ml.change(lang)"
                            :class="activeLanguage(lang)"
                            v-text="lang"
                        />
                    </li>
                    <li class="nav-item">
                        <router-link :to="{path: '/profile'}" class="nav-link">
                            <i class="nc-icon nc-circle-09 mr-2 ic-custom-2"></i> {{$ml.with('VueJS').get('txtProfile')}}
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <a href="/logout" class="nav-link">
                            <i class="nc-icon nc-button-power mr-2 ic-custom-2"></i> 
                            {{$ml.with('VueJS').get('txtLogOut')}}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</template>

<script>
import Select2 from '../components/SelectTwo/SelectTwo.vue'
import { mapGetters, mapActions } from "vuex"

export default {
    components: {
        Select2
    },

    data() {
        return {
            selectTeam: this.currentTeam
        }
    },

    computed: {
        ...mapGetters({
            loginUser: 'loginUser',
            currentTeamOption: 'currentTeamOption',
            currentTeam: 'currentTeam'
        }),

        routeName() {
            const { name } = this.$route
            return this.capitalizeFirstLetter(name)
        }
    },
    
    methods: {
        ...mapActions({
            setTranslateTexts: 'setTranslateTexts',
            setLoginUser: 'setLoginUser',
            setCurrentTeam: 'setCurrentTeam'
        }),
        
        activeLanguage(language) {
            $('body').attr('class', '').addClass('language-'+this.$ml.current);
            if ( this.$ml.current === language )
                return 'bg-success';
            return 'bg-secondary';
        },

        capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1)
        },

        toggleSidebar() {
            this.$sidebar.displaySidebar(!this.$sidebar.showSidebar)
        },

        hideSidebar() {
            this.$sidebar.displaySidebar(false)
        }
    },

    mounted() {
        const _this = this;

        const userID = document.querySelector("meta[name='user-id']").getAttribute('content')
        _this.setLoginUser(userID)

        const _translateTexts = _this.$ml.with("VueJS");
        _this.setTranslateTexts(_translateTexts);
    }
}
</script>

<style lang="scss">
.ic-custom {
    font-size: 24px;
    vertical-align: text-top;
}

.ic-custom-2 {
    font-size: 18px;
    vertical-align: text-top;
}

.navbar {
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 28px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 30px;
    }

    .select2-container .select2-selection--single {
        height: 30px;
        border-radius: 0;
        font-size: 14px;
        font-weight: 700;
    }
}

</style>