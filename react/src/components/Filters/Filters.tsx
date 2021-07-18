import React, { useState } from 'react'
import { useTranslation } from 'react-multi-lang'

import './Filters.css'

export enum SupportedPeriods {
    DAILY,
    WEEKLY,
    MONTHLY,
    YEARLY,
    ALL_THE_TIME
}

interface StatisticsFilterProps {
    supportedTypes?: SupportedPeriods[],
    trigger?: Function
}

export const StatisticsFilter = ( props: StatisticsFilterProps ) => {

    const t = useTranslation()

    const [active, setActive] = useState<number>(0)

    return(
        <ul className="statistics-filter">
            { props.supportedTypes ? props.supportedTypes.map((item, index) => {
                let period = t(SupportedPeriods[item].toLowerCase())
                return(
                <li onClick={() => { setActive(index); if(props.trigger) props.trigger(SupportedPeriods[item].toLowerCase()) }} className={active === index ? "active" : ""}>
                    <i>{ period[0] }</i><i>{ t(period.slice(1)) }</i>
                </li> )
            }) : "" }
        </ul>
    )
}