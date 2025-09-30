import { useEffect, useState, useRef } from '@wordpress/element';
import useContextLibrary from './useContextLibrary';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
/**
 * Custom hook for fetching patterns from the template library.
 *
 * @returns {Object} An object containing patterns, loading state, loadMoreRef, and hasMore flag.
 */
const usePatternQuery = () => {
	const { templateType, dispatch, patterns, syncLibrary, searchInput, filter, patternsPage, payload } = useContextLibrary();
	const [loading, setLoading] = useState(false);
	const [hasMore, setHasMore] = useState(true);
	const loadMoreRef = useRef(null);

	// Fetch patterns
	useEffect(() => {
		if (syncLibrary) {
			dispatch({
				type: 'SET_PATTERNS',
				patterns: []
			});
			dispatch({
				type: "SET_PATTERNS_PAGE",
				patternsPage: 1
			});
			setHasMore(true);
		}
		const patternFetch = async () => {
			try {
				patterns && patterns.length === 0 && setLoading(true);
				let queryParams = {
					page: patternsPage,
					per_page: 16,
				};
				
				// Add search parameter
				if (searchInput && searchInput.trim() !== '') {
					queryParams.search = searchInput.trim();
				}
				
				// Add category filter
				if (filter.category && filter.category !== 'all') {
					queryParams.cat = filter.category;
				}
				
				// Add content type filter (all, free, pro)
				if (filter.contentType && filter.contentType !== 'all') {
					queryParams.type = filter.contentType;
				}
				
				// Add sorting parameter
				if (filter.sortedBy) {
					queryParams.sort = filter.sortedBy;
				}
				
				if (templateType === 'patterns') {
					// Single API call with all filters combined
					// Use local table-builder-essential API
					const path = addQueryArgs('table-builder/v1/layout-manager-api/patterns', queryParams);
					const json = await apiFetch({ 
						path: path,
						method: 'GET'
					});
					let filteredPatterns = json?.posts || [];
					
					// Handle pagination - check if this is first page or filter change
					const isFirstPageOrFilterChange = patternsPage === 1 || syncLibrary;
					
					if (isFirstPageOrFilterChange) {
						// First load or filter change - replace patterns
						dispatch({
							type: 'SET_PATTERNS',
							patterns: filteredPatterns,
						});
					} else {
						// Load more - append to existing patterns
						dispatch({
							type: 'SET_PATTERNS',
							patterns: [...patterns, ...filteredPatterns],
						});
					}
					
					// Update pagination state
					if (json?.posts.length < queryParams.per_page) {
						setHasMore(false);
					} else {
						dispatch({
							type: "SET_PATTERNS_PAGE",
							patternsPage: patternsPage + 1
						});
					}
				}
			} catch (error) {
				console.error(`Error fetching patterns: ${error}`);
				setLoading(false);
			} finally {
				setLoading(false);
			}
		};

		// Always fetch patterns when filters change
		patternFetch();
		
		const onIntersection = (items) => {
			const loaderItem = items[0];
			if (loaderItem.isIntersecting && hasMore && !loading) {
				patternFetch();
			}
		};

		const observer = new IntersectionObserver(onIntersection);
		if (observer && loadMoreRef.current) {
			observer.observe(loadMoreRef.current);
		}

		return () => {
			if (observer) observer.disconnect();
			dispatch({
				type: 'SET_SYNC_LIBRARY',
				syncLibrary: false
			})
		};
	}, [templateType, searchInput, filter.category, filter.contentType, filter.sortedBy, syncLibrary, patternsPage]);

	// Reset pagination when filters change
	useEffect(() => {
		if (patternsPage > 1) {
			dispatch({
				type: "SET_PATTERNS_PAGE",
				patternsPage: 1
			});
			setHasMore(true);
		}
	}, [searchInput, filter.category, filter.contentType, filter.sortedBy]);

	return { patterns, loading, loadMoreRef, hasMore };
};

export default usePatternQuery;
