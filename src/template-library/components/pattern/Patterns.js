import useContextLibrary from "../../hooks/useContextLibrary";
import Filter from "./Filter";
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { dispatch as dataDispatch, select } from '@wordpress/data';
import { parse } from '@wordpress/blocks';
import Pattern from "./Pattern";
import Empty from "../common/Empty";
import updateTemplateBlocks from "@/template-library/includes/updateTemplateBlocks";
import apiFetch from '@wordpress/api-fetch';
import usePatternQuery from "@/template-library/hooks/usePatternQuery";
import ContentLoader from "../common/ContentLoader";
import useDownloadCount from "@/template-library/hooks/useDownloadCount";

/**
 * Renders the Patterns component.
 *
 * @returns {JSX.Element} The rendered Patterns component.
 */
const Patterns = () => {
	const { dispatch, searchInput, imageImportType, filter } = useContextLibrary();
	const { patterns, loading, loadMoreRef, hasMore } = usePatternQuery();
	const { insertBlocks, insertAfterBlock, replaceBlocks } = dataDispatch('core/block-editor');
	const { getSelectedBlockClientId } = select('core/block-editor');
	const updateDownloadCount = useDownloadCount();

	useEffect(() => {
		apiFetch({ path: '/gutenkit/v1/settings' })
			.then((data) => {
				const remoteImagePermission = data.settings.remote_image.status === 'active' ? 'upload' : '';
				dispatch({
					type: 'SET_IMAGE_IMPORT_TYPE',
					imageImportType: remoteImagePermission
				});
			})
	}, [])

	const insertPattern = (content) => {
		const selectedBlockClientId = getSelectedBlockClientId();

		if (selectedBlockClientId) {
			insertAfterBlock(selectedBlockClientId);
			const newSelectedBlockClientId = getSelectedBlockClientId();
			replaceBlocks(newSelectedBlockClientId, content);
		} else {
			insertBlocks(content);
		}
	}

	const onPatternImport = async (pattern) => {
		const content = parse(pattern.content);
		if (imageImportType === "upload") {
			const newUpdatedContent = await updateTemplateBlocks(content); // Await the top-level call
			insertPattern(newUpdatedContent);
		} else {
			insertPattern(content);
		}

		await dispatch({
			type: 'SET_LOAD_LIBRARY',
			loadLibrary: false
		});
	}

	const onDownloadCount = async (pattern) => {
		await updateDownloadCount(pattern.ID);
	}

	console.log("patterns", patterns)


	return (
		<>
			<Filter />
			<div className="table-builder-library-list table-builder-pattern-part">
				<div className="table-builder-pattern">
					{patterns && patterns.length === 0 && loading ? (
						<ContentLoader type='patterns' />
					) : (
						<>
							{patterns &&
								patterns.map((pattern, index) => {
									const uniqueKey = pattern?.id || pattern?.ID || `pattern-${index}`;
									return (
										<Pattern 
											key={uniqueKey} 
											pattern={pattern} 
											onPatternImport={onPatternImport} 
											onDownloadCount={onDownloadCount} 
										/>
									);
								})}
						</>
					)}
				</div>
				{hasMore && <button className="has-more-data" ref={loadMoreRef}></button>}
				{(patterns && patterns.length === 0 && !loading) && <Empty />}
			</div>

		</>
	);
}

export default Patterns;