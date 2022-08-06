import React from 'react'
import { useState } from 'react'
import { useTranslation } from 'react-multi-lang'
import { InputField, SelectField, Textarea } from '../../../components/FormElements/FormElements'
import { WhiteboxLoader } from '../../../components/Loader/Loader'

import Search from '../../../assets/images/vectors/search.svg'
import DetailsModal from '../../../components/DetailsModal/DetailsModal'
import { Col, Row } from 'react-grid-system'
import API from '../../../services/api/api'
import { toast } from 'react-toastify'
import { useHistory } from 'react-router-dom'
import { useEffect } from 'react'
import ClientProfile from './ClientProfle/ClientProfile'
import ClientStage from './StagingProfle/ClientStage'
import Modal from '../../../components/Modal/Modal'
import { getFileName, getPercentage, numberWithCommas, toFixed } from '../../../services/hoc/helpers'
import { FinancialStatusMenu } from '../../../components/PredefinedMenus/PredefinedMenus'
import ImportAttachments from './ImportAttachments/ImportAttachments'

const TYPES = [
    {label: 'ON-Balance', value: 'onbalance'},
    {label: 'OFF-Balance', value: 'offbalance'}
]

const LIMITS = [
    {label: 'LIMITS: ON', value: 'yes'},
    {label: 'LIMITS: OFF', value: 'no'}
]

export default () => {
    
    // History
    const history = useHistory();

    // Hooks
    const [isLoading, setIsLoading] = useState<boolean>(false)
    const [showDetails, setShowDetails] = useState<boolean>(false)
    const [cif, setCIF] = useState<string | null>(null)
    const [client, setClient] = useState<any>(null)
    const [active_account, setActiveAccount] = useState<number>(0)
    const [showLimits, setShowLimits] = useState<'direct_limit_account' | 'undirect_limit_account' | false>(false)
    const [financialStatus, setFinancialStatus] = useState<string>('');
    const [showImportAttachments, setShowImportAttachments] = useState<boolean>(false);
    
    // Years & Quarters
    const [years, setYears] = useState<any>({})
    const [selectedYear, setSelectedYear] = useState<any>()
    const [selectedQuarter, setSelectedQuarter] = useState<any>()
    // const [activeAccountInfo, setActiveAcountInfo] = useState<any>()

    // Client type
    const [clientType, setClientType] = useState<any>(TYPES[0])
    const [limits, setLimits] = useState<any>(LIMITS[1]);

    const [isOpenEditRate, setIsOpenEditRate] = useState<boolean>(false)
    const [isOpenEditStage, setIsOpenEditStage] = useState<boolean>(false)
    const [isOpenECL, setIsOpenECL] = useState<boolean>(false)

    const [moreECLDetails, setMoreECLDetails] = useState<boolean>(false)

    // Translation
    const t = useTranslation()

    // API
    const ENDPOINTS = new API()

    const search = ((e?: React.FormEvent<HTMLFormElement>, customCIF?: string, year?: string, quarter?: string) => {
        e?.preventDefault()

        let search_cif = cif || customCIF

        if(!search_cif)
            return

        setIsLoading(true)
        const params = new URLSearchParams(location.search);
        params.set('cif', String(search_cif == '0' ? '00000' : search_cif));
        window.history.replaceState({}, '', `${location.pathname}?${params}`);
        const query = new URLSearchParams(location.search);
        const type: any = query.get('client_type')
        const limitsParam: any = query.get('limits')
        const query_year =  year || query.get('year')
        const query_quarter =  quarter || query.get('quarter')
    
        if(limitsParam) {
            setShowLimits('direct_limit_account');
        }
        ENDPOINTS.clients().search_cif({ cif: search_cif == '0' ? '00000' : search_cif, balance: type === 'offbalance' ? 'off' : 'on', limit: limitsParam, year: query_year ? +query_year : undefined, quarter: query_quarter || undefined })
        .then((response: any) => {
            setIsLoading(false)
            if(response.data.data === null) {
                toast("We couldn't find a client with this CIF.", {
                    progressStyle: { background: "tomato" }
                })
                return
            }
            // If the response is object then convert it to an array
            if(response.data.data?.client_accounts instanceof Object) {
                response.data.data.client_accounts = Object.values(response.data.data?.client_accounts);
            }
            setClient(response.data.data)
            setFinancialStatus(response.data?.data?.financial_status);
            setShowDetails(true)
            let yearsToSave: any = {};
            response.data.data?.filter?.map((record: any) => {
                if(!yearsToSave[record.year]) yearsToSave[record.year] = { label: record.year, value: record.year, quarters: [] }
                yearsToSave[record.year]?.quarters?.push(record.quarter);
            })
            setYears(yearsToSave);
            if (!selectedYear) setSelectedYear(yearsToSave[Object.keys(yearsToSave)[0]]);
            if (!selectedQuarter) {
                const firstQuarter = yearsToSave[Object.keys(yearsToSave)[0]]?.quarters[0];
                setSelectedQuarter({ label: firstQuarter, value: firstQuarter });
            }
        })
        .catch(() => {
            setIsLoading(false)
            toast("We couldn't find a client with this CIF.", {
                progressStyle: { background: "tomato" }
            })
        })

    })

    useEffect(() => {
        const query = new URLSearchParams(location.search);
        const query_cif = query.get('cif')
        if(query_cif && !isLoading && !showDetails) {
            setCIF(query_cif)
            search(undefined, query_cif)
        }
        const type = query.get('client_type')
        if(type) {
            setClientType(TYPES.find(item => item.value === type) || clientType)
        }
        const limitsParam = query.get('limits');
        if(limitsParam) {
            setLimits(LIMITS.find(item => item.value === limitsParam) || limits)
        }
    }, [])

    // useEffect(() => {
    //     if(!client)
    //         return
    //     const query = new URLSearchParams(location.search);
    //     const query_year = query.get('year')
    //     const query_quarter = query.get('quarter')
    //     let new_years: any = {}
    //     let current: number | null = null
    //     client.client_accounts[active_account]?.account_infos?.map((item: any, index: number) => {
    //         if(typeof new_years[item.year] === "undefined") {
    //             new_years[item.year] = { label: item.year, value: item.year, quarters: [] }
    //         }
    //         new_years[item.year].quarters.push({
    //             label: item.quarter,
    //             value: index
    //         })
    //         if(query_year && query_quarter) {
    //             if(query_year == item.year && query_quarter == item.quarter) {
    //                 current = index
    //                 setSelectedYear({ label: item.year, value: item.year })
    //                 setSelectedQuarter({ label: item.quarter, value: index })
    //             }
    //         } else if(!current) {
    //             current = index
    //             setSelectedYear({ label: item.year, value: item.year })
    //             setSelectedQuarter({ label: item.quarter, value: index })
    //         }
    //     })
    //     if(!current) {
    //         let year = client.client_accounts[active_account]?.account_infos[0]?.year;
    //         let quarter = client.client_accounts[active_account]?.account_infos[0]?.quarter;
    //         setSelectedYear({ label: year, value: year });
    //         setSelectedQuarter({ label: quarter, value: quarter });
    //     }
    //     setYears(new_years)
    //     setActiveAcountInfo(current || 0)
    // }, [client, active_account])

    const changeYear = (selected: any) => {
        setSelectedYear(selected)
        setSelectedQuarter({ label: years[selected.value].quarters[0], value: years[selected.value].quarters[0] })
        // setActiveAcountInfo(years[selected.value].quarters[0].value)
        const params = new URLSearchParams(location.search);
        params.set('year', String(selected.value));
        params.set('quarter', String(years[selected.value].quarters[0]));
        window.history.replaceState({}, '', `${location.pathname}?${params}`);
        search(undefined, undefined, selected.value, years[selected.value].quarters[0])
        setIsLoading(true);
    }

    const changeQuarter = (selected: any) => {
        setSelectedQuarter(selected)
        // setActiveAcountInfo(selected.value)
        const params = new URLSearchParams(location.search);
        params.set('year', String(selectedYear?.value));
        params.set('quarter', String(selected.value));
        window.history.replaceState({}, '', `${location.pathname}?${params}`);
        search(undefined, undefined, selectedYear?.value, selected.value)
        setIsLoading(true);
    }

    const changeClientType = (selected: any) => {
        setClientType(selected);
        const params = new URLSearchParams(location.search);
        params.set('client_type', String(selected.value));
        window.history.replaceState({}, '', `${location.pathname}?${params}`);
        window.location.reload();
    }

    const changeLimits = (selected: any) => {
        setLimits(selected);
        const params = new URLSearchParams(location.search);
        if(selected?.value)
            params.set('limits', String(selected.value));
        else
            params.delete('limits')
        window.history.replaceState({}, '', `${location.pathname}?${params}`);
        window.location.reload();
    }

    // Force stage
    const [showForceStage, setShowForceStage] = useState(false);
    const [stage, setStage] = useState<number>(0);
    const [loadingStage, setLoadingStage] = useState(false);

    // Force grade
    const [showForceGrade, setShowForceGrade] = useState(false);
    const [grade, setGrade] = useState<number>(0);
    const [loadingGrade, setLoadingGrade] = useState(false);

    return (
        <div className="search-client">

            <DetailsModal data={{}} isOpen={false} toggle={() => { }} />

            {showDetails ?
                <>
                    {isLoading ? <WhiteboxLoader /> : ""}
                    <div className="search-client-actions">
                        <Row>
                            <Col md={12}>
                                    <Row>
                                        <Col md={3} component="form" onSubmit={(e) => e.preventDefault()}>
                                            <InputField
                                                onKeyPress={(e: React.KeyboardEvent<HTMLInputElement>) => {
                                                    e.stopPropagation();
                                                    if(e.key === "Enter")
                                                        search()
                                                }}
                                                style={{ background: "#FFF", border: "1px solid #DDD" }}
                                                value={cif == '0' ? '00000' : cif}
                                                onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => { setCIF(e.target.value) }}
                                                placeholder={t("client_cif")} />
                                        </Col>
                                        <Col md={1.5} component="form" onSubmit={(e) => e.preventDefault()}>
                                            <SelectField placeholder="Year" options={Object.values(years)} value={selectedYear} onChange={(selected: any) => changeYear(selected)} />
                                        </Col>
                                        <Col md={1.5} component="form"onSubmit={(e) => e.preventDefault()}>
                                            <SelectField placeholder="Quarter" options={years[selectedYear?.value]?.quarters.map((q: string) => ({ label: q, value: q }))} value={selectedQuarter} onChange={(selected: any) => changeQuarter(selected)} />
                                        </Col>
                                        {/* <Col md={2} component="form"onSubmit={(e) => e.preventDefault()}>
                                            <SelectField placeholder="Client Type" options={TYPES} value={clientType} onChange={(selected: any) => changeClientType(selected)} />
                                        </Col>
                                        <Col md={2} component="form"onSubmit={(e) => e.preventDefault()}>
                                            <SelectField placeholder="Limits" options={LIMITS} value={limits} onChange={(selected: any) => changeLimits(selected)} />
                                        </Col> */}
                                        <Col md={4} style={{ position: "relative", top: 11, textAlign: "right" }} className="actions">
                                            <button className="button color-gold" onClick={() => setIsOpenEditStage(true)}>{t("client_stage")}</button>
                                            <span className="margin-10" />
                                            <button className="button bg-gold color-white" onClick={() => setIsOpenEditRate(true)}>{t("client_rate")}</button>
                                        </Col>
                                    </Row>
                            </Col>
                        </Row>
                    </div>

                    <table className="details-table margin-top-50" style={{ width: "100%", overflow: 'visible' }}>
                        <tbody>
                            <tr>
                                <td>{t("cif")}</td>
                                <td>{client.cif}</td>
                                <td>{t("name")}</td>
                                <td>{client.name}</td>
                                <td>{t("branch")}</td>
                                <td>{client.branch_name}</td>
                            </tr>
                            <tr>
                                <td>{t("class_type")}</td>
                                <td>{client.class_type_name}</td>
                                <td>Financial status</td>
                                <td>
                                    {financialStatus}
                                </td>
                                <td style={{ background: '#1abc62', color: "#FFF" }}>ECL</td>
                                { client.direct_limit_account ?
                                <td style={{ background: '#17a656', color: "#FFF" }}>{numberWithCommas(toFixed(client?.direct_limit_account?.ecl + client?.undirect_limit_account?.ecl, 2))}</td> :
                                <td style={{ background: '#17a656', color: "#FFF" }}>{numberWithCommas(toFixed(client?.client_accounts?.map((account: any) => Number(account?.account_infos[0]?.ecl || 0)).reduce((a: number, b: number) => a + b, 0) + (showLimits ? client.limit_account?.ecl || 0 : 0), 2))}</td>
                                }
                            </tr>
                        </tbody>
                    </table>
                    
                    { client.direct_limit_account &&
                    <>
                        <h2>Limits</h2>
                        <table className='details-table margin-top-30' style={{ width: '100%', overflow: 'visible' }}>
                            <tbody>
                                <tr>
                                    <td>{t('direct_limit')}</td>
                                    <td>{numberWithCommas(client.direct_limit)}</td>
                                    <td>{t('unused_direct_limit')}</td>
                                    <td>{numberWithCommas(client.unused_direct_limit)}</td>
                                    <td>{t('used_direct_limit')}</td>
                                    <td>{numberWithCommas(client.used_direct_limit)}</td>
                                </tr>
                                <tr>
                                    <td>{t('indirect_limit')}</td>
                                    <td>{numberWithCommas(client.un_direct_limit)}</td>
                                    <td>{t('unused_indirect_limit')}</td>
                                    <td>{numberWithCommas(client.unused_undirect_limit)}</td>
                                    <td>{t('used_indirect_limit')}</td>
                                    <td>{numberWithCommas(client.used_un_direct_limit)}</td>
                                </tr>
                            </tbody>
                        </table>
                    </> }
                    
                    <ul className="tabs">
                        {
                            !!client[showLimits || ''] &&
                            <li className={showLimits === 'direct_limit_account' ? 'active' : ''} onClick={() => setShowLimits('direct_limit_account')}>Direct limits account</li>
                        }
                        {
                            !!client.undirect_limit_account &&
                            <li className={showLimits === 'undirect_limit_account' ? 'active' : ''} onClick={() => setShowLimits('undirect_limit_account')}>Undirect limits account</li>
                        }
                        { !client.direct_limit_account &&
                        <>
                        {
                            client.client_accounts?.map((account: any, index: number) => (
                                <li className={active_account === index && !showLimits ? "active" : ""} onClick={() => { setActiveAccount(index); setShowLimits(false); }}>{t("account")}: {account.loan_key} / {account.type_name}</li>
                            ))
                        }
                        </>
                        }
                    </ul>
                    { showLimits ?
                    <>
                    {/* Limits account */}
                    <table className="details-table margin-top-50" style={{ width: "100%", overflow: 'visible' }}>
                        <tbody>
                            <tr>
                                <td>Stage</td>
                                <td>
                                    <div className='force' style={{ display: 'flex', justifyContent: 'space-between', cursor: 'pointer' }} onClick={() => setShowForceStage(true)}>
                                        {client?.stage_no}
                                        <i className="icon-edit" />
                                    </div>
                                </td>
                                <td>Grade</td>
                                <td>
                                    <div className='force' style={{ display: 'flex', justifyContent: 'space-between', cursor: 'pointer' }} onClick={() => setShowForceGrade(true)}>
                                        {client?.final_grade}
                                        <i className="icon-edit" />
                                    </div>
                                </td>
                                <td>PD</td>
                                <td>{getPercentage(client[showLimits || '']?.pd)}</td>
                            </tr>
                            <tr>
                                <td>LGD</td>
                                <td>{client[showLimits || '']?.final_lgd ? getPercentage(client[showLimits || '']?.final_lgd) : 'N/A'}</td>
                                <td>EAD</td>
                                <td>{client[showLimits || '']?.ead ? numberWithCommas(toFixed(Number(client[showLimits || '']?.ead), 2)) : 'N/A'}</td>
                                <td style={{ background: "#3595f6", color: "#FFF" }}>Account ECL</td>
                                <td style={{ background: "#2478ce", color: "#FFF" }}>{numberWithCommas(toFixed(Number(client[showLimits || '']?.ecl), 2))}</td>
                            </tr>
                        </tbody>
                    </table>

                    <button className="button color-gold margin-top-30" onClick={() => setIsOpenECL(true)}>Show ECL Details</button>
                    <br />
                    <br />
                    </> :
                    <>
                    {/* Normal Accounts */}
                    <table className="details-table margin-top-50" style={{ width: "100%" }}>
                        <tbody>
                            <tr>
                                <td>Stage</td>
                                <td>
                                    <div className='force' style={{ display: 'flex', justifyContent: 'space-between', cursor: 'pointer' }} onClick={() => setShowForceStage(true)}>
                                        {client.client_accounts[active_account]?.account_infos[0]?.stage_no}
                                        <i className="icon-edit" />
                                    </div>
                                </td>
                                <td>Grade</td>
                                <td>
                                    <div className='force' style={{ display: 'flex', justifyContent: 'space-between', cursor: 'pointer' }} onClick={() => setShowForceGrade(true)}>
                                        {client.client_accounts[active_account]?.account_infos[0]?.final_grade}
                                        <i className="icon-edit" />
                                    </div>
                                </td>
                                <td>PD</td>
                                <td>{getPercentage(client.client_accounts[active_account]?.account_infos[0]?.pd)}</td>
                            </tr>
                            <tr>
                                <td>LGD</td>
                                <td>{client.client_accounts[active_account]?.account_infos[0]?.final_lgd ? getPercentage(client.client_accounts[active_account]?.account_infos[0]?.final_lgd) : 'N/A'}</td>
                                <td>EAD</td>
                                <td>{client.client_accounts[active_account]?.account_infos[0]?.ead ? numberWithCommas(toFixed(Number(client.client_accounts[active_account]?.account_infos[0]?.ead), 2)) : 'N/A'}</td>
                                <td style={{ background: "#3595f6", color: "#FFF" }}>Account ECL</td>
                                <td style={{ background: "#2478ce", color: "#FFF" }}>{numberWithCommas(toFixed(Number(client.client_accounts[active_account]?.account_infos[0]?.ecl), 2))}</td>
                            </tr>
                        </tbody>
                    </table>

                    <button className="button color-gold margin-top-30" onClick={() => setIsOpenECL(true)}>Show ECL Details</button>

                    <table className="details-table margin-top-30" style={{ width: "100%" }}>
                        <tbody>
                            <tr>
                                <td>{t("facility_type")}</td>
                                <td>{client.client_accounts[active_account]?.type_name}</td>
                                <td>{t("number_of_reschedule")}</td>
                                <td>{client.client_accounts[active_account]?.account_infos[0]?.number_of_reschedule}</td>
                            </tr>
                            <tr>
                                <td>{t("currency_type")}</td>
                                <td>{client.client_accounts[active_account]?.currency_name}</td>
                                <td>{t("guarantee_ccy")}</td>
                                <td>{client.client_accounts[active_account]?.gu_currency_name}</td>
                            </tr>
                            <tr>
                                <td>{t("outstanding_fcy")}</td>
                                <td>{numberWithCommas(client.client_accounts[active_account]?.account_infos[0]?.outstanding_fcy) === numberWithCommas(client.client_accounts[active_account]?.account_infos[0]?.outstanding_lcy) ? '-' : numberWithCommas(client.client_accounts[active_account]?.account_infos[0]?.outstanding_fcy)}</td>
                                <td>{t("cm_guarantee")}</td>
                                <td>{numberWithCommas(client.client_accounts[active_account]?.account_infos[0]?.cm_guarantee)}</td>
                            </tr>
                            <tr>
                                <td>{t("outstanding_lcy")}</td>
                                <td>{numberWithCommas(client.client_accounts[active_account]?.account_infos[0]?.outstanding_lcy)}</td>
                                <td>{t("estimated_value_of_stock_collateral")}</td>
                                <td>{numberWithCommas(client.client_accounts[active_account]?.account_infos[0]?.estimated_value_of_stock_collateral)}</td>
                            </tr>
                            <tr>
                                <td>{t("accrued_interest_lcy")}</td>
                                <td>{numberWithCommas(client.client_accounts[active_account]?.account_infos[0]?.accrued_interest_lcy)}</td>
                                <td>{t("pv_securities_guarantees")}</td>
                                <td>{numberWithCommas(client.client_accounts[active_account]?.account_infos[0]?.pv_securities_guarantees)}</td>
                            </tr>
                            <tr>
                                <td>{t("suspended_lcy")}</td>
                                <td>{numberWithCommas(client.client_accounts[active_account]?.account_infos[0]?.suspended_lcy)}</td>
                                <td>{t("mortgages")}</td>
                                <td>{numberWithCommas(client.client_accounts[active_account]?.account_infos[0]?.mortgages)}</td>
                            </tr>
                            <tr>
                                <td>{t("interest_received_in_advance_lcy")}</td>
                                <td>{client.client_accounts[active_account]?.account_infos[0]?.interest_received_in_advance_lcy}</td>
                                <td>{t("estimated_value_of_real_estate_collateral")}</td>
                                <td>{numberWithCommas(client.client_accounts[active_account]?.account_infos[0]?.estimated_value_of_real_estate_collateral)}</td>
                            </tr>
                            <tr>
                                <td>{t("st_date")}</td>
                                <td>{client.client_accounts[active_account]?.account_infos[0]?.st_date}</td>
                                <td>{t("80_per_estimated_value_of_real_estate_collateral")}</td>
                                <td>{numberWithCommas(client.client_accounts[active_account]?.account_infos[0]?.['80_per_estimated_value_of_real_estate_collateral'])}</td>
                            </tr>
                            <tr>
                                <td>{t("mat_date")}</td>
                                <td>{client.client_accounts[active_account]?.account_infos[0]?.mat_date}</td>
                                <td>{t("pv_re_guarantees")}</td>
                                <td>{numberWithCommas(client.client_accounts[active_account]?.account_infos[0]?.pv_re_guarantees)}</td>
                            </tr>
                            <tr>
                                <td>{t("sp_date")}</td>
                                <td>{client.client_accounts[active_account]?.account_infos[0]?.sp_date}</td>
                                <td>{t("interest_rate")}</td>
                                <td>{getPercentage(client.client_accounts[active_account]?.account_infos[0]?.interest_rate)}</td>
                            </tr>
                            <tr>
                                <td>{t("past_due_days")}</td>
                                <td>{client.client_accounts[active_account]?.account_infos[0]?.past_due_days}</td>
                                <td>{t("pay_method")}</td>
                                <td>{client.client_accounts[active_account]?.account_infos[0]?.pay_method}</td>
                            </tr>
                            { client.client_accounts[active_account]?.account_infos[0]?.account?.document_type &&
                            <tr>
                                <td>{t("document_type")}</td>
                                <td>{client.client_accounts[active_account]?.account_infos[0]?.account?.document_type?.name}</td>
                                <td>{t("document_type_ccf")}</td>
                                <td>{getPercentage(client.client_accounts[active_account]?.account_infos[0]?.account?.document_type?.ccf)}</td>
                            </tr>
                            }

                        </tbody>
                    </table>
                    </> }
                    
                    <br /><br />

                    <div className='client-attachments'>
                        <label>Attachments</label>
                        <div className="attachments">
                            <ul>
                                { client.attachments?.map((attachment: any) => (
                                    <li><a href={attachment?.path}><i className="icon-attachment"></i>{getFileName(attachment?.path)}</a></li>
                                )) }
                                { client.attachments?.length === 0 && <li className='text-center' style={{ padding: 20 }}>No attachments</li> }
                            </ul>
                            <div className="margin-top-20">
                                <button className="button bg-gold color-white" onClick={() => setShowImportAttachments(true)}>Add attachments</button>
                            </div>
                        </div>
                    </div>
                    <br /><br />
                    {/* Import Attachments */}
                    <ImportAttachments open={showImportAttachments} toggle={() => setShowImportAttachments(false)} client_id={client.id} />
                    <ClientProfile isOpen={isOpenEditRate} toggle={() => setIsOpenEditRate(false)} client_id={client.id} class_type={client.class_type_id} financial_status={financialStatus} changeFinancialStatus={setFinancialStatus} />
                    <ClientStage isOpen={isOpenEditStage} toggle={() => setIsOpenEditStage(false)} client_id={client.id} class_type={client.class_type_id} />
                    <Modal open={isOpenECL} toggle={() => setIsOpenECL(false)}>
                        <div className="text-right"><button className={ moreECLDetails ? "button color-white bg-gold" : "button color-gold" } onClick={() => setMoreECLDetails(!moreECLDetails)}>{ moreECLDetails ? "Less Details" : "More Details" }</button></div>
                        <div className="margin-top-20" />
                        <table className="table margin-top-30" style={{ width: "90vw", margin: 0 }}>
                            <thead>
                                <tr>
                                    <th>Repayment date</th>
                                    { moreECLDetails && <th>Days Between</th> }
                                    { moreECLDetails && <th>Repayment Indicator</th> }
                                    <th>Repayment</th>
                                    <th>EAD</th>
                                    { moreECLDetails && <th>Days for Discounting</th> }
                                    <th>PD cum</th>
                                    <th>PD Marginal</th>
                                    <th>LGD</th>
                                    <th>Discount rate</th>
                                    <th>EL</th>
                                    <th>CUM EL</th>
                                    { moreECLDetails && <th>12-M Selector</th> }
                                    <th>12-M EL</th>
                                </tr>
                            </thead>
                            <tbody>
                                {(showLimits ? client[showLimits || '']?.ecl_data : client.client_accounts[active_account]?.account_infos[0]?.ecl_data)?.map((item: any) => (
                                    <tr>
                                        <td>{item.repayment_date}</td>
                                        { moreECLDetails && <td>{item.days_between}</td> }
                                        { moreECLDetails && <td>{item.repayment_indicator}</td> }
                                        <td title={item.repayment}>{numberWithCommas(toFixed(Number(item.repayment), 2))}</td>
                                        <td title={item.ead_end_of_period}>{numberWithCommas(toFixed(Number(item.ead_end_of_period), 2))}</td>
                                        { moreECLDetails && <td>{item.days_for_discount}</td> }
                                        <td title={item.pd_cum}>{getPercentage(item.pd_cum)}</td>
                                        <td title={item.pd_marginal}>{getPercentage(item.pd_marginal)}</td>
                                        <td title={item.lgd}>{getPercentage(item.lgd)}</td>
                                        <td title={item.discount_rate}>{getPercentage(item.discount_rate)}</td>
                                        <td title={item.el}>{numberWithCommas(toFixed(Number(item.el), 2))}</td>
                                        <td title={item.cum_el}>{numberWithCommas(toFixed(Number(item.cum_el), 2))}</td>
                                        { moreECLDetails && <td>{toFixed(Number(item['12_m_selector']), 2)}</td> }
                                        <td title={item['12_m_el']}>{numberWithCommas(toFixed(Number(item['12_m_el']), 2))}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </Modal>
                    <Modal open={showForceStage} toggle={() => setShowForceStage(false)}>
                        { loadingStage && <WhiteboxLoader /> }
                        <div style={{ minWidth: 350 }}>
                            <h2 style={{ marginTop: 0 }}>Force Stage</h2>
                            <InputField
                                defaultValue={client.client_accounts[active_account]?.account_infos[0]?.stage_no}
                                placeholder="Client Stage"
                                onChange={(e: any) => setStage(+e.target.value)}
                                />
                            <button className='button color-white bg-gold' onClick={() => {
                                const stageToSave = stage || client.client_accounts[active_account]?.account_infos[0]?.stage_no;
                                if(stageToSave) {
                                    setLoadingStage(true);
                                    ENDPOINTS.clients().setStage({ id: client.id, stage: stageToSave })
                                    .then(() => {
                                        setLoadingStage(false);
                                        setShowForceStage(false);
                                        search(undefined, cif || '');
                                    });
                                }
                            }}>Save Stage</button>
                        </div>
                    </Modal>
                    <Modal open={showForceGrade} toggle={() => setShowForceGrade(false)}>
                        { loadingGrade && <WhiteboxLoader /> }
                        <div style={{ minWidth: 350 }}>
                            <h2 style={{ marginTop: 0 }}>Force Grade</h2>
                            <InputField
                                defaultValue={client.client_accounts[active_account]?.account_infos[0]?.final_grade}
                                placeholder="Client Grade"
                                onChange={(e: any) => setGrade(e.target.value)} />
                            <button className='button color-white bg-gold' onClick={() => {
                                const gradeToSave = grade || client.client_accounts[active_account]?.account_infos[0]?.final_grade;
                                if(gradeToSave) {
                                    setLoadingGrade(true);
                                    ENDPOINTS.clients().setGrade({ id: client.id, grade: gradeToSave })
                                    .then(() => {
                                        setLoadingGrade(false);
                                        setShowForceGrade(false);
                                        search(undefined, cif || '');
                                    });
                                }
                            }}>Save Grade</button>
                        </div>
                    </Modal>
                </> :
                <>
                    <form style={{ maxWidth: 500, background: "#F9F9F9", padding: "100px 40px", borderRadius: 10, position: 'relative' }} onSubmit={search}>
                        {isLoading ? <WhiteboxLoader /> : ""}
                        <h1 className="text-center" style={{ margin: "0 0 40px" }}>{t("search_for_client")}</h1>
                        <InputField
                            value={cif}
                            onChange={(e: React.ChangeEvent<HTMLInputElement>) => setCIF(e.target.value)}
                            style={{ background: "#FFF", border: "1px solid #DDD" }}
                            placeholder={t("client_cif")} />
                        <div className="text-center margin-top-40"><button className="button bg-gold color-white round" style={{ padding: "0 50px" }}>{t("search_client")}</button></div>
                    </form>
                    {/* <img src={Search} alt="Search" className="search-image" /> */}
                </>}
        </div>
    )

}