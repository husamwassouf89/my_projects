import React, { useEffect, useRef, useState } from 'react'
import { useTranslation } from 'react-multi-lang'

// Redux
import { useDispatch, useSelector } from 'react-redux'
import { StagingSlice, StagingState, staging } from './StagingSlice'

// API
import API from '../../services/api/api'

// Components
import TableActionBar from '../../components/TableActionBar/TableActionBar'
import { DashboardTable } from '../../components/Table/Table'
import { EllipsisLoader, WhiteboxLoader } from '../../components/Loader/Loader'
import { ClassesMenu } from '../../components/PredefinedMenus/PredefinedMenus'
import { SelectField } from '../../components/FormElements/FormElements'

import { years } from '../../services/hoc/helpers'
import { Link } from 'react-router-dom'

export default () => {

    // Translation
    const t = useTranslation()

    // Redux
    const dispatch = useDispatch()
    const state = useSelector( ( state: { staging: StagingState } ) => state.staging )

    // Hooks
    const [keyword, setKeyword] = useState<string>("")

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
        dispatch( StagingSlice.actions.reset() )
        setKeyword(value)
    }

    // Fetch Data
    const fetchData = (page: number, page_size: number = 10) => {

        dispatch( StagingSlice.actions.setIsFetching( true ) )

        ENDPOINTS.staging_profile().staging_list({ page, page_size, class_type_id: classType?.value, year, quarter, keyword })
        .then((response: any) => {
            let staging_list: staging[] = response.data.data.clients.map((staging: any): staging => ({
                id: staging.id,
                class_type: staging.class_type_name,
                cif: staging.cif,
                name: staging.name,
                financial_status: staging.financial_status,
                stage: staging.stage_no
            }))
            
            dispatch( StagingSlice.actions.addStaging( staging_list ) )
            dispatch( StagingSlice.actions.setHasMore( page < Number(response.data.data.last_page) ) )
            console.log(page !== Number(response.data.data.last_page))
            if( !state.isLoaded )
                dispatch( StagingSlice.actions.setIsLoaded( true ) )
        })
    }

    useEffect(() => {
        tableRef.current?.reset()
        dispatch( StagingSlice.actions.reset() )
    }, [classType, year, quarter])

    interface tableDataType { [key: string]: { [key: string]: any } }
    const generateData: () => tableDataType = () => {
        
        let data: tableDataType = {}
        state.staging_list.map((staging, index) => {
            data[staging.id] = {
                cif: staging.cif,
                name: staging.name,
                class_type: staging.class_type,
                stage: staging.stage,
                actions: <div className="show-on-hover">
                            <Link to={ `/search-client?cif=${staging.cif}` + (year ? `&year=${year}` : '') + (quarter ? `&quarter=${quarter}` : '') }><i className="icon-info" style={{ color: "#333" }} /></Link>
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
                <form>
                    <div className="filters">
                        <div className="filter" key="StagingFilter">
                            <ClassesMenu
                                isClearable
                                value={classType}
                                onChange={(selected: any) => setClassType(selected)}
                                placeholder="Filter by class type"
                                />
                        </div>
                        <div className="filter">
                            <SelectField isClearable defaultValue={year ? { label: year, value: year } : undefined} onChange={(selected: { value: number; }) => setYear(selected?.value)} placeholder={t("year")} options={years} />
                        </div>
                        <div className="filter">
                            <SelectField isClearable defaultValue={quarter ? { label: quarter?.toUpperCase(), value: quarter } : undefined} onChange={(selected: { value: 'q1' | 'q2' | 'q3' | 'q4'; }) => setQuarter(selected?.value)} placeholder={t("quarter")} options={[
                                { label: "Q1", value: "q1" },
                                { label: "Q2", value: "q2" },
                                { label: "Q3", value: "q3" },
                                { label: "Q4", value: "q4" }
                            ]} />
                        </div>
                    </div>
                </form>
                <TableActionBar
                    title={t("staging")}
                    search={search}
                    showFilter={false}
                    />
                
                <DashboardTable
                    ref={tableRef}
                    header={[ t("cif"), t("name"), t("class_type"), t("stage"), "" ]}
                    body={generateData()}
                    hasMore={state.hasMore}
                    loadMore={fetchData}
                    />
                
            </> : <div className="center"><EllipsisLoader /></div> }
        </>
    )

}