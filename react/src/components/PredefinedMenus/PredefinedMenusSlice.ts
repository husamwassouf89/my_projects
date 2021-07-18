import { createSlice, PayloadAction } from "@reduxjs/toolkit";

// Models
type select = {
    value: string | number,
    label: string
}

// Predefined state
export interface predefinedState {
    materials: {
        list: select[],
        // attributes?: { [key: number]: attribute[] },
        isLoaded: boolean
    },
    services: {
        list: select[],
        // attributes?: { [key: number]: attribute[] },
        isLoaded: boolean
    },
    roles: {
        list: select[],
        isLoaded: boolean
    },
    employees: {
        list: select[],
        isLoaded: boolean
    },
    products: {
        list: select[],
        isLoaded: boolean
    },
    clients: {
        list: select[],
        isLoaded: boolean
    },
    suppliers: {
        list: select[],
        isLoaded: boolean
    },
    orders: {
        list: select[],
        isLoaded: boolean
    }
}

const initialPredefinedState: predefinedState = {
    materials: {
        list: [],
        // attributes: {},
        isLoaded: false
    },
    services: {
        list: [],
        // attributes: {},
        isLoaded: false
    },
    roles: {
        list: [],
        isLoaded: false
    },
    employees: {
        list: [],
        isLoaded: false
    },
    products: {
        list: [],
        isLoaded: false
    },
    clients: {
        list: [],
        isLoaded: false
    },
    suppliers: {
        list: [],
        isLoaded: false
    },
    orders: {
        list: [],
        isLoaded: false
    }
}

// Predefined slice
export const predefinedMenusSlice = createSlice({
    name: 'predefined',
    initialState: initialPredefinedState,
    reducers: {
        // setMaterials: ( state, {payload}: PayloadAction<{ list: select[], attributes?: { [key: string]: attribute[] } }> ) => {
        //     state.materials = {
        //         ...state.materials,
        //         list: payload.list,
        //         isLoaded: true
        //     }
        // },
        // setMaterialAttributes: ( state, {payload}: PayloadAction<{ material_id: number, attributes: attribute[] }> ) => {
        //     if(state.materials.attributes)
        //         state.materials.attributes[payload.material_id] = payload.attributes
        // },
        // setServices: ( state, {payload}: PayloadAction<{ list: select[], attributes?: { [key: string]: attribute[] } }> ) => {
        //     state.services = {
        //         ...state.services,
        //         list: payload.list,
        //         isLoaded: true
        //     }
        // },
        // setServiceAttributes: ( state, {payload}: PayloadAction<{ service_id: number, attributes: attribute[] }> ) => {
        //     if(state.services.attributes)
        //         state.services.attributes[payload.service_id] = payload.attributes
        // },
        setRoles: ( state, {payload}: PayloadAction<{ list: select[] }> ) => {
            state.roles = {
                list: payload.list,
                isLoaded: true
            }
        },
        setEmployees: ( state, {payload}: PayloadAction<{ list: select[] }> ) => {
            state.employees = {
                list: payload.list,
                isLoaded: true
            }
        },
        setProducts: ( state, {payload}: PayloadAction<{ list: select[] }> ) => {
            state.products = {
                list: payload.list,
                isLoaded: true
            }
        },
        setClients: ( state, {payload}: PayloadAction<{ list: select[] }> ) => {
            state.clients = {
                list: payload.list,
                isLoaded: true
            }
        },
        setSuppliers: ( state, {payload}: PayloadAction<{ list: select[] }> ) => {
            state.suppliers = {
                list: payload.list,
                isLoaded: true
            }
        },
        setOrders: ( state, {payload}: PayloadAction<{ list: select[] }> ) => {
            state.orders = {
                list: payload.list,
                isLoaded: true
            }
        }
    }
})