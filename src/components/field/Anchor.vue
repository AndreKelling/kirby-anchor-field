<template>
  <k-field
    class="k-doi-field"
    :label="label"
    :disabled="disabled"
    :help="help"
    :required="required"
    :when="when"
    @focus="onFocus"
    :counter="{ count: localValue?.length }"
  >
    <k-input
      :after="after"
      :before="before"
      :icon="icon"
      theme="field"
      type="text"
      :value="localValue"
      @input="onInput"/>
    <footer v-if="error" class="k-field-footer">
      <div class="k-help k-field-help k-text" style="color: var(--color-red-650)">
        <p>{{ error }}</p>
      </div>
    </footer>
  </k-field>
</template>

<script>
export default {
  props: {
    after: String,
    before: {
      type: String,
      default: "#"
    },
    disabled: Boolean,
    endpoints: Object,
    help: {
      type: String,
      default: 'Provided as an option for anchor navigation.',
    },
    label: String,
    required: Boolean,
    when: String,
    value: String,
    icon: {
      type: String,
      default: "anchor"
    },
  },
  data() {
    return {
      localValue: this.value ?? "",
      error: null,
      anchorOptions: [],
    };
  },
  watch: {
    value(val) {
      if (val !== this.localValue) {
        this.localValue = val ?? "";
      }
    },
  },
  created() {
    this.fetchAnchors();
    this.$panel.events.on("model.update", this.fetchAnchors);
  },
  beforeDestroy() {
    this.$panel.events.off("model.update", this.fetchAnchors);
  },
  methods: {
    async fetchAnchors() {
      if (!this.endpoints?.model) return;
      const pageId = this.endpoints.model.replace(/^\/pages\//, "");
      try {
        this.anchorOptions = await this.$api.get(`anchor-options/${pageId}`);
      } catch (e) {
        console.error(e);
      }
    },
    getBlockFieldValue() {
      let parent = this.$parent;
      while (parent) {
        const source = parent.value;
        if (source && typeof source === "object") {
          // check specifically for a title, headline or topline field
          const fill = source.title || source.headline || source.topline;
          if (fill && typeof fill === "string") return fill;
        }
        parent = parent.$parent;
      }
      return null;
    },
    sanitize(value) {
      // CSS and URL safe
      return value
        .toLowerCase()
        .replace(/\s+/g, "-")
        .replace(/[^a-z0-9\-_]/g, "")
        .replace(/^-+|-+$/g, "");
    },
    onFocus() {
      if (this.localValue) return;
      const fill = this.getBlockFieldValue();
      if (!fill) return;
      const sanitized = this.sanitize(fill);
      if (sanitized) {
        this.localValue = sanitized;
        this.$emit("input", sanitized);
      }
    },
    onInput(value) {
      if (value && /[^a-zA-Z0-9\-_]/.test(value)) {
        this.error = "Only letters, numbers, hyphens and underscores are allowed. Other Characters will be removed.";
      } else {
        this.error = null;
      }

      const sanitized = this.sanitize(value);

      if (sanitized.length > 0) {
        const existing = this.anchorOptions.map(o => o.value.replace(/^#/, ""));
        this.error = existing.includes(sanitized)
          ? "This anchor ID is already used by another block on this page."
          : null;
      }

      this.localValue = sanitized;
      this.$emit("input", sanitized);
    }
  }
}
</script>
