import { useEffect } from "react"
import { useState } from "react"
import { useTranslation } from "react-multi-lang"
import { EllipsisLoader } from "../../../../components/Loader/Loader"
import Modal from "../../../../components/Modal/Modal"
import { DashboardTable } from "../../../../components/Table/Table"
import API from "../../../../services/api/api"
import AddRate from "./AddStage"

interface IProps {
    isOpen: boolean;
    toggle: Function;
    client_id: number;
    class_type: number;
}

export default (props: IProps) => {

    // Translation
    const t = useTranslation()
    
    // Hooks
    const [isLoaded, setIsLoaded] = useState<boolean>(false)

    const [showQuestions, setShowQuestions] = useState<boolean>(false)
    const [editable, setEditable] = useState<boolean>(true)
    const [answers, setAnswers] = useState<number[]>([])
    const [values, setValues] = useState<any>({})

    const [profiles, setProfiles] = useState<any>()

    // API
    const ENDPOINTS = new API()

    useEffect(() => {
        if(!isLoaded) {
            // Fetch data
            ENDPOINTS.staging_profile().index({ page: 1, page_size: 1000 }, props.client_id)
            .then((response: any) => {
                setProfiles(response.data.data)
                setIsLoaded(true)
            })
        }
    }, [isLoaded])

    const getProfiles = () => {
        return profiles.reduce((profilesObject: any, profile: any) => {
            profilesObject[profile.id] = { date: new Date(profile.created_at).toLocaleDateString(), actions: <div className="show-on-hover"><i className="icon-info" onClick={() => {
                let tmp = {...values}
                setAnswers(profile.answers.map((answer: any) => {
                    if(answer.with_value === "Yes")
                        tmp[answer.staging_option_id] = answer.value
                    return answer.staging_option_id
                }))
                setValues(tmp)
                setEditable(false)
                setShowQuestions(true)
            }} /></div> };
            return profilesObject;
        }, {});
    }

    useEffect(() => {
        if(!props.isOpen) {
            setShowQuestions(false)
            setEditable(true)
            setAnswers([])
            setValues({})
        }
    }, [props.isOpen])

    return(
        <Modal open={props.isOpen} toggle={props.toggle}>
            { !showQuestions ?
            <>
            { isLoaded ?
            <div style={{ minWidth: 500, textAlign: "left" }} className="profiles">
                <h2 style={{ margin: "0 0 20px", display: "inline-block" }}>{t("client_stages")}</h2>
                <button className="button bg-gold color-white" style={{ float: "right", position: "relative", top: -7 }} onClick={() => {
                    setShowQuestions(true)
                    setEditable(true)
                }}>{t("edit_stage")}</button>
                <div style={{ marginBottom: -20 }}>
                    <DashboardTable
                        header={[t("date"), ""]}
                        body={getProfiles()}
                        />
                </div>
            </div> : <EllipsisLoader /> }
            </> : 
            <AddRate class_type={props.class_type} client_id={props.client_id} defaultAnswers={answers} readonly={!editable} defaultValues={values} /> }
        </Modal>
    )
}