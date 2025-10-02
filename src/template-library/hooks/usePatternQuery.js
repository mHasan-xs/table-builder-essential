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
	const activeRequestRef = useRef(null); // For request deduplication

	// Fetch patterns
	useEffect(() => {
		const patternFetch = async () => {
			try {
				// Cancel previous request if still pending (request deduplication)
				if (activeRequestRef.current) {
					activeRequestRef.current.abort();
				}
				
				// Create new AbortController for this request
				const controller = new AbortController();
				activeRequestRef.current = controller;
				
				// Handle sync library - always clear and reset
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
				
				// Clear patterns when starting a new search/filter (page 1)
				if (patternsPage === 1 && !syncLibrary) {
					dispatch({
						type: 'SET_PATTERNS',
						patterns: []
					});
					setHasMore(true);
				}
				
				// Show loading only when patterns array is empty
				if (patterns && patterns.length === 0) {
					setLoading(true);
				}
				
				// Optimize API parameters for search performance
				let queryParams = {
					page: patternsPage,
					per_page: searchInput ? 20 : 16, // Load more results for search to reduce pagination
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
					// Use local table-builder-essential API with abort signal
					const path = addQueryArgs('table-builder/v1/layout-manager-api/patterns', queryParams);
					const json = await apiFetch({ 
						path: path,
						method: 'GET',
						signal: controller.signal // Add abort signal for request cancellation
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
						// Load more - append to existing patterns, but avoid duplicates
						const existingIds = patterns.map(p => p.id || p.ID);
						const newPatterns = filteredPatterns.filter(pattern => 
							!existingIds.includes(pattern.id || pattern.ID)
						);
						
						dispatch({
							type: 'SET_PATTERNS',
							patterns: [...patterns, ...newPatterns],
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
				// Don't log errors for aborted requests (normal behavior)
				if (error.name !== 'AbortError') {
					console.error(`Error fetching patterns: ${error}`);
				}
				setLoading(false);
			} finally {
				setLoading(false);
				// Clear the active request reference
				if (activeRequestRef.current) {
					activeRequestRef.current = null;
				}
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
			// Cancel any pending requests on cleanup
			if (activeRequestRef.current) {
				activeRequestRef.current.abort();
				activeRequestRef.current = null;
			}
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
		}
	}, [searchInput, filter.category, filter.contentType, filter.sortedBy]);

	return { patterns, loading, loadMoreRef, hasMore };
};

export default usePatternQuery;
