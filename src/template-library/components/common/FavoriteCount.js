import { __ } from '@wordpress/i18n';
import FavoriteIcon from '../icons/favorite';

export default function FavoriteCount({ count }) {
	return (
		<div className="table-builder-library__favorite">
			<span className="table-builder-library__favorite-icon">
				<FavoriteIcon />
			</span>
			<span className="table-builder-library__favorite-text">{__(`Favorites ( ${count} )`, 'table-builder-block')}</span>
		</div>
	)
}