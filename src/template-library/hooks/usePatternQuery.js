import { useEffect, useState, useRef } from '@wordpress/element';
import useContextLibrary from './useContextLibrary';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';


const usePatternQuery = () => {
	const { templateType, dispatch, patterns, syncLibrary, searchInput, filter, patternsPage, payload } = useContextLibrary();
	const [loading, setLoading] = useState(false);
	const [hasMore, setHasMore] = useState(true);
	const loadMoreRef = useRef(null);
	const activeRequestRef = useRef(null); 
	const previousFiltersRef = useRef({
		category: filter.category,
		contentType: filter.contentType,
		sortedBy: filter.sortedBy,
		search: searchInput
	});

	// Check if filters changed to trigger immediate loading state
	useEffect(() => {
		const currentFilters = {
			category: filter.category,
			contentType: filter.contentType,
			sortedBy: filter.sortedBy,
			search: searchInput
		};

		// Check if any filter has changed
		const filtersChanged = Object.keys(currentFilters).some(
			key => currentFilters[key] !== previousFiltersRef.current[key]
		);

		if (filtersChanged) {
			setLoading(true);
			previousFiltersRef.current = currentFilters;
		}
	}, [filter.category, filter.contentType, filter.sortedBy, searchInput]);

	// Fetch patterns
	useEffect(() => {
		const patternFetch = async () => {
			try {
				if (activeRequestRef.current) {
					activeRequestRef.current.abort();
				}
				
				// Create new AbortController for this request
				const controller = new AbortController();
				activeRequestRef.current = controller;
				
				// Determine if this is a fresh load (page 1) or pagination (page > 1)
				const isFirstPage = patternsPage === 1;
				const shouldClearPatterns = syncLibrary || isFirstPage;
				
				// Ensure loading state is set (should already be set by filter change effect)
				setLoading(true);
				

				
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
				else if (isFirstPage) {
					dispatch({
						type: 'SET_PATTERNS',
						patterns: []
					});
					setHasMore(true);
				}
				
				// Optimize API parameters for search performance
				let queryParams = {
					page: patternsPage,
					per_page: searchInput ? 20 : 16
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
					const path = addQueryArgs('table-builder/v1/layout-manager-api/patterns', queryParams);
					const json = await apiFetch({ 
						path: path,
						method: 'GET',
						signal: controller.signal 
					});
					let filteredPatterns = json?.posts || [];
					
					// Handle pagination - check if this is first page or filter change
					const isFirstPageOrFilterChange = patternsPage === 1 || syncLibrary;
					
					if (isFirstPageOrFilterChange) {
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
					
					// Update pagination state - no need to increment page here since it's done in intersection observer
					if (json?.posts.length < queryParams.per_page) {
						setHasMore(false);
					}
				}
			} catch (error) {
				// Don't log errors for aborted requests (normal behavior)
				if (error.name !== 'AbortError') {
					console.error(`Error fetching patterns: ${error}`);
				}
			} finally {
				setLoading(false);
				if (activeRequestRef.current) {
					activeRequestRef.current = null;
				}
			}
		};

		// Always fetch patterns when filters change
		patternFetch();

		return () => {
			if (activeRequestRef.current) {
				activeRequestRef.current.abort();
				activeRequestRef.current = null;
			}
		};
	}, [templateType, searchInput, filter.category, filter.contentType, filter.sortedBy, syncLibrary, patternsPage]);

	// Handle sync completion separately to avoid race conditions
	useEffect(() => {
		if (syncLibrary && !loading) {
			// Reset sync state after a short delay to allow animation to show
			const timer = setTimeout(() => {
				dispatch({
					type: 'SET_SYNC_LIBRARY',
					syncLibrary: false
				});
			}, 300); // Show animation for 1 second

			return () => clearTimeout(timer);
		}
	}, [syncLibrary, loading, dispatch]);

	// Separate useEffect for intersection observer to handle infinite scroll
	useEffect(() => {
		const onIntersection = (items) => {
			const loaderItem = items[0];
			if (loaderItem.isIntersecting && hasMore && !loading && patterns.length > 0) {
				dispatch({
					type: "SET_PATTERNS_PAGE",
					patternsPage: patternsPage + 1
				});
			}
		};

		const observer = new IntersectionObserver(onIntersection, {
			rootMargin: '100px' 
		});
		
		if (loadMoreRef.current) {
			observer.observe(loadMoreRef.current);
		}

		return () => {
			if (observer) {
				observer.disconnect();
			}
		};
	}, [hasMore, loading, patterns.length, patternsPage]); 

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
