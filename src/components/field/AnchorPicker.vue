<template>
  <k-field
    class="k-anchorpicker-field"
    :disabled="disabled"
    :help="help"
    :label="label"
    :required="required"
  >
    <div v-if="entries.length" class="k-anchorpicker-entries">
      <div class="k-anchorpicker-entry-header">
        <div class="k-anchorpicker-handle-placeholder" />
        <k-label class="k-anchorpicker-item">
          {{ $t('anchor-picker.label') }}
        </k-label>
        <k-label class="k-anchorpicker-item">
          {{ $t('anchor-picker.anchor') }}
        </k-label>
        <k-label
          v-if="colors"
          class="k-anchorpicker-item k-anchorpicker-color-column"
        >
          {{ $t('anchor-picker.color') }}
        </k-label>
        <div class="k-anchorpicker-button-placeholder" />
      </div>
      <k-draggable
        :list="entries"
        @change="emit"
      >
        <div
          v-for="(entry, i) in entries"
          :key="entry._id"
          class="k-anchorpicker-entry"
        >
          <k-sort-handle class="k-anchorpicker-handle" />
          <k-input
            type="text"
            class="k-anchorpicker-item"
            :disabled="disabled"
            placeholder="Label"
            :value="entry.label"
            @input="update(i, 'label', $event)"
          />
          <k-input
            type="select"
            class="k-anchorpicker-item"
            :disabled="disabled"
            :options="anchorOptions"
            :value="entry.anchor"
            required
            empty="— Select anchor —"
            @input="update(i, 'anchor', $event)"
          />
          <component
            :is="'k-' + colorFieldProps.type + '-field'"
            v-if="colors"
            v-bind="colorFieldProps"
            class="k-anchorpicker-item k-anchorpicker-color-column"
            :disabled="disabled"
            :value="entry.color"
            @input="update(i, 'color', $event)"
          />
          <k-button
            icon="remove"
            :disabled="disabled"
            @click="remove(i)"
          />
        </div>
      </k-draggable>
      <div class="k-anchorpicker-entry-footer">
        <div class="k-anchorpicker-handle-placeholder" />
        <div class="k-anchorpicker-item k-help k-field-help k-text">

        </div>
        <div class="k-anchorpicker-item k-help k-field-help k-text">
          {{ $t('anchor-picker.save-hint') }}
        </div>
        <div
          v-if="colors"
          class="k-anchorpicker-item k-anchorpicker-color-column"
        />
        <div class="k-anchorpicker-button-placeholder" />
      </div>
    </div>
    <k-button icon="add" :disabled="disabled" @click="add">
      {{ $t('anchor-picker.add') }}
    </k-button>
  </k-field>
</template>

<script>
export default {
  props: {
    disabled: Boolean,
    endpoints: Object,
    help: String,
    label: String,
    required: Boolean,
    value: Array,
    colors: [Boolean, Array, Object],
  },
  data() {
    return {
      anchorOptions: [],
      entries: [],
      entriesLocal: [],
      nextId: 0,
    };
  },
  computed: {
    colorFieldProps() {
      const base = typeof this.colors === "object" ? this.colors : {};
      return {
        ...base,
        label: false,
        type: base.type || "color-palette",
      };
    }
  },
  watch: {
    value(val) {
      // for reverting action of changes in kirby!
      const norm = v => v ?? "";
      const incoming = Array.isArray(val) ? val : [];
      if (
        incoming.length === this.entries.length &&
        incoming.every((e, i) =>
          norm(e.label) === norm(this.entries[i].label) &&
          norm(e.anchor) === norm(this.entries[i].anchor) &&
          norm(e.color) === norm(this.entries[i].color)
        )
      ) {
        return;
      }

      this.entries = incoming.map(e => ({ ...e, _id: ++this.nextId }));
    },
    "endpoints.model"() {
      this.fetchAnchors();
    },
  },
  created() {
    this.entries = Array.isArray(this.value) ? this.value.map(e => ({ ...e, _id: ++this.nextId })) : [];
    this.entriesLocal = [...this.entries];
    this.fetchAnchors();
    this.$panel.events.on("model.update", this.fetchAnchors);
  },
  beforeDestroy() {
    this.$panel.events.off("model.update", this.fetchAnchors);
  },
  methods: {
    add() {
      this.entries.push({ label: "", anchor: "", color: "", _id: ++this.nextId });
      this.emit();
    },
    remove(i) {
      this.entries.splice(i, 1);
      this.emit();
    },
    update(i, key, val) {
      this.$set(this.entries[i], key, val);
      this.emit();
    },
    emit() {
      this.$emit("input", this.entries);
    },
    async fetchAnchors() {
      if (!this.endpoints?.model) return;
      const pageId = this.endpoints.model.replace(/^\/pages\//, "");
      try {
        this.anchorOptions = await this.$api.get(`anchor-options/${pageId}`);
      } catch (e) {
        console.error(e);
      }
    },
  },
};
</script>

<style>
.k-anchorpicker-entries {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}
.k-anchorpicker-entry,
.k-anchorpicker-entry-header,
.k-anchorpicker-entry-footer {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  margin-bottom: 2px;

  .k-label {
    color: var(--color-gray-700);
  }
}
.k-anchorpicker-handle {
  width: 2rem;
  height: 2.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-gray-500);
}
.k-anchorpicker-handle-placeholder {
  width: 2rem;
}
.k-anchorpicker-button-placeholder {
  width: 2.25rem;
}
.k-anchorpicker-item {
  flex: 1;

  &:first-of-type {
    flex: 0 0 180px;
  }
}
.k-anchorpicker-color-column {
  flex: 0 0 250px !important;
}
</style>
