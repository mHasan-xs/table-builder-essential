import LinkIcon from "../icons/Link";
import PreviewIcon from "../icons/Preview";

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