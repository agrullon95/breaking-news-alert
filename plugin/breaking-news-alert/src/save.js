import { useBlockProps, RichText } from "@wordpress/block-editor";

export default function Save({ attributes }) {
  const blockProps = useBlockProps.save();

  return (
    <RichText.Content
      {...blockProps}
      tagName="div"
      value={attributes.message}
    />
  );
}
