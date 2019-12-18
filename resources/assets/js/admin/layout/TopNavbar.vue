<template>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <router-link :to="{path: '/'}" class="py-1 d-block d-sm-none">
                <div class="logo-img">
                    <img width="50" src="/images/logo.png" :alt="title">
                </div>
            </router-link>
            <p class="navbar-brand d-none d-sm-block"><i class="nc-icon nc-android mr-2 ic-custom"></i>Welcome {{ currentUser.name }}</p>
            <button type="button" class="navbar-toggler navbar-toggler-right" :class="{toggled: $sidebar.showSidebar}" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation" @click="toggleSidebar">
                <span class="navbar-toggler-bar burger-lines"></span>
                <span class="navbar-toggler-bar burger-lines"></span>
                <span class="navbar-toggler-bar burger-lines"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <router-link :to="{path: '/profile'}" class="nav-link">
                            <i class="nc-icon nc-circle-09 mr-2 ic-custom-2"></i> 
                            Profile
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <a href="/logout" class="nav-link">
                            <i class="nc-icon nc-button-power mr-2 ic-custom-2"></i> 
                            Log out
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</template>
<script>
export default {
    computed: {
        routeName() {
            const { name } = this.$route
            return this.capitalizeFirstLetter(name)
        }
    },
    data() {
        return {
            activeNotifications: false,
            userID: document.querySelector("meta[name='user-id']").getAttribute('content'),
            currentUser: {},
            title: 'Job Time',
        }
    },
    mounted() {
        this.fetch();
    },
    methods: {
        fetch() {
            let uri = '/data/users/' + this.userID;
            axios.get(uri).then((response) => {
                this.currentUser = response.data.user;
            });
        },
        capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1)
        },
        toggleNotificationDropDown() {
            this.activeNotifications = !this.activeNotifications
        },
        closeDropDown() {
            this.activeNotifications = false
        },
        toggleSidebar() {
            this.$sidebar.displaySidebar(!this.$sidebar.showSidebar)
        },
        hideSidebar() {
            this.$sidebar.displaySidebar(false)
        }
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