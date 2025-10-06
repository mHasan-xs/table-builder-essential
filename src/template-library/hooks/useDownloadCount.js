import { useCallback } from 'react';
import apiFetch from '@wordpress/api-fetch';

/**
 * Custom hook to update the download count of a pattern.
 *
 * @returns {Function} The function to update the download count.
 */
const useDownloadCount = () => {
	const updateDownloadCount = useCallback(async (patternID) => {
		if (!patternID) {
			return;
		}
		
		try {
			const response = await apiFetch({
				path: `/table-builder/v1/layout-manager-api/download-count/${patternID}`,
				method: 'POST',
			});
			return response;
		} catch (error) {
			throw error;
		}
	}, []);

	return updateDownloadCount;
};

export default useDownloadCount;