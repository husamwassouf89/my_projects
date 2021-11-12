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
import { getPercentage, numberWithCommas, toFixed } from '../../../services/hoc/helpers'
import { FinancialStatusMenu } from '../../../components/PredefinedMenus/PredefinedMenus'

export default () => {
    
    // History
    const history = useHistory();

    // Hooks
    const [isLoading, setIsLoading] = useState<boolean>(false)
    const [showDetails, setShowDetails] = useState<boolean>(false)
    const [cif, setCIF] = useState<number | null>(null)
    const [client, setClient] = useState<any>(null)
    const [active_account, setActiveAccount] = useState<number>(0)
    const [financialStatus, setFinancialStatus] = useState<string>('');
    
    // Years & Quarters
    const [years, setYears] = useState<any>()
    const [selectedYear, setSelectedYear] = useState<any>()
    const [selectedQuarter, setSelectedQuarter] = useState<any>()
    const [activeAccountInfo, setActiveAcountInfo] = useState<any>()

    const [isOpenEditRate, setIsOpenEditRate] = useState<boolean>(false)
    const [isOpenEditStage, setIsOpenEditStage] = useState<boolean>(false)
    const [isOpenECL, setIsOpenECL] = useState<boolean>(false)

    const [moreECLDetails, setMoreECLDetails] = useState<boolean>(false)

    // Translation
    const t = useTranslation()

    // API
    const ENDPOINTS = new API()

    const search = ((e?: React.FormEvent<HTMLFormElement>, customCIF?: number) => {
        e?.preventDefault()

        let search_cif = cif || customCIF

        if(!search_cif)
            return

        setIsLoading(true)
        const params = new URLSearchParams(location.search);
        params.set('cif', String(search_cif));
        window.history.replaceState({}, '', `${location.pathname}?${params}`);
        ENDPOINTS.clients().search_cif({ cif: search_cif })
        .then((response: any) => {
            setIsLoading(false)
            if(response.data.data === null) {
                toast("We couldn't find a client with this CIF.", {
                    progressStyle: { background: "tomato" }
                })
                return
            }
            setClient(response.data.data)
            setFinancialStatus(response.data?.data?.financial_status);
            setShowDetails(true)
        })

    })

    useEffect(() => {
        const query = new URLSearchParams(location.search);
        const query_cif = query.get('cif')
        if(query_cif && !isLoading && !showDetails) {
            setCIF(Number(query_cif))
            search(undefined, Number(query_cif))
        }
    }, [])

    useEffect(() => {
        if(!client)
            return
        const query = new URLSearchParams(location.search);
        const query_year = query.get('year')
        const query_quarter = query.get('quarter')
        let new_years: any = {}
        let current: number | null = null
        client.client_accounts[active_account].account_infos?.map((item: any, index: number) => {
            if(typeof new_years[item.year] === "undefined") {
                new_years[item.year] = { label: item.year, value: item.year, quarters: [] }
            }
            new_years[item.year].quarters.push({
                label: item.quarter,
                value: index
            })
            if(query_year && query_quarter) {
                if(query_year == item.year && query_quarter == item.quarter) {
                    current = index
                    setSelectedYear({ label: item.year, value: item.year })
                    setSelectedQuarter({ label: item.quarter, value: index })
                }
            } else if(!current) {
                current = index
                setSelectedYear({ label: item.year, value: item.year })
                setSelectedQuarter({ label: item.quarter, value: index })
            }
        })
        if(!current) {
            let year = client.client_accounts[active_account].account_infos[0]?.year;
            let quarter = client.client_accounts[active_account].account_infos[0]?.quarter;
            setSelectedYear({ label: year, value: year });
            setSelectedQuarter({ label: quarter, value: quarter });
        }
        setYears(new_years)
        setActiveAcountInfo(current || 0)
    }, [client, active_account])

    const changeYear = (selected: any) => {
        setSelectedYear(selected)
        setSelectedQuarter(years[selected.value].quarters[0])
        setActiveAcountInfo(years[selected.value].quarters[0].value)
        const params = new URLSearchParams(location.search);
        params.set('year', String(selected.value));
        window.history.replaceState({}, '', `${location.pathname}?${params}`);
    }

    const changeQuarter = (selected: any) => {
        setSelectedQuarter(selected)
        setActiveAcountInfo(selected.value)
        const params = new URLSearchParams(location.search);
        params.set('quarter', String(selected.value));
        window.history.replaceState({}, '', `${location.pathname}?${params}`);
    }

    return (
        <div className="search-client">

            <DetailsModal data={{}} isOpen={false} toggle={() => { }} />

            {showDetails ?
                <>
                    {isLoading ? <WhiteboxLoader /> : ""}
                    <div className="search-client-actions">
                        <Row>
                            <Col md={2.5}>
                                <h2>{t("search_for_client")}</h2>
                            </Col>
                            <Col md={9.5}>
                                    <Row>
                                        <Col md={2} component="form" onSubmit={(e) => e.preventDefault()}>
                                            <InputField
                                                onKeyPress={(e: React.KeyboardEvent<HTMLInputElement>) => {
                                                    e.stopPropagation();
                                                    if(e.key === "Enter")
                                                        search()
                                                }}
                                                style={{ background: "#FFF", border: "1px solid #DDD" }}
                                                value={cif}
                                                onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => { setCIF(Number(e.target.value)) }}
                                                placeholder={t("client_cif")} />
                                        </Col>
                                        <Col md={2.5} component="form" onSubmit={(e) => e.preventDefault()}>
                                            <SelectField placeholder="Year" options={Object.values(years)} value={selectedYear} onChange={(selected: any) => changeYear(selected)} />
                                        </Col>
                                        <Col md={2.5} component="form"onSubmit={(e) => e.preventDefault()}>
                                            <SelectField placeholder="Quarter" options={years[selectedYear.value].quarters} value={selectedQuarter} onChange={(selected: any) => changeQuarter(selected)} />
                                        </Col>
                                        <Col md={5} style={{ position: "relative", top: 11, textAlign: "right" }} className="actions">
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
                                <td style={{ background: '#17a656', color: "#FFF" }}>{numberWithCommas(toFixed(client.client_accounts.map((account: any) => Number(account?.account_infos[activeAccountInfo]?.ecl || 0)).reduce((a: number, b: number) => a + b, 0), 2))}</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <ul className="tabs">
                        {
                            client.client_accounts?.map((account: any, index: number) => (
                                <li className={active_account === index ? "active" : ""} onClick={() => setActiveAccount(index)}>{t("account")}: {account.loan_key} / {account.type_name}</li>
                            ))
                        }
                    </ul>
                    
                    <table className="details-table margin-top-50" style={{ width: "100%" }}>
                        <tbody>
                            <tr>
                                <td>Stage</td>
                                <td>{client.client_accounts[active_account]?.account_infos[activeAccountInfo].stage_no || 'N/A'}</td>
                                <td>Grade</td>
                                <td>{client.client_accounts[active_account]?.account_infos[activeAccountInfo].final_grade || 'N/A'}</td>
                                <td>PD</td>
                                <td>{getPercentage(client.client_accounts[active_account]?.account_infos[activeAccountInfo].pd)}</td>
                            </tr>
                            <tr>
                                <td>LGD</td>
                                <td>{client.client_accounts[active_account]?.account_infos[activeAccountInfo].final_lgd ? getPercentage(client.client_accounts[active_account]?.account_infos[activeAccountInfo].final_lgd) : 'N/A'}</td>
                                <td>EAD</td>
                                <td>{client.client_accounts[active_account]?.account_infos[activeAccountInfo].ead ? numberWithCommas(toFixed(Number(client.client_accounts[active_account]?.account_infos[activeAccountInfo].ead), 2)) : 'N/A'}</td>
                                <td style={{ background: "#3595f6", color: "#FFF" }}>Account ECL</td>
                                <td style={{ background: "#2478ce", color: "#FFF" }}>{numberWithCommas(toFixed(Number(client.client_accounts[active_account]?.account_infos[activeAccountInfo].ecl), 2))}</td>
                            </tr>
                        </tbody>
                    </table>

                    <button className="button color-gold margin-top-30" onClick={() => setIsOpenECL(true)}>Show ECL Details</button>

                    <table className="details-table margin-top-30" style={{ width: "100%" }}>
                        <tbody>
                            <tr>
                                <td>{t("type")}</td>
                                <td>{client.client_accounts[active_account]?.type_name}</td>
                                <td>{t("number_of_reschedule")}</td>
                                <td>{client.client_accounts[active_account]?.account_infos[activeAccountInfo].number_of_reschedule}</td>
                            </tr>
                            <tr>
                                <td>{t("currency_name")}</td>
                                <td>{client.client_accounts[active_account]?.currency_name}</td>
                                <td>{t("pay_method")}</td>
                                <td>{client.client_accounts[active_account]?.account_infos[activeAccountInfo].pay_method}</td>
                            </tr>
                            <tr>
                                <td>{t("guarantee_ccy")}</td>
                                <td>{client.client_accounts[active_account]?.gu_currency_name}</td>
                                <td>{t("cm_guarantee")}</td>
                                <td>{numberWithCommas(client.client_accounts[active_account]?.account_infos[activeAccountInfo].cm_guarantee)}</td>
                            </tr>
                            <tr>
                                <td>{t("outstanding_lcy")}</td>
                                <td>{numberWithCommas(client.client_accounts[active_account]?.account_infos[activeAccountInfo].outstanding_lcy)}</td>
                                <td>{t("past_due_days")}</td>
                                <td>{client.client_accounts[active_account]?.account_infos[activeAccountInfo].past_due_days}</td>
                            </tr>
                            <tr>
                                <td>{t("outstanding_fcy")}</td>
                                <td>{numberWithCommas(client.client_accounts[active_account]?.account_infos[activeAccountInfo].outstanding_fcy)}</td>
                                <td>{t("pv_re_guarantees")}</td>
                                <td>{numberWithCommas(client.client_accounts[active_account]?.account_infos[activeAccountInfo].pv_re_guarantees)}</td>
                            </tr>
                            <tr>
                                <td>{t("accrued_interest_lcy")}</td>
                                <td>{numberWithCommas(client.client_accounts[active_account]?.account_infos[activeAccountInfo].accrued_interest_lcy)}</td>
                                <td>{t("pv_securities_guarantees")}</td>
                                <td>{numberWithCommas(client.client_accounts[active_account]?.account_infos[activeAccountInfo].pv_securities_guarantees)}</td>
                            </tr>
                            <tr>
                                <td>{t("suspended_lcy")}</td>
                                <td>{numberWithCommas(client.client_accounts[active_account]?.account_infos[activeAccountInfo].suspended_lcy)}</td>
                                <td>{t("estimated_value_of_stock_collateral")}</td>
                                <td>{numberWithCommas(client.client_accounts[active_account]?.account_infos[activeAccountInfo].estimated_value_of_stock_collateral)}</td>
                            </tr>
                            <tr>
                                <td>{t("interest_received_in_advance_lcy")}</td>
                                <td>{client.client_accounts[active_account]?.account_infos[activeAccountInfo].interest_received_in_advance_lcy}</td>
                                <td>{t("estimated_value_of_real_estate_collateral")}</td>
                                <td>{numberWithCommas(client.client_accounts[active_account]?.account_infos[activeAccountInfo].estimated_value_of_real_estate_collateral)}</td>
                            </tr>
                            <tr>
                                <td>{t("st_date")}</td>
                                <td>{client.client_accounts[active_account]?.account_infos[activeAccountInfo].st_date}</td>
                                <td>{t("80_per_estimated_value_of_real_estate_collateral")}</td>
                                <td>{numberWithCommas(client.client_accounts[active_account].account_infos[activeAccountInfo]['80_per_estimated_value_of_real_estate_collateral'])}</td>
                            </tr>
                            <tr>
                                <td>{t("mat_date")}</td>
                                <td>{client.client_accounts[active_account]?.account_infos[activeAccountInfo].mat_date}</td>
                                <td>{t("interest_rate")}</td>
                                <td>{getPercentage(client.client_accounts[active_account]?.account_infos[activeAccountInfo].interest_rate)}</td>
                            </tr>
                            <tr>
                                <td>{t("sp_date")}</td>
                                <td>{client.client_accounts[active_account]?.account_infos[activeAccountInfo].sp_date}</td>
                                <td>{t("mortgages")}</td>
                                <td>{numberWithCommas(client.client_accounts[active_account]?.account_infos[activeAccountInfo].mortgages)}</td>
                            </tr>
                        </tbody>
                    </table>
                    <br /><br />
                    <ClientProfile isOpen={isOpenEditRate} toggle={() => setIsOpenEditRate(false)} client_id={client.id} class_type={client.class_type_id} financial_status={client.financial_status} changeFinancialStatus={setFinancialStatus} />
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
                                {client.client_accounts[active_account]?.account_infos[activeAccountInfo].ecl_data?.map((item: any) => (
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
                </> :
                <>
                    <form style={{ maxWidth: 500, background: "#F9F9F9", padding: "100px 40px", borderRadius: 10, position: 'relative' }} onSubmit={search}>
                        {isLoading ? <WhiteboxLoader /> : ""}
                        <h1 className="text-center" style={{ margin: "0 0 40px" }}>{t("search_for_client")}</h1>
                        <InputField
                            value={cif}
                            onChange={(e: React.ChangeEvent<HTMLInputElement>) => setCIF(Number(e.target.value))}
                            style={{ background: "#FFF", border: "1px solid #DDD" }}
                            placeholder={t("client_cif")} />
                        <div className="text-center margin-top-40"><button className="button bg-gold color-white round" style={{ padding: "0 50px" }}>{t("search_client")}</button></div>
                    </form>
                    <img src={Search} alt="Search" className="search-image" />
                </>}
        </div>
    )

}