const initialState = {
	loadLibrary: false,
	syncLibrary: false,
	searchInput: '',
	sortedBy: 'recent',
	templateType: 'patterns',
	pages: [],
	showSinglePage: false,
	singleTemplate: {},
	patterns: [],
	templates: [],
	favorite: [],
	filter: {
		category: 'all',
		sortedBy: '',
		contentType: 'all'
	},
	contentType: [
		{ id: 1, title: 'All', slug: 'all' },
		{ id: 2, title: 'Free', slug: 'free' },
		{ id: 3, title: 'Pro', slug: 'pro' }
	],
	categories: [],
	templatesCategories: {},
	imageImportType: "upload",

	patternsPage: 1,
	pageTemplatesPage: 1,
	templatesPage: 1,
	templatesHasMore: true,
	pageHasMore: true,
	keyWords: '',
}

const reducer = (state, action) => {
	switch (action.type) {
		case 'SET_LOAD_LIBRARY':
			return {
				...state,
				loadLibrary: action.loadLibrary
			}
		case 'SET_SYNC_LIBRARY':
			return {
				...state,
				syncLibrary: action.syncLibrary
			}
		case 'SET_SEARCH_INPUT':
			return {
				...state,
				searchInput: action.searchInput
			}
		case 'SET_TEMPLATE_TYPE':
			return {
				...state,
				templateType: action.templateType
			}
		case 'SET_PAGES':
			return {
				...state,
				pages: action.pages
			}
		case 'SET_IS_SINGLE_PAGE':
			return {
				...state,
				showSinglePage: action.showSinglePage
			}
		case 'SET_SINGLE_TEMPLATE':
			return {
				...state,
				singleTemplate: action.singleTemplate
			}
		case 'SET_PATTERNS':
			return {
				...state,
				patterns: action.patterns
			}
		case 'SET_TEMPLATES':
			return {
				...state,
				templates: action.templates
			}
		case 'SET_FAVORITE':
			return {
				...state,
				favorite: action.favorite
			}
		case 'SET_FILTER':
			return {
				...state,
				filter: action.filter
			}
		case 'SET_CATEGORIES':
			return {
				...state,
				categories: action.categories
			}
		case 'SET_TEMPLATES_CATEGORIES':
			return {
				...state,
				templatesCategories: action.templatesCategories
			}
		case 'SET_IMAGE_IMPORT_TYPE':
			return {
				...state,
				imageImportType: action.imageImportType
			}

		// page number

		case 'SET_PATTERNS_PAGE':
			return {
				...state,
				patternsPage: action.patternsPage
			}
		case 'SET_PAGE_TEMPLATES_PAGE':
			return {
				...state,
				pageTemplatesPage: action.pageTemplatesPage
			}

		case 'SET_TEMPLATES_PAGE':
			return {
				...state,
				templatesPage: action.templatesPage
			}
		// templatesHasMore
		case 'SET_TEMPLATES_HAS_MORE':
			return {
				...state,
				templatesHasMore: action.templatesHasMore
			}
		// pageHasMore
		case 'SET_PAGE_HAS_MORE':
			return {
				...state,
				pageHasMore: action.pageHasMore
			}
		// keyWords
		case 'SET_KEY_WORDS':
			return {
				...state,
				keyWords: action.keyWords
			}
		default:
			return state;
	}
}

export { initialState, reducer }


