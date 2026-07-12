/**
 * A reusable wrapper for Axios POST requests.
 * @param {string} url - The endpoint URL
 * @param {FormData|Object} data - The data to send
 * @param {Object} [options] - Optional settings and callbacks
 * @param {function} [options.onSuccess] - Callback when status === 'success'
 * @param {function} [options.onError] - Callback when status === 'error' or HTTP fails
 * @param {Object} [options.config] - Axios request configuration object
 */
async function apiPost(url, data, { onSuccess, onError, onDefault, config = {} } = {}) {
    try {
        const response = await axios.post(url, data, config);

        if (response.data.status === 'success') {
            if (onSuccess) onSuccess(response.data);
        } else if (response.data.status === 'error') {
            console.error("Application Error:", response.data.message);
            if (onError) onError(response.data.message, response.data); }

        if (onDefault) onDefault(response.data);

    } catch (error) {
        handleApiError(error, onError);
    }
}

/**
 * A reusable wrapper for Axios GET requests.
 * @param {string} url - The endpoint URL
 * @param {Object} [params={}] - The query parameters to append to the URL
 * @param {Object} [options] - Optional settings and callbacks
 * @param {function} [options.onSuccess] - Callback when status === 'success'
 * @param {function} [options.onError] - Callback when status === 'error' or HTTP fails
 * @param {Object} [options.config] - Axios request configuration object
 */
async function apiGet(url, params = {}, { onSuccess, onError, onDefault, config = {} } = {}) {
    try {
        const requestConfig = { ...config, params: params };
        const response = await axios.get(url, requestConfig);

        if (response.data.status === 'success') {
            if (onSuccess) onSuccess(response.data);
        } else if (response.data.status === 'error') {
            console.error("Application Error:", response.data.message);
            if (onError) onError(response.data.message); }

        if (onDefault) onDefault(response.data);

    } catch (error) {
        handleApiError(error, onError);
    }
}

/**
 * Internal helper to handle server/network errors and frontend bugs.
 * @param {Error} error - The caught error object
 * @param {function} [onError] - User-defined error callback
 */
function handleApiError(error, onError) {
    if (axios.isAxiosError(error)) {
        // Captures 403 (CSRF failures), 500 (Server crashes), etc.
        console.error("Network/Server Error:", error.response?.data?.message || error.message);

        if (onError) {
            const userMessage = error.response?.data?.message || "Connection or server error occurred.";
            onError(userMessage, error.response?.data);
        }
    } else {
        // Captures coding mistakes inside the onSuccess/onError callbacks
        console.error("Frontend UI Bug detected in callback:", error);
        throw error; 
    }
}