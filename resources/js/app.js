import '@tabler/core/dist/js/tabler.esm.js';
import './admin';
import './permissions';
import './users';
import './roles';

if (document.getElementById('logbook-form')) {
    import('./modules/procesos/logbookPdf.js');
}
