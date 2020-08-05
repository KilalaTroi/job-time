<template>
    <ul class="nav nav-mobile-menu">
        <li class="nav-item">
            <router-link :to="{path: '/profile'}" class="nav-link">
                <i class="nc-icon nc-circle-09"></i> {{$ml.with('VueJS').get('mnProfile')}}
            </router-link>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/logout">
                <i class="nc-icon nc-button-power"></i> {{$ml.with('VueJS').get('mnLogOut')}}
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