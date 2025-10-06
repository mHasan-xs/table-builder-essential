import { Button, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState, useEffect, useCallback, useMemo } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import classNames from 'classnames';
import LazyImage from '../LazyImage';
import ExternalLink from '../common/ExternalLink';
import Download from '../icons/Download';
import PreviewIcon from '../icons/Preview';

const THUMBNAIL_SELECTOR = '.table-builder-library-list-item-inner-content-thumbnail:not(.is-loading)';
const CLASS_DISABLED = 'disabled';
const useIsEditor = () => {
	return useSelect((select) => {
		const blockEditorStore = select('core/block-editor');
		return blockEditorStore !== undefined;
	}, []);
};

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
	const isEditor = useIsEditor();

	useEffect(() => {
		if (!isImporting) return;

		const thumbnails = document.querySelectorAll(THUMBNAIL_SELECTOR);
		thumbnails.forEach(thumbnail => thumbnail.classList.add(CLASS_DISABLED));
	}, [isImporting]);

	const handleImport = useCallback(async () => {
		setIsImporting(true);
		try {
			await onPatternImport(pattern);
			// Update download count after successful import
			try {
				await onDownloadCount(pattern);
			} catch (downloadError) {
				// Don't block import if download count fails
				console.warn('Failed to update download count:', downloadError);
			}
		} finally {
			setIsImporting(false);
		}
	}, [pattern, onPatternImport, onDownloadCount]);

	// Open live preview in new tab
	const handlePreview = useCallback(() => {
		const previewUrl = pattern?.live_preview_url;
		if (previewUrl && previewUrl.trim() !== '') {
			// Ensure URL has protocol
			let url = previewUrl.trim();
			if (!url.startsWith('http://') && !url.startsWith('https://')) {
				url = 'https://' + url;
			}
			
			window.open(url, '_blank', 'noopener,noreferrer');
		}
	}, [pattern]);

	const thumbnailClass = classNames('table-builder-library-list-item-inner-content-thumbnail', { 'is-loading': isImporting });
	const listItemClass = classNames('table-builder-library-list-item', {
		'pro-inactive': isProPattern && !isProActive,
		'has-premium-badge': isProPattern
	});

	const titleClass = classNames('table-builder-library-list-item__title', { 'is-premium': isProPattern });
	const hasPreviewUrl = pattern?.live_preview_url && pattern.live_preview_url.trim() !== '';

	const renderActionButtons = () => {
		if (isProPattern && !isProActive) {
			return (
				<ExternalLink href="https://wpgutenkit.com/pricing/">
					{__('Requires GutenKit Blocks PRO', 'table-builder-block')}
				</ExternalLink>
			);
		}

		// On frontend - show both preview and import/download buttons
		if (!isEditor) {
			return (
				<div className="table-builder-pattern-actions">
					{hasPreviewUrl && (
						<Button
							onClick={handlePreview}
							className="table-builder-preview-button"
							icon={<PreviewIcon />}
							variant="primary"
						>
							{__('Preview', 'table-builder-block')}
						</Button>
					)}
				</div>
			);
		}

		// In editor - only show import button
		return (
			<Button
				onClick={handleImport}
				className="table-builder-import-button"
				icon={isImporting ? <Spinner className="importing-spinner" /> : <Download />}
				disabled={isImporting}
				variant="primary"
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
					{renderActionButtons()}
				</div>
			</div>
			<div className={titleClass}>
				<span className="item-title">{pattern?.title}</span>
			</div>
		</div>
	);
}

export default Pattern;