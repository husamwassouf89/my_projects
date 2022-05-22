import React, { useEffect, useRef, useState } from 'react'
import { useTranslation } from 'react-multi-lang'

// Redux
import { useDispatch, useSelector } from 'react-redux'
import { IRSSlice, IRSState, irs } from './IRSSlice'

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
    const state = useSelector( ( state: { irs: IRSState } ) => state.irs )

    // Hooks
    const [keyword, setKeyword] = useState<string>("")

    const [classType, setClassType] = useState<any>();
    const [year, setYear] = useState<number>()
    const [quarter, setQuarter] = useState<'q1' | 'q2' | 'q3' | 'q4'>()
    const [filter, setFilter] = useState<any>({ label: 'No IRS', value: 'without' });

    // API
    const ENDPOINTS = new API()

    // Table ref
    type TableHandle = React.ElementRef<typeof DashboardTable>;
    const tableRef = useRef<TableHandle>(null);
    
    // Search
    const search = (value: string) => {
        tableRef.current?.reset()
        dispatch( IRSSlice.actions.reset() )
        setKeyword(value)
    }

    // Fetch Data
    const fetchData = (page: number, page_size: number = 10) => {

        dispatch( IRSSlice.actions.setIsFetching( true ) )

        ENDPOINTS.irs().index({ page, page_size, class_type_id: classType?.value, year, quarter, keyword, filter_type: filter?.value })
        .then((response: any) => {
            let IRSs: irs[] = response.data.data.clients.map((irs: any): irs => ({
                id: irs.id,
                class_type: irs.class_type_name,
                cif: irs.cif,
                name: irs.name,
                financial_status: irs.financial_status,
                score: irs.final_score,
                grade: irs.final_grade
            }))
            
            dispatch( IRSSlice.actions.addIRSs( IRSs ) )
            dispatch( IRSSlice.actions.setHasMore( page < Number(response.data.data.last_page) ) )
            console.log(page !== Number(response.data.data.last_page))
            if( !state.isLoaded )
                dispatch( IRSSlice.actions.setIsLoaded( true ) )
        })
    }

    useEffect(() => {
        tableRef.current?.reset()
        dispatch( IRSSlice.actions.reset() )
    }, [classType, year, quarter, filter])

    interface tableDataType { [key: string]: { [key: string]: any } }
    const generateData: () => tableDataType = () => {
        
        let data: tableDataType = {}
        state.IRSs.map((irs, index) => {
            data[irs.id] = {
                class_type: irs.cif,
                year: irs.name,
                quarter: irs.class_type,
                financial_status: irs.financial_status,
                score: irs.score,
                grade: irs.grade,
                actions: <div className="show-on-hover">
                            <Link to={ `/search-client?cif=${irs.cif}` + (year ? `&year=${year}` : '') + (quarter ? `&quarter=${quarter}` : '') }><i className="icon-info" style={{ color: "#333" }} /></Link>
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
                        <div className='filter'>
                            <SelectField
                                placeholder="Filter"
                                options={[
                                    { label: 'All', value: 'all' },
                                    { label: 'Has IRS', value: 'with' },
                                    { label: 'No IRS', value: 'without' }
                                ]}
                                onChange={(selected: any) => setFilter(selected)}
                                value={filter}
                            />
                        </div>
                        <div className="filter" key="IRSFilter">
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
                    title={t("irs")}
                    search={search}
                    showFilter={false}
                    />
                
                <DashboardTable
                    ref={tableRef}
                    header={[ t("cif"), t("name"), t("class_type"), t("financial_status"), t("score"), t("grade"), "" ]}
                    body={generateData()}
                    hasMore={state.hasMore}
                    loadMore={fetchData}
                    />
                
            </> : <div className="center"><EllipsisLoader /></div> }
        </>
    )

}