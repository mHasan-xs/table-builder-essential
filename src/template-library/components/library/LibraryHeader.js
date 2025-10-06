import { Button } from '@wordpress/components';
import { useEffect, useRef, useCallback, useMemo } from '@wordpress/element';
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

const useIsEditor = () => {
	return useSelect((select) => {
		const blockEditorStore = select('core/block-editor');
		return blockEditorStore !== undefined;
	}, []);
};

const LibraryHeader = () => {
	const { loadLibrary, templateType, dispatch, syncLibrary, filter, showSinglePage } = useContextLibrary();
	const isEditor = useIsEditor();
	const activeRef = useRef(null);

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

	const doSearch = useDebounce((term) => {
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
		
		doSearch(cleanValue);
	}, [doSearch, dispatch, filter]);

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

	const handleSortChange = useCallback((val) => {
		dispatch({ type: 'SET_PATTERNS_PAGE', patternsPage: 1 });
		dispatch({
			type: 'SET_FILTER',
			filter: { ...filter, sortedBy: val }
		});
	}, [dispatch, filter]);

	const handleSyncClick = useCallback(() => {
		// Don't allow sync if already syncing
		if (syncLibrary) return;
		
		// Reset patterns to force a fresh fetch
		dispatch({ type: 'SET_PATTERNS', patterns: [] });
		dispatch({ type: 'SET_PATTERNS_PAGE', patternsPage: 1 });
		
		// Trigger sync
		dispatch({
			type: 'SET_SYNC_LIBRARY',
			syncLibrary: true
		});
	}, [dispatch, syncLibrary]);
	

	const displayStyle = useMemo(() => 
		showSinglePage 
			? { opacity: '0', visibility: 'hidden', cursor: 'none' }
			: { opacity: '1', visibility: 'visible', cursor: 'pointer' },
		[showSinglePage]
	);

	return (
		<div className="interface-interface-skeleton__header table-builder-library-header">
			<div className="edit-post-header edit-site-header-edit-mode table-builder-library-header-content">
				<div className="table-builder-library-logo">
					Table Builder
				</div>
				<div className="table-builder-library-search">
					<div className="table-builder-library-select" style={displayStyle}>
						<span>Sorted by:</span>
						<SelectField
							options={SORT_OPTIONS}
							value={filter?.sortedBy}
							onChange={handleSortChange}
							placeholder="Sort By"
							error={false}
						/>
					</div>
					<div style={displayStyle}>
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
							<div className="table-builder-library-icon" style={displayStyle}>
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
							<span className="table-builder-library-separate" style={displayStyle} />
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