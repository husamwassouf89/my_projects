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
                <FileUploader
                    type="clients"
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