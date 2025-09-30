import apiFetch from '@wordpress/api-fetch';
const uploadImages = async (image) => {
    try {
        const response = await apiFetch({
            path: '/gutenkit/v1/media-upload-from-url',
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(image),
        })

        return response;
    } catch (error) {
        throw error; // Rethrow the error for handling at a higher level, if needed
    }
}

export default uploadImages;