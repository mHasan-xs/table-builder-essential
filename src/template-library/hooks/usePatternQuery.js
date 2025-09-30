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
				
				// Add content type filter (all, free, pro)
				if (filter.contentType && filter.contentType !== 'all') {
					queryParams.type = filter.contentType;
				}
				
				if (templateType === 'patterns') {
					// Fetch patterns
					if (searchInput === '') {
						if (filter.category === 'all') {
							// Use local table-builder-essential API
							const path = addQueryArgs('table-builder/v1/layout-manager-api/patterns', queryParams);
							const json = await apiFetch({ 
								path: path,
								method: 'GET'
							});
							let filteredPatterns = json?.posts || [];
							
							// Apply frontend filtering for content type if API doesn't support it
							if (filter.contentType && filter.contentType !== 'all') {
								filteredPatterns = filteredPatterns.filter(pattern => {
									if (filter.contentType === 'pro') {
										return pattern.pro === true || pattern.is_pro === true || pattern.type === 'pro';
									} else if (filter.contentType === 'free') {
										return !pattern.pro && !pattern.is_pro && pattern.type !== 'pro';
									}
									return true;
								});
							}
							
							dispatch({
								type: 'SET_PATTERNS',
								patterns: [...patterns, ...filteredPatterns],
							});
							if (json?.posts.length < queryParams.per_page) {
								setHasMore(false);
							} else {
								dispatch({
									type: "SET_PATTERNS_PAGE",
									patternsPage: patternsPage + 1
								});
							}
						} else {
							queryParams.cat = filter.category;
							queryParams.page = 1;
							queryParams.per_page = 50;

							// Use local table-builder-essential API
							const path = addQueryArgs('table-builder/v1/layout-manager-api/patterns', queryParams);
							const json = await apiFetch({ 
								path: path,
								method: 'GET'
							});
							let filteredPatterns = json?.posts || [];
							
							// Apply frontend filtering for content type if API doesn't support it
							if (filter.contentType && filter.contentType !== 'all') {
								filteredPatterns = filteredPatterns.filter(pattern => {
									if (filter.contentType === 'pro') {
										return pattern.pro === true || pattern.is_pro === true || pattern.type === 'pro';
									} else if (filter.contentType === 'free') {
										return !pattern.pro && !pattern.is_pro && pattern.type !== 'pro';
									}
									return true;
								});
							}
							
							dispatch({
								type: 'SET_PATTERNS',
								patterns: filteredPatterns,
							});
							dispatch({
								type: "SET_PATTERNS_PAGE",
								patternsPage: 1
							});
						}
					} else {
						dispatch({
							type: 'SET_FILTER',
							filter: {}
						})
						queryParams.search = searchInput.toLowerCase();
						queryParams.page = 1;
						queryParams.per_page = 100;
						// Use local table-builder-essential API
						const path = addQueryArgs('table-builder/v1/layout-manager-api/patterns', queryParams);
						const json = await apiFetch({ 
							path: path,
							method: 'GET'
						});
						let filteredPatterns = json?.posts || [];
						
						// Apply frontend filtering for content type if API doesn't support it
						if (filter.contentType && filter.contentType !== 'all') {
							filteredPatterns = filteredPatterns.filter(pattern => {
								if (filter.contentType === 'pro') {
									return pattern.pro === true || pattern.is_pro === true || pattern.type === 'pro';
								} else if (filter.contentType === 'free') {
									return !pattern.pro && !pattern.is_pro && pattern.type !== 'pro';
								}
								return true;
							});
						}
						
						dispatch({
							type: 'SET_PATTERNS',
							patterns: filteredPatterns,
						});
						// setHasMore(false);
						dispatch({
							type: "SET_PATTERNS_PAGE",
							patternsPage: 1
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

		if (filter.category !== "all" || searchInput !== "" || (filter.contentType && filter.contentType !== "all")) {
			patternFetch();
		}
		const onIntersection = (items) => {
			const loaderItem = items[0];
			if (loaderItem.isIntersecting && hasMore && filter.category === 'all' && (!filter.contentType || filter.contentType === 'all')) {
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
	}, [templateType, searchInput, filter.category, filter.contentType, syncLibrary, patternsPage]);

	return { patterns, loading, loadMoreRef, hasMore };
};

export default usePatternQuery;
