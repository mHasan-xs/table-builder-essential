import { __ } from '@wordpress/i18n';
import useContextLibrary from '@/template-library/hooks/useContextLibrary';
import EmptyIcon from '../icons/empty';

const Empty = () => {
	const { searchInput, filter } = useContextLibrary();

	// Generate dynamic message based on search context
	const getEmptyMessage = () => {
		const search = searchInput?.trim();

		if (search) {
			return __(`No table layouts found for "${search}". Try different search terms or browse all layouts.`, 'table-builder-block');
		}

		if (filter.category && filter.category !== 'all') {
			return __('No table layouts found in the selected category. Try browsing other categories or clear your filters.', 'table-builder-block');
		}

		return __("We couldn't find any table layouts matching your criteria. Try adjusting your search terms or filters.", 'table-builder-block');
	};


	return (
		<div className="table-builder-library-empty" >
			<EmptyIcon />
			<h4 className="table-builder-library-empty-title">{__('No Results Found', 'table-builder-block')}</h4>
			<p className='table-builder-library-empty-description'>{getEmptyMessage()}</p>
		</div>
	)
}

export default Empty