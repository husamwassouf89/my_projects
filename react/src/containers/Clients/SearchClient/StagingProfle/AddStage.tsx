import React, { useEffect, useRef, useState } from "react"
import { CategoriesMenu } from "../../../../components/PredefinedMenus/PredefinedMenus"
import API from "../../../../services/api/api"

import select_vector from '../../../assets/images/vectors/select.svg'
import { EllipsisLoader, WhiteboxLoader } from "../../../../components/Loader/Loader"
import { Collapse } from "react-collapse"
import { InputField, NumberField, RadioButton } from "../../../../components/FormElements/FormElements"

import './AddStage.css'
import { t } from "react-multi-lang"

interface IProps {
    class_type: number;
    client_id: number;
    defaultAnswers: number[];
    defaultValues: any;
    readonly: boolean;
}

export default (props: IProps) => {

    const questionsRef = useRef<HTMLDivElement>(null)
    const saveRef =  useRef<HTMLDivElement>(null)
    
    const [isLoaded, setIsLoaded] = useState<boolean>(false)
    const [category, setCategory] = useState<any>(null)
    const [questions, setQuestions] = useState<any>([])
    const [answers, setAnswers] = useState<number[]>(props.defaultAnswers)
    const [values, setValues] = useState<any>(props.defaultValues)
    const [submitting, setSubmitting] = useState<boolean>(false)

    const ENDPOINTS = new API()

    useEffect(() => {
        if(isLoaded)
            return
        ENDPOINTS.staging_profile().questions({ class_type_id: props.class_type })
        .then((response: any) => {
            setQuestions(response.data.data)
            setIsLoaded(true)
        })
    }, [isLoaded])

    const addAnswer = (answer: number, toRemove: number[]) => {
        let tmp = [...answers]
        toRemove.map(item => {
            let index = tmp.indexOf(item)
            if(index !== -1)
                tmp.splice(index, 1)
        })
        setAnswers([...tmp, answer])
    }

    useEffect(() => {
        saveRef?.current?.removeAttribute("style")
        // saveRef?.current?.setAttribute("style", `bottom: calc( calc( 100vh - ${questionsRef?.current?.parentElement?.parentElement?.offsetHeight}px ) / 2 )`)
    })

    const submit = () => {

        setSubmitting(true)
        ENDPOINTS.staging_profile().store({ client_id: props.client_id, answers: answers.map(answer => ({
            id: answer,
            value: values[answer] || 0
        })) })
        .then(() => {
            window.location.reload()
        })
    }

    return(
        <div style={{ minWidth: 500 }} ref={questionsRef} className="add-profile">
            { submitting && <WhiteboxLoader /> }
            <h2 style={{ margin: "0 0 20px" }}>{ props.readonly ? t("show_stage") : t("edit_stage") }</h2>
            <>
            { isLoaded ?
            <div className="profile-questions questions">
                { questions.length === 0 && <div className="text-center" style={{ margin: "40px 0", opacity: .5 }}>No questions in this category.</div> }
                {
                    questions.map((question: any) => (
                        <div className="question margin-top-20">
                            <header>
                                <label>{question.text}</label>
                            </header>
                            <Collapse isOpened={true}>
                                <form>
                                    <ul>
                                        { question.options?.map((answer: any, a_index: number) => (
                                            <li key={a_index}>
                                                { answer.type === "Linked" ?
                                                    <NumberField disabled={props.readonly} style={{ background: "#FFF", border: "1px solid #DDD" }} label={question.text} value={values[answer.id] || 0} onChange={(e: React.ChangeEvent<HTMLInputElement>) => {
                                                        let tmp = {...values}
                                                        tmp[answer.id] = e.target.value
                                                        setValues(tmp)
                                                        addAnswer(answer.id, [])
                                                    }} /> :
                                                    <label>
                                                        <RadioButton name={question.id} onChange={() => addAnswer(answer.id, question.options.map((a: any) => a.id))} label={answer.text} checked={answers.includes(answer.id)} disabled={props.readonly} />
                                                        { answers.includes(answer.id) && answer.with_value === "Yes" && <NumberField disabled={props.readonly} style={{ background: "#FFF", border: "1px solid #DDD" }} label={"عدد أيام التجاوز"} value={values[answer.id] || 0} onChange={(e: React.ChangeEvent<HTMLInputElement>) => {
                                                        let tmp = {...values}
                                                        tmp[answer.id] = e.target.value
                                                        setValues(tmp)
                                                    }} /> }
                                                    </label>
                                                }
                                            </li>
                                        )) }
                                    </ul>
                                </form>
                            </Collapse>
                        </div>
                    ))
                }
                { !props.readonly && <div className="save" ref={saveRef}><button disabled={answers.length < questions?.length} className="button bg-gold color-white" onClick={submit}>{t("submit_data")}</button></div> }
            </div> : <EllipsisLoader /> }
            </>
        </div>
    )
}