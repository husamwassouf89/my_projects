import { createSlice, PayloadAction } from "@reduxjs/toolkit";

// Models
export interface client {
    id: number;
    loan_key: string;
    cif: number;
    name?: string;
    class_type?: string;
    type?: string;
}

// clients state
export interface clientsState {
    isLoaded: boolean, // First load
    isLoading: boolean, // On filtering laoder
    isFetching: boolean,
    hasMore: boolean,
    clients: client[]
}

const initialState: clientsState = {
    isLoaded: false,
    isLoading: false,
    isFetching: false,
    hasMore: true,
    clients: [
        {
            id: 1,
            loan_key: "3214109000002",
            cif: 20268,
            name: "كريم خفيف صندل الشحماني",
            class_type: "Corporate",
            type: "Loans"
        },
        {
            id: 2,
            loan_key: "3214109000003",
            cif: 20882,
            name: "سعد جبار سالم",
            class_type: "Corporate",
            type: "Loans"
        },
        {
            id: 3,
            loan_key: "3214109000004",
            cif: 20120,
            name: "شركة المصالح للخدمات النفطية ",
            class_type: "Corporate",
            type: "Loans"
        },
        {
            id: 4,
            loan_key: "3214110000002",
            cif: 20099,
            name: "شركة صناعات علاء الدين",
            class_type: "Corporate",
            type: "Loans"
        },
        {
            id: 5,
            loan_key: "3214109000002",
            cif: 20268,
            name: "كريم خفيف صندل الشحماني",
            class_type: "Corporate",
            type: "Loans"
        },
        {
            id: 6,
            loan_key: "3214109000003",
            cif: 20882,
            name: "سعد جبار سالم",
            class_type: "Corporate",
            type: "Loans"
        },
        {
            id: 7,
            loan_key: "3214109000004",
            cif: 20120,
            name: "شركة المصالح للخدمات النفطية ",
            class_type: "Corporate",
            type: "Loans"
        },
        {
            id: 8,
            loan_key: "3214110000002",
            cif: 20099,
            name: "شركة صناعات علاء الدين",
            class_type: "Corporate",
            type: "Loans"
        }
    ]
}

// clients slice
export const clientsSlice = createSlice({
    name: 'clients',
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
        addClients: ( state, {payload}: PayloadAction<client[]> ) => {
            state.clients = [ ...state.clients, ...payload ]
        },
        updateClient: ( state, {payload}: PayloadAction<client> ) => {
            let index = state.clients.findIndex( client => client.id === payload.id )
            if( index !== -1 )
                state.clients[index] = payload
        },
        deleteClients: ( state, {payload}: PayloadAction<number[]> ) => {
            payload.map(id => {
                let index = state.clients.findIndex( client => client.id === id )
                if( index != -1 )
                    state.clients.splice( index, 1 )
            })
        },
        reset: ( state ) => {
            state.clients = []
            state.hasMore = true
        }
    }
})