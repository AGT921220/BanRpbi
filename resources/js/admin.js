import axios from 'axios';

const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute('content');

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

/**
 * GET
 */
export function get(url, config = {}) {
    return axios.get(url, config).then((response) => response.data);
}

/**
 * POST
 */
export function post(url, data = {}, config = {}) {
    return axios.post(url, data, config).then((response) => response.data);
}

window.BanHttp = { get, post };

export default window.BanHttp;
