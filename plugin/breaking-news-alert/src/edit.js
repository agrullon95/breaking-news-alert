import { __ } from "@wordpress/i18n";
import { useBlockProps, RichText } from "@wordpress/block-editor";

export default function Edit({ attributes, setAttributes }) {
  const blockProps = useBlockProps();

  return (
    <RichText
      {...blockProps}
      tagName="div"
      value={attributes.message}
      onChange={(value) => setAttributes({ message: value })}
      placeholder={__("Enter breaking news...", "bna")}
    />
  );
}
