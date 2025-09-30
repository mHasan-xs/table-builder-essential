import { useRef, useEffect, useSyncExternalStore } from '@wordpress/element';
import { createRoot } from 'react-dom/client';
import TemplateLibrary from './components/library/TemplateLibrary';
import TemplateProvider from './provider/TemplateProvider';
import { registerPlugin } from '@wordpress/plugins';
import { subscribe } from '@wordpress/data';
import './style/template-library.scss';

const useToolbarElement = () => {
  return useSyncExternalStore(
    (callback) => {
      const unsubscribe = subscribe(callback);
      return () => unsubscribe();
    },
    () => {
      return document.querySelector(
        '.edit-post-header__toolbar, .editor-header__toolbar, .edit-site-header-edit-mode__start'
      );
    }
  );
};

const AddRoot = () => {
  const rootRef = useRef(null);
  const toolbarElement = useToolbarElement();

  useEffect(() => {
    if (!toolbarElement) return;

    let rootElement = document.getElementById('table-builder-essential-template-library');
    if (!rootElement) {
      rootElement = document.createElement('div');
      rootElement.id = 'table-builder-essential-template-library';
      toolbarElement.appendChild(rootElement);
    }

    if (!rootRef.current && rootElement) {
      rootRef.current = createRoot(rootElement);
    }

    if (rootRef.current) {
      rootRef.current.render(
        <TemplateProvider>
          <TemplateLibrary />
        </TemplateProvider>
      );
    }

    return () => {
      if (rootRef.current) {
        rootRef.current.unmount();
        rootRef.current = null;
      }
    };
  }, [toolbarElement]);

  return null;
};

registerPlugin('table-builder-essential-template-library', {
  render: AddRoot,
});