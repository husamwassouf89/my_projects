import { createSlice, PayloadAction } from "@reduxjs/toolkit";

// Models
type select = {
    value: string | number,
    label: string
}

// Predefined state
export interface predefinedState {
    classes: {
        list: select[],
        isLoaded: boolean
    },
    categories: {
        list: select[],
        isLoaded: boolean
    },
    financial_statuses: {
        list: select[],
        isLoaded: boolean
    },
}

const initialPredefinedState: predefinedState = {
    classes: {
        list: [],
        isLoaded: false
    },
    categories: {
        list: [],
        isLoaded: false
    },
    financial_statuses: {
        list: [],
        isLoaded: false
    },
}

// Predefined slice
export const predefinedMenusSlice = createSlice({
    name: 'predefined',
    initialState: initialPredefinedState,
    reducers: {
        setClasses: ( state, {payload}: PayloadAction<{ list: select[] }> ) => {
            state.classes = {
                ...state.classes,
                list: payload.list,
                isLoaded: true
            }
        },
        setCategories: ( state, {payload}: PayloadAction<{ list: select[] }> ) => {
            state.categories = {
                ...state.categories,
                list: payload.list,
                isLoaded: true
            }
        },
        setFinancialStatuses: ( state, {payload}: PayloadAction<{ list: select[] }> ) => {
            state.financial_statuses = {
                ...state.financial_statuses,
                list: payload.list,
                isLoaded: true
            }
        },
    }
})