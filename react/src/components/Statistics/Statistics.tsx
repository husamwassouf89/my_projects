import React, { FunctionComponent } from 'react'
import { Line } from 'react-chartjs-2'

import { StatisticsFilter, SupportedPeriods } from '../Filters/Filters'
import { EllipsisLoader } from '../Loader/Loader'

import './Statistics.css'

interface NumberBoxProps {
    label: string,
    value: number|string|React.ReactNode,
    showFilter?: boolean,
    filterPeriods?: SupportedPeriods[],
    filterTrigger?: Function,
    isLoading: boolean
}

const defaultFilterPeriods = [
    SupportedPeriods.DAILY,
    SupportedPeriods.WEEKLY,
    SupportedPeriods.MONTHLY,
    SupportedPeriods.YEARLY,
    SupportedPeriods.ALL_THE_TIME
]

export const NumberBox: FunctionComponent<NumberBoxProps> = (props: NumberBoxProps) => {
    return (
        <div className="number-box">
            { props.isLoading ?
            <div className="center"><EllipsisLoader /></div> :
            <>
                {props.showFilter ?
                <StatisticsFilter trigger={props.filterTrigger} supportedTypes={props.filterPeriods} /> : ""}
                <h2>{props.label}</h2>
                <span>{props.value}</span>
            </> }
        </div>
    )
}
NumberBox.defaultProps = {
    showFilter: true,
    filterPeriods: defaultFilterPeriods
}


interface LineChartProps {
    title: string,
    labels: (string|number)[],
    datasets: {
        data: number[],
        color: string,
        label: string
    }[]
}

export const LineChart = (props: LineChartProps) => {

    const chartOptions = {
        tooltips: {
            mode: 'index',
            intersect: false
        },
        hover: {
            mode: 'index',
            intersect: false
        },
        responsive: true,
        maintainAspectRatio: false,
        legend: {
            display: true,
            labels: {
                fontColor: '#333',
                fontSize: 10,
                boxWidth: 30
            }
        },
        scales: {
            xAxes: [{
                gridLines: {
                    display: false
                },
                ticks: {
                    autoSkip: true,
                    maxTicksLimit: 10
                }
            }],
            yAxes: [{
                gridLines: {
                    display: true,
                    borderDash: [3],
                    lineWidth: 1,
                    color: '#AAA'
                },
                ticks: {
                    maxTicksLimit: 6,
                    beginAtZero: true,
                }
            }]
        }
    }

    let datasets: any[] = []
    props.datasets.map( item => datasets.push({
        borderColor: item.color,
        pointBackgroundColor: item.color,
        pointHoverBackgroundColor: item.color,
        pointHoverBorderColor: item.color,
        borderWidth: 1.5,
        pointBorderWidth: .2,
        backgroundColor: "transparent",
        label: item.label,
        data: item.data
    }))

    const chartData = {
        labels: props.labels,
        datasets: datasets
    }

    return (
        <div className="chart-box" >
            <h2>{props.title}</h2>
            <div>
                <Line
                    height={250}
                    data={chartData}
                    options={chartOptions}
                    />
            </div>
        </div >
    )

}