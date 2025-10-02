import { Button } from '@wordpress/components';
import useContextLibrary from '../../hooks/useContextLibrary';
import { useEffect, useRef } from '@wordpress/element';
// import GutenkitLogo from '../icons/GutenkitLogo';
import Reload from '../icons/Reload';
import Close from '../icons/Close';
import useDebounce from '@/template-library/hooks/useDebounce';
import SearchBar from '../common/SearchBar ';
import SelectField from '../common/SelectField';

import SynclibraryTooltip from '../icons/SynclibraryTooltip';
import CloseTooltip from '../icons/CloseTooltip';
import { useSelect } from '@wordpress/data';

const useIsEditor = () => {
	return useSelect((select) => {
		const blockEditorStore = select('core/block-editor');
		return blockEditorStore !== undefined;
	}, []);
};



const LibraryHeader = () => {
	const { loadLibrary, templateType, dispatch, syncLibrary, filter, showSinglePage } = useContextLibrary();
	const isEditor = useIsEditor();

	useEffect(() => {
		document.addEventListener('keydown', function (event) {
			if (event.key === "Escape" || event.key === "Esc") {
				handleLoadLibrary();
			}
		});
	}, [])

	const activeRef = useRef(null);

	// active button style
	useEffect(() => {
		if (activeRef.current) {
			let button = Array.from(activeRef.current.querySelectorAll('.table-builder-library-menu-item'));
			let activeButton = button.findIndex((element) =>
				element.classList.contains('is-active')
			);
			if (activeButton !== -1) {
				let width = button[activeButton].clientWidth + 2;
				activeRef.current.style.setProperty(
					'--width',
					`${width}px`
				);

				let translateBefore = button
					.slice(0, activeButton)
					.reduce((acc, el) => acc + el.clientWidth + 20, 0);
				activeRef.current.style.setProperty(
					'--translate',
					`${translateBefore}px`
				);
			}
		}
	}, [templateType])


	const handleLoadLibrary = () => {
		dispatch({
			type: 'SET_LOAD_LIBRARY',
			loadLibrary: !loadLibrary
		})
	}

	// Optimized search with improved debouncing
	const doSearch = useDebounce((term) => {
		// Always update search input, even for empty values
		dispatch({
			type: 'SET_SEARCH_INPUT',
			searchInput: term || ''
		});
	}, 250); // Faster response for better UX

	const handleChange = (value) => {
		// Sanitize the value
		const cleanValue = value ? value.trim() : '';
		
		// When user starts searching, clear category selection to show it's global search
		if (cleanValue && cleanValue.length > 0 && filter.category && filter.category !== 'all') {
			dispatch({
				type: 'SET_FILTER',
				filter: {
					...filter,
					category: 'all'
				}
			});
		}
		
		doSearch(cleanValue);
	};

	const handleClose = () => {
		// Clear both search states to keep them synchronized
		dispatch({
			type: 'SET_SEARCH_INPUT',
			searchInput: ''
		});

		dispatch({
			type: 'SET_KEY_WORDS',
			keyWords: ''
		});

		// Always clear patterns when closing search to trigger fresh data load
		dispatch({
			type: 'SET_PATTERNS',
			patterns: [],
		});

		// Reset pagination
		dispatch({
			type: "SET_PATTERNS_PAGE",
			patternsPage: 1
		});

		// Reset category to 'All' when clearing search if it's not already 'all'
		if (filter.category && filter.category !== 'all' && templateType === 'patterns') {
			dispatch({
				type: 'SET_FILTER',
				filter: {
					...filter,
					category: 'all'
				}
			});
		}
	}

	const { sortedBy } = filter || {};
	const handleSelectChange = (val) => {
		// Clear existing patterns to trigger fresh data load with new sort
		dispatch({
			type: 'SET_PATTERNS',
			patterns: []
		});
		
		// Reset pagination when changing sort
		dispatch({
			type: 'SET_PATTERNS_PAGE',
			patternsPage: 1
		});
		
		// Update sort filter
		dispatch({
			type: 'SET_FILTER',
			filter: {
				...filter,
				sortedBy: val
			}
		});
	}


	const displayNone = showSinglePage ? { opacity: '0', visibility: 'hidden', cursor: 'none' } : { opacity: '1', visibility: 'visible', cursor: 'pointer' }
	return (
		<div className="interface-interface-skeleton__header table-builder-library-header">
			<div className="edit-post-header edit-site-header-edit-mode table-builder-library-header-content">
				<div className="table-builder-library-logo">
					Table Builder
				</div>
				<div className="table-builder-library-search">
					<div className="table-builder-library-select" style={displayNone}>
						<span>Sorted by:</span>
						<SelectField
							options={[
								{ value: 'recent', label: 'Recent' },
								{ value: 'popular', label: 'Popular' },
							]}
							value={sortedBy}
							onChange={handleSelectChange}
							placeholder="Sort By"
							error={false}
						/>
					</div>
					<div style={displayNone}>
						<SearchBar
							onChange={handleChange}
							onClick={(event) => event.target.focus()}
							onClose={handleClose}
							className="table-builder-library-search-input"
							placeholder={`Search ${templateType}...`}
						/>
					</div>
					{isEditor && (
						<>
							<div className="table-builder-library-icon" style={displayNone}>
								<Button
									variant='tertiary'
									icon={<Reload />}
									className={`table-builder-library-synchronize ${syncLibrary ? 'is-active' : ''}`}
									onClick={() => dispatch({
										type: 'SET_SYNC_LIBRARY',
										syncLibrary: true
									})}
								/>
								<SynclibraryTooltip />
							</div>
							<span className="table-builder-library-separate" style={displayNone}></span>
							<div className="table-builder-library-icon">
								<Button
									onClick={handleLoadLibrary}
									variant='tertiary'
									icon={<Close />}
									className="table-builder-template-library__close"
								/>
								<CloseTooltip />
							</div>
						</>
					)}

				</div>

			</div>
		</div >
	);
};

export default LibraryHeader;