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
}

const initialPredefinedState: predefinedState = {
    classes: {
        list: [],
        isLoaded: false
    },
    categories: {
        list: [],
        isLoaded: false
    }
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
                ...state.classes,
                list: payload.list,
                isLoaded: true
            }
        },
    }
})