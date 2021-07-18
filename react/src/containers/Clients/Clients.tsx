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
    // const fetchData = (page: number, page_size: number = 10) => {

    //     dispatch( usersSlice.actions.setIsFetching( true ) )

    //     ENDPOINTS.users().index({ page, page_size, keyword })
    //     .then((response: any) => {
    //         let users: user[] = response.data.data.users.map((user: any): user => ({
    //             employee: user.employee,
    //             id: user.id,
    //             name: user.employee.name,
    //             email: user.email
    //         }))
            
    //         dispatch( usersSlice.actions.addUsers( users ) )
    //         dispatch( usersSlice.actions.setHasMore( page < Number(response.data.data.last_page) ) )
    //         console.log(page !== Number(response.data.data.last_page))
    //         if( !state.isLoaded )
    //             dispatch( usersSlice.actions.setIsLoaded( true ) )
    //     })
    // }

    const fetchData = (page: number, page_size: number = 10) => {

        dispatch( clientsSlice.actions.setIsFetching( true ) )

        setTimeout(() => {
            dispatch( clientsSlice.actions.setIsLoaded( true ) )
        }, 1000);

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
                    title={t("clients")}
                    search={search}
                    showFilter={false}
                    />
                
                <DashboardTable
                    ref={tableRef}
                    header={[ t("loan_key"), t("cif"), t("name"), t("class_type"), t("type"), "" ]}
                    body={generateData()}
                    hasMore={false}
                    // loadMore={fetchData}
                    />

                
            </> : <div className="center"><EllipsisLoader /></div> }
        </>
    )

}