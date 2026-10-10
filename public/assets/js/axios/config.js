const csrfName = document.querySelector('meta[name="csrf-token-name"]')?.getAttribute('content') || 'csrf_test_name';
let csrfHash = document.querySelector('meta[name="csrf-token-hash"]')?.getAttribute('content') || '';

function getCsrfInfo() {
    const metaName = document.querySelector('meta[name="csrf-token-name"]')?.getAttribute('content') || csrfName;
    const metaHash = document.querySelector('meta[name="csrf-token-hash"]')?.getAttribute('content') || csrfHash;
    return { name: metaName, hash: metaHash };
}

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

axios.interceptors.request.use((config) => {
    const method = (config.method || 'get').toLowerCase();
    const protectedMethods = ['post', 'put', 'patch', 'delete'];

    if (protectedMethods.includes(method)) {
        const { name, hash } = getCsrfInfo();
        config.headers['x-csrf-token'] = hash;
        config.headers['X-CSRF-TOKEN'] = hash;

        if (config.data instanceof FormData) {
            if (!config.data.has(name)) {
                config.data.append(name, hash);
            } else {
                config.data.set(name, hash);
            }
        } else if (config.data && typeof config.data === 'object' && !(config.data instanceof Blob)) {
            config.data[name] = hash;
        }
    }
    return config;
}, error => Promise.reject(error));

// Variables to manage the retry queue
let isFetchingCsrf = false;
let csrfSubscribers = [];

function onCsrfFetched(token) {
    csrfSubscribers.forEach(callback => callback(token));
    csrfSubscribers = [];
}

function addCsrfSubscriber(callback) {
    csrfSubscribers.push(callback);
}

// Response Interceptor
axios.interceptors.response.use((response) => {
    // 1. Success - Update token if the server sent a new one in the headers
    const newHeaderHash = response.headers['x-csrf-token'] || response.headers['X-CSRF-TOKEN'];
    if (newHeaderHash) {
        csrfHash = newHeaderHash;
        document.querySelector('meta[name="csrf-token-hash"]')?.setAttribute('content', newHeaderHash);
        document.querySelectorAll(`input[name="${csrfName}"]`).forEach(input => {
            input.value = newHeaderHash;
        });
    }
    return response;

}, (error) => {
    const originalRequest = error.config;

    // 2. Catch the 403 Forbidden (CSRF Desync)
    if (error.response && error.response.status === 403 && !originalRequest._retry) {
        originalRequest._retry = true; // Mark as retrying to prevent infinite loops

        if (!isFetchingCsrf) {
            isFetchingCsrf = true;
            
            // Quietly ping the server for a fresh token
            axios.get('/api/csrf-token').then(res => {
                const newToken = res.data.csrf_hash;
                
                // Update global variables and DOM
                csrfHash = newToken;
                document.querySelector('meta[name="csrf-token-hash"]')?.setAttribute('content', newToken);
                document.querySelectorAll(`input[name="${csrfName}"]`).forEach(input => {
                    input.value = newToken;
                });
                
                isFetchingCsrf = false;
                onCsrfFetched(newToken); // Release the queue
            }).catch(err => {
                // If the token fetch fails (e.g., session completely died), force a hard reload
                window.location.reload();
            });
        }

        // Add the failed request to the queue and wait for the new token
        return new Promise(resolve => {
            addCsrfSubscriber(newToken => {
                const { name } = getCsrfInfo();
                originalRequest.headers['x-csrf-token'] = newToken;
                originalRequest.headers['X-CSRF-TOKEN'] = newToken;
                if (originalRequest.data instanceof FormData) {
                    originalRequest.data.set(name, newToken);
                } else if (originalRequest.data && typeof originalRequest.data === 'object' && !(originalRequest.data instanceof Blob)) {
                    originalRequest.data[name] = newToken;
                }
                resolve(axios(originalRequest)); // Retry the original request!
            });
        });
    }

    // 3. Update token if the server sent a new one in a standard error response
    const errorHash = error.response?.headers?.['x-csrf-token'] || error.response?.headers?.['X-CSRF-TOKEN'];
    if (errorHash) {
        csrfHash = errorHash;
        document.querySelector('meta[name="csrf-token-hash"]')?.setAttribute('content', errorHash);
        document.querySelectorAll(`input[name="${csrfName}"]`).forEach(input => {
            input.value = errorHash;
        });
    }

    // 4. Standard error logging
    if (error.response && error.response.data) {
        console.error("Server says:", error.response.data.message || "Internal Server Error");
    } else {
        console.error("Connection failed:", error.message);
    }

    return Promise.reject(error);
});