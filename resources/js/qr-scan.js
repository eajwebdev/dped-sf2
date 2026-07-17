import jsQR from 'jsqr';

/**
 * Shared camera + QR decoding for the scan screens.
 *
 * Uses the native BarcodeDetector when the browser has one (Chrome/Edge on
 * Android) and falls back to jsQR on a canvas everywhere else (iOS Safari,
 * Firefox, older WebViews), so camera scanning works on any phone as long as
 * the page is served over HTTPS.
 */
const hasCamera = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);

function createEngine() {
    if ('BarcodeDetector' in window) {
        const detector = new BarcodeDetector({ formats: ['qr_code'] });
        return {
            async detect(video) {
                const codes = await detector.detect(video);
                return codes.length ? codes[0].rawValue : null;
            },
        };
    }

    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    return {
        async detect(video) {
            if (!video.videoWidth) return null;
            // Downscale big camera frames — jsQR is CPU-bound.
            const scale = Math.min(1, 640 / video.videoWidth);
            canvas.width = Math.round(video.videoWidth * scale);
            canvas.height = Math.round(video.videoHeight * scale);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            const img = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(img.data, img.width, img.height, { inversionAttempts: 'dontInvert' });
            return code ? code.data : null;
        },
    };
}

window.qrScan = {
    supported: hasCamera,
    unsupportedReason: hasCamera
        ? ''
        : (window.isSecureContext
            ? 'This browser has no camera support — type the code instead.'
            : 'The camera only works over a secure connection. Open this page with https://.'),

    createEngine,

    async open(video) {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'environment' } },
            audio: false,
        });
        video.srcObject = stream;
        await video.play().catch(() => {});
        return stream;
    },

    errorMessage(e) {
        switch (e && e.name) {
            case 'NotAllowedError':
            case 'SecurityError':
                return 'Camera permission was denied. Allow camera access in the browser settings and retry.';
            case 'NotFoundError':
            case 'OverconstrainedError':
                return 'No usable camera was found on this device.';
            case 'NotReadableError':
                return 'The camera is in use by another app. Close it and retry.';
            default:
                return 'Could not start the camera. Type the code instead.';
        }
    },
};
