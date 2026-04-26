import Chart from 'chart.js/auto';
window.Chart = Chart;

import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import ResizeImage from 'quill-resize-image';

Quill.register('modules/resize', ResizeImage);
window.Quill = Quill;
