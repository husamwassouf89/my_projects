import React, { useState } from 'react'

// Redux
import { useDispatch, useSelector } from 'react-redux'
import { predefinedMenusSlice, predefinedState } from './PredefinedMenusSlice'

// API
import API from '../../services/api/api'

// Components
import { SelectField } from '../FormElements/FormElements'

export const ClassesMenu = (props: any) => {

    // Redux
    const dispatch = useDispatch()
    const state: predefinedState = useSelector((state: { predefined_menus: predefinedState }) => state.predefined_menus)

    // Hooks
    const [isFetching, setIsFetching] = useState<boolean>(false)

    // API
    const ENDPOINTS = new API()

    const fetchData = () => {
        
        ENDPOINTS.other().predefined()
        .then((response: any) => {
            let list = response.data.data.class_types.map((item: any) => ({ value: item.id, label: item.name }))
            dispatch( predefinedMenusSlice.actions.setClasses({ list }) )
            setIsFetching(false)
        })

    }

    return (
        <SelectField
            {...props}
            isLoading={isFetching}
            options={state.classes.list}
            onMenuOpen={() => {
                if (!state.classes.isLoaded && !isFetching) {
                    setIsFetching(true)
                    fetchData()
                }

            }}
        />
    )

}


export const CategoriesMenu = (props: any) => {

    // Redux
    const dispatch = useDispatch()
    const state: predefinedState = useSelector((state: { predefined_menus: predefinedState }) => state.predefined_menus)

    // Hooks
    const [isFetching, setIsFetching] = useState<boolean>(false)

    // API
    const ENDPOINTS = new API()

    const fetchData = () => {
        
        ENDPOINTS.other().predefined()
        .then((response: any) => {
            let list = response.data.data.categories.map((item: any) => ({ value: item.id, label: item.name }))
            dispatch( predefinedMenusSlice.actions.setCategories({ list }) )
            setIsFetching(false)
        })

    }

    return (
        <SelectField
            {...props}
            isLoading={isFetching}
            options={state.categories.list}
            onMenuOpen={() => {
                if (!state.categories.isLoaded && !isFetching) {
                    setIsFetching(true)
                    fetchData()
                }

            }}
        />
    )

}