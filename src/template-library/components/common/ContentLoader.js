import classnames from 'classnames';
import { renderPattern } from '../icons/renderPattern';
import { renderCategory } from '../icons/renderCategory';

const ITEMS_COUNT = 20;

const RENDERERS = {
  patterns: renderPattern,
  categories: renderCategory,
};

export default function ContentLoader({ type }) {
  const containerClass = classnames('loader-container', {
    load: type === 'categories',
    template: type === 'patterns',
  });

  const renderer = RENDERERS[type];
  if (!renderer) return null;

  return (
    <div className={containerClass}>
      {Array.from({ length: ITEMS_COUNT }, (_, index) => renderer(index))}
    </div>
  );
}