import useContextLibrary from '../../hooks/useContextLibrary';
import { __ } from '@wordpress/i18n';
import useCategoryQuery from '@/template-library/hooks/useCategoryQuery';
import ContentLoader from '../common/ContentLoader';
import RadioField from '../common/RadioField';

/**
 * Filter component for the template library.
 * @returns {JSX.Element} The rendered Filter component.
 */
const Filter = () => {
	const { filter, dispatch, templateType, contentType, searchInput } = useContextLibrary();
	const { categories, loading } = useCategoryQuery();
	
	// Check if search is active to provide visual feedback
	const isSearchActive = searchInput && searchInput.trim().length > 0;
	/**
	 * Handles the category filter.
	 *
	 * @param {string} newCategory - The new category value.
	 * @returns {void}
	 */
	const handleCategoryFilter = (newCategory) => {
		// Clear search when changing category
		dispatch({
			type: 'SET_SEARCH_INPUT',
			searchInput: ''
		});
		dispatch({
			type: 'SET_KEY_WORDS',
			keyWords: ''
		});
		
		// Reset pagination when changing category
		dispatch({
			type: 'SET_PATTERNS_PAGE',
			patternsPage: 1
		});
		
		// Update category filter - let usePatternQuery handle clearing patterns
		dispatch({
			type: 'SET_FILTER',
			filter: {
				...filter,
				category: newCategory
			}
		});
	}


	const handleContentTypeFilter = (newContentType) => {
		// Reset patterns page when changing content type
		dispatch({
			type: 'SET_PATTERNS_PAGE',
			patternsPage: 1
		});
		// Update filter - let usePatternQuery handle clearing patterns
		dispatch({
			type: 'SET_FILTER',
			filter: {
				...filter,
				contentType: newContentType
			}
		});
	}


	return (
		<div className="table-builder-library-filter">
			{
				categories && categories.length === 0 && loading ? (
					<ContentLoader type='categories' />
				) : (
					<div className="table-builder-library-filter__inner">
						<div className="table-builder-library-filter-content-type">
							<h3 className="table-builder-library-filter-title">{__('Type', 'table-builder-blocks-addon')}</h3>
							<RadioField content={contentType} value={filter?.contentType} onChange={(value) => handleContentTypeFilter(value)} />
						</div>
						<h3 className="table-builder-library-filter-title">{__('Category', 'table-builder-blocks-addon')}</h3>
						<ul className="table-builder-library-filter-category-list">
							{
								categories.map((category, index) => {
									const isActive = category.slug === filter?.category;
									return (
										<li key={category.id || index} className="table-builder-library-filter-category-list-item">
											<button
												className={`table-builder-library-filter-category-list-title ${isActive ? 'is-active' : ''} ${isSearchActive ? 'search-disabled' : ''}`}
												onClick={() => !isSearchActive && handleCategoryFilter(category.slug)}
												aria-pressed={isActive}
												disabled={isSearchActive}
												title={isSearchActive ? __('Categories are disabled during search - search works across all categories', 'table-builder-essential') : `Filter by ${category.title} (${category.count} items)`}
											>
												<span className="category-title">{category.title}</span>
												<span className='list-title-count'>{category.count}</span>
											</button>
										</li>
									)
								})
							}
						</ul>
					</div>
				)
			}
		</div>
	)
}

export default Filter;