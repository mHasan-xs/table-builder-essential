import { Button } from '@wordpress/components';
import { __ } from "@wordpress/i18n";
import { createPortal, useEffect, useCallback } from '@wordpress/element';
import classnames from 'classnames';
import LibraryHeader from './LibraryHeader';
import LibraryBody from './LibraryBody';
import useContextLibrary from '../../hooks/useContextLibrary';

function TemplateLibrary({ className }) {
	const { dispatch, loadLibrary } = useContextLibrary();
	// const { gutenkitLogo } = window.tableBuilder.editorIcon;

	const toggleLibrary = useCallback(() => {
		dispatch({
			type: 'SET_LOAD_LIBRARY',
			loadLibrary: !loadLibrary,
		});
	}, [dispatch, loadLibrary]);

	useEffect(() => {
		const mainEditor = document.querySelector('.interface-interface-skeleton__editor:not(.table-builder-template-library)');
		const logo = document.querySelector('.edit-site-layout .edit-site-layout__hub');

		if (loadLibrary) {
			mainEditor?.classList.add('hide-editor');
			logo?.classList.add('hide-editor');
		} else {
			mainEditor?.classList.remove('hide-editor');
			logo?.classList.remove('hide-editor');
		}
	}, [loadLibrary]);

	return (
		<>
			<Button
				// icon={gutenkitLogo}
				iconSize={16}
				onClick={toggleLibrary}
				className="table-builder-template-library-btn"
				variant="primary"
			>
				{__('Table Template Library (Essential)', 'table-builder-block')}
			</Button>
			{loadLibrary && createPortal(
				<div className={classnames('interface-interface-skeleton__editor', 'table-builder-template-library', className)}>
					<LibraryHeader />
					<LibraryBody />
				</div>,
				document.querySelector('.interface-interface-skeleton')
			)}
		</>
	);
}

export default TemplateLibrary;
