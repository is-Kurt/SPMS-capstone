const csrfName = document.querySelector('meta[name="csrf-token-name"]').getAttribute('content');
let csrfHash = document.querySelector('meta[name="csrf-token-hash"]').getAttribute('content');

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

axios.interceptors.request.use((config) => {
    const method = config.method.toLowerCase();
    const protectedMethods = ['post', 'put', 'patch', 'delete'];

    if (protectedMethods.includes(method)) {
        config.headers['x-csrf-token'] = csrfHash;
    }
    return config;
}, error => Promise.reject(error));

// Response Interceptor
axios.interceptors.response.use((response) => {
    const newHeaderHash = response.headers['x-csrf-token'];
    
    if (newHeaderHash) {
        csrfHash = newHeaderHash;
        document.querySelector('meta[name="csrf-token-hash"]').setAttribute('content', newHeaderHash);
        document.querySelectorAll(`input[name="${csrfName}"]`).forEach(input => {
            input.value = newHeaderHash;
        });
    }
    return response;
}, (error) => {
    const errorHash = error.response?.headers?.['x-csrf-token'] || error.response?.headers?.['X-CSRF-TOKEN'];
    if (errorHash) {
        csrfHash = errorHash;
        document.querySelector('meta[name="csrf-token-hash"]').setAttribute('content', errorHash);
        document.querySelectorAll(`input[name="${csrfName}"]`).forEach(input => {
            input.value = errorHash;
        });
    }

    if (error.response && error.response.data) {
        console.error("Server says:", error.response.data.message || "Internal Server Error");
    } else {
        console.error("Connection failed:", error.message);
    }

    return Promise.reject(error);
});