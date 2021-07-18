import React from 'react'
import { useState } from 'react'
import { useTranslation } from 'react-multi-lang'
import { InputField, SelectField, Textarea } from '../../components/FormElements/FormElements'
import { WhiteboxLoader } from '../../components/Loader/Loader'

import Search from '../../assets/images/vectors/search.svg'
import DetailsModal from '../../components/DetailsModal/DetailsModal'
import { Col, Row } from 'react-grid-system'

export default () => {

    // Hooks
    const [isLoading, setIsLoading] = useState<boolean>(false)
    const [showDetails, setShowDetails] = useState<boolean>(false)
    const [cif, setCIF] = useState<string>("")

    // Translation
    const t = useTranslation()

    const search = ((e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault()
        setIsLoading(true)
        setTimeout(() => {
            setIsLoading(false)
            setShowDetails(true)
        }, 2000);
    })

    return (
        <div className="search-client">

            <DetailsModal data={{}} isOpen={false} toggle={() => { }} />

            {showDetails ?
                <>
                    <div className="search-client-actions">
                        <Row>
                            <Col md={3}>
                                <h2>{t("search_for_client")}</h2>
                            </Col>
                            <Col md={9}>
                                <form action="">
                                    <Row>
                                        <Col md={2}>
                                            <InputField
                                                style={{ background: "#FFF", border: "1px solid #DDD" }}
                                                value={"20120"}
                                                onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => { }}
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
                                <td>20120</td>
                                <td>Loan Key</td>
                                <td>3214109000004</td>
                            </tr>
                            <tr>
                                <td>Branch</td>
                                <td>MAIN BRANCH</td>
                                <td>Class type</td>
                                <td>Corporate</td>
                            </tr>
                            <tr>
                                <td>Type</td>
                                <td>Loans</td>
                                <td>Name</td>
                                <td>كريم خفيف صندل الشحماني</td>
                            </tr>
                            <tr>
                                <td>CCY#</td>
                                <td>001</td>
                                <td>CCY</td>
                                <td>IQD</td>
                            </tr>
                            <tr>
                                <td>Outstanding FCY</td>
                                <td> 115,425,000</td>
                                <td>Outstanding LCY</td>
                                <td> 115,425,000</td>
                            </tr>
                            <tr>
                                <td>Accrued interest LCY</td>
                                <td> 87,441,658</td>
                                <td>Suspended LCY</td>
                                <td> 87,441,658</td>
                            </tr>
                            <tr>
                                <td>Interest received in advance LCY</td>
                                <td>-</td>
                                <td>St. Date</td>
                                <td>18-Aug-09</td>
                            </tr>
                            <tr>
                                <td>Mat. Date</td>
                                <td>31-Dec-14</td>
                                <td>SP. Date</td>
                                <td>31-Dec-14</td>
                            </tr>
                            <tr>
                                <td>Past due days</td>
                                <td>2282</td>
                                <td>Number Of Reschedule</td>
                                <td>1</td>
                            </tr>
                            <tr>
                                <td>Guarantee CCY</td>
                                <td>IQD</td>
                                <td>CM Guarantee</td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>PV Securities Guarantees</td>
                                <td>-</td>
                                <td>PV RE Guarantees</td>
                                <td>520,000,000</td>
                            </tr>
                            <tr>
                                <td>Interest Rate</td>
                                <td>11.00%</td>
                                <td>Number of Installments</td>
                                <td>1</td>
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
                            onChange={(e: React.ChangeEvent<HTMLInputElement>) => setCIF(e.target.value)}
                            style={{ background: "#FFF", border: "1px solid #DDD" }}
                            placeholder={t("client_cif")} />
                        <div className="text-center margin-top-40"><button className="button bg-gold color-white round" style={{ padding: "0 50px" }}>{t("search_client")}</button></div>
                    </form>
                    <img src={Search} alt="Search" style={{ position: 'fixed', top: 150, right: 0, height: "calc(100vh - 150px)" }} />
                </>}
        </div>
    )

}