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
    }
}

const initialPredefinedState: predefinedState = {
    classes: {
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
    }
})