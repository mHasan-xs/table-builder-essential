	export const renderCategory = (index) => (
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