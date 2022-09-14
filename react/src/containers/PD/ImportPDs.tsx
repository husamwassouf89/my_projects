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
import { Confirm } from '../../components/Alerts/Alerts'

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
    const [ecoParameterBaseValue, setEcoParameterBaseValue] = useState<string>("1")
    const [ecoParameterBaseWeight, setEcoParameterBaseWeight] = useState<string>("0.3")
    const [ecoParameterMildValue, setEcoParameterMildValue] = useState<string>("1")
    const [ecoParameterMildWeight, setEcoParameterMildWeight] = useState<string>("0.3")
    const [ecoParameterHeavyValue, setEcoParameterHeavyValue] = useState<string>("1")
    const [ecoParameterHeavyWeight, setEcoParameterHeavyWeight] = useState<string>("0.4")

    // API
    const ENDPOINTS = new API()

    const calcWeight = (weight: string): number => {
        let n = parseFloat(weight);
        return n > 1 ? n / 100 : n;
    }

    // Import
    const importPD = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault()
        
        setSubmitError(true)
        
        if(!classType || !year || !quarter || !importFile)
            return
        
        setIsLoading(true)

        ENDPOINTS.pd().store({
            class_type_id: classType || 0,
            attachment_ids: attachments,
            path: importFile,
            quarter,
            year: "" + year,
            eco_parameter_base_value: Number(ecoParameterBaseValue),
            eco_parameter_base_weight: calcWeight(ecoParameterBaseWeight),
            eco_parameter_mild_value: Number(ecoParameterMildValue),
            eco_parameter_mild_weight: calcWeight(ecoParameterMildWeight),
            eco_parameter_heavy_value: Number(ecoParameterHeavyValue),
            eco_parameter_heavy_weight: calcWeight(ecoParameterHeavyWeight)
        })
        .then((response: any) => {
            if(!response.data.data)
                Confirm({ message: response?.data?.message, onConfirm: () => {}, hideCancel: true })
            else
                toast(response?.data?.message, {
                    progressStyle: { background: "#925b97" }
                })
            // toast("Your PD file has been imported successfully!", {
            //     progressStyle: { background: "#925b97" }
            // })
        })
        .catch((error: any) => {
            toast(error?.response?.data?.error, {
                progressStyle: { background: "#925b97" }
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
                <ClassesMenu error={ submitError && !classType ? t("required_error") : "" } onChange={(selected: { value: number; }) => setClassType(selected.value)} bg="white" placeholder={t("class_type")} />
                <SelectField error={ submitError && !year ? t("required_error") : "" } onChange={(selected: { value: number; }) => setYear(selected.value)} bg="white" placeholder={t("year")} options={years} />
                <SelectField error={ submitError && !quarter ? t("required_error") : "" } onChange={(selected: { value: 'q1' | 'q2' | 'q3' | 'q4'; }) => setQuarter(selected.value)} bg="white" placeholder={t("quarter")} options={[
                    { label: "Q1", value: "q1" },
                    { label: "Q2", value: "q2" },
                    { label: "Q3", value: "q3" },
                    { label: "Q4", value: "q4" },
                ]} />
            { (classType !== 6 && classType !== 7) &&
                <>
                <div className="weight">
                    <label>Eco parameter - <strong>Base</strong></label>
                    <Row>
                        <Col md={6}>
                            <InputField bg={"white"} onChange={(e: React.ChangeEvent<HTMLInputElement>) => setEcoParameterBaseWeight(e.target.value)} value={ecoParameterBaseWeight} placeholder={t("weight")} />
                        </Col>
                        <Col md={6}>
                            <InputField bg={"white"} onChange={(e: React.ChangeEvent<HTMLInputElement>) => setEcoParameterBaseValue(e.target.value)} value={ecoParameterBaseValue} placeholder={t("value")} />
                        </Col>
                    </Row>
                </div>

                <div className="weight">
                    <label>Eco parameter - <strong>Mild</strong></label>
                    <Row>
                        <Col md={6}>
                            <InputField bg={"white"} onChange={(e: React.ChangeEvent<HTMLInputElement>) => setEcoParameterMildWeight(e.target.value)} value={ecoParameterMildWeight} placeholder={t("weight")} />
                        </Col>
                        <Col md={6}>
                            <InputField bg={"white"} onChange={(e: React.ChangeEvent<HTMLInputElement>) => setEcoParameterMildValue(e.target.value)} value={ecoParameterMildValue} placeholder={t("value")} />
                        </Col>
                    </Row>
                </div>

                <div className="weight">
                    <label>Eco parameter - <strong>Heavy</strong></label>
                    <Row>
                        <Col md={6}>
                            <InputField bg={"white"} onChange={(e: React.ChangeEvent<HTMLInputElement>) => setEcoParameterHeavyWeight(e.target.value)} value={ecoParameterHeavyWeight} placeholder={t("weight")} />
                        </Col>
                        <Col md={6}>
                            <InputField bg={"white"} onChange={(e: React.ChangeEvent<HTMLInputElement>) => setEcoParameterHeavyValue(e.target.value)} value={ecoParameterHeavyValue} placeholder={t("value")} />
                        </Col>
                    </Row>
                </div>
                </>
                }

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
                <p className='text-center'><a href="#" style={{ color: '#925b97', textDecoration: 'none' }}>Download import template</a></p>
            </form>
            <br /> <br />
            <img src={Import} alt="Import" className="search-image" />

        </div>
    )

}