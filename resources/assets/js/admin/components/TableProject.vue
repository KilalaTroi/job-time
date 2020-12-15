<template>
  <table class="table">
    <thead>
      <slot name="columns">
        <tr width="66">
          <th>
            <div class="form-check">
              <label for="all" class="form-check-label">
                <input
                  id="all"
                  class="form-check-input"
                  type="checkbox"
                  @click="checkAll"
                  v-model="allCheck"
                />
                <span class="form-check-sign"></span>
                <span>
                  <slot></slot>
                </span>
              </label>
            </div>
          </th>
          <th
            v-for="(column, index) in columns"
            :key="index"
            :width="column.width"
            :class="column.class"
          >
            {{ column.value }}
          </th>
          <th width="110" class="text-center">
            {{ $ml.with("VueJS").get("txtAction") }}
          </th>
        </tr>
      </slot>
    </thead>
    <tbody>
      <tr v-for="(item, index) in projectData.data" :key="index">
        <td>
          <div class="form-check">
            <label :for="item.issue_id" class="form-check-label">
              <input
                :id="item.issue_id"
                class="form-check-input"
                type="checkbox"
                v-model="itemCheck"
                @change="checkItem"
                :value="item.issue_id"
              />
              <span class="form-check-sign"></span>
              <span>
                <slot></slot>
              </span>
            </label>
          </div>
        </td>
        <slot :row="item">
          <td
            v-for="(column, index) in columns"
            :key="index"
            :class="column.class"
          >
            <button
              v-if="checkProjectColumn(column)"
              @click="getItem(item.issue_id)"
              type="button"
              class="btn btn-xs btn-default mr-2"
              data-toggle="modal"
              data-target="#editProject"
              data-backdrop="static"
              data-keyboard="false"
            >
              <i class="fa fa-pencil" aria-hidden="true"></i>
            </button>
            <button
              v-if="checkIssueColumn(column)"
              @click="getItem(item.issue_id)"
              type="button"
              class="btn btn-xs btn-default mr-2"
              data-toggle="modal"
              data-target="#editIssue"
              data-backdrop="static"
              data-keyboard="false"
            >
              <i class="fa fa-pencil" aria-hidden="true"></i>
            </button>

            <span
              v-if="checkTypeColor(column)"
              :style="setBackground(itemValue(item, column))"
              :class="'cl-' + column.id"
              class="type-color"
            ></span>
            <span
              v-else
              :class="'cl-' + column.id"
              v-html="itemValue(item, column)"
            ></span>
          </td>
        </slot>
        <td class="text-center">
          <button
            @click="archiveItem({ id: item.issue_id, status: item.status })"
            type="button"
            class="btn btn-xs btn-second"
          >
            <i
              :class="archiveClass(item.status)"
              aria-hidden="true"
              title="archive"
            ></i>
          </button>
          <button
            @click="
              deleteItem({
                id: item.issue_id,
                msgText: $ml.with('VueJS').get('msgConfirmDelete'),
              })
            "
            type="button"
            class="btn btn-xs btn-danger ml-sm-2"
          >
            <i class="fa fa-trash" aria-hidden="true"></i>
          </button>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script>
import { mapGetters, mapActions } from "vuex";

export default {
  name: "table-project",
  computed: {
    ...mapGetters({
      columns: "projects/columns",
      projectData: "projects/data",
      itemValue: "table/itemValue",
      checkTypeColor: "table/checkTypeColor",
      setBackground: "table/setBackground",
    }),
  },
  data() {
    return {
      itemCheck: [],
      allCheck: false,
    };
  },
  methods: {
    ...mapActions({
      getItem: "projects/getItem",
      archiveItem: "projects/archiveItem",
      deleteItem: "projects/deleteItem",
    }),

    checkItem() {
      const lengthAll = this.projectData.data.length;
      const lengthChecked = this.itemCheck.length;
      if (lengthAll == lengthChecked) this.allCheck = true;
      else this.allCheck = false;
      this.$emit("check-item", this.itemCheck);
    },

    checkAll() {
      this.itemCheck = [];
      if (!this.allCheck) {
        for (const k in this.projectData.data) {
          this.itemCheck.push(this.projectData.data[k].issue_id);
        }
      }
      this.$emit("check-item", this.itemCheck);
    },

    checkProjectColumn(data) {
      return data.id === "project";
    },
    checkIssueColumn(data) {
      return data.id === "issue";
    },
    archiveClass(archive) {
      return archive === "archive" ? "fa fa-unlock" : "fa fa-archive";
    },
  },
  watch: {
		projectData: [
		{
      handler: function() {
        this.allCheck = false;
        this.itemCheck = [];
        this.$emit("check-item", this.itemCheck);
      },
      deep: true
		}
		]
	}
};
</script>
<style>
</style>