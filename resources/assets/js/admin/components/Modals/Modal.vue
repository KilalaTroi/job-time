<template>
  <div class="modal fade">
    <div class="modal-dialog modal-dialog-centered" :class="sizeClasses">
      <div class="modal-content bg-light">
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">
            <slot name="title"></slot>
          </h4>
          <button type="button" class="btn btn-xs btn-danger ml-2" data-dismiss="modal" @click="$emit('reset-validation')">
            <i aria-hidden="true" class="fa fa-times"></i>
          </button>
        </div>

        <!-- Modal body -->
        <div class="modal-body">
          <hr>
          <slot></slot>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
    export default {
        name: 'modal',
        props: {
            sizeClasses: {
                type: [String, Object, Array],
                description: "Modal content css classes"
            }
        }
    }
</script>