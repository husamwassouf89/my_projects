import React, { useRef } from 'react'
import { useState } from 'react'
import { Collapse } from 'react-collapse'
import { Col, Row } from 'react-grid-system'
import { useTranslation } from 'react-multi-lang'
import { useDispatch, useSelector } from 'react-redux'
import { InputField, NumberField, SelectField } from '../../components/FormElements/FormElements'
import { EllipsisLoader, WhiteboxLoader } from '../../components/Loader/Loader'
import { CategoriesMenu, ClassesMenu, FinancialStatusMenu } from '../../components/PredefinedMenus/PredefinedMenus'

import './IRS.css'
import { answer, IRSSlice, IRSState, question } from './IRSSlice'

import select_vector from '../../assets/images/vectors/select.svg'
import { useEffect } from 'react'
import API from '../../services/api/api'
import { toast } from 'react-toastify'
import { Prompt } from 'react-router-dom'
import ReactTooltip from 'react-tooltip'

export default () => {

    // Hooks
    const [isLoaded, setIsLoaded] = useState<boolean>(false)
    const [classType, setClassType] = useState<any>(null)
    const [category, setCategory] = useState<any>(null)
    const [financialStatus, setFinancialStatus] = useState<any>(null)
    const [ irsID, setIRSID ] = useState<number>(0)
    const [ isSaving, setIsSaving ] = useState<number>(0)

    // Redux
    const dispatch = useDispatch()
    const state = useSelector( ( state: { irs: IRSState } ) => state.irs)

    // Translation
    const t = useTranslation()

    // API
    const ENDPOINTS = new API()

    const save = async () => {

        let questions = state.questions.filter(question => question.status !== "saved" && question.question).map(question => ({
            id: question.id,
            text: question.question,
            max_options_value: Math.max.apply(Math, question.answers?.length === 0 ? [0] : question.answers?.map(answer => Number(answer.rate) || 0) || []),
            options: question.answers?.filter(answer => answer.rate && answer.answer).map(answer => ({
                id: answer.id,
                text: answer.answer,
                value: answer.rate
            }))
        }))

        if(questions.length === 0) {
            toast("Your data is already saved!", {
                progressStyle: { background: "#925b97" }
            })
        }
        
        for(var i = 0; i < questions.length; i++) {
            let question = questions[i];

            // TODO: Change it to more efficient way
            let question_index = state.questions.findIndex(q => q.question === question.text)
            if(state.questions[question_index].isSaving)
                return
            dispatch(IRSSlice.actions.editQuestion({ index: question_index, question: { isSaving: true } }))

            setIsSaving(prev => prev + 1)

            if(question.id) {
                ENDPOINTS.irs().update({
                    ...question
                })
                .then((response: any) => {
                    setIsSaving(prev => prev - 1)
                    let q = response.data.data
                    dispatch( IRSSlice.actions.editQuestion({
                        index: question_index,
                        question: {
                            id: q.id,
                            status: "saved",
                            isSaving: false,
                            answers: q.options.map((option: any): answer => ({
                                id: option.id,
                                answer: option.text,
                                rate: option.value
                            }))
                        }
                    }) )
                })
            } else {
                const response = await ENDPOINTS.irs().store({
                    ...question,
                    category_id: category.value,
                    class_type_id: classType.value,
                    financial_status: financialStatus.value
                });
                setIsSaving(prev => prev - 1)
                let q = response.data.data
                dispatch( IRSSlice.actions.editQuestion({
                    index: question_index,
                    question: {
                        id: q.id,
                        status: "saved",
                        isSaving: false,
                        answers: q.options.map((option: any): answer => ({
                            id: option.id,
                            answer: option.text,
                            rate: option.value
                        }))
                    }
                }) )
            }
        }

    }

    const isSaved = (): boolean => {
        return state.questions?.filter(question => question.status !== "saved").length === 0
    }

    useEffect(() => {
        if(classType && category && financialStatus) {
            setIsLoaded(false)
            
            // Fetch
            ENDPOINTS.irs().irs({ class_type_id: classType.value, category_id: category.value, financial_status: financialStatus.value })
            .then(((response: any) => {
                let irs = response.data.data
                setIRSID(irs.id)
                dispatch( IRSSlice.actions.setPercentage(irs.questions.map((question: any) => Number(question.max_options_value)).reduce((a: number, b: number) => a + b, 0)) )
                setIsLoaded(true)
                dispatch(IRSSlice.actions.setQuestions(
                    irs.questions?.map((question: any): question => ({
                        id: question.id,
                        question: question.text,
                        status: "saved",
                        answers: question.options.map((option: any): answer => ({
                            id: option.id,
                            answer: option.text,
                            rate: option.value
                        }))
                    }))
                ))
            }))

        }
    }, [classType, category, financialStatus])

    return (
        <div className="irs">
            <form onSubmit={(e: React.FormEvent<HTMLFormElement>) => e.preventDefault()}>
                <Row>
                    <Col md={4}>
                        <label>{t("class_type")}</label>
                        <ClassesMenu onChange={(selected: { value: number; }) => setClassType(selected)} placeholder="Class type" />
                    </Col>
                    <Col md={4}>
                        <label>{t("factor")}</label>
                        <CategoriesMenu onChange={(selected: { value: number; }) => setCategory(selected)} placeholder="Factor" />
                    </Col>
                    <Col md={4}>
                        <label>{t("Financial Status")}</label>
                        <FinancialStatusMenu onChange={(selected: { value: number; }) => setFinancialStatus(selected)} placeholder="Financial Status" />
                    </Col>
                    { classType && category && financialStatus && isLoaded &&
                    <>
                    <Col md={8}>
                        <h3 style={{ lineHeight: "50px" }}><span style={{ fontWeight: "normal" }}>Questions related to </span>{classType.label} &#x3E; {category.label} Factors</h3>
                    </Col>
                    
                    <Col md={2} className="text-right">
                        { Number(state.percentage) > 100 ?
                        <div data-tip="Your total percentage is above 100%" style={{ display: 'inline-block' }}>
                            <button className="button bg-gold color-white" style={ isSaving || isSaved() || Number(state.percentage) > 100 ? { opacity: .5, marginTop: 12 } : { marginTop: 12 }} disabled={isSaving > 0 || isSaved() || Number(state.percentage) > 100} onClick={save}>{ isSaving ? "Saving..." : "Save" }</button>
                            <ReactTooltip backgroundColor='tomato' effect='solid' />
                        </div> :
                        <div>
                            <button className="button bg-gold color-white" style={ isSaving || isSaved() || Number(state.percentage) > 100 ? { opacity: .5, marginTop: 12 } : { marginTop: 12 }} disabled={isSaving > 0 || isSaved() || Number(state.percentage) > 100} onClick={save}>{ isSaving ? "Saving..." : "Save" }</button>
                        </div>
                        }

                    </Col>

                    <Col md={2}>
                        <NumberField min={0} max={100} placeholder={t("max_percentage")} value={state.percentage} disabled style={Number(state.percentage) > 100 ? { border: '1px solid tomato', color: 'tomato' } : {}} />
                    </Col>
                    </> }

                </Row>
            </form>
            
            {/* Questions */}
            {
                classType && category && financialStatus ?
                isLoaded ?
                <div className="questions">
                    
                    {
                        state.questions.map((question, q_index) => (
                            <div className="question margin-top-20">
                                <header>
                                    <input placeholder="Type a question" value={question.question} onChange={(e: React.ChangeEvent<HTMLInputElement>) => dispatch(IRSSlice.actions.editQuestion({ index: q_index, question: { question: e.target.value } }))} />
                                </header>
                                <Collapse isOpened={true}>
                                    <ul>
                                        { question.answers?.map((answer, a_index) => (
                                            <li>
                                                <input className="answer" value={answer.answer} placeholder="Type an answer" onChange={(e: React.ChangeEvent<HTMLInputElement>) => dispatch(IRSSlice.actions.editAnswer({ q_index, a_index, answer: { answer: e.target.value } }))} />
                                                <div className="percentage">
                                                    <InputField placeholder="Answer rate" value={answer.rate} onChange={(e: React.ChangeEvent<HTMLInputElement>) => dispatch(IRSSlice.actions.editAnswer({ q_index, a_index, answer: { rate: e.target.value } }))} />
                                                </div>
                                            </li>
                                        )) }
                                        <button className="button color-gold margin-top-30" style={{ margin: 30 }} onClick={() => dispatch( IRSSlice.actions.addAnswer({ q_index, answer: {} }) )}>Add answer</button>
                                    </ul>
                                </Collapse>
                            </div>
                        ))
                    }
                
                    <button className="button bg-gold color-white margin-top-30" onClick={() => dispatch( IRSSlice.actions.addQuestion({answers: []}) )}>Add question</button>

                    <br />
                    <br />

                </div> :
                <div className="text-center margin-top-50">
                    <EllipsisLoader />
                </div> :
                <div className="text-center margin-top-30">
                    <img src={select_vector} style={{ maxWidth: 300 }} />
                    <p>Please select a class type and a category</p>
                </div> }
                
                { !isSaved() &&
                <Prompt message='You have unsaved changes, are you sure you want to leave?' /> }

        </div>
    )

}