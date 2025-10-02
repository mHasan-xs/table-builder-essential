	export const renderPattern = (index) => (
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