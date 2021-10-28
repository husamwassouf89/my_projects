import { useEffect, useRef, useState } from "react"
import { CategoriesMenu } from "../../../components/PredefinedMenus/PredefinedMenus"
import API from "../../../services/api/api"

import select_vector from '../../../assets/images/vectors/select.svg'
import { EllipsisLoader, WhiteboxLoader } from "../../../components/Loader/Loader"
import { Collapse } from "react-collapse"
import { RadioButton } from "../../../components/FormElements/FormElements"

import './AddRate.css'
import { t } from "react-multi-lang"

interface IProps {
    class_type: number;
    client_id: number;
    defaultAnswers: number[];
    readonly: boolean;
}

export default (props: IProps) => {

    const questionsRef = useRef<HTMLDivElement>(null)
    const saveRef =  useRef<HTMLDivElement>(null)
    
    const [isLoaded, setIsLoaded] = useState<boolean>(false)
    const [category, setCategory] = useState<any>(null)
    const [questions, setQuestions] = useState<any>([])
    const [answers, setAnswers] = useState<number[]>(props.defaultAnswers)
    const [submitting, setSubmitting] = useState<boolean>(false)

    const ENDPOINTS = new API()

    useEffect(() => {

        if(category) {
            setIsLoaded(false)
            ENDPOINTS.irs().irs({ class_type_id: props.class_type, category_id: category?.value })
            .then((response: any) => {
                setQuestions(response.data.data.questions)
                setIsLoaded(true)
            })
        }

    }, [category])

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
        saveRef?.current?.setAttribute("style", `bottom: calc( calc( 100vh - ${questionsRef?.current?.parentElement?.offsetHeight}px ) / 2 )`)
    })

    const submit = () => {
        setSubmitting(true)
        ENDPOINTS.irs_profile().store({ client_id: props.client_id, answers })
        .then(() => {
            window.location.reload()
        })
    }

    return(
        <div style={{ minWidth: 500 }} ref={questionsRef} className="add-profile">
            { submitting && <WhiteboxLoader /> }
            <h2 style={{ margin: "0 0 20px" }}>{ props.readonly ? t("show_rate") : t("add_new_rate") }</h2>
            <form onSubmit={e => e.preventDefault()}>
                <CategoriesMenu onChange={(selected: { value: number; }) => setCategory(selected)} placeholder={t("factor")} />
            </form>
            {
                category ?
                <>
                { isLoaded ?
                <div className="profile-questions questions">
                    { questions.length === 0 && <div className="text-center" style={{ margin: "40px 0", opacity: .5 }}>{t("no_questions")}</div> }
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
                                                    <label>
                                                        <RadioButton name={question.id} onChange={() => addAnswer(answer.id, question.options.map((a: any) => a.id))} label={answer.text} checked={answers.includes(answer.id)} disabled={props.readonly} />
                                                        <span className={ "value" + (answers.includes(answer.id) ? " active" : "") }>{answer.value}</span>
                                                    </label>
                                                </li>
                                            )) }
                                        </ul>
                                    </form>
                                </Collapse>
                            </div>
                        ))
                    }
                    <br />
                    { questions.length > 0 && <>{category.label} rate: <strong>{ [].concat.apply([], questions.map((question: any) => question.options.filter((answer: any) => answers.includes(answer.id)).map((answer: any) => answer.value))).reduce((a, b) => a + b, 0) }</strong></> }
                    { !props.readonly && <div className="save" ref={saveRef}><button className="button bg-gold color-white" onClick={submit}>{t("submit_data")}</button></div> }
                </div> : <EllipsisLoader /> }
                </> :
                <div className="text-center margin-top-30">
                    <img src={select_vector} style={{ maxWidth: 300 }} />
                    <p>{t("please_select_a_category")}</p>
                </div>
            }
        </div>
    )
}