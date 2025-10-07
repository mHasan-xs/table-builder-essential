import { Button } from '@wordpress/components';
import { useEffect, useRef, useCallback } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import useContextLibrary from '../../hooks/useContextLibrary';
import useDebounce from '@/template-library/hooks/useDebounce';
import SearchBar from '../common/SearchBar ';
import SelectField from '../common/SelectField';
import Reload from '../icons/Reload';
import Close from '../icons/Close';
import SynclibraryTooltip from '../icons/SynclibraryTooltip';
import CloseTooltip from '../icons/CloseTooltip';

const SORT_OPTIONS = [
	{ value: 'recent', label: 'Recent' },
	{ value: 'popular', label: 'Popular' },
];

const useIsEditor = () => useSelect((select) => select('core/block-editor') !== undefined, []);

const LibraryHeader = () => {
	const { loadLibrary, templateType, dispatch, syncLibrary, filter } = useContextLibrary();
	const isEditor = useIsEditor();
	const activeRef = useRef(null);

	// Load/close library handler
	const handleLoadLibrary = useCallback(() => {
		dispatch({
			type: 'SET_LOAD_LIBRARY',
			loadLibrary: !loadLibrary
		});
	}, [dispatch, loadLibrary]);

	const handleEscapeKey = useCallback((event) => {
		if (event.key === 'Escape' || event.key === 'Esc') {
			handleLoadLibrary();
		}
	}, [handleLoadLibrary]);

	useEffect(() => {
		document.addEventListener('keydown', handleEscapeKey);
		return () => document.removeEventListener('keydown', handleEscapeKey);
	}, [handleEscapeKey]);

	// Active button 
	useEffect(() => {
		const container = activeRef.current;
		if (!container) return;

		const buttons = Array.from(container.querySelectorAll('.table-builder-library-menu-item'));
		const activeIndex = buttons.findIndex(el => el.classList.contains('is-active'));
		
		if (activeIndex === -1) return;
		const width = buttons[activeIndex].clientWidth + 2;
		const translateBefore = buttons
			.slice(0, activeIndex)
			.reduce((acc, el) => acc + el.clientWidth + 20, 0);

		container.style.setProperty('--width', `${width}px`);
		container.style.setProperty('--translate', `${translateBefore}px`);
	}, [templateType]);


	// Handle search change
	const debounceSearch = useDebounce((term) => {
		dispatch({
			type: 'SET_SEARCH_INPUT',
			searchInput: term || ''
		});
	}, 250);

	const handleSearchChange = useCallback((value) => {
		const cleanValue = value?.trim() || '';
		
		if (cleanValue && filter.category && filter.category !== 'all') {
			dispatch({
				type: 'SET_FILTER',
				filter: { ...filter, category: 'all' }
			});
		}
		
		debounceSearch(cleanValue);
	}, [debounceSearch, dispatch, filter]);

	// Handle search close
	const handleSearchClose = useCallback(() => {
		dispatch({ type: 'SET_SEARCH_INPUT', searchInput: '' });
		dispatch({ type: 'SET_KEY_WORDS', keyWords: '' });
		dispatch({ type: 'SET_PATTERNS_PAGE', patternsPage: 1 });

		if (filter.category !== 'all' && templateType === 'patterns') {
			dispatch({
				type: 'SET_FILTER',
				filter: { ...filter, category: 'all' }
			});
		}
	}, [dispatch, filter, templateType]);


	//  Handle sort change
	const handleSortChange = useCallback((val) => {
		dispatch({ type: 'SET_PATTERNS_PAGE', patternsPage: 1 });
		dispatch({
			type: 'SET_FILTER',
			filter: { ...filter, sortedBy: val }
		});
	}, [dispatch, filter]);


	// Sync library handler
	const handleSyncClick = useCallback(() => {
		if (syncLibrary) return;
		
		dispatch({ type: 'SET_PATTERNS', patterns: [] });
		dispatch({ type: 'SET_PATTERNS_PAGE', patternsPage: 1 });
		dispatch({
			type: 'SET_SYNC_LIBRARY',
			syncLibrary: true
		});
	}, [dispatch, syncLibrary]);
	

	return (
		<div className="interface-interface-skeleton__header table-builder-library-header">
			<div className="edit-post-header edit-site-header-edit-mode table-builder-library-header-content">
				<div className="table-builder-library-logo">
					Table Builder
				</div>
				<div className="table-builder-library-search">
					<div className="table-builder-library-select">
						<span>Sorted by:</span>
						<SelectField
							options={SORT_OPTIONS}
							value={filter?.sortedBy}
							onChange={handleSortChange}
							placeholder="Sort By"
							error={false}
						/>
					</div>
					<div>
						<SearchBar
							onChange={handleSearchChange}
							onClick={(e) => e.target.focus()}
							onClose={handleSearchClose}
							className="table-builder-library-search-input"
							placeholder={`Search ${templateType}...`}
						/>
					</div>
					{isEditor && (
						<>
							<div className="table-builder-library-icon">
								<Button
									variant="tertiary"
									icon={<Reload />}
									className={`table-builder-library-synchronize ${syncLibrary ? 'is-active' : ''}`}
									onClick={handleSyncClick}
									disabled={syncLibrary}
									title={syncLibrary ? 'Syncing...' : 'Sync Library'}
								/>
								<SynclibraryTooltip />
							</div>
							<span className="table-builder-library-separate"/>
							<div className="table-builder-library-icon">
								<Button
									onClick={handleLoadLibrary}
									variant="tertiary"
									icon={<Close />}
									className="table-builder-template-library__close"
								/>
								<CloseTooltip />
							</div>
						</>
					)}
				</div>
			</div>
		</div>
	);
};

export default LibraryHeader;