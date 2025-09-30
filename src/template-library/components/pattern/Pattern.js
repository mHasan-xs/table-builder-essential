import { Button, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import LazyImage from '../LazyImage';
import { useState, useEffect } from '@wordpress/element';
import classNames from 'classnames';
import ExternalLink from '../common/ExternalLink';
import Download from '../icons/Download';

/**
 * Renders a pattern component.
 *
 * @param {Object} props - The component props.
 * @param {Object} props.pattern - The pattern object.
 * @param {Function} props.onPatternImport - The function to handle pattern import.
 * @param {Function} props.onDownloadCount - The function to handle download count.
 * @returns {JSX.Element} The pattern component.
 */
function Pattern({ pattern, onPatternImport, onDownloadCount }) {
	const { useHasProActive } = window.tableBuilder.helpers;
	const isProActive = useHasProActive({ windowVariable: 'tableBuilder', hookName: 'tablebuilder.isProActive', cookieName: 'isTableBuilderValid', apiPath: 'tablebuilder' });
	const [patternImporting, setPatternImporting] = useState(false);
	useEffect(() => {
		if (patternImporting) {
			const thumbnails = document.querySelectorAll('.table-builder-library-list-item-inner-content-thumbnail:not(.is-loading)');
			thumbnails.forEach(thumbnail => {
				thumbnail.classList.add('disabled');
			})
		}
	}, [patternImporting])

	const thumbnailClass = classNames(
		'table-builder-library-list-item-inner-content-thumbnail',
		{ 'is-loading': patternImporting },
	);
	const listItemClass = classNames(
		'table-builder-library-list-item',
		{ 'pro-inactive': pattern?.package === 'pro' && !isProActive },
	);
	const titleClass = classNames(
		'table-builder-library-list-item__title',
		{ 'is-premium': pattern?.package === 'pro' },
	);

	return (
		<div className={listItemClass}>
			<div className={thumbnailClass}>
				<LazyImage src={pattern?.thumbnail} alt={pattern?.title} />
				<div className="table-builder-library-list-item-inner-content-overlay">
					{(pattern?.package === 'pro' && isProActive) &&
						<Button
							onClick={async () => {
								setPatternImporting(true);
								await onPatternImport(pattern);
								await onDownloadCount(pattern);
								setPatternImporting(false);
							}}
							className='table-builder-import-button'
							icon={patternImporting ? <Spinner className='importing-spinner' /> : <Download />}
							disabled={patternImporting ? true : false}>
							{patternImporting ? __('Importing', 'gutenkit-blocks-addon') : __('Import', 'gutenkit-blocks-addon')}
						</Button>
					}

					{(pattern?.package === 'pro' && !isProActive) &&
						<ExternalLink href="https://wpgutenkit.com/pricing/">
							{__('Requires GutenKit Blocks PRO', 'gutenkit-blocks-addon')}
						</ExternalLink>
					}

					{pattern?.package === 'free' &&
						<Button
							onClick={async () => {
								setPatternImporting(true);
								await onPatternImport(pattern);
								await onDownloadCount(pattern);
								setPatternImporting(false);
							}}
							className='table-builder-import-button'
							icon={patternImporting ? <Spinner className='importing-spinner' /> : <Download />}
							disabled={patternImporting ? true : false}>
							{patternImporting ? __('Importing', 'gutenkit-blocks-addon') : __('Import', 'gutenkit-blocks-addon')}
						</Button>
					}
				</div>
			</div>
			<div className={titleClass}>
				<span className='item-title'>{pattern?.title}</span>
			</div>
		</div>
	)
}

export default Pattern;