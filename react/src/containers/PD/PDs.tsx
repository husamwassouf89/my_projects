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
import ClientsPD from './PDDetails/ClientsPD'
import { Confirm } from '../../components/Alerts/Alerts'
import { ClassesMenu } from '../../components/PredefinedMenus/PredefinedMenus'
import { SelectField } from '../../components/FormElements/FormElements'

import { years } from '../../services/hoc/helpers'

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

    const [classType, setClassType] = useState<any>();
    const [year, setYear] = useState<number>()
    const [quarter, setQuarter] = useState<'q1' | 'q2' | 'q3' | 'q4'>()

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

        ENDPOINTS.pd().index({ page, page_size, class_type_id: classType?.id, year, quarter, keyword })
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

    useEffect(() => {
        tableRef.current?.reset()
        dispatch( pdsSlice.actions.reset() )
    }, [classType, year, quarter])

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
                            <i className="icon-delete" onClick={(e: React.MouseEvent<HTMLLIElement>) => {
                                e.stopPropagation()
                                Confirm({
                                    message: t("delete_confirmation"),
                                    onConfirm: () => remove(pd.id)
                                })
                            }} />
                        </div>
            }
        })

        return data
    }

    // Delete
    const remove = (id: number) => {
        
        dispatch( pdsSlice.actions.setIsLoading(true) )
        ENDPOINTS.pd().delete({ id })
        .then(() => {
            dispatch( pdsSlice.actions.setIsLoading(false) )
            dispatch( pdsSlice.actions.deletePDs([id]) )
        })

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
                <form>
                    <div className="filters">
                        <div className="filter" key="PDFilter">
                            <ClassesMenu
                                value={classType}
                                onChange={(selected: any) => setClassType(selected)}
                                placeholder="Filter by class type"
                                />
                        </div>
                        <div className="filter">
                            <SelectField defaultValue={year ? { label: year, value: year } : undefined} onChange={(selected: { value: number; }) => setYear(selected?.value)} placeholder={t("year")} options={years} />
                        </div>
                        <div className="filter">
                            <SelectField defaultValue={quarter ? { label: quarter?.toUpperCase(), value: quarter } : undefined} onChange={(selected: { value: 'q1' | 'q2' | 'q3' | 'q4'; }) => setQuarter(selected?.value)} placeholder={t("quarter")} options={[
                                { label: "Q1", value: "q1" },
                                { label: "Q2", value: "q2" },
                                { label: "Q3", value: "q3" },
                                { label: "Q4", value: "q4" }
                            ]} />
                        </div>
                    </div>
                </form>
                <TableActionBar
                    title={t("pds")}
                    search={search}
                    showFilter={false}
                    />
                
                <DashboardTable
                    ref={tableRef}
                    header={[ t("class_type"), t("year"), t("quarter"), "" ]}
                    body={generateData()}
                    hasMore={state.hasMore}
                    loadMore={fetchData}
                    />

                <Modal open={showDetails} toggle={() => setShowDetails(false)}>
                    {
                        loadingDetails ? <EllipsisLoader /> :
                        <>
                        <ClientsPD PDDetails={PDDetails} />
                        </>
                    }
                </Modal>

                
            </> : <div className="center"><EllipsisLoader /></div> }
        </>
    )

}