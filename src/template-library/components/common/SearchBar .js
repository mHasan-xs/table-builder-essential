import useContextLibrary from '@/template-library/hooks/useContextLibrary';
import { useCallback } from '@wordpress/element';

/**
 * SearchBar component for displaying a search input field with an icon.
 *
 * @param {Object} props - The component props.
 * @param {Function} props.onChange - The callback function to handle input change.
 * @param {Function} props.onClick - The callback function to handle click event.
 * @param {Function} props.onMouseLeave - The callback function to handle mouse leave event.
 * @param {Function} props.onClose - The callback function to handle close event.
 * @param {string} props.className - The CSS class name for the search container.
 * @param {string} props.placeholder - The placeholder text for the search input field.
 * @returns {JSX.Element} The rendered SearchBar component.
 */
const SearchBar = ({ onChange, onClick, onMouseLeave, onClose, className, placeholder }) => {
	const { keyWords, dispatch } = useContextLibrary();
	/**
	 * Handles the input change event.
	 *
	 * @param {Object} event - The input change event object.
	 */
	const handleInputChange = useCallback((event) => {
		const input = event.target.value;
		dispatch({
			type: 'SET_KEY_WORDS',
			keyWords: input
		});

		// Call the onChange callback with the input value
		onChange(input);
	}, [onChange]);

	/**
	 * Clears the search input and triggers the onClose callback.
	 */
	const clearSearch = () => {
		dispatch({
			type: 'SET_KEY_WORDS',
			keyWords: ''
		});
		onClose();
	};

	return (
		<div className={`search-container ${className}`}>
			<input
				type="search"
				value={keyWords}
				onChange={handleInputChange}
				onClick={onClick}
				onMouseLeave={onMouseLeave}
				placeholder={placeholder}
			/>
			<span className="icon" onClick={clearSearch}>
				{keyWords === '' ? (
					// Search icon SVG
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
						<path d="M13 5c-3.3 0-6 2.7-6 6 0 1.4.5 2.7 1.3 3.7l-3.8 3.8 1.1 1.1 3.8-3.8c1 .8 2.3 1.3 3.7 1.3 3.3 0 6-2.7 6-6S16.3 5 13 5zm0 10.5c-2.5 0-4.5-2-4.5-4.5s2-4.5 4.5-4.5 4.5 2 4.5 4.5-2 4.5-4.5 4.5z"></path>
					</svg>
				) : (
					// Close icon SVG
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
						<path d="M12 13.06l3.712 3.713 1.061-1.06L13.061 12l3.712-3.712-1.06-1.06L12 10.938 8.288 7.227l-1.061 1.06L10.939 12l-3.712 3.712 1.06 1.061L12 13.061z"></path>
					</svg>
				)}
			</span>
		</div>
	);
};

export default SearchBar;
