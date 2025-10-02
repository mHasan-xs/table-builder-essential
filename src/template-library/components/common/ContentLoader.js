import classnames from 'classnames';
export default function ContentLoader({ type }) {
	const itemsToRender = 20;
	const container = classnames('loader-container', {
		load: type === 'categories',
		template: type === 'patterns',
	});
	
	const renderItem = (index) => (
		<svg
			key={index}
			className="loader"
			xmlns="http://www.w3.org/2000/svg"
			preserveAspectRatio="xMidYMid meet"
			viewBox="0 0 438 600"
			fill="none">
			<defs>
				<linearGradient id="gradient1" x1="0%" y1="0%" x2="100%" y2="0%" gradientTransform="translate(-2 0)">
					<stop offset="0%" style={{ stopColor: '#D7D8DD', stopOpacity: 1 }}></stop>
					<stop offset="50%" style={{ stopColor: '#E4E5EA', stopOpacity: 1 }}></stop>
					<stop offset="100%" style={{ stopColor: '#D7D8DD', stopOpacity: 1 }}></stop>
					<animateTransform attributeName="gradientTransform" type="translate" values="-2 0; 0 0; 2 0" dur="1.1s" repeatCount="indefinite"></animateTransform>
				</linearGradient>
			</defs>
			<rect width="438" height="600" fill="white" />
			<rect x="10" y="10" width="418" height="530" fill="url(#gradient1)" />
			<rect x="30" y="558" width="200" height="8" rx="4" fill="url(#gradient1)" />
			<rect x="30" y="576" width="120" height="6" rx="3" fill="url(#gradient1)" />
			<circle cx="393" cy="570" r="15" fill="url(#gradient1)" />
		</svg>
	);

	const renderCategory = (index) => (
		<svg
			key={index}
			className="loader category"
			xmlns="http://www.w3.org/2000/svg"
			preserveAspectRatio="xMidYMid meet"
			viewBox="0 0 260 40"
			fill="none"
		>
			<defs>
				<linearGradient id="gradient2" x1="0%" y1="0%" x2="100%" y2="0%" gradientTransform="translate(-2 0)">
					<stop offset="0%" style={{ stopColor: '#D7D8DD', stopOpacity: 1 }} />
					<stop offset="50%" style={{ stopColor: '#E4E5EA', stopOpacity: 1 }} />
					<stop offset="100%" style={{ stopColor: '#D7D8DD', stopOpacity: 1 }} />
					<animateTransform attributeName="gradientTransform" type="translate" values="-2 0; 0 0; 2 0" dur="1.1s" repeatCount="indefinite" />
				</linearGradient>
			</defs>
			<rect width="260" height="40" rx="4" fill="url(#gradient2)" />
			<rect x="16" y="16" width="140" height="8" rx="4" fill="url(#gradient2)" />
			<rect x="230" y="15" width="14" height="10" rx="5" fill="url(#gradient2)" />
		</svg>
	);

	const renderPattern = (index) => (
		<svg
			key={index}
			className="loader"
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 362 270"
			fill="none"
			preserveAspectRatio="xMidYMid meet"
		>
			<defs>
				<linearGradient id="gradient3" x1="0%" y1="0%" x2="100%" y2="0%" gradientTransform="translate(-2 0)">
					<stop offset="0%" style={{ stopColor: '#D7D8DD', stopOpacity: 1 }} />
					<stop offset="50%" style={{ stopColor: '#E4E5EA', stopOpacity: 1 }} />
					<stop offset="100%" style={{ stopColor: '#D7D8DD', stopOpacity: 1 }} />
					<animateTransform attributeName="gradientTransform" type="translate" values="-2 0; 0 0; 2 0" dur="1.1s" repeatCount="indefinite" />
				</linearGradient>
			</defs>
			<rect width="362" height="270" fill="white" />
			<rect x="10" y="10" width="342" height="220" fill="url(#gradient3)" />
			<rect x="111" y="246" width="140" height="8" rx="4" fill="url(#gradient3)" />
		</svg>
	);

	// Render the items based on the type
	const renderItems = () => {
		if (type === 'pages') {
			return Array.from({ length: itemsToRender }, (_, index) => renderItem(index));
		} else if (type === 'patterns') {
			return Array.from({ length: itemsToRender }, (_, index) => renderPattern(index));
		} else if (type === 'categories') {
			return Array.from({ length: itemsToRender }, (_, index) => renderCategory(index));
		} 
		return null;
	};

	return <div className={container}>{renderItems()}</div>;
}
