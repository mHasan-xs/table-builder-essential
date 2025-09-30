import { useCallback } from 'react';
import apiFetch from '@wordpress/api-fetch';

/**
 * Custom hook to update the download count of a pattern.
 *
 * @returns {Function} The function to update the download count.
 */
const useDownloadCount = () => {
	const updateDownloadCount = useCallback(async (patternID) => {
		try {
			const response = await fetch(`https://wpgutenkit.com/wp-json/gkit/v1/layout-manager-api/update-download-count/${patternID}`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
			});
			console.log("🚀 ~ useDownloadCount ~ response:", response)
			if (!response.ok) {
				throw new Error('Network response was not ok');
			}
		} catch (error) {
			console.error(error);
		}
	}, []);

	return updateDownloadCount;
};

export default useDownloadCount;