const findRepeaterImage = (repeaterValue) => {
    if(!repeaterValue || !repeaterValue.length) return;
    let result = [];
    for (const key in repeaterValue) {
        let value = repeaterValue[key];
        if(value.hasOwnProperty('backgroundImage') && value?.backgroundImage?.url){
            result.push({
                backgroundImage: value?.backgroundImage
            });
        }

        if(value.hasOwnProperty('image') && value?.image?.url){
            result.push({
                image: value
            });
        }
    }
    
    return result.length > 0 ? result : null;
}

export default findRepeaterImage;