import React from 'react'
import { useState } from 'react'
import { useTranslation } from 'react-multi-lang'
import { InputField, NumberField, SelectField, Textarea } from '../../components/FormElements/FormElements'
import { WhiteboxLoader } from '../../components/Loader/Loader'

import Import from '../../assets/images/vectors/import.svg'
import DetailsModal from '../../components/DetailsModal/DetailsModal'
import { Col, Row } from 'react-grid-system'
import FileUploader from '../../components/FileUploader/FileUploader'
import { toast } from 'react-toastify'
import API from '../../services/api/api'
import { ClassesMenu } from '../../components/PredefinedMenus/PredefinedMenus'

export default () => {

    // Hooks
    const [isLoading, setIsLoading] = useState<boolean>(false)

    // Translation
    const t = useTranslation()

    // Years
    let currentYear = (new Date()).getFullYear()
    let oldestYear = currentYear - 50
    let years = Array.from({ length: (oldestYear - currentYear) / -1 + 1}, (_, i) => ({ value: currentYear + (i * -1), label: currentYear + (i * -1) }))

    // Fields
    const [submitError, setSubmitError] = useState<boolean>(false)
    const [classType, setClassType] = useState<number | null>(null)
    const [year, setYear] = useState<number | null>(null)
    const [quarter, setQuarter] = useState<'q1' | 'q2' | 'q3' | 'q4' | null>(null)
    const [importFile, setImportFile] = useState<string | null>(null)
    const [attachments, setAttachments] = useState<number[]>([])
    const [ecoParameterBaseValue, setEcoParameterBaseValue] = useState<number>(1)
    const [ecoParameterBaseWeight, setEcoParameterBaseWeight] = useState<number>(1)
    const [ecoParameterMildValue, setEcoParameterMildValue] = useState<number>(1)
    const [ecoParameterMildWeight, setEcoParameterMildWeight] = useState<number>(1)
    const [ecoParameterHeavyValue, setEcoParameterHeavyValue] = useState<number>(1)
    const [ecoParameterHeavyWeight, setEcoParameterHeavyWeight] = useState<number>(1)

    // API
    const ENDPOINTS = new API()

    // Import
    const importPD = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault()
        
        setSubmitError(true)
        
        if(!classType || !year || !quarter || !importFile)
            return
        
        setIsLoading(true)

        ENDPOINTS.pd().store({
            class_type_id: classType || 0,
            attachments,
            path: importFile,
            quarter,
            year,
            eco_parameter_base_value: ecoParameterBaseValue,
            eco_parameter_base_weight: ecoParameterBaseWeight,
            eco_parameter_mild_value: ecoParameterMildValue,
            eco_parameter_mild_weight: ecoParameterMildWeight,
            eco_parameter_heavy_value: ecoParameterHeavyValue,
            eco_parameter_heavy_weight: ecoParameterHeavyWeight
        })
        .then((response: any) => {
            toast("Your PD file has been imported successfully!", {
                progressStyle: { background: "#1ABC9C" }
            })
        })
        .finally(() => {
            setIsLoading(false)
        })

    }

    return (
        <div className="import-clients">

            <form style={{ maxWidth: 500, background: "#F9F9F9", padding: "40px", borderRadius: 10, position: 'relative' }} onSubmit={importPD}>
                {isLoading ? <WhiteboxLoader /> : ""}
                <h1 className="text-center" style={{ margin: "0 0 40px" }}>{t("import_clients")}</h1>
                <ClassesMenu error={ submitError && !classType ? t("required_error") : "" } onChange={(selected: { value: number; }) => setClassType(selected.value)} bg="white" placeholder="Class type" />
                <SelectField error={ submitError && !year ? t("required_error") : "" } onChange={(selected: { value: number; }) => setYear(selected.value)} bg="white" placeholder="PD year" options={years} />
                <SelectField error={ submitError && !quarter ? t("required_error") : "" } onChange={(selected: { value: 'q1' | 'q2' | 'q3' | 'q4'; }) => setQuarter(selected.value)} bg="white" placeholder="PD quarter" options={[
                    { label: "Q1", value: "q1" },
                    { label: "Q2", value: "q2" },
                    { label: "Q3", value: "q3" },
                    { label: "Q4", value: "q4" },
                ]} />
                
                <div className="weight">
                    <label>Eco parameter - <strong>Base</strong></label>
                    <Row>
                        <Col md={6}>
                            <NumberField bg={"white"} min={0} max={1} onChange={(e: React.ChangeEvent<HTMLInputElement>) => setEcoParameterBaseWeight(Number(e.target.value))} value={ecoParameterBaseWeight} placeholder="Weight" />
                        </Col>
                        <Col md={6}>
                            <NumberField bg={"white"} onChange={(e: React.ChangeEvent<HTMLInputElement>) => setEcoParameterBaseValue(Number(e.target.value))} value={ecoParameterBaseValue} placeholder="Value" />
                        </Col>
                    </Row>
                </div>

                <div className="weight">
                    <label>Eco parameter - <strong>Mild</strong></label>
                    <Row>
                        <Col md={6}>
                            <NumberField bg={"white"} min={0} max={1} onChange={(e: React.ChangeEvent<HTMLInputElement>) => setEcoParameterMildWeight(Number(e.target.value))} value={ecoParameterMildWeight} placeholder="Weight" />
                        </Col>
                        <Col md={6}>
                            <NumberField bg={"white"} onChange={(e: React.ChangeEvent<HTMLInputElement>) => setEcoParameterMildWeight(Number(e.target.value))} value={ecoParameterMildValue} placeholder="Value" />
                        </Col>
                    </Row>
                </div>

                <div className="weight">
                    <label>Eco parameter - <strong>Heavy</strong></label>
                    <Row>
                        <Col md={6}>
                            <NumberField bg={"white"} min={0} max={1} onChange={(e: React.ChangeEvent<HTMLInputElement>) => setEcoParameterHeavyWeight(Number(e.target.value))} value={ecoParameterHeavyWeight} placeholder="Weight" />
                        </Col>
                        <Col md={6}>
                            <NumberField bg={"white"} onChange={(e: React.ChangeEvent<HTMLInputElement>) => setEcoParameterHeavyWeight(Number(e.target.value))} value={ecoParameterHeavyValue} placeholder="Value" />
                        </Col>
                    </Row>
                </div>

                <FileUploader
                    required
                    labelIdle="Select import file"
                    type={"pd"}
                    onStartUploading={() => {}}
                    onErrorUploading={() => {}}
                    onRemove={() => {}}
                    onUpload={(file) => {
                        setImportFile(file.data[0])
                    }} />
                <FileUploader
                    type="attachments"
                    allowMultiple={true}
                    labelIdle="Attachments"
                    onStartUploading={() => {}}
                    onErrorUploading={() => {}}
                    onRemove={() => {}}
                    onUpload={(file) => {
                        setAttachments(prevAttachments => [ ...prevAttachments, file.data[0] ])
                    }} />
                <div className="text-center margin-top-40"><button className="button bg-gold color-white round" style={{ padding: "0 50px" }}>{t("import")}</button></div>
            </form>
            <br /> <br />
            <img src={Import} alt="Import" style={{ position: 'fixed', top: 150, right: 0, height: "calc(100vh - 150px)" }} />

        </div>
    )

}