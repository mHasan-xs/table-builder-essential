import { useEffect, useCallback, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { dispatch as dataDispatch, select } from '@wordpress/data';
import { parse } from '@wordpress/blocks';
import apiFetch from '@wordpress/api-fetch';
import useContextLibrary from '../../hooks/useContextLibrary';
import usePatternQuery from '@/template-library/hooks/usePatternQuery';
import useDownloadCount from '@/template-library/hooks/useDownloadCount';
import updateTemplateBlocks from '@/template-library/includes/updateTemplateBlocks';
import Filter from './Filter';
import Pattern from './Pattern';
import Empty from '../common/Empty';
import ContentLoader from '../common/ContentLoader';

const API_SETTINGS_PATH = '/gutenkit/v1/settings';
const BLOCK_EDITOR_STORE = 'core/block-editor';

const Patterns = () => {
	const { dispatch, imageImportType, filter } = useContextLibrary();
	const { patterns, loading, loadMoreRef, hasMore } = usePatternQuery();
	const updateDownloadCount = useDownloadCount();

	const { insertBlocks, insertAfterBlock, replaceBlocks } = dataDispatch(BLOCK_EDITOR_STORE);
	const { getSelectedBlockClientId } = select(BLOCK_EDITOR_STORE);

	useEffect(() => {
		apiFetch({ path: API_SETTINGS_PATH })
			.then((data) => {
				const remoteImagePermission = data.settings.remote_image.status === 'active' 
					? 'upload' 
					: '';
				
				dispatch({
					type: 'SET_IMAGE_IMPORT_TYPE',
					imageImportType: remoteImagePermission
				});
			})
			.catch(console.error);
	}, [dispatch]);

	const insertPattern = useCallback((content) => {
		const selectedBlockClientId = getSelectedBlockClientId();

		if (selectedBlockClientId) {
			insertAfterBlock(selectedBlockClientId);
			const newSelectedBlockClientId = getSelectedBlockClientId();
			replaceBlocks(newSelectedBlockClientId, content);
		} else {
			insertBlocks(content);
		}
	}, [getSelectedBlockClientId, insertAfterBlock, replaceBlocks, insertBlocks]);

	const handlePatternImport = useCallback(async (pattern) => {
		const content = parse(pattern.content);
		
		const processedContent = imageImportType === 'upload'
			? await updateTemplateBlocks(content)
			: content;

		insertPattern(processedContent);

		dispatch({
			type: 'SET_LOAD_LIBRARY',
			loadLibrary: false
		});
	}, [imageImportType, insertPattern, dispatch]);

	const handleDownloadCount = useCallback(async (pattern) => {
		await updateDownloadCount(pattern.ID);
	}, [updateDownloadCount]);

	const showLoader = useMemo(() => 
		patterns.length === 0 && loading,
		[patterns.length, loading]
	);

	const showEmpty = useMemo(() => 
		patterns.length === 0 && !loading,
		[patterns.length, loading]
	);

	const renderPatterns = () => {
		// Show skeleton loader during initial load or filter transitions
		if (showLoader) {
			return <ContentLoader type="patterns" />;
		}

		return patterns.map((pattern, index) => {
			const uniqueKey = pattern?.id || pattern?.ID || `pattern-${index}`;
			return (
				<Pattern
					key={uniqueKey}
					pattern={pattern}
					onPatternImport={handlePatternImport}
					onDownloadCount={handleDownloadCount}
				/>
			);
		});
	};

	return (
		<>
			<Filter />
			<div className="table-builder-library-list table-builder-pattern-part">
				<div className="table-builder-pattern">
					{renderPatterns()}
				</div>
				{hasMore && <button className="has-more-data" ref={loadMoreRef} />}
				{showEmpty && <Empty />}
			</div>
		</>
	);
};

export default Patterns;