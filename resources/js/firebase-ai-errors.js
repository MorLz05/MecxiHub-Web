export class UserFacingError extends Error {}

export const CONNECTION_LOST_MESSAGE = 'Conexión perdida. Revisa tu conexión e inténtalo de nuevo.';

const FRIENDLY_MESSAGES = {
    network: CONNECTION_LOST_MESSAGE,
    quota: 'El asistente está recibiendo muchas solicitudes en este momento. Inténtalo de nuevo en unos minutos.',
    timeout: 'La respuesta tardó demasiado. Inténtalo de nuevo.',
    auth: 'No se pudo verificar tu acceso al asistente. Recarga la página e inicia sesión de nuevo si el problema continúa.',
    provider: 'El asistente no está disponible por el momento. Inténtalo de nuevo más tarde.',
    unknown: 'Ocurrió un problema al consultar el asistente. Inténtalo de nuevo.',
};

function messageOf(error) {
    return error instanceof Error ? error.message : String(error ?? '');
}

export function isRateLimitError(message) {
    return /\b429\b|too many requests|resource.exhausted/i.test(message);
}

export function isModelTimeoutError(message) {
    return /timeout has expired|timed?\s*out|deadline exceeded/i.test(message);
}

export function classifyError(error, online = typeof navigator === 'undefined' ? true : navigator.onLine) {
    if (error instanceof UserFacingError) return 'validation';
    const message = messageOf(error);
    if (isRateLimitError(message)) return 'quota';
    if (!online) return 'network';
    const status = Number(message.match(/\[(\d{3})\b/)?.[1] || 0);
    if (!status && /failed to fetch|networkerror|network request failed|load failed|err_internet|offline|fetch-error|error fetching/i.test(message)) {
        return 'network';
    }
    if (isModelTimeoutError(message)) return 'timeout';
    if (/app.?check|permission.denied|unauthenticated|\b40[13]\b/i.test(message)) return 'auth';
    if (status || /AI\//.test(message)) return 'provider';
    return 'unknown';
}

// Nunca devuelve detalles internos (URLs, modelos, trazas); solo mensajes de UserFacingError.
export function readableError(error, online) {
    const kind = classifyError(error, online);
    return kind === 'validation' ? error.message : FRIENDLY_MESSAGES[kind];
}

export function logQuotaRetryWindow(error) {
    const message = messageOf(error);
    if (!isRateLimitError(message)) return;

    const retryDelayMatch = message.match(/retryDelay["'\\]*\s*:\s*["'\\]*(\d+(?:\.\d+)?)s/i);
    if (!retryDelayMatch) {
        console.warn('[MecxiBot] El proveedor aplicó un límite de uso sin indicar cuándo reintentar.');
        return;
    }

    const retryAfterSeconds = Number(retryDelayMatch[1]);
    console.warn('[MecxiBot] Periodo estimado antes de reintentar (estimación del proveedor).', {
        retryAfterSeconds,
        retryAtLocal: new Date(Date.now() + retryAfterSeconds * 1000).toLocaleString(),
    });
}
