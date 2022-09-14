import React, { useState } from 'react'
import { useTranslation } from 'react-multi-lang'

import './TableActionBar.css'

interface ActionBarProps {
    title?: string;
    search?: Function;
    delete?: Function;
    add?: Function;
    addText?: string;
    showDelete?: boolean;
    showFilter?: boolean;
    second_button?: string;
    second_button_action?: Function;
}

export default (props: ActionBarProps) => {

    const t = useTranslation()

    const [searchValue, setSearchValue] = useState<string>("")

    return(
        <div className="action-bar">
            { props.title ? <h2>{props.title}</h2> : "" }
            { props.search ?
            <div className="search" style={{ display: 'none' }}>
                <input type="text" value={searchValue} placeholder={t('search')}
                onChange={(e: React.ChangeEvent<HTMLInputElement>) => setSearchValue(e.target.value)}
                onKeyPress={(e: React.KeyboardEvent<HTMLInputElement>) => {
                    if(e.key === "Enter" && props.search)
                        props.search(e.currentTarget.value)
                }} />
                { searchValue === "" ?
                <i className="icon-search" /> :
                <i className="icon-close" onClick={() => {
                    setSearchValue("")
                    if(props.search)
                        props.search("")
                }} /> }
            </div> : "" }
            <div className="actions">
                { props.showDelete ? <button className="delete" onClick={() => { if(props.delete) props.delete() }}><i className="icon-delete"></i></button> : "" }
                { props.showFilter === false ?  "" : <button className="filter"><i className="icon-filter-2"></i> {t("filter")}</button> }
                { props.second_button ? <button className="add secondary" onClick={() => { if(props.second_button_action) props.second_button_action() }}>{props.second_button}</button> : "" }
                { props.add ? <button className="add" onClick={() => { if(props.add) props.add() }}><i className="icon-plus"></i> {props.addText}</button> : "" }
            </div>
        </div>
    )

}