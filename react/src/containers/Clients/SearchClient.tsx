import React from 'react'
import { useState } from 'react'
import { useTranslation } from 'react-multi-lang'
import { InputField, SelectField, Textarea } from '../../components/FormElements/FormElements'
import { WhiteboxLoader } from '../../components/Loader/Loader'

import Search from '../../assets/images/vectors/search.svg'
import DetailsModal from '../../components/DetailsModal/DetailsModal'
import { Col, Row } from 'react-grid-system'
import API from '../../services/api/api'
import { toast } from 'react-toastify'
import { useHistory } from 'react-router-dom'
import { useEffect } from 'react'

export default () => {
    
    // History
    const history = useHistory();

    // Hooks
    const [isLoading, setIsLoading] = useState<boolean>(false)
    const [showDetails, setShowDetails] = useState<boolean>(false)
    const [cif, setCIF] = useState<number | null>(null)
    const [client, setClient] = useState<any>(null)
    const [active_account, setActiveAccount] = useState<number>(0)

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

        const params = new URLSearchParams({cif: String(search_cif) });
        history.replace({ pathname: location.pathname, search: params.toString() });  

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

    return (
        <div className="search-client">

            <DetailsModal data={{}} isOpen={false} toggle={() => { }} />

            {showDetails ?
                <>
                    {isLoading ? <WhiteboxLoader /> : ""}
                    <div className="search-client-actions">
                        <Row>
                            <Col md={3}>
                                <h2>{t("search_for_client")}</h2>
                            </Col>
                            <Col md={9}>
                                <form onSubmit={(e: React.FormEvent<HTMLFormElement>) => e.preventDefault()}>
                                    <Row>
                                        <Col md={2}>
                                            <InputField
                                                onKeyPress={(e: React.KeyboardEvent<HTMLInputElement>) => {
                                                    if(e.key === "Enter")
                                                        search()
                                                }}
                                                style={{ background: "#FFF", border: "1px solid #DDD" }}
                                                value={cif}
                                                onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => { setCIF(Number(e.target.value)) }}
                                                placeholder={t("client_cif")} />
                                        </Col>
                                        <Col md={3}>
                                            <SelectField placeholder="PD year" options={[
                                                { label: "2021", value: "2021" },
                                                { label: "2020", value: "2020" },
                                                { label: "2019", value: "2019" },
                                                { label: "2018", value: "2018" },
                                                { label: "2017", value: "2017" },
                                                { label: "2016", value: "2016" },
                                                { label: "2015", value: "2015" },
                                                { label: "2014", value: "2014" }
                                            ]} />
                                        </Col>
                                        <Col md={3}>
                                            <SelectField placeholder="PD quarter" options={[
                                                { label: "Q1", value: "Q1" },
                                                { label: "Q2", value: "Q2" },
                                                { label: "Q3", value: "Q3" },
                                                { label: "Q4", value: "Q4" },
                                            ]} />
                                        </Col>
                                        <Col md={4} style={{ position: "relative", top: 11, textAlign: "right" }}>
                                            <button className="button color-gold">IRS history</button>
                                            <span className="margin-10" />
                                            <button className="button bg-gold color-white">Edit client rate</button>
                                        </Col>
                                    </Row>
                                </form>
                            </Col>
                        </Row>
                    </div>

                    <table className="details-table margin-top-50" style={{ width: "100%" }}>
                        <tbody>
                            <tr>
                                <td>CIF</td>
                                <td>{client.cif}</td>
                                <td>Name</td>
                                <td>{client.name}</td>
                                <td>Branch</td>
                                <td>{client.branch_name}</td>
                                <td>Class type</td>
                                <td>{client.class_type_name}</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <ul className="tabs">
                        {
                            client.client_accounts?.map((account: any, index: number) => (
                                <li className={active_account === index ? "active" : ""} onClick={() => setActiveAccount(index)}>Account: {account.loan_key}</li>
                            ))
                        }
                    </ul>

                    <table className="details-table margin-top-30" style={{ width: "100%" }}>
                        <tbody>
                            <tr>
                                <td>type</td>
                                <td>{client.client_accounts[active_account]?.type_name}</td>
                                <td>80_per_estimated_value_of_real_estate_collateral</td>
                                <td>{client.client_accounts[active_account]['80_per_estimated_value_of_real_estate_collateral']}</td>
                            </tr>
                            <tr>
                                <td>accrued_interest_lcy</td>
                                <td>{client.client_accounts[active_account]?.accrued_interest_lcy}</td>
                                <td>cm_guarantee</td>
                                <td>{client.client_accounts[active_account]?.cm_guarantee}</td>
                            </tr>
                            <tr>
                                <td>currency_name</td>
                                <td>{client.client_accounts[active_account]?.currency_name}</td>
                                <td>estimated_value_of_real_estate_collateral</td>
                                <td>{client.client_accounts[active_account]?.estimated_value_of_real_estate_collateral}</td>
                            </tr>
                            <tr>
                                <td>guarantee_ccy</td>
                                <td>{client.client_accounts[active_account]?.guarantee_ccy}</td>
                                <td>estimated_value_of_stock_collateral</td>
                                <td>{client.client_accounts[active_account]?.estimated_value_of_stock_collateral}</td>
                            </tr>
                            <tr>
                                <td>interest_rate</td>
                                <td>{client.client_accounts[active_account]?.interest_rate}</td>
                                <td>interest_received_in_advance_lcy</td>
                                <td>{client.client_accounts[active_account]?.interest_received_in_advance_lcy}</td>
                            </tr>
                            <tr>
                                <td>mat_date</td>
                                <td>{client.client_accounts[active_account]?.mat_date}</td>
                                <td>mortgages</td>
                                <td>{client.client_accounts[active_account]?.number_of_installments}</td>
                            </tr>
                            <tr>
                                <td>number_of_reschedule</td>
                                <td>{client.client_accounts[active_account]?.number_of_reschedule}</td>
                                <td>outstanding_fcy</td>
                                <td>{client.client_accounts[active_account]?.outstanding_fcy}</td>
                            </tr>
                            <tr>
                                <td>past_due_days</td>
                                <td>{client.client_accounts[active_account]?.past_due_days}</td>
                                <td>pay_method</td>
                                <td>{client.client_accounts[active_account]?.pay_method}</td>
                            </tr>
                            <tr>
                                <td>pv_re_guarantees</td>
                                <td>{client.client_accounts[active_account]?.pv_re_guarantees}</td>
                                <td>pv_securities_guarantees</td>
                                <td>{client.client_accounts[active_account]?.sp_date}</td>
                            </tr>
                            <tr>
                                <td>st_date</td>
                                <td>{client.client_accounts[active_account]?.st_date}</td>
                                <td>suspended_lcy</td>
                                <td>{client.client_accounts[active_account]?.suspended_lcy}</td>
                            </tr>
                        </tbody>
                    </table>
                    <br /><br />
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
                    <img src={Search} alt="Search" style={{ position: 'fixed', top: 150, right: 0, height: "calc(100vh - 150px)" }} />
                </>}
        </div>
    )

}