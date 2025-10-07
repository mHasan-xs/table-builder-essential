import CheckMark from "../icons/CheckMark";

const RadioField = ({ content = [], radioType = "fill", value, onChange }) => {

    return (
        <div className={`table-builder-radio-button-group${radioType !== 'fill' ? `-${radioType}` : ''}`}>
            {content.map((tab, i) => {
                const { slug, title } = tab;
                const isActive = value === slug;
                const classes = `table-builder-radio-button-container-${radioType}${isActive ? '-checked' : ''}`;
                const label = title;

                return (
                    <label key={slug || i} htmlFor={slug} className={`table-builder-radio-button-container ${classes}`}>
                        <input
                            type="radio"
                            id={slug}
                            name={`table-builder-${radioType}-group`}
                            checked={isActive}
                            onChange={() => onChange?.(slug)}
                            className={`table-builder-radio-button-input-${radioType}`}
                        />
                        <span className={`table-builder-custom-radio-button-${radioType}`}>
                            <span className={`table-builder-custom-radio-button-${radioType}-activemark`}>
                                {radioType === 'fill' && <CheckMark />}
                            </span>
                        </span>
                        <span className="table-builder-radio-button-text">{label}</span>
                    </label>
                );
            })}
        </div>
    );
}


export default RadioField;