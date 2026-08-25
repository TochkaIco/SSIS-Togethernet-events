import * as Sentry from "@sentry/browser";
import Chart from 'chart.js/auto';
window.Chart = Chart;

import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import ResizeImage from 'quill-resize-image';

Quill.register('modules/resize', ResizeImage);
window.Quill = Quill;

Sentry.init({
    dsn: import.meta.env.SENTRY_LARAVEL_DSN,
    replaysSessionSampleRate: 1.0,
    replaysOnErrorSampleRate: 1.0,
    integrations: [
        Sentry.replayIntegration({
            maskAllText: true,
            blockAllMedia: true,
        }),
    ],
});
