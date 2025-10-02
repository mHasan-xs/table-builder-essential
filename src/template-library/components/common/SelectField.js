import { useState, useRef, useEffect, useCallback, useMemo } from '@wordpress/element';
import DownArrowIcon from '../icons/DownArrowIcon';

const getClassName = (...classes) => classes.filter(Boolean).join(' ');

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
    
    const selectedOption = useMemo(
        () => options.find(opt => opt.value === value),
        [options, value]
    );
    
    const lastIndex = options.length - 1;

    useEffect(() => {
        if (!isOpen) return;

        const handleClickOutside = (e) => {
            if (!selectRef.current?.contains(e.target)) {
                setIsOpen(false);
            }
        };

        document.addEventListener('click', handleClickOutside);
        return () => document.removeEventListener('click', handleClickOutside);
    }, [isOpen]);

    const handleToggle = useCallback(() => {
        if (!disabled) setIsOpen(prev => !prev);
    }, [disabled]);

    const handleSelect = useCallback((option) => {
        if (option.disabled) return;
        onChange(option.value);
        setIsOpen(false);
    }, [onChange]);

    const handleKeyDown = useCallback((e) => {
        if (disabled) return;

        switch (e.key) {
            case 'Escape':
                setIsOpen(false);
                break;
            case 'Enter':
            case ' ':
                e.preventDefault();
                if (isOpen && focusIndex >= 0) {
                    handleSelect(options[focusIndex]);
                } else {
                    setIsOpen(true);
                }
                break;
            case 'ArrowDown':
                e.preventDefault();
                if (!isOpen) {
                    setIsOpen(true);
                } else {
                    setFocusIndex(focusIndex === lastIndex ? 0 : focusIndex + 1);
                }
                break;
            case 'ArrowUp':
                if (!isOpen) return;
                e.preventDefault();
                setFocusIndex(focusIndex <= 0 ? lastIndex : focusIndex - 1);
                break;
        }
    }, [disabled, isOpen, focusIndex, lastIndex, options, handleSelect]);

    const handleMouseEnter = useCallback((index) => {
        setFocusIndex(index);
    }, []);

    const selectClasses = useMemo(() => getClassName(
        'table-builder-custom-select',
        isOpen && 'open',
        disabled && 'disabled',
        error && 'error',
        className
    ), [isOpen, disabled, error, className]);

    return (
        <div ref={selectRef} className={selectClasses}>
            <button
                type="button"
                className="select-trigger"
                onClick={handleToggle}
                onKeyDown={handleKeyDown}
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
                    {options.map((option, index) => {
                        const optionClasses = getClassName(
                            'select-option',
                            index === focusIndex && 'focused',
                            option.disabled && 'disabled',
                            selectedOption?.value === option.value && 'selected'
                        );

                        return (
                            <li
                                key={option.value}
                                role="option"
                                aria-selected={selectedOption?.value === option.value}
                                className={optionClasses}
                                onClick={() => handleSelect(option)}
                                onMouseEnter={() => handleMouseEnter(index)}
                            >
                                {option.label}
                            </li>
                        );
                    })}
                </ul>
            )}
        </div>
    );
};

export default SelectField;