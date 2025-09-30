import LinkIcon from "../icons/Link";
import PreviewIcon from "../icons/Preview";

/**
 * Renders an external link component.
 *
 * @param {Object} props - The component props.
 * @param {string} props.href - The URL of the external link.
 * @param {ReactNode} props.children - The content of the link.
 * @param {string} [props.rel] - The rel attribute for the link.
 * @returns {JSX.Element} The rendered external link component.
 */
const ExternalLink = ({ href, icon = true, children, ...props }) => {
	const rel = props.rel ? `${props.rel} noreferrer noopener` : 'noreferrer noopener';

	return (
		<a
			{...props}
			className="table-builder-external-link"
			href={href}
			target="_blank"
			rel={rel}
		>
			{children}
			{icon ? <LinkIcon /> : <PreviewIcon />}

		</a>
	);
};

export default ExternalLink;