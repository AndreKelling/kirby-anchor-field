import Anchor from "./components/field/Anchor.vue"
import AnchorPicker from "./components/field/AnchorPicker.vue"

panel.plugin("andrekelling/kirby-anchor-field", {
  fields: {
    "anchor": Anchor,
    "anchorpicker": AnchorPicker
  }
})
