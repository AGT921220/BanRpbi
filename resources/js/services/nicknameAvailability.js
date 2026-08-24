/**
 * Comprueba disponibilidad de nickname vía fetch same-origin.
 */

/**
 * @param {string} nickname
 * @param {number|string|null} ignoreUserId
 * @param {string} checkUrl
 * @returns {Promise<{available: boolean, message: string, nickname: string}>}
 */
export async function checkNicknameAvailability(
    nickname,
    ignoreUserId = null,
    checkUrl = '/users/check-nickname',
) {
    const params = new URLSearchParams();
    params.set('nickname', (nickname || '').trim());

    if (ignoreUserId) {
        params.set('ignore_user_id', String(ignoreUserId));
    }

    const response = await fetch(`${checkUrl}?${params.toString()}`, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    if (!response.ok) {
        throw new Error('No se pudo validar el nickname.');
    }

    return response.json();
}
