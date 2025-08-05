import { registerBlockType } from "@wordpress/blocks";
import Edit from "./edit";
import Save from "./save";
import "./style.css";

registerBlockType("bna/alert", {
  edit: Edit,
  save: Save,
});
