import * as Sentry from "@sentry/browser";
import Chart from 'chart.js/auto';
window.Chart = Chart;

import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import ResizeImage from 'quill-resize-image';

Quill.register('modules/resize', ResizeImage);
window.Quill = Quill;

const sentryDsn = document.querySelector('meta[name="sentry-dsn"]')?.getAttribute('content')
    || import.meta.env.VITE_SENTRY_LARAVEL_DSN
    || import.meta.env.VITE_SENTRY_DSN;
if (sentryDsn) {
    Sentry.init({
        dsn: sentryDsn,
        replaysSessionSampleRate: 1.0,
        replaysOnErrorSampleRate: 1.0,
        integrations: [
            Sentry.replayIntegration({
                maskAllText: true,
                blockAllMedia: true,
            }),
        ],
    });
}
