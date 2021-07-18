import React from 'react'
import { useTranslation } from 'react-multi-lang'

// Stylesheet
import './Dashboard.css'

// Cookies
import { useCookies } from 'react-cookie'

// Components
import { SideNav, TopNav } from '../../components/Nav/Nav'
import SearchClient from '../../containers/Clients/SearchClient'
import Clients from '../../containers/Clients/Clients'
import ImportClients from '../../containers/Clients/ImportClients'
import PDs from '../../containers/PD/PDs'
import ImportPDs from '../../containers/PD/ImportPDs'

export default (props: any) => {

    const t = useTranslation()

    const navList = [
        {
            icon: "icon-users",
            name: t("clients"),
            show: true,
            childs: [
                {
                    icon: "icon-product",
                    name: t("search_client"),
                    link: "/search-client",
                    show: true
                },
                {
                    icon: "icon-hammer",
                    name: t("all_clients"),
                    link: "/all-clients",
                    show: true
                },
                {
                    icon: "icon-tasks",
                    name: t("import_clients"),
                    link: "/import-clients",
                    show: true
                },
            ]
        },
        {
            icon: "icon-overview",
            name: t("pd"),
            show: true,
            childs: [
                {
                    icon: "icon-product",
                    name: t("import"),
                    link: "/import-pd",
                    show: true
                },
                {
                    icon: "icon-hammer",
                    name: t("view_all"),
                    link: "/all-pds",
                    show: true
                }
            ]
        },
        {
            icon: "icon-star",
            name: t("irs"),
            show: true,
            link: "/irs"
        },
        {
            icon: "icon-gears",
            name: t("settings"),
            show: true,
            link: "/settings"
        }
    ]

    let section = props.match.params.section ? props.match.params.section.toLowerCase() : "search-client"
    
    const dashboardContent = () => {
        switch (section) {
            case "search-client":
                return(<SearchClient />)
            case "all-clients":
                return(<Clients />)
            case "import-clients":
                return(<ImportClients />)
            case 'all-pds':
                return(<PDs />)
            case 'import-pd':
                return(<ImportPDs />)
            default:
                break;

        }
    }

    return(
        <div className="dashboard-page">
            <SideNav list={navList} active={section} />
            <div className="main-side">

                <TopNav />

                <div className="content">
                    { dashboardContent() }
                </div>

            </div>
        </div>
    )
}