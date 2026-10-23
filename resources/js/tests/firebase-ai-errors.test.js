import test from 'node:test';
import assert from 'node:assert/strict';
import {
    classifyError,
    CONNECTION_LOST_MESSAGE,
    readableError,
    UserFacingError,
} from '../firebase-ai-errors.js';

const fetchFailure = new Error('AI: Error fetching from https://firebasevertexai.googleapis.com/v1beta/projects/x/models/gemini-3.8-flash:streamGenerateContent: Failed to fetch (AI/fetch-error)');
const quota = new Error('AI: Error fetching from https://firebasevertexai.googleapis.com/models/gemini-3.8-flash: [429 Too Many Requests] RESOURCE_EXHAUSTED retryDelay":"44s (AI/fetch-error)');

test('network failures show the friendly connection message without internals', () => {
    assert.equal(classifyError(fetchFailure, true), 'network');
    assert.equal(readableError(fetchFailure, true), CONNECTION_LOST_MESSAGE);
    assert.equal(classifyError(new Error('anything'), false), 'network');
});

test('quota errors are classified separately and never leak urls or model names', () => {
    assert.equal(classifyError(quota, true), 'quota');
    assert.doesNotMatch(readableError(quota, true), /gemini|https?:|429|firebase/i);
});

test('provider errors other than quota are not quota (no model fallback)', () => {
    const overloaded = new Error('AI: Error fetching from https://x: [500 Internal Server Error] high demand (AI/fetch-error)');
    assert.equal(classifyError(overloaded, true), 'provider');
    assert.doesNotMatch(readableError(overloaded, true), /gemini|https?:|500/i);
});

test('validation errors keep their corrective message', () => {
    const error = new UserFacingError('El folio es obligatorio.');
    assert.equal(classifyError(error, true), 'validation');
    assert.equal(readableError(error, true), 'El folio es obligatorio.');
});
