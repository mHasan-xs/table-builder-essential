import useContextLibrary from '@/template-library/hooks/useContextLibrary';
import { useCallback } from '@wordpress/element';
import { SearchIcon, CloseIcon } from '../icons/search';


const SearchBar = ({ onChange, onClick, onMouseLeave, onClose, className, placeholder }) => {
	const { keyWords, dispatch } = useContextLibrary();

	const sanitizeInput = (input) => {
		const sanitized = input
			.replace(/<[^>]*>/g, '')
			.replace(/[<>&"']/g, '')
			.substring(0, 100)
			.trim();
		
		return sanitized;
	};


	const handleInputChange = useCallback((event) => {
		const rawInput = event.target.value;
		const sanitizedInput = sanitizeInput(rawInput);
		
		dispatch({
			type: 'SET_KEY_WORDS',
			keyWords: sanitizedInput
		});

		// Call the onChange callback with the sanitized input value
		onChange(sanitizedInput);
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
				{keyWords === '' ? <SearchIcon /> : <CloseIcon />}
			</span>
		</div>
	);
};

export default SearchBar;
