import { useEffect } from "react"
import { useState } from "react"
import { useTranslation } from "react-multi-lang"
import { EllipsisLoader } from "../../../../components/Loader/Loader"
import Modal from "../../../../components/Modal/Modal"
import { FinancialStatusMenu } from "../../../../components/PredefinedMenus/PredefinedMenus"
import { DashboardTable } from "../../../../components/Table/Table"
import API from "../../../../services/api/api"
import AddRate from "./AddRate"

interface IProps {
    isOpen: boolean;
    toggle: Function;
    client_id: number;
    class_type: number;
    financial_status: string;
    changeFinancialStatus(value: string): any;
}

export default (props: IProps) => {

    // Translation
    const t = useTranslation()
    
    // Hooks
    const [classType, setClassType] = useState<number | null>(null)
    const [isLoaded, setIsLoaded] = useState<boolean>(false)

    const [showQuestions, setShowQuestions] = useState<boolean>(false)
    const [editable, setEditable] = useState<boolean>(true)
    const [answers, setAnswers] = useState<number[]>([])

    const [profiles, setProfiles] = useState<any>()

    // API
    const ENDPOINTS = new API()

    useEffect(() => {
        if(!isLoaded) {
            // Fetch data
            ENDPOINTS.irs_profile().index({ page: 1, page_size: 1000 }, props.client_id)
            .then((response: any) => {
                setProfiles(response.data.data)
                setIsLoaded(true)
            })
        }
    }, [isLoaded])

    const getProfiles = () => {
        return profiles.reduce((profilesObject: any, profile: any) => {
            profilesObject[profile.id] = { date: new Date(profile.created_at).toLocaleDateString(), class_type: profile.answers.map((answer: any) => answer.answer_value).reduce((a: number, b: number) => a + b, 0), actions: <div className="show-on-hover"><i className="icon-info" onClick={() => {
                setEditable(false)
                setShowQuestions(true)
                setAnswers(profile.answers.map((answer: any) => answer.option_id))
            }} /></div> };
            return profilesObject;
        }, {});
    }

    useEffect(() => {
        if(!props.isOpen) {
            setShowQuestions(false)
            setEditable(true)
            setAnswers([])
        }
    }, [props.isOpen])

    return(
        <Modal open={props.isOpen} toggle={props.toggle}>
            { !showQuestions ?
            <>
            { isLoaded ?
            <div style={{ minWidth: 500, minHeight: 400, textAlign: "left" }} className="profiles">
                <h2 style={{ margin: "0 0 20px", display: "block" }}>{t("client_rates")}</h2>
                <form style={{ display: 'inline-block', width: 250, margin: '-10px 0 20px', position: 'relative', zIndex: 100 }}>
                    <FinancialStatusMenu
                        defaultValue={{ value: props.financial_status, label: props.financial_status }}
                        onChange={(selected: any) => {
                            ENDPOINTS.clients().change_financial_status({ id: props.client_id, financial_status: selected.value })
                            .then(() => props.changeFinancialStatus(selected.label))
                        }}
                        />
                </form>
                <button className="button bg-gold color-white" style={{ float: "right" }} onClick={() => {
                    setShowQuestions(true)
                    setEditable(true)
                }}>{t("add_new_rate")}</button>
                <div style={{ marginBottom: -20 }}>
                    <DashboardTable
                        header={[t("date"), t("rate"), ""]}
                        body={getProfiles()}
                        />
                </div>
            </div> : <EllipsisLoader /> }
            </> : 
            <AddRate class_type={props.class_type} client_id={props.client_id} defaultAnswers={answers} readonly={!editable} financial_status={props.financial_status} /> }
        </Modal>
    )
}