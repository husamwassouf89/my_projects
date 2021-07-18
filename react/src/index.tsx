// React
import React from 'react';
import ReactDOM from 'react-dom';

// Redux
import { Provider } from "react-redux";
import store from './services/store/store'

// Cookies
import { CookiesProvider } from 'react-cookie';

// Stylesheet
import './assets/css/icons.css'

// Translation Hook
import { setTranslations, setDefaultLanguage } from 'react-multi-lang'
import en from './laguages/en.json'
import ar from './laguages/ar.json'

// Routes
import AppRoutes from './services/routes/AppRoutes';
import './assets/css/global.css'

// Setting up translations
setTranslations({ en, ar })
setDefaultLanguage(localStorage.getItem("lang") ? String(localStorage.getItem("lang")) : 'en')

ReactDOM.render(
    <Provider store={store}>
        <CookiesProvider>
            <React.StrictMode>
                <AppRoutes />
            </React.StrictMode>
        </CookiesProvider>
    </Provider>,
    document.getElementById('root')
);