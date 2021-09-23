try {
    if (!window.localStorage || !window.sessionStorage) {
        throw new Error();
    }
} catch (e) {
    (function () {
        'use strict';
    })();
}
