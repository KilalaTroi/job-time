<template>
    <ul class="nav nav-mobile-menu">
        <!-- <base-dropdown>
            <template slot="title">
                <i class="fa fa-globe"></i>
                <b class="caret"></b>
                <span class="notification">5 Notifications</span>
            </template>
            <a class="dropdown-item" href="#">Notification 1</a>
            <a class="dropdown-item" href="#">Notification 2</a>
            <a class="dropdown-item" href="#">Notification 3</a>
            <a class="dropdown-item" href="#">Notification 4</a>
            <a class="dropdown-item" href="#">Another notification</a>
        </base-dropdown>
        <li class="nav-item">
            <a href="#" class="nav-link">
                <i class="nc-icon nc-zoom-split hidden-lg-up"></i>
                <span class="d-lg-none">Search</span>
            </a>
        </li>
        <base-dropdown title="Dropdown">
            <a class="dropdown-item" href="#">Action</a>
            <a class="dropdown-item" href="#">Another action</a>
            <a class="dropdown-item" href="#">Something</a>
            <a class="dropdown-item" href="#">Something else here</a>
            <div class="divider"></div>
            <a class="dropdown-item" href="#">Separated link</a>
        </base-dropdown>
        <li class="nav-item">
            <a class="nav-link" to="/profile">
                <span class="no-icon">Profile</span>
            </a>
        </li> -->
        <li class="nav-item">
            <router-link :to="{path: '/profile'}" class="nav-link">
                <i class="nc-icon nc-circle-09"></i> Profile
            </router-link>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/logout">
                <i class="nc-icon nc-button-power"></i> Log out
            </a>
        </li>
    </ul>
</template>
<script>
export default {
    name: 'mobile-menu'
}
</script>
<style>
</style>