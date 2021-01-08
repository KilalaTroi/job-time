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
                <ul class="navbar-nav">
                    <li class="languages ml-auto nav-item mr-3">
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
import { mapGetters, mapActions } from "vuex"

export default {
    computed: {
        ...mapGetters({
            loginUser: 'loginUser',
            currentLang: 'currentLang',
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
            setCurrentLang: 'setCurrentLang',
            setCurrentTeam: 'setCurrentTeam',
            setReportNotify: 'setReportNotify',
        }),

        activeLanguage(language) {
            $('body').attr('class', '').addClass('language-' + this.currentLang);

            if ( this.currentLang === language )
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

    async created(){
        const _this = this, team = localStorage.getItem('team');
        _this.setCurrentLang(_this.$ml.current)

        const _translateTexts = _this.$ml.with("VueJS")
        _this.setTranslateTexts(_translateTexts)

        const userID = document.querySelector("meta[name='user-id']").getAttribute('content')

        let teamDefault = document.querySelector("meta[name='team-default']").getAttribute('content').split(',')[0];
        if(userID == 1) if(team) teamDefault = team
        _this.setCurrentTeam(teamDefault)

        // await _this.setLoginUser(userID)

        _this.setReportNotify(this.currentTeam.id)

        $(document).on("click", ".languages button", function () {
           _this.setCurrentLang(_this.$ml.current)
        });
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

</style>