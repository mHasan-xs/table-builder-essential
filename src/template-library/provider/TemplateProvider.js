import Context from "../context";
import { useReducer } from "@wordpress/element";
import { initialState, reducer } from "../reducers";

const TemplateProvider = ({ children }) => {
	const { useHasProActive } = window.tableBuilder.helpers;
	const { version } = window.tableBuilder;
	const isProActive = useHasProActive({ windowVariable: 'tableBuilder', hookName: 'tablebuilder.isProActive', cookieName: 'isTableBuilderValid', apiPath: 'tablebuilder' });

	const payload = {
		version: version,
		auth: 'tablebuilder',
		premium: isProActive
	};

	const [state, dispatch] = useReducer(reducer, initialState);

	const newData = {
		...state,
		payload
	};

	return (
		<Context.Provider value={{ ...newData, dispatch }}>
			{children}
		</Context.Provider>
	)
}

export default TemplateProvider;
