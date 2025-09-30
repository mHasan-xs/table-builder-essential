import { useState, useRef, useEffect } from '@wordpress/element';
import DownArrowIcon from '../icons/DownArrowIcon';

const SelectField = ({
    options = [],
    value,
    onChange,
    placeholder = 'Select an option',
    disabled = false,
    className = '',
    error = false
}) => {
    const [isOpen, setIsOpen] = useState(false);
    const [focusIndex, setFocusIndex] = useState(-1);
    const selectRef = useRef(null);
    const selectedOption = options.find(opt => opt.value === value);

    useEffect(() => {
        if (!isOpen) return;

        const handleClick = (e) => {
            if (!selectRef.current?.contains(e.target)) {
                setIsOpen(false);
            }
        };

        document.addEventListener('click', handleClick);
        return () => document.removeEventListener('click', handleClick);
    }, [isOpen]);

    const toggle = () => !disabled && setIsOpen(!isOpen);

    const select = (option) => {
        if (option.disabled) return;
        onChange(option.value);
        setIsOpen(false);
    };

    const handleKey = (e) => {
        if (disabled) return;

        const { key } = e;
        const lastIndex = options.length - 1;

        if (key === 'Escape') {
            setIsOpen(false);
            return;
        }

        if (key === 'Enter' || key === ' ') {
            e.preventDefault();
            if (!isOpen) {
                setIsOpen(true);
            } else if (focusIndex >= 0) {
                select(options[focusIndex]);
            }
            return;
        }

        if (key === 'ArrowDown') {
            e.preventDefault();
            if (!isOpen) {
                setIsOpen(true);
            } else {
                setFocusIndex(focusIndex === lastIndex ? 0 : focusIndex + 1);
            }
            return;
        }

        if (key === 'ArrowUp' && isOpen) {
            e.preventDefault();
            setFocusIndex(focusIndex <= 0 ? lastIndex : focusIndex - 1);
        }
    };

    const baseClasses = 'table-builder-custom-select';
    const stateClasses = [
        isOpen && 'open',
        disabled && 'disabled',
        error && 'error'
    ].filter(Boolean).join(' ');

    const selectClasses = `${baseClasses} ${stateClasses} ${className}`.trim();

    return (
        <div ref={selectRef} className={selectClasses}>
            <button
                type="button"
                className="select-trigger"
                onClick={toggle}
                onKeyDown={handleKey}
                disabled={disabled}
                aria-expanded={isOpen}
                aria-haspopup="listbox"
            >
                <span className={selectedOption ? 'select-value' : 'select-placeholder'}>
                    {selectedOption?.label || options[0]?.label}
                </span>
                <DownArrowIcon className="select-arrow" />
            </button>

            {isOpen && (
                <ul className="select-dropdown" role="listbox">
                    {options.map((option, i) => (
                        <li
                            key={option.value}
                            role="option"
                            aria-selected={selectedOption?.value === option.value}
                            className={['select-option', i === focusIndex && 'focused', option.disabled && 'disabled',
                                selectedOption?.value === option.value && 'selected'
                            ].filter(Boolean).join(' ')}
                            onClick={() => select(option)}
                            onMouseEnter={() => setFocusIndex(i)}
                        >
                            {option.label}
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
};

export default SelectField;