import { ReactSVG, useEffect, useState } from "react";
import { Col, Row } from "react-grid-system";
import { Checkbox, InputField, NumberField, SimpleCheckbox } from "../../components/FormElements/FormElements";
import { WhiteboxLoader } from "../../components/Loader/Loader";
import API from "../../services/api/api";

export default () => {

    const [activeTab, setActiveTab] = useState(0);

    const [generalFields, setGeneralFields] = useState<{ id: number; value: number | string; description: string; }[]>([]);
    const [savingGenerael, setSavingGeneral] = useState(false);
    const [isLoading, setIsLoading] = useState(true);

    // Documents
    const [documentsFields, setDocumentsFields] = useState<{ id: number; ccf: number | string; name: string; }[]>([]);
    const [savingDocuments, setSavingDocuments] = useState(false);

    // PD & LGD
    const [preDefined, setPreDefined] = useState<any>({});
    const [lgdFields, setLgdFields] = useState<any>({});
    const [lgdColumns, setLgdColumns] = useState<string[]>([]);

    const ENDPOINTS = new API();

    useEffect(() => {
        ENDPOINTS.settings().general()
            .then((res: any) => {
                setGeneralFields(res.data?.data);
                setIsLoading(false);
            });
        ENDPOINTS.settings().documents({ page: 1, page_size: 1000 })
            .then((res: any) => {
                setDocumentsFields(res.data?.data?.document_types);
                setIsLoading(false);
            });
        ENDPOINTS.settings().predefined({ page: 1, page_size: 1000 })
            .then((res: any) => {
                let tempPreDefined = { ...preDefined };
                res.data?.data['pre-defined']?.map((item: any) => {
                    if(!tempPreDefined[item.class_type_name]) tempPreDefined[item.class_type_name] = {};
                    if(!tempPreDefined[item.class_type_name][item.stage_name]) tempPreDefined[item.class_type_name][item.stage_name] = [];
                    tempPreDefined[item.class_type_name][item.stage_name].push({
                        id: item.id,
                        grade: item.grade_name,
                        pd: item.pd,
                        lgd: item.lgd,
                        currency_type: item.currency_type
                    });
                });
                setPreDefined(tempPreDefined);
            });
        ENDPOINTS.settings().lgd({ page: 1, page_size: 1000 })
            .then((res: any) => {
                let tempLgd = { ...lgdFields };
                let tempLgdColumns = [...lgdColumns];
                res.data?.data['lgd-guarantee']?.map((item: any) => {
                    if(!tempLgd[item.guarantee_name]) tempLgd[item.guarantee_name] = {};
                    if(!tempLgd[item.guarantee_name][item.stage_name]) tempLgd[item.guarantee_name][item.stage_name] = [];
                    tempLgd[item.guarantee_name][item.stage_name].push(item);
                    if(!tempLgdColumns.includes(item.class_type_name))
                        tempLgdColumns.push(item.class_type_name)
                });
                setLgdFields(tempLgd);
                setLgdColumns(tempLgdColumns);
            });
    }, []);

    return (
        <>
            {isLoading ? <WhiteboxLoader /> :
                <div>
                    <ul className="tabs">
                        {["General", "Credit Conversion Factors(CCF)", "PD & LGD"].map((item, index) => <li className={activeTab === index ? 'active' : ''} onClick={() => setActiveTab(index)}>{item}</li>)}
                    </ul>

                    {/* General */}
                    { activeTab === 0 &&
                    <div style={{ maxWidth: 400, marginTop: 40 }}>
                        {generalFields?.map((field, index) =>
                        <>
                        { field.id == 1 ?
                        <Checkbox checked={field.value === 1} label={field.description.replace(' (0: false, 1:true)', '')} onChange={(e: any) => {
                            setGeneralFields(prev => {
                                const temp = [...prev];
                                temp[index].value = e.target.checked ? 1 : 0;
                                return temp;
                            })
                        }} /> :
                        <NumberField label={field.description} value={field.value} onChange={(e: React.ChangeEvent<HTMLInputElement>) => {
                            setGeneralFields(prev => {
                                const temp = [...prev];
                                temp[index].value = e.target.value;
                                return temp;
                            })
                        }} /> }
                        </>
                        )}
                        <br /><br />
                        <button className="button bg-gold color-white" style={{ padding: '0 50px' }} disabled={savingGenerael} onClick={() => {
                            setSavingGeneral(true);
                            Promise.all(generalFields.map(field => ENDPOINTS.settings().saveGeneral({ id: field.id, value: field.value })))
                                .then(() => setSavingGeneral(false));
                        }}>{savingGenerael ? 'Saving...' : 'Save'}</button>
                        <br /><br />
                    </div> }

                    {/* Documents */}
                    { activeTab === 1 &&
                    <div style={{ maxWidth: 400, marginTop: 40 }}>
                        {documentsFields?.map((field, index) => <NumberField label={field.name} value={field.ccf} onChange={(e: React.ChangeEvent<HTMLInputElement>) => {
                            setDocumentsFields(prev => {
                                const temp = [...prev];
                                temp[index].ccf = e.target.value;
                                return temp;
                            })
                        }} />)}
                        <br /><br />
                        <button className="button bg-gold color-white" style={{ padding: '0 50px' }} disabled={savingDocuments} onClick={() => {
                            setSavingDocuments(true);
                            Promise.all(documentsFields.map(field => ENDPOINTS.settings().saveDocuments({ id: field.id, ccf: field.ccf })))
                                .then(() => setSavingDocuments(false));
                        }}>{savingDocuments ? 'Saving...' : 'Save'}</button>
                        <br /><br />
                    </div> }

                    {/* PD & LGD */}
                    { activeTab === 2 &&
                    <>
                    <h3>Guarantee</h3>
                    {
                        <table className="table">
                            <thead>
                                <tr>
                                    <th>Stage</th>
                                    {lgdColumns.map(col => <th>{col}</th>)}
                                </tr>
                            </thead>
                            <tbody>
                                {
                                    Object.keys(lgdFields).map(key => (
                                        <>
                                            <tr><td style={{ paddingLeft: 20, fontWeight: 'bold', fontSize: 18 }} colSpan={lgdColumns.length + 1}>{key}</td></tr>
                                            {
                                                Object.keys(lgdFields[key]).map(stageKey => (
                                                    <tr>
                                                        <td style={{ paddingLeft: 20 }}>{stageKey}</td>
                                                        {lgdColumns.map(col => <td style={{ padding: '0 10px' }}>{lgdFields[key][stageKey].find((item: any) => item.class_type_name === col) &&
                                                        <NumberField label="Value" value={lgdFields[key][stageKey].find((item: any) => item.class_type_name === col).value} style={{ background: "#FFF", border: "1px solid #DDD" }} onChange={(e: React.ChangeEvent<HTMLInputElement>) => {
                                                            ENDPOINTS.settings().saveLgd({ id: lgdFields[key][stageKey].find((item: any) => item.class_type_name === col).id, value: +e.target.value });
                                                            const temp = { ...lgdFields };
                                                            temp[key][stageKey][lgdFields[key][stageKey].findIndex((item: any) => item.class_type_name === col)].value = +e.target.value;
                                                            setLgdFields(temp);
                                                        }} />
                                                        }</td>)}
                                                    </tr>
                                                ))
                                            }
                                        </>
                                    ))
                                }
                            </tbody>
                        </table>
                    }
                    {
                        Object.keys(preDefined).map((key) => (
                            <>
                            <h3>{key}</h3>
                            <table className="table">
                                <thead>
                                    <tr>
                                        <th style={{ minWidth: 100 }}>Grade</th>
                                        {
                                            preDefined[key][Object.keys(preDefined[key])[0]]?.map((item: any) => <th>{key === 'Central Bank' ? item.currency_type : item.grade}</th>)
                                        }
                                    </tr>
                                </thead>
                                <tbody>
                                    {
                                        Object.keys(preDefined[key]).map((stageKey) => (
                                            <tr>
                                                <td style={{ textAlign: 'center' }}>{stageKey}</td>
                                                { preDefined[key][stageKey]?.map((item: any, index: any) => 
                                                <td style={{ padding: '0 10px' }}>
                                                    { item.pd !== null &&
                                                    <NumberField label="PD" value={item.pd === -1 ? undefined : item.pd} style={{ background: "#FFF", border: "1px solid #DDD" }} onChange={(e: React.ChangeEvent<HTMLInputElement>) => {
                                                        ENDPOINTS.settings().savePredefined({ id: item.id, pd: +e.target.value, lgd: item.lgd });
                                                        const temp = { ...preDefined };
                                                        temp[key][stageKey][index].pd = +e.target.value;
                                                        setPreDefined(temp);
                                                    }} />
                                                    }
                                                    <NumberField label="LGD" value={item.lgd == -1 ? undefined : item.lgd} style={{ background: "#FFF", border: "1px solid #DDD" }} onChange={(e: React.ChangeEvent<HTMLInputElement>) => {
                                                        ENDPOINTS.settings().savePredefined({ id: item.id, lgd: +e.target.value, pd: item.lgd });
                                                        const temp = { ...preDefined };
                                                        temp[key][stageKey][index].lgd = +e.target.value;
                                                        setPreDefined(temp);
                                                    }} />
                                                </td>
                                                )}
                                            </tr>
                                        ))
                                    }
                                </tbody>
                            </table>
                            </>
                        ))
                    }
                    <br /><br />
                    </> }
                </div>
            }
        </>
    );
}