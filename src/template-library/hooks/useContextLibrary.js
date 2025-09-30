import Context from "../context";
import { useContext } from "@wordpress/element";

const useContextLibrary = () => {
	const libraryContext = useContext(Context);

	if (!libraryContext) {
		throw new Error('useContextLibrary must be used within a TemplateLibraryProvider');
	}

	return libraryContext;
}

export default useContextLibrary;

