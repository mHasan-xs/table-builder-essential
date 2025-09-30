import { useEffect, useRef } from "@wordpress/element";

/**
 * Debounces a callback function with a specified delay.
 *
 * @param {function} callback - The callback function to be debounced
 * @param {number} delay - The delay in milliseconds
 * @return {function} The debounced callback function
 */
const useDebounce = (callback, delay) => {
	const timeOutIdRef = useRef(null);
	const previousValueRef = useRef(null);

	useEffect(() => {
		return () => {
			clearTimeout(timeOutIdRef.current);
		};
	}, []);

	const debounceCallback = (value) => {
		clearTimeout(timeOutIdRef.current);

		if (value !== previousValueRef.current) {
			timeOutIdRef.current = setTimeout(() => {
				callback(value);
				previousValueRef.current = value;
			}, delay);
		}
	};

	return debounceCallback;
};

export default useDebounce;
