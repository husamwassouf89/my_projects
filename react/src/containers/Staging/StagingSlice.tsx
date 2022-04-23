import { createSlice, PayloadAction } from "@reduxjs/toolkit";

// Models
export interface staging {
    id: number;
    cif: number;
    name: string;
    financial_status: string;
    stage: number;
    class_type: string;
}

// clients state
export interface StagingState {
    isLoaded: boolean, // First load
    isLoading: boolean, // On filtering laoder
    isFetching: boolean,
    hasMore: boolean,
    staging_list: staging[]
}

const initialState: StagingState = {
    isLoaded: false,
    isLoading: false,
    isFetching: false,
    hasMore: true,
    staging_list: []
}

// Staging slice
export const StagingSlice = createSlice({
    name: 'staging',
    initialState,
    reducers: {
        setIsLoaded: ( state, {payload}: PayloadAction<boolean> ) => {
            state.isLoaded = payload
        },
        setIsLoading: ( state, {payload}: PayloadAction<boolean> ) => {
            state.isLoading = payload
        },
        setIsFetching: ( state, {payload}: PayloadAction<boolean> ) => {
            state.isFetching = payload
        },
        setHasMore: ( state, {payload}: PayloadAction<boolean> ) => {
            state.hasMore = payload
        },
        addStaging: ( state, {payload}: PayloadAction<staging[]> ) => {
            state.staging_list = [ ...state.staging_list, ...payload ]
        },
        reset: ( state ) => {
            state.hasMore = true
            state.staging_list = []
        }
    }
})