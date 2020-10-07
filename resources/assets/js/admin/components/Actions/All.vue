<template>
  <div>
    <button
      @click="getItem(dataItem.id)"
      type="button"
      class="btn btn-xs btn-default"
      data-toggle="modal"
      data-target="#itemDetail"
      data-backdrop="static"
      data-keyboard="false"
    >
      <i class="fa fa-pencil" aria-hidden="true"></i>
    </button>
    <button type="button" class="btn btn-xs btn-danger ml-sm-2" @click="deleteItem($ml.with('VueJS').get('msgConfirmDelete'))">
      <i class="fa fa-trash" aria-hidden="true"></i>
    </button>
  </div>
</template>

<script>
export default {
  name: "action",
  props: ["dataPath", "dataItem"],

  methods: {
    deleteItem(msgText) {
      const requestData = {
        'id' : this.dataItem.id,
        'msgText': msgText
      };
      return this.$store.dispatch(this.dataPath + "/deleteItem", requestData);
    },
    getItem() {
      return this.$store.dispatch(this.dataPath + "/getItem", this.dataItem.id);
    },
  }
};
</script>