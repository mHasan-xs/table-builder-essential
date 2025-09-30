import { useEffect, useState, useRef } from '@wordpress/element';

const LazyImage = ({ src, alt }) => {
	const { libraryPlaceholderImage } = window.tableBuilder.helpers;
	const [imageSrc, setImageSrc] = useState(libraryPlaceholderImage);
	const [isIntersecting, setIsIntersecting] = useState(false);
	const imgRef = useRef(null);

	useEffect(() => {
		let observer;
		let image;

		const handleIntersection = (entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					setIsIntersecting(true);
					observer.unobserve(image);
				}
			});
		};

		// Create an IntersectionObserver and observe the image element
		if (typeof window !== 'undefined' && 'IntersectionObserver' in window) {
			observer = new IntersectionObserver(handleIntersection);
			image = imgRef.current;
			if (image) {
				observer.observe(image);
			}
		}

		// Load the image once it comes into the viewport
		if (isIntersecting) {
			const img = new Image();
			img.src = src;
			img.onload = () => {
				setImageSrc(src);
			};
		}

		return () => {
			if (observer && image) {
				observer.unobserve(image);
			}
		};
	}, [src, isIntersecting]);

	return <img className="lazy-image" src={imageSrc} alt={alt} ref={imgRef} />;
};

export default LazyImage;
