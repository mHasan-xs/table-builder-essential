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

	// new code 
	const doSearch = useDebounce((term) => {
		dispatch({
			type: 'SET_SEARCH_INPUT',
			searchInput: term
		})
	}, 500);

	const handleChange = (value) => {
		doSearch(value);
	};

	const handleClose = () => {
		if (filter.category !== '' && templateType === 'patterns') {
			dispatch({
				type: 'SET_FILTER',
				filter: {
					category: 'all'
				}
			})

			// change search input to action name
			dispatch({
				type: 'SET_SEARCH_INPUT',
				searchInput: ''
			})

			dispatch({
				type: 'SET_PATTERNS',
				patterns: [],
			});
		}
	}

	const { sortedBy } = filter || {};
	const handleSelectChange = (val) => {
		dispatch({
			type: 'SET_FILTER',
			filter: {
				...filter,
				sortedBy: val
			}
		})
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