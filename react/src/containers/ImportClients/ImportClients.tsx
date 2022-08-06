import React from 'react'
import { useState } from 'react'
import { useTranslation } from 'react-multi-lang'
import { InputField, SelectField, Textarea } from '../../components/FormElements/FormElements'
import { WhiteboxLoader } from '../../components/Loader/Loader'

import Import from '../../assets/images/vectors/import.svg'
import DetailsModal from '../../components/DetailsModal/DetailsModal'
import { Col, Row } from 'react-grid-system'
import FileUploader from '../../components/FileUploader/FileUploader'
import API from '../../services/api/api'
import { toast } from 'react-toastify'
import { years } from '../../services/hoc/helpers'
import { Confirm } from '../../components/Alerts/Alerts'

export default (props: { type: 'clients' | 'banks' | 'documents' | 'limits'; link: string; }) => {

    const { type } = props;

    // Hooks
    const [isLoading, setIsLoading] = useState<boolean>(false)
    const [importFile, setImportFile] = useState<string | null>(null)
    const [submitError, setSubmitError] = useState<boolean>(false)
    const [year, setYear] = useState<number | null>(null)
    const [quarter, setQuarter] = useState<'q1' | 'q2' | 'q3' | 'q4' | null>(null)

    // Translation
    const t = useTranslation()

    // API
    const ENDPOINTS = new API()

    // Import
    const importClients = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault()

        Confirm({
            message: "How do you want to import this data?",
            okayText: 'Append to old data',
            cancelText: 'Replace with old data',
            onAction: (action) => {
                setIsLoading(true)

                const endpoint = type === 'limits' ? ENDPOINTS.clients().import_limits : ENDPOINTS.clients().store

                endpoint({
                    path: importFile || "",
                    year: String(year) || "",
                    quarter: quarter || "q1",
                    type,
                    replace: action === 'cancel' ? true : undefined
                })
                    .then((response: any) => {
                        toast("Your clients file has been imported successfully!", {
                            progressStyle: { background: "#925b97" }
                        })
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
        });

    }

    return (
        <div className="import-clients">

            <form style={{ maxWidth: 500, background: "#F9F9F9", padding: "100px 40px", borderRadius: 10, position: 'relative' }} onSubmit={importClients}>
                {isLoading ? <WhiteboxLoader /> : ""}
                <h1 className="text-center" style={{ margin: "0 0 40px" }}>{t("import_clients")}</h1>
                <SelectField error={ submitError && !year ? t("required_error") : "" } onChange={(selected: { value: number; }) => setYear(selected.value)} bg="white" placeholder={t("year")} options={years} />
                <SelectField error={ submitError && !quarter ? t("required_error") : "" } onChange={(selected: { value: 'q1' | 'q2' | 'q3' | 'q4'; }) => setQuarter(selected.value)} bg="white" placeholder={t("quarter")} options={[
                    { label: "Q1", value: "q1" },
                    { label: "Q2", value: "q2" },
                    { label: "Q3", value: "q3" },
                    { label: "Q4", value: "q4" }
                ]} />
                <FileUploader
                    required
                    type="clients"
                    onStartUploading={() => {}}
                    onErrorUploading={() => {}}
                    onRemove={() => {}}
                    onUpload={(file) => {
                        setImportFile(file.data[0])
                    }} />
                <div className="text-center margin-top-40"><button className="button bg-gold color-white round" style={{ padding: "0 50px" }}>{t("import")}</button></div>
                <p className='text-center'><a href={props.link} style={{ color: '#925b97', textDecoration: 'none' }}>Download import template</a></p>
            </form>
            {/* <img src={Import} alt="Import" className="search-image" /> */}

        </div>
    )

}