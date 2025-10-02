import { Button, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState, useEffect, useCallback, useMemo } from '@wordpress/element';
import classNames from 'classnames';
import LazyImage from '../LazyImage';
import ExternalLink from '../common/ExternalLink';
import Download from '../icons/Download';

const THUMBNAIL_SELECTOR = '.table-builder-library-list-item-inner-content-thumbnail:not(.is-loading)';
const CLASS_DISABLED = 'disabled';

const useProStatus = () => {
	const { useHasProActive } = window.tableBuilder.helpers;
	return useHasProActive({
		windowVariable: 'tableBuilder',
		hookName: 'tablebuilder.isProActive',
		cookieName: 'isTableBuilderValid',
		apiPath: 'tablebuilder'
	});
};

const isPatternPro = (pattern) => {
	return pattern?.package === 'pro' ||
		pattern?.groups?.some(group => group.package === 'pro');
};

function Pattern({ pattern, onPatternImport, onDownloadCount }) {
	const isProActive = useProStatus();
	const [isImporting, setIsImporting] = useState(false);

	const isProPattern = useMemo(() => isPatternPro(pattern), [pattern]);

	useEffect(() => {
		if (!isImporting) return;

		const thumbnails = document.querySelectorAll(THUMBNAIL_SELECTOR);
		thumbnails.forEach(thumbnail => thumbnail.classList.add(CLASS_DISABLED));
	}, [isImporting]);

	const handleImport = useCallback(async () => {
		setIsImporting(true);
		try {
			await onPatternImport(pattern);
			await onDownloadCount(pattern);
		} finally {
			setIsImporting(false);
		}
	}, [pattern, onPatternImport, onDownloadCount]);

	const thumbnailClass = classNames('table-builder-library-list-item-inner-content-thumbnail', { 'is-loading': isImporting });

	const listItemClass = classNames('table-builder-library-list-item', {
		'pro-inactive': isProPattern && !isProActive,
		'has-premium-badge': isProPattern
	});

	const titleClass = classNames('table-builder-library-list-item__title', { 'is-premium': isProPattern });

	const renderActionButton = () => {
		if (isProPattern && !isProActive) {
			return (
				<ExternalLink href="https://wpgutenkit.com/pricing/">
					{__('Requires GutenKit Blocks PRO', 'table-builder-block')}
				</ExternalLink>
			);
		}

		return (
			<Button
				onClick={handleImport}
				className="table-builder-import-button"
				icon={isImporting ? <Spinner className="importing-spinner" /> : <Download />}
				disabled={isImporting}
			>
				{isImporting
					? __('Importing', 'table-builder-block')
					: __('Import', 'table-builder-block')
				}
			</Button>
		);
	};

	return (
		<div className={listItemClass}>
			<div className={thumbnailClass}>
				<LazyImage src={pattern?.thumbnail} alt={pattern?.title} />
				<div className="table-builder-library-list-item-inner-content-overlay">
					{renderActionButton()}
				</div>
			</div>
			<div className={titleClass}>
				<span className="item-title">{pattern?.title}</span>
			</div>
		</div>
	);
}

export default Pattern;