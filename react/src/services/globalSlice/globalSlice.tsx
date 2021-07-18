import { createSlice, PayloadAction } from "@reduxjs/toolkit";

// Global state
export interface globalState {
    isLoading: boolean
}

const initialGlobalState: globalState = {
    isLoading: false
}

// Global slice
export const globalSlice = createSlice({
    name: 'global',
    initialState: initialGlobalState,
    reducers: {
        setIsLoading: ( state, {payload}: PayloadAction<boolean> ) => {
            state.isLoading = payload
        },
    }
})