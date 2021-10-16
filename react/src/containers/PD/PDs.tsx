import React, { useEffect, useRef, useState } from 'react'
import { useTranslation } from 'react-multi-lang'

// Redux
import { useDispatch, useSelector } from 'react-redux'
import { pd, pdsSlice, pdsState } from './PDsSlice'

// API
import API from '../../services/api/api'

// Components
import TableActionBar from '../../components/TableActionBar/TableActionBar'
import { DashboardTable } from '../../components/Table/Table'
import { EllipsisLoader, WhiteboxLoader } from '../../components/Loader/Loader'
import Modal from '../../components/Modal/Modal'
import { Col, Row } from 'react-grid-system'
import { getPercentage, toFixed } from '../../services/hoc/helpers'

export default () => {

    // Translation
    const t = useTranslation()

    // Redux
    const dispatch = useDispatch()
    const state = useSelector( ( state: { pds: pdsState } ) => state.pds )

    // Hooks
    const [keyword, setKeyword] = useState<string>("")
    const [showDetails, setShowDetails] = useState<boolean>(false)
    const [loadingDetails, setLoadingDetails] = useState<boolean>(true)
    const [PDDetails, setPDDetails] = useState<any>(null)
    const [activeTab, setActiveTab] = useState<'default_calculation' | 'final_pd' | 'cumulative_pd'>('cumulative_pd')

    // API
    const ENDPOINTS = new API()

    // Table ref
    type TableHandle = React.ElementRef<typeof DashboardTable>;
    const tableRef = useRef<TableHandle>(null);
    
    // Search
    const search = (value: string) => {
        tableRef.current?.reset()
        dispatch( pdsSlice.actions.reset() )
        setKeyword(value)
    }

    // Fetch Data
    const fetchData = (page: number, page_size: number = 10) => {

        dispatch( pdsSlice.actions.setIsFetching( true ) )

        ENDPOINTS.pd().index({ page, page_size, keyword })
        .then((response: any) => {
            let pds: pd[] = response.data.data.pds.map((pd: any): pd => ({
                id: pd.id,
                class_type: pd.class_type_name,
                quarter: pd.quarter,
                year: pd.year
            }))
            
            dispatch( pdsSlice.actions.addPDs( pds ) )
            dispatch( pdsSlice.actions.setHasMore( page < Number(response.data.data.last_page) ) )
            console.log(page !== Number(response.data.data.last_page))
            if( !state.isLoaded )
                dispatch( pdsSlice.actions.setIsLoaded( true ) )
        })
    }

    interface tableDataType { [key: string]: { [key: string]: any } }
    const generateData: () => tableDataType = () => {
        
        let data: tableDataType = {}
        state.pds.map((pd, index) => {
            data[pd.id] = {
                class_type: pd.class_type,
                year: pd.year,
                quarter: pd.quarter,
                actions: <div className="show-on-hover">
                            <i className="icon-info" onClick={(e: React.MouseEvent<HTMLLIElement>) => {
                                e.stopPropagation()
                                setShowDetails(true)
                                setLoadingDetails(true)
                                ENDPOINTS.pd().show({ id: pd.id })
                                .then((response: any) => {
                                    setPDDetails(response.data.data)
                                    setLoadingDetails(false)
                                })
                            }} />
                        </div>
            }
        })

        return data
    }

    // First fetch
    useEffect(() => {
        if( !state.isLoaded && !state.isFetching )
            fetchData(1)
    }, [])

    return(
        <>
            { state.isLoaded ?
            <>
                { state.isLoading ? <WhiteboxLoader /> : ""}
                <TableActionBar
                    title={t("pds")}
                    search={search}
                    showFilter={false}
                    />
                
                <DashboardTable
                    ref={tableRef}
                    header={[ t("class_type"), t("year"), t("quarter"), "" ]}
                    body={generateData()}
                    hasMore={false}
                    // loadMore={fetchData}
                    />

                <Modal open={showDetails} toggle={() => setShowDetails(false)}>
                    {
                        loadingDetails ? <EllipsisLoader /> :
                        <div style={{ width: "90vw" }}>
                            <ul className="tabs text-left" style={{ marginTop: 0, marginBottom: 40 }}>
                                <li className={activeTab === "cumulative_pd" ? "active" : ""} onClick={() => setActiveTab("cumulative_pd")}>Migration Matrix</li>
                                <li className={activeTab === "default_calculation" ? "active" : ""} onClick={() => setActiveTab("default_calculation")}>Corpr Default Calculation</li>
                                <li className={activeTab === "final_pd" ? "active" : ""} onClick={() => setActiveTab("final_pd")}>Corpr Final PD</li>
                            </ul>
                            { activeTab === "default_calculation" ?
                            <table className="table">
                                <thead>
                                    <tr>
                                        <th>Degree</th>
                                        <th>Default Rate</th>
                                        <th>PD-TTC</th>
                                        <th>PD-TTC after Regression</th>
                                        <th>Asset correlation</th>
                                        <th>TTC to PIT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {[...Array(10)].map((x, i) =>    
                                        <tr>
                                            <td>{i + 1}</td>
                                            <td title={PDDetails?.default_rate[i]}>{getPercentage(PDDetails?.default_rate[i])}</td>
                                            <td title={PDDetails?.pd_ttc[i]}>{getPercentage(PDDetails?.pd_ttc[i])}</td>
                                            <td title={PDDetails?.pd_ttc_after_regression[i]}>{getPercentage(PDDetails?.pd_ttc_after_regression[i])}</td>
                                            <td title={PDDetails?.asset_correlation[i]}>{getPercentage(PDDetails?.asset_correlation[i])}</td>
                                            <td title={PDDetails?.ttc_to_pit[i]}>{getPercentage(PDDetails?.ttc_to_pit[i])}</td>
                                        </tr>
                                    )}
                                </tbody>
                            </table> : activeTab === "final_pd" ?
                            <table className="table">
                                <thead>
                                    <tr>
                                        <th colSpan={4}></th>
                                        <th title={PDDetails?.eco_parameter_base_weight}>{getPercentage( PDDetails?.eco_parameter_base_weight )}</th>
                                        <th title={PDDetails?.eco_parameter_mild_weight}>{getPercentage( PDDetails?.eco_parameter_mild_weight )}</th>
                                        <th title={PDDetails?.eco_parameter_heavy_weight}>{getPercentage( PDDetails?.eco_parameter_heavy_weight )}</th>
                                        <th colSpan={2}></th>
                                    </tr>
                                    <tr>
                                        <th rowSpan={2}>Degree</th>
                                        <th colSpan={3}>FX Macroeconomic Parameter</th>
                                        <th colSpan={3}>Inclusion to the FX Percentages</th>
                                        <th style={{ background: '#723b77' }} rowSpan={2}>Final Calibrated wieghted PD</th>
                                        <th style={{ background: '#723b77' }} rowSpan={2}>Final Calibrated Used PD</th>
                                    </tr>
                                    <tr>
                                        <th style={{ background: "#3498db", borderColor: "#3498db" }}>Base</th>
                                        <th style={{ background: "#f39c12", borderColor: "#f39c12" }}>Mild Covid19 Shock</th>
                                        <th style={{ background: "#e74c3c", borderColor: "#e74c3c" }}>Heavy Covid19 Shock</th>
                                        <th style={{ background: "#3498db", borderColor: "#3498db" }}>Base</th>
                                        <th style={{ background: "#f39c12", borderColor: "#f39c12" }}>Mild Covid19 Shock</th>
                                        <th style={{ background: "#e74c3c", borderColor: "#e74c3c" }}>Heavy Covid19 Shock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {[...Array(10)].map((x, i) =>    
                                        <tr>
                                            <td>{i + 1}</td>
                                            <td title={ PDDetails?.eco_parameter_base_value }>{toFixed( PDDetails?.eco_parameter_base_value, 2 )}</td>
                                            <td title={ PDDetails?.eco_parameter_mild_value }>{toFixed( PDDetails?.eco_parameter_mild_value, 2 )}</td>
                                            <td title={ PDDetails?.eco_parameter_heavy_value }>{toFixed( PDDetails?.eco_parameter_heavy_value, 2 )}</td>
                                            <td title={PDDetails?.inclusion.base[i]}>{getPercentage(PDDetails?.inclusion.base[i])}</td>
                                            <td title={PDDetails?.inclusion.mild[i]}>{getPercentage(PDDetails?.inclusion.mild[i])}</td>
                                            <td title={PDDetails?.inclusion.heavy[i]}>{getPercentage(PDDetails?.inclusion.heavy[i])}</td>
                                            <td title={PDDetails?.final_calibrated_weighted_pd[i]} style={{ fontWeight: 'bold' }}>{getPercentage(PDDetails?.final_calibrated_weighted_pd[i])}</td>
                                            <td title={PDDetails?.final_calibrated_used_PD[i]} style={{ fontWeight: 'bold' }}>{getPercentage(PDDetails?.final_calibrated_used_PD[i])}</td>
                                        </tr>
                                    )}
                                </tbody>
                            </table> :
                            <table className="table">
                                <thead>
                                    <tr>
                                        <th>Degree</th>
                                        <th>1</th>
                                        <th>2</th>
                                        <th>3</th>
                                        <th>4</th>
                                        <th>5</th>
                                        <th>6</th>
                                        <th>7</th>
                                        <th>8</th>
                                        <th>9</th>
                                        <th>10</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {[...Array(10)].map((x, i) =>
                                        <tr>
                                            <td>{i + 1}</td>
                                            {[...Array(10)].map((x, j) =>
                                                <td title={PDDetails?.pd[i][j]}>{getPercentage(PDDetails?.pd[i][j])}</td>
                                            )}
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                            }
                        </div>
                    }
                </Modal>

                
            </> : <div className="center"><EllipsisLoader /></div> }
        </>
    )

}