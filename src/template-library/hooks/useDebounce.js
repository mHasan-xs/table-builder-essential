import { useEffect, useRef } from "@wordpress/element";

/**
 * Enhanced debounce hook with improved search performance.
 *
 * @param {function} callback - The callback function to be debounced
 * @param {number} delay - The delay in milliseconds
 * @return {function} The debounced callback function
 */
const useDebounce = (callback, delay) => {
	const timeOutIdRef = useRef(null);
	const previousValueRef = useRef(null);
	const callbackRef = useRef(callback);

	// Update callback ref when callback changes
	useEffect(() => {
		callbackRef.current = callback;
	}, [callback]);

	useEffect(() => {
		return () => {
			if (timeOutIdRef.current) {
				clearTimeout(timeOutIdRef.current);
			}
		};
	}, []);

	const debounceCallback = (value) => {
		// Clear any existing timeout
		if (timeOutIdRef.current) {
			clearTimeout(timeOutIdRef.current);
		}

		// Immediate execution for empty values (clearing search)
		if (!value || value.trim() === '') {
			if (previousValueRef.current !== value) {
				callbackRef.current(value);
				previousValueRef.current = value;
			}
			return;
		}

		// Debounce non-empty values
		if (value !== previousValueRef.current) {
			timeOutIdRef.current = setTimeout(() => {
				callbackRef.current(value);
				previousValueRef.current = value;
			}, delay);
		}
	};

	return debounceCallback;
};

export default useDebounce;
