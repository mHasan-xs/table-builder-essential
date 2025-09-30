import { __ } from '@wordpress/i18n';
/**
 * Renders the favorite count component.
 *
 * @param {Object} props - The component props.
 * @param {number} props.count - The count of favorites.
 * @returns {JSX.Element} The favorite count component.
 */
export default function FavoriteCount({ count }) {
	return (
		<div className="table-builder-library__favorite">
			<span className="table-builder-library__favorite-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="15" viewBox="0 0 16 15" fill="none">
					<path d="M13.2238 1.69591C11.3466 0.544462 9.70828 1.00848 8.72408 1.74761C8.32046 2.05067 8.11872 2.2022 8 2.2022C7.88128 2.2022 7.67954 2.05067 7.27592 1.74761C6.29173 1.00848 4.65336 0.544462 2.77621 1.69591C0.312648 3.20706 -0.244796 8.19243 5.43767 12.3984C6.52 13.1995 7.06116 13.6 8 13.6C8.93884 13.6 9.48001 13.1995 10.5623 12.3984C16.2448 8.19243 15.6873 3.20706 13.2238 1.69591Z" stroke="#111722" strokeWidth="1.5" strokeLinecap="round" />
				</svg>
			</span>
			<span className="table-builder-library__favorite-text">{__(`Favorites ( ${count} )`, 'table-builder-block')}</span>
		</div>
	)
}