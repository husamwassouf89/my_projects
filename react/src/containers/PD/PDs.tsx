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

export default () => {

    // Translation
    const t = useTranslation()

    // Redux
    const dispatch = useDispatch()
    const state = useSelector( ( state: { pds: pdsState } ) => state.pds )

    // Hooks
    const [keyword, setKeyword] = useState<string>("")

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

    // const fetchData = (page: number, page_size: number = 10) => {

    //     dispatch( pdsSlice.actions.setIsFetching( true ) )

    //     setTimeout(() => {
    //         dispatch( pdsSlice.actions.setIsLoaded( true ) )
    //     }, 1000);

    // }

    interface tableDataType { [key: string]: { [key: string]: any } }
    const generateData: () => tableDataType = () => {
        
        let data: tableDataType = {}
        state.pds.map((pd, index) => {
            data[pd.id] = {
                year: pd.year,
                quarter: pd.quarter,
                actions: <div className="show-on-hover">
                            <i className="icon-info" />
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
                    header={[ t("year"), t("quarter"), "" ]}
                    body={generateData()}
                    hasMore={false}
                    // loadMore={fetchData}
                    />

                
            </> : <div className="center"><EllipsisLoader /></div> }
        </>
    )

}