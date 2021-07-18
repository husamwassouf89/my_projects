import { createSlice, PayloadAction } from "@reduxjs/toolkit";

// Login state
export interface loginState {
    isLoading: boolean,
    isSuccess: boolean,
    isError: boolean
}

const initialLoginState: loginState = {
    isLoading: false,
    isSuccess: false,
    isError: false
}

// Login slice
export const loginSlice = createSlice({
    name: 'login',
    initialState: initialLoginState,
    reducers: {
        init: state => initialLoginState,
        load: ( state ) => {
            state.isLoading = true
        },
        success: ( state ) => {
            state.isSuccess = true
        },
        error: ( state, {payload}: PayloadAction<boolean> ) => {
            state.isLoading = false
            state.isError = payload
        },
    }
})