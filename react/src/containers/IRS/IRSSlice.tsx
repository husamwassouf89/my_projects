import { createSlice, PayloadAction } from "@reduxjs/toolkit";

// Models
export interface answer {
    id?: number;
    answer?: string;
    rate?: number;
}

export interface question {
    id?: number;
    question?: string;
    answers?: answer[]
}

// clients state
export interface IRSState {
    isLoaded: boolean, // First load
    isLoading: boolean, // On filtering laoder
    isFetching: boolean,
    hasMore: boolean,
    questions: question[]
}

const initialState: IRSState = {
    isLoaded: false,
    isLoading: false,
    isFetching: false,
    hasMore: true,
    questions: [
        {
            id: 1,
            question: "",
            answers: [
                {
                    id: 1,
                    answer: "",
                    rate: 0
                }
            ]
        }
    ]
}

// IRS slice
export const IRSSlice = createSlice({
    name: 'irs',
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
        addQuestions: ( state, {payload}: PayloadAction<question[]> ) => {
            state.questions = [ ...state.questions, ...payload ]
        },
        addQuestion: ( state, {payload}: PayloadAction<question> ) => {
            state.questions.push(payload)
        },
        deleteQuestion: ( state, {payload}: PayloadAction<number> ) => {
            let index = state.questions.findIndex(item => item.id === payload)
            if(index)
                state.questions.splice(index, 1)
        },
        editQuestion: ( state, {payload}: PayloadAction<{ index: number; question: question }> ) => {
            state.questions[payload.index] = { ...state.questions[payload.index], ...payload.question }
        },
        addAnswer: ( state, {payload}: PayloadAction<{ q_index: number; answer: answer }> ) => {
            state.questions[payload.q_index].answers?.push(payload.answer)
        },
        deleteAnswer: ( state, {payload}: PayloadAction<{ q_index: number; a_index: number; }> ) => {
            state.questions[payload.q_index].answers?.splice(payload.a_index, 1)
        },
        editAnswer: ( state, {payload}: PayloadAction<{ q_index: number; a_index: number; answer: answer }> ) => {
            let answers = state.questions[payload.q_index].answers
            if( answers !== undefined)
                answers[payload.a_index] = { ...answers[payload.a_index], ...payload.answer }
            state.questions[payload.q_index].answers = answers
        },
        reset: ( state ) => {
            state.questions = []
            state.hasMore = true
        }
    }
})