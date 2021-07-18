import React from 'react'
import { useState } from 'react'
import { useTranslation } from 'react-multi-lang'
import { InputField, SelectField, Textarea } from '../../components/FormElements/FormElements'
import { WhiteboxLoader } from '../../components/Loader/Loader'

import Import from '../../assets/images/vectors/import.svg'
import DetailsModal from '../../components/DetailsModal/DetailsModal'
import { Col, Row } from 'react-grid-system'
import FileUploader from '../../components/FileUploader/FileUploader'

export default () => {

    // Hooks
    const [isLoading, setIsLoading] = useState<boolean>(false)

    // Translation
    const t = useTranslation()

    return (
        <div className="import-clients">

            <form style={{ maxWidth: 500, background: "#F9F9F9", padding: "100px 40px", borderRadius: 10, position: 'relative' }}>
                {isLoading ? <WhiteboxLoader /> : ""}
                <h1 className="text-center" style={{ margin: "0 0 40px" }}>{t("import_clients")}</h1>
                <SelectField bg="white" placeholder="PD year" options={[
                    { label: "2021", value: "2021" },
                    { label: "2020", value: "2020" },
                    { label: "2019", value: "2019" },
                    { label: "2018", value: "2018" },
                    { label: "2017", value: "2017" },
                    { label: "2016", value: "2016" },
                    { label: "2015", value: "2015" },
                    { label: "2014", value: "2014" }
                ]} />
                <SelectField bg="white" placeholder="PD quarter" options={[
                    { label: "Q1", value: "Q1" },
                    { label: "Q2", value: "Q2" },
                    { label: "Q3", value: "Q3" },
                    { label: "Q4", value: "Q4" },
                ]} />
                <FileUploader
                    labelIdle="Select import file"
                    field="certificate"
                    type={["other"]}
                    onStartUploading={() => {}}
                    onErrorUploading={() => {}}
                    onRemove={() => {}}
                    onUpload={() => {}} />
                <FileUploader
                    allowMultiple={true}
                    labelIdle="Attachments"
                    field="certificate"
                    type={["other"]}
                    onStartUploading={() => {}}
                    onErrorUploading={() => {}}
                    onRemove={() => {}}
                    onUpload={() => {}} />
                <div className="text-center margin-top-40"><button className="button bg-gold color-white round" style={{ padding: "0 50px" }}>{t("import")}</button></div>
            </form>
            <img src={Import} alt="Import" style={{ position: 'fixed', top: 150, right: 0, height: "calc(100vh - 150px)" }} />

        </div>
    )

}