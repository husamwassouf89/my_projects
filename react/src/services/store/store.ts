import { configureStore } from "@reduxjs/toolkit";

// Slices
import { globalSlice } from "../globalSlice/globalSlice";
import { loginSlice } from "../../containers/LoginForm/LoginFormSlice";
import { predefinedMenusSlice } from '../../components/PredefinedMenus/PredefinedMenusSlice'

import { clientsSlice } from "../../containers/Clients/ClientsSlice";
import { pdsSlice } from "../../containers/PD/PDsSlice";


const reducer = {
    global: globalSlice.reducer,
    login: loginSlice.reducer,
    predefined_menus: predefinedMenusSlice.reducer,
    clients: clientsSlice.reducer,
    pds: pdsSlice.reducer
}

export default configureStore({
    reducer,
    devTools: process.env.NODE_ENV !== 'production'
})