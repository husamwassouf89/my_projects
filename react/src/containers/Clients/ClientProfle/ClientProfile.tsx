import { useEffect } from "react"
import { useState } from "react"
import { EllipsisLoader } from "../../../components/Loader/Loader"
import Modal from "../../../components/Modal/Modal"
import { DashboardTable } from "../../../components/Table/Table"
import API from "../../../services/api/api"
import AddRate from "./AddRate"

interface IProps {
    isOpen: boolean;
    toggle: Function;
    client_id: number;
    class_type: number;
}

export default (props: IProps) => {
    
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
            profilesObject[profile.id] = { date: new Date(profile.created_at).toLocaleDateString(), class_type: profile.class_type_name, actions: <div className="show-on-hover"><i className="icon-info" onClick={() => {
                setEditable(false)
                setShowQuestions(true)
                setAnswers(profile.answers.map((answer: any) => answer.option_id))
            }} /></div> };
            return profilesObject;
        }, {});
    }

    return(
        <Modal open={props.isOpen} toggle={props.toggle}>
            { !showQuestions ?
            <>
            { isLoaded ?
            <div style={{ minWidth: 500, textAlign: "left" }}>
                <h2 style={{ margin: "0 0 20px", display: "inline-block" }}>Client's rates</h2>
                <button className="button bg-gold color-white" style={{ float: "right", position: "relative", top: -7 }} onClick={() => {
                    setShowQuestions(true)
                    setEditable(true)
                }}>Add new rate</button>
                <div style={{ marginRight: -30, marginBottom: -20 }}>
                    <DashboardTable
                        header={["Date", "Class type", ""]}
                        body={getProfiles()}
                        />
                </div>
            </div> : <EllipsisLoader /> }
            </> : 
            <AddRate class_type={props.class_type} client_id={props.client_id} defaultAnswers={answers} readonly={!editable} /> }
        </Modal>
    )
}