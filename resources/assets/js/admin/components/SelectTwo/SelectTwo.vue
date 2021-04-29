<template>
  <select>
    <slot></slot>
  </select>
</template>

<script>
export default {
  name: "select-2",
  props: ["options", "value", "allowClear", "placeholder"],
  data() {
    return {
      params: {},
    };
  },
  mounted: function () {
    const _this = this;
    _this.params.data = this.options;
    if (_this.allowClear) {
      _this.params.allowClear = _this.allowClear;
      _this.params.placeholder = "";
      if (_this.placeholder) _this.params.placeholder = _this.placeholder;
    }

    $(_this.$el).select2(_this.params).val(_this.value).trigger("change").on("change", function () {
        _this.$emit("input", _this.value);
    });
  },
  watch: {
    value: function (newValue, oldValue) {
      if (!(newValue == oldValue) && (newValue || newValue >= 0)) $(this.$el).val(newValue).trigger("change");
    },
    options: function (options) {
      const _this = this;
      _this.params.data = options;
      // update options
      $(this.$el).empty().select2(this.params);
      if (!$(".select2.no-disable-first-value").length) $('.select2 option[value="0"]').prop("disabled", true);
    },
  },
  destroyed: function () {
    $(this.$el).off().select2("destroy");
  },
};
</script>

<style lang="scss">
.select2-container {
  width: 100% !important;
}
.select2-container .select2-selection--single {
  height: 40px;
}
.select2-container--default
  .select2-selection--single
  .select2-selection__rendered {
  line-height: 40px;
}
.select2-container--default
  .select2-selection--single
  .select2-selection__arrow {
  height: 38px;
}
</style>