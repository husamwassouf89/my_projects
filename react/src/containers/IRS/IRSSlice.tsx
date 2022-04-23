import { createSlice, PayloadAction } from "@reduxjs/toolkit";

// Models
export interface answer {
    id?: number;
    answer?: string;
    rate?: string;
}

export interface question {
    id?: number;
    question?: string;
    status?: "new" | "saved" | "update";
    answers?: answer[],
    isSaving?: boolean
}

export interface irs {
    id: number;
    cif: number;
    name: string;
    financial_status: string;
    score: number;
    grade: number;
    class_type: string;
}

// clients state
export interface IRSState {
    isLoaded: boolean, // First load
    isLoading: boolean, // On filtering laoder
    isFetching: boolean,
    hasMore: boolean,
    questions: question[],
    percentage: number,
    IRSs: irs[]
}

const initialState: IRSState = {
    isLoaded: false,
    isLoading: false,
    isFetching: false,
    hasMore: true,
    questions: [],
    percentage: 0,
    IRSs: []
}

// Calculate percentage
const calculatePercentage = (state: IRSState): number => {
    return state.questions.map(question =>
        Math.max.apply(Math, question.answers?.length === 0 ? [0] : question.answers?.map(answer => Number(answer.rate) || 0) || [])
    ).reduce((a, b) => a + b, 0)
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
        setPercentage: ( state, {payload}: PayloadAction<number> ) => {
            state.percentage = payload
        },
        setQuestions: ( state, {payload}: PayloadAction<question[]> ) => {
            state.questions = payload
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
            state.questions[payload.index] = { ...state.questions[payload.index], status: "update", ...payload.question }
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
            state.questions[payload.q_index].status = "update"
            state.questions[payload.q_index].answers = answers
            state.percentage = calculatePercentage(state)
        },
        addIRSs: ( state, {payload}: PayloadAction<irs[]> ) => {
            state.IRSs = [ ...state.IRSs, ...payload ]
        },
        reset: ( state ) => {
            state.questions = []
            state.hasMore = true
            state.IRSs = []
        }
    }
})