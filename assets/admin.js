import './scss/admin.scss'

import Translator from './translator/Translator';
import Spinner from './ui/Spinner'
import ConfirmModal from './ui/ConfirmModal'
import Toast from './ui/Toast'
import JsResponseHandler from './jsresponse/JsResponseHandler';
import configureHandler from './jsresponse/Configure'
import PonchoDataTable from './datatable/PonchoDataTable';
import PonchoCollection from './form/PonchoCollection';
import DatePicker from './form/DatePicker';
import PasswordTogglable from './form/PasswordTogglable';
import BindUtils from './utils/BindUtils';
import PonchoNotification from './PonchoNotification';
import PonchoSidebar from './PonchoSidebar';
import PonchoAutocomplete from './form/PonchoAutocomplete';

const locale = document.querySelector('html').getAttribute('lang') || 'en'

window.poncho = {
    locale,
    translator: new Translator(locale),
    spinner: Spinner,
    confirmModal: ConfirmModal,
    toast: Toast,
    jsResponseHandler: new JsResponseHandler()
}

// --- Configure jsResponseHandler
configureHandler(poncho.jsResponseHandler);

// --- DataTable.js
customElements.define('poncho-datatable', PonchoDataTable);

// --- Forms
customElements.define('poncho-datepicker', DatePicker, {extends: 'input'});
customElements.define('poncho-collection', PonchoCollection);
customElements.define('poncho-autocomplete', PonchoAutocomplete, {extends: 'select'});
customElements.define('password-togglable', PasswordTogglable, {extends: 'div'});

// --- Admin components
customElements.define('poncho-notification', PonchoNotification, {extends: 'li'});
customElements.define('poncho-sidebar', PonchoSidebar, {extends: 'nav'});


// --- Bind some elements
BindUtils.enableAll();
