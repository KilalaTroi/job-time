<template>
  <div class="form-group">
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#itemCreate" data-backdrop="static" data-keyboard="false">
      <i class="fa fa-plus"></i>
      <slot name="title"></slot>
    </button>
  </div>
</template>
<script>
    export default {
        name: 'create-button'
    }
</script>