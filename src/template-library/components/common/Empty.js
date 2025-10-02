import { __ } from '@wordpress/i18n';
import useContextLibrary from '@/template-library/hooks/useContextLibrary';

/**
 * Renders a component to display when no results are found.
 *
 * @returns {JSX.Element} The Empty component.
 */
const Empty = () => {
	const { searchInput, filter } = useContextLibrary();
	
	// Generate dynamic message based on search context
	const getEmptyMessage = () => {
		if (searchInput && searchInput.trim() !== '') {
			return __(`No table layouts found for "${searchInput}". Try different search terms or browse all layouts.`, 'table-builder-block');
		} else if (filter.category && filter.category !== 'all' && filter.category.length > 0) {
			return __('No table layouts found in the selected category. Try browsing other categories or clear your filters.', 'table-builder-block');
		} else {
			return __('We couldn\'t find any table layouts matching your criteria. Try adjusting your search terms or filters.', 'table-builder-block');
		}
	};
	return (
		<div className="table-builder-library-empty" style={{ 
			textAlign: 'center', 
			color: '#64748b', 
			padding: '80px 40px 60px 40px',
			display: 'flex',
			flexDirection: 'column',
			alignItems: 'center',
			justifyContent: 'center',
			minHeight: '400px',
			background: 'linear-gradient(135deg, #f8fafc 0%, #ffffff 100%)',
			borderRadius: '16px',
			margin: '24px',
		}}>
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 400 157" style={{
				opacity: '0.8',
				width: '300px',
			}}>
				<path fill="#F1C20E" d="m79 66-6-13a54 54 0 0 0-3-5v-1c-3-9-12-13-21-14a37 37 0 0 0-20 5 45 45 0 0 0-15 11c-4 4-7 8-9 13-4 11-5 22-5 33 0 10 1 20 5 29 3 10 8 19 16 25 5 3 10 6 15 7a37 37 0 0 0 34-14l7-13c3-7 4-15 4-23a80 80 0 0 0 1-10c1-10 0-20-3-30m-19 67-8 10-1 1-1 1-1 1-1 1h-3a18 18 0 0 1-3-1c-4-2-8-5-11-9-4-5-7-11-10-18a83 83 0 0 1-5-21l-1-12c1-9 3-19 6-27a58 58 0 0 1 4-7l4-6 1-2h1v-1a33 33 0 0 1 3-1v-1h1v1l14 13a86 86 0 0 1 12 20 86 86 0 0 1 7 23 83 83 0 0 1 1 11 78 78 0 0 1-6 17 75 75 0 0 1-3 7"/>
				<path fill="#13151D" d="M50 144c-7-1-13-7-17-12-5-7-8-14-10-22-5-15-6-32-1-47 3-8 6-16 13-21l1-2h-4c-7 5-11 12-14 20-3 7-4 16-4 24-1 17 4 34 13 49 5 7 12 14 21 15l3-2c1-1 0-2-1-2m9-99-3 1-2 1c7 6 12 14 14 23l5-1c-2-9-7-18-14-24m17 35a1 1 0 0 0 0-1 8 8 0 0 0-1-1 1 1 0 0 0 0-1 1 1 0 0 0-1 0h-1l-2 1v1l1 3v2a1 1 0 0 0 0 1h2l2-1 1-1a9 9 0 0 0-1-3"/>
				<path fill="#49767E" d="M176 17c1-1 0-2-1-3l-5-4h-1l-2-2h-1l-8 13-1 1c-1 2-1 2-2 1a15 15 0 0 0-3-4l-1-1-2-1-1-1-2-1-3-1-4-1c-4-2-9-2-13-1l-5 2-9 7-4 6-2 2v2h-1c-4 12-8 23-9 35l-1 2v12l-1 3v4h1v13l1 4v4l1 2 1 4v3l2 4c2 10 12 14 20 15h13l3-1 4-1c6-4 11-8 15-14 5-7 7-14 8-22 3-12 4-23 4-35 0-9-1-17-3-26l1-3c5-5 8-11 11-17m-46 3h6l8 4 3 3 1 3-2 1c-3 1-6 2-10 1-3-1-6-4-7-7l1-5m23 72-1 8c-2 10-7 19-15 25-5 4-10 5-16 3h-1c-5-1-9-6-12-11l-1-1v-1c-5-13-5-26-4-40 1-12 4-24 7-35l3-7 5-7 1 2c1 7 7 15 15 16 4 1 8 2 13 0l7-2 2 1v12c0 12-1 25-3 37"/>
				<path fill="#9CC4A8" d="M204 9zm0 0zM268 37c-1-5-2-10-5-14s-7-8-11-10c-9-6-20-8-30-9-7 0-14 0-21 2-4 1-7 3-9 6l-2 2v2l-1 3a7 7 0 0 0 1 2l1 47a4478 4478 0 0 0 4 83h4l18-3 7-2 4-3 1-8-2-37V80c10-2 20-6 28-13 9-7 14-18 13-30m-11 11c-1 5-4 11-7 16a38 38 0 0 1-7 6l-3 2h-1l-2 1-1 1h-1l-1 1h-3l-2 1h-1a47 47 0 0 1-3 0 16 16 0 0 0-6 1l-3 3 2 50v13a6 6 0 0 1-1 1l-4 1-8 1a4458 4458 0 0 1-4-104 4410 4410 0 0 1 0-21v-1a7 7 0 0 1 0-2v-4l1-4 1-1h2-1 4l8 1a63 63 0 0 1 8 3c6 2 12 6 16 11 5 6 7 15 6 23"/>
				<path fill="#9CC4A8" d="M204 9zm0 0h-1a6 6 0 0 0 1 0zm0 0a6 6 0 0 1 1 0h-1zm1 0zm38 26-3-6-4-4c-4-2-9-2-13-2l-5 1-3 2v1l-2 2-1 2 3 30 4 1c5 1 11-2 15-5 4-2 6-6 8-10l1-12m-17 20-3-26h1a19 19 0 0 1 2 1 13 13 0 0 1 2 1 9 9 0 0 1 2 2c2 3 2 6 2 8l-2 10a18 18 0 0 1-3 3 28 28 0 0 1-1 1"/>
				<path fill="#13151D" d="M334 119c-1-9-6-17-12-24-7-9-15-16-20-26-3-5-5-11-3-17l3-6 4-3h1a26 26 0 0 1 1 0l1-1a29 29 0 0 1 2 0h2l2 1a13 13 0 0 1 3 3l4 2 7 1c2-1 7-2 5-4-4-7-15-9-24-9s-18 3-24 8c-3 3-4 6-4 9-1 4-1 8 1 11 2 8 8 16 14 22 6 8 13 15 17 23 3 6 4 13 3 19-1 4-4 9-8 12a29 29 0 0 1-5 4 27 27 0 0 1-3 1h-1a32 32 0 0 1-1 0h-1a36 36 0 0 1-1 0h-3a17 17 0 0 1-2-2l-3-3-3-2-6-1-6 1c-1 0-3 1-2 2 1 3 4 6 8 8 5 2 11 3 18 3 5 0 11 0 16-2s9-4 12-7c6-6 10-15 8-23"/>
				<path fill="#EA9194" d="M383 124c0-2-2-2-3-2l-3 1 2-2-2-1h-3l-4 1-2 2-1 1a57 57 0 0 0 0 13l1 3 3 1h5l1-1h1l3-1 2-7v-8m-10 14a2 2 0 0 1-1 0v-15h1l-1 1 1 1h1l-2 4 1 1 1 8h-1m5-9a68 68 0 0 0 0-2v-1 3"/>
				<path fill="#C76169" d="M384 120h-4v1h-6l-4 1-3 3v10l1 3 2 1h7l4-2 3-2 1-1 1-3v-7c0-2 0-4-2-4m-7 15h-3l-1-2a27 27 0 0 1 0-2 33 33 0 0 1-1-5v-2h7l1 4v7h-1z"/>
				<path fill="#EA9194" d="m397 2-2-1a91 91 0 0 0-13-1h-3l-1 2 1 1-2 1a390 390 0 0 0 1 71l-5 35 1 2 1 1h1l1-8a292 292 0 0 0 1 6h4l3-1 5-41 6-42 3-23-2-2m-8 15v-6a151 151 0 0 1 0 17 9 9 0 0 0-1 0l1-11m-5-5a1298 1298 0 0 0-1 10 353 353 0 0 1 0-3l1-7m-3 37 1-17 1 1-1 18-1 1v-3m8-5-3 23v-5l2-20 1-5 1-1v-7l1-1V13a151 151 0 0 0 0-8h4z"/>
				<path fill="#C76169" d="M398 2c-7-2-15-2-22-1l-3 2a3111 3111 0 0 1-3 108v1l1 1 1 1 2 1h7l3-1h1l-1-1 2-2h-1l1-1a4321 4321 0 0 1 11-84 4351 4351 0 0 1 3-23l-2-1m-11 43a4296 4296 0 0 0-8 64h-2a3135 3135 0 0 0 2-40l1-43V3h4a58 58 0 0 1 2 0 62 62 0 0 1 4 1s1 0 0 0h1a55 55 0 0 1 1 0h1a4359 4359 0 0 0-6 41"/>
			</svg>
			<h4 style={{
				color: '#1a202c',
				fontSize: '24px',
				fontWeight: '700',
				margin: '0 0 16px 0',
				fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
				letterSpacing: '-0.025em'
			}}>{__('No Results Found', 'table-builder-block')}</h4>
			<p style={{
				color: '#64748b',
				fontSize: '16px',
				lineHeight: '1.6',
				margin: '0',
				maxWidth: '400px',
				fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif'
			}}>{getEmptyMessage()}</p>
		</div>
	)
}

export default Empty