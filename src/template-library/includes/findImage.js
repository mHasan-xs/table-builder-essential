const findImage = (attributes) => {
  const result = {};
  for (const key in attributes) {
    const value = attributes[key];
    if (typeof value === 'object' && value.hasOwnProperty('backgroundImage') && value?.backgroundImage && value?.backgroundImage?.url) {
      result[key] = value
    }

    if (typeof value === 'object' && value.hasOwnProperty('compat') && value.hasOwnProperty('filename')) {
      result[key] = value
    }

    if (key === 'blockID') {
      result[key] = value
    }

    if(Array.isArray(value) && value.length > 0 ){
      const hasImage = value.some(item => item.hasOwnProperty('backgroundImage') && item?.backgroundImage?.url);
      if(hasImage){
        result[key] = value
      }
    }
  }

  return Object.keys(result).length > 1 ? result : null
}

export default findImage;