import React, { useState } from 'react'

// Redux
import { useDispatch, useSelector } from 'react-redux'
import { predefinedMenusSlice, predefinedState } from './PredefinedMenusSlice'

// API
import API from '../../services/api/api'

// Components
import { SelectField } from '../FormElements/FormElements'

export const MaterialsMenu = (props: any) => {

    // Redux
    const dispatch = useDispatch()
    const state: predefinedState = useSelector((state: { predefined_menus: predefinedState }) => state.predefined_menus)

    // Hooks
    const [isFetching, setIsFetching] = useState<boolean>(false)

    // API
    const ENDPOINTS = new API()

    const fetchData = () => {
        
        ENDPOINTS.components('material').index({ page: 1, page_size: 1000 })
        .then((response: any) => {
            let list = response.data.data.components.map((component: any) => ({ value: component.id, label: component.name }))
            // dispatch( predefinedMenusSlice.actions.setMaterials({ list }) )
            setIsFetching(false)
        })

    }

    return (
        <SelectField
            {...props}
            isLoading={isFetching}
            options={state.materials.list}
            onMenuOpen={() => {
                if (!state.materials.isLoaded && !isFetching) {
                    setIsFetching(true)
                    fetchData()
                }

            }}
        />
    )

}

export const ServicesMenu = (props: any) => {

    // Redux
    const dispatch = useDispatch()
    const state: predefinedState = useSelector((state: { predefined_menus: predefinedState }) => state.predefined_menus)

    // Hooks
    const [isFetching, setIsFetching] = useState<boolean>(false)

    // API
    const ENDPOINTS = new API()

    const fetchData = () => {
        
        ENDPOINTS.components('service').index({ page: 1, page_size: 1000 })
        .then((response: any) => {
            let list = response.data.data.components.map((component: any) => ({ value: component.id, label: component.name }))
            // dispatch( predefinedMenusSlice.actions.setServices({ list }) )
            setIsFetching(false)
        })

    }

    return (
        <SelectField
            {...props}
            isLoading={isFetching}
            options={state.services.list}
            onMenuOpen={() => {
                if (!state.services.isLoaded && !isFetching) {
                    setIsFetching(true)
                    fetchData()
                }

            }}
        />
    )

}


export const RolesMenu = (props: any) => {

    // Redux
    const dispatch = useDispatch()
    const state: predefinedState = useSelector((state: { predefined_menus: predefinedState }) => state.predefined_menus)

    // Hooks
    const [isFetching, setIsFetching] = useState<boolean>(false)

    // API
    const ENDPOINTS = new API()

    const fetchData = () => {
        
        ENDPOINTS.roles().index({ page: 1, page_size: 100 })
        .then((response: any) => {
            let list = response.data.data.roles.map((role: any) => ({ value: role.id, label: role.name }))
            dispatch( predefinedMenusSlice.actions.setRoles({ list }) )
            setIsFetching(false)
        })
        

    }

    return (
        <SelectField
            {...props}
            isLoading={isFetching}
            options={state.roles.list}
            onMenuOpen={() => {
                if (!state.roles.isLoaded && !isFetching) {
                    setIsFetching(true)
                    fetchData()
                }

            }}
        />
    )

}


export const EmployeesMenu = (props: any) => {

    // Redux
    const dispatch = useDispatch()
    const state: predefinedState = useSelector((state: { predefined_menus: predefinedState }) => state.predefined_menus)

    // Hooks
    const [isFetching, setIsFetching] = useState<boolean>(false)

    // API
    const ENDPOINTS = new API()

    const fetchData = () => {
        
        ENDPOINTS.employees().index({ page: 1, page_size: 100, has_user: props.has_user })
        .then((response: any) => {
            let list = response.data.data.employees.map((employee: any) => ({ value: employee.id, label: employee.name }))
            dispatch( predefinedMenusSlice.actions.setEmployees({ list }) )
            setIsFetching(false)
        })
        

    }

    return (
        <SelectField
            {...props}
            isLoading={isFetching}
            options={state.employees.list}
            onMenuOpen={() => {
                if (!state.employees.isLoaded && !isFetching) {
                    setIsFetching(true)
                    fetchData()
                }

            }}
        />
    )

}


export const ClientsMenu = (props: any) => {

    // Redux
    const dispatch = useDispatch()
    const state: predefinedState = useSelector((state: { predefined_menus: predefinedState }) => state.predefined_menus)

    // Hooks
    const [isFetching, setIsFetching] = useState<boolean>(false)

    // API
    const ENDPOINTS = new API()

    const fetchData = () => {
        
        ENDPOINTS.clients().index({ page: 1, page_size: 100 })
        .then((response: any) => {
            let list = response.data.data.clients.map((client: any) => ({ value: client.id, label: client.name }))
            dispatch( predefinedMenusSlice.actions.setClients({ list }) )
            setIsFetching(false)
        })
        

    }

    return (
        <SelectField
            {...props}
            isLoading={isFetching}
            options={state.clients.list}
            onMenuOpen={() => {
                if (!state.clients.isLoaded && !isFetching) {
                    setIsFetching(true)
                    fetchData()
                }

            }}
        />
    )

}


export const SuppliersMenu = (props: any) => {

    // Redux
    const dispatch = useDispatch()
    const state: predefinedState = useSelector((state: { predefined_menus: predefinedState }) => state.predefined_menus)

    // Hooks
    const [isFetching, setIsFetching] = useState<boolean>(false)

    // API
    const ENDPOINTS = new API()

    const fetchData = () => {
        
        ENDPOINTS.suppliers().index({ page: 1, page_size: 100 })
        .then((response: any) => {
            let list = response.data.data.suppliers.map((supplier: any) => ({ value: supplier.id, label: supplier.name }))
            dispatch( predefinedMenusSlice.actions.setSuppliers({ list }) )
            setIsFetching(false)
        })
        

    }

    return (
        <SelectField
            {...props}
            isLoading={isFetching}
            options={state.suppliers.list}
            onMenuOpen={() => {
                if (!state.suppliers.isLoaded && !isFetching) {
                    setIsFetching(true)
                    fetchData()
                }

            }}
        />
    )

}



export const ProductsMenu = (props: any) => {

    // Redux
    const dispatch = useDispatch()
    const state: predefinedState = useSelector((state: { predefined_menus: predefinedState }) => state.predefined_menus)

    // Hooks
    const [isFetching, setIsFetching] = useState<boolean>(false)

    // API
    const ENDPOINTS = new API()

    const fetchData = () => {
        
        ENDPOINTS.products().index({ page: 1, page_size: 100 })
        .then((response: any) => {
            let list = response.data.data.products.map((product: any) => ({ value: product.id, label: product.name }))
            dispatch( predefinedMenusSlice.actions.setProducts({ list }) )
            setIsFetching(false)
        })
        

    }

    return (
        <SelectField
            {...props}
            isLoading={isFetching}
            options={state.products.list}
            onMenuOpen={() => {
                if (!state.products.isLoaded && !isFetching) {
                    setIsFetching(true)
                    fetchData()
                }

            }}
        />
    )

}


export const OrdersMenu = (props: any) => {

    // Redux
    const dispatch = useDispatch()
    const state: predefinedState = useSelector((state: { predefined_menus: predefinedState }) => state.predefined_menus)

    // Hooks
    const [isFetching, setIsFetching] = useState<boolean>(false)

    // API
    const ENDPOINTS = new API()

    const fetchData = () => {
        
        ENDPOINTS.orders().index({ page: 1, page_size: 100 })
        .then((response: any) => {
            let list = response.data.data.orders.map((order: any) => ({ value: order.id, label: order.order_no }))
            dispatch( predefinedMenusSlice.actions.setOrders({ list }) )
            setIsFetching(false)
        })
        

    }

    return (
        <SelectField
            {...props}
            isLoading={isFetching}
            options={state.orders.list}
            onMenuOpen={() => {
                if (!state.orders.isLoaded && !isFetching) {
                    setIsFetching(true)
                    fetchData()
                }

            }}
        />
    )

}