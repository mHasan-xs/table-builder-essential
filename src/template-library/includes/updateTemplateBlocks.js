import findImage from "./findImage";
import findRepeaterImage from "./findRepeaterImage";
import uploadImages from "./uploadImage";

const updateTemplateBlocks = async (content) => {
    if (!content) return;
    const updatedContent = []; // Store the updated blocks
    for (const block of content) {
        if (block?.attributes) {
            const imageAttributes = findImage(block.attributes);
            if (imageAttributes) {
                for (const key of Object.keys(imageAttributes)) {
                    let value = imageAttributes[key];
                    if (key !== "blockID" && value.hasOwnProperty("backgroundImage") && value?.backgroundImage) {
                        const uploadNewImage = await uploadImages(value?.backgroundImage);
                        if (uploadNewImage) {
                            block.attributes[key] = {
                                ...value,
                                backgroundImage: {
                                    ...uploadNewImage,
                                    imageUrl: uploadNewImage.url
                                }
                            }
                        }
                    }

                    if (key !== "blockID" && value.hasOwnProperty("compat") && value.hasOwnProperty("filename")) {
                        const uploadNewImage = await uploadImages(value);
                        if (uploadNewImage) {
                            block.attributes[key] = uploadNewImage
                        }
                    }

                    if (key !== "blockID" && Array.isArray(value) && value.length > 0) {
                        let _repeaterKey = key;
                        let _repeaterValue = value;
                        const repeaterImages = findRepeaterImage(_repeaterValue);
                        const newRepeaterValue = [];
                        if (repeaterImages) {
                            for (const key in _repeaterValue) {
                                let originalObject = _repeaterValue[key];
                                let image = repeaterImages[key];
                                if (image && image.hasOwnProperty("backgroundImage") && image?.backgroundImage) {
                                    const uploadNewImage = await uploadImages(image?.backgroundImage);
                                    if (uploadNewImage) {
                                        newRepeaterValue.push({
                                            ...originalObject,
                                            backgroundImage: uploadNewImage
                                        })
                                    }
                                }

                                if (image && image.hasOwnProperty("image") && image?.image) {
                                    const uploadNewImage = await uploadImages(image?.image);
                                    if (uploadNewImage) {
                                        newRepeaterValue.push({
                                            ...originalObject,
                                            image: uploadNewImage
                                        })
                                    }
                                }
                            }
                        }

                        block.attributes[_repeaterKey] = newRepeaterValue
                    }
                }
            }
        }
        if (block.innerBlocks && block.innerBlocks.length > 0) {
            block.innerBlocks = await updateTemplateBlocks(block.innerBlocks);
        }

        updatedContent.push(block); // Add the updated block to the result
    }

    return updatedContent;
}

export default updateTemplateBlocks;