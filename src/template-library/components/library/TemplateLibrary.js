import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { createPortal, useEffect, useCallback, useMemo } from '@wordpress/element';
import classnames from 'classnames';
import LibraryHeader from './LibraryHeader';
import LibraryBody from './LibraryBody';
import useContextLibrary from '../../hooks/useContextLibrary';

const SELECTORS = {
	mainEditor: '.interface-interface-skeleton__editor:not(.table-builder-template-library)',
	logo: '.edit-site-layout .edit-site-layout__hub',
	portal: '.interface-interface-skeleton'
};

const CLASS_HIDE = 'hide-editor';

function TemplateLibrary({ className }) {
	const { dispatch, loadLibrary } = useContextLibrary();

	const toggleLibrary = useCallback(() => {
		dispatch({
			type: 'SET_LOAD_LIBRARY',
			loadLibrary: !loadLibrary,
		});
	}, [dispatch, loadLibrary]);

	useEffect(() => {
		const mainEditor = document.querySelector(SELECTORS.mainEditor);
		const logo = document.querySelector(SELECTORS.logo);

		if (!mainEditor && !logo) return;

		const method = loadLibrary ? 'add' : 'remove';
		mainEditor?.classList[method](CLASS_HIDE);
		logo?.classList[method](CLASS_HIDE);
	}, [loadLibrary]);

	const portalContainer = useMemo(() =>
		document.querySelector(SELECTORS.portal),
		[]
	);

	const libraryClasses = useMemo(() => classnames('interface-interface-skeleton__editor', 'table-builder-template-library', className), [className]);

	return (
		<>
			<Button
				iconSize={16}
				onClick={toggleLibrary}
				className="table-builder-template-library-btn"
				variant="primary"
			>
				{__('Table Template Library', 'table-builder-block')}
			</Button>
			{loadLibrary && portalContainer && createPortal(
				<div className={libraryClasses}>
					<LibraryHeader />
					<LibraryBody />
				</div>,
				portalContainer
			)}
		</>
	);
}

export default TemplateLibrary;