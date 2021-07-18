import { createSlice, PayloadAction } from "@reduxjs/toolkit";

// Models
export interface pd {
    id: number;
    year: number;
    quarter: "Q1" | "Q2" | "Q3" | "Q4";
}

// pds state
export interface pdsState {
    isLoaded: boolean, // First load
    isLoading: boolean, // On filtering laoder
    isFetching: boolean,
    hasMore: boolean,
    pds: pd[]
}

const initialState: pdsState = {
    isLoaded: false,
    isLoading: false,
    isFetching: false,
    hasMore: true,
    pds: [
        {
            id: 1,
            year: 2021,
            quarter: "Q1"
        },
        {
            id: 2,
            year: 2021,
            quarter: "Q2"
        },
        {
            id: 3,
            year: 2020,
            quarter: "Q1"
        },
        {
            id: 4,
            year: 2020,
            quarter: "Q2"
        },
        {
            id: 5,
            year: 2020,
            quarter: "Q3"
        },
        {
            id: 6,
            year: 2020,
            quarter: "Q4"
        },
        {
            id: 7,
            year: 2019,
            quarter: "Q1"
        },
        {
            id: 8,
            year: 2019,
            quarter: "Q2"
        }
    ]
}

// pds slice
export const pdsSlice = createSlice({
    name: 'pds',
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
        addPDs: ( state, {payload}: PayloadAction<pd[]> ) => {
            state.pds = [ ...state.pds, ...payload ]
        },
        updatePD: ( state, {payload}: PayloadAction<pd> ) => {
            let index = state.pds.findIndex( pd => pd.id === payload.id )
            if( index !== -1 )
                state.pds[index] = payload
        },
        deletePDs: ( state, {payload}: PayloadAction<number[]> ) => {
            payload.map(id => {
                let index = state.pds.findIndex( pd => pd.id === id )
                if( index != -1 )
                    state.pds.splice( index, 1 )
            })
        },
        reset: ( state ) => {
            state.pds = []
            state.hasMore = true
        }
    }
})