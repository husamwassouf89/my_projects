import React, { useEffect, useRef, useState } from 'react'
import { useTranslation } from 'react-multi-lang'

// Redux
import { useDispatch, useSelector } from 'react-redux'
import { client, clientsSlice, clientsState } from './ClientsSlice'

// API
import API from '../../services/api/api'

// Components
import TableActionBar from '../../components/TableActionBar/TableActionBar'
import { DashboardTable } from '../../components/Table/Table'
import { EllipsisLoader, WhiteboxLoader } from '../../components/Loader/Loader'
import { Link } from 'react-router-dom'

export default () => {

    // Translation
    const t = useTranslation()

    // Redux
    const dispatch = useDispatch()
    const state = useSelector( ( state: { clients: clientsState } ) => state.clients )

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
        dispatch( clientsSlice.actions.reset() )
        setKeyword(value)
    }

    // Fetch Data
    const fetchData = (page: number, page_size: number = 10) => {

        dispatch( clientsSlice.actions.setIsFetching( true ) )

        ENDPOINTS.clients().index({ page, page_size })
        .then((response: any) => {
            let clients: client[] = response.data.data.clients.map((client: any): client => ({
                id: client.id,
                loan_key: client.loan_key,
                cif: client.cif,
                name: client.name,
                class_type: client.class_type_name,
                type: client.type
            }))
            
            dispatch( clientsSlice.actions.addClients( clients ) )
            dispatch( clientsSlice.actions.setHasMore( page < Number(response.data.data.last_page) ) )
            console.log(page !== Number(response.data.data.last_page))
            if( !state.isLoaded )
                dispatch( clientsSlice.actions.setIsLoaded( true ) )
        })
    }

    interface tableDataType { [key: string]: { [key: string]: any } }
    const generateData: () => tableDataType = () => {
        
        let data: tableDataType = {}
        state.clients.map((client, index) => {
            data[client.id] = {
                loan_key: client.loan_key,
                cif: client.cif,
                name: client.name,
                class_type: client.class_type,
                type: client.type,
                actions: <div className="show-on-hover">
                            <Link to={ "/search-client?cif=" + client.cif }><i className="icon-info" style={{ color: "#333" }} /></Link>
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
                    title={t("clients")}
                    search={search}
                    showFilter={false}
                    />
                
                <DashboardTable
                    ref={tableRef}
                    header={[ t("loan_key"), t("cif"), t("name"), t("class_type"), t("type"), "" ]}
                    body={generateData()}
                    hasMore={state.hasMore}
                    loadMore={fetchData}
                    />

                
            </> : <div className="center"><EllipsisLoader /></div> }
        </>
    )

}