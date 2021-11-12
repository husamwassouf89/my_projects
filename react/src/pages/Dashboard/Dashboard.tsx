import React from 'react'
import { useTranslation } from 'react-multi-lang'

// Stylesheet
import './Dashboard.css'

// Cookies
import { useCookies } from 'react-cookie'

// Components
import { SideNav, TopNav } from '../../components/Nav/Nav'
import SearchClient from '../../containers/Clients/SearchClient/SearchClient'
import Clients from '../../containers/Clients/Clients'
import ImportClients from '../../containers/ImportClients/ImportClients'
import PDs from '../../containers/PD/PDs'
import ImportPDs from '../../containers/PD/ImportPDs'
import IRS from '../../containers/IRS/IRS'

export default (props: any) => {

    const t = useTranslation()

    const navList = [
        {
            icon: "icon-search-1",
            name: t("search_client"),
            link: "/search-client",
            show: true
        },
        {
            icon: "icon-users",
            name: t("direct_credit_facilities"),
            sub_name: 'Corporate – SME – Retail',
            show: true,
            childs: [
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
            icon: "icon-users",
            name: t("Financial institutions"),
            sub_name: 'Banks - investments',
            show: true,
            childs: [
                {
                    icon: "icon-hammer",
                    name: t("all_clients"),
                    link: "/institutions",
                    show: true
                },
                {
                    icon: "icon-tasks",
                    name: t("import_clients"),
                    link: "/import-institutions",
                    show: true
                },
            ]
        },
        {
            icon: "icon-users",
            name: t("OFF-Balance"),
            sub_name: 'Corporate – SME – Retail',
            show: true,
            childs: [
                {
                    icon: "icon-hammer",
                    name: t("all_clients"),
                    link: "/offbalance",
                    show: true
                },
                {
                    icon: "icon-tasks",
                    name: t("import_clients"),
                    link: "/import-offbalance",
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
        // {
        //     icon: "icon-gears",
        //     name: t("settings"),
        //     show: true,
        //     link: "/settings"
        // }
    ]

    let section = props.match.params.section ? props.match.params.section.toLowerCase() : "search-client"
    
    const dashboardContent = () => {
        switch (section) {
            case "search-client":
                return(<SearchClient />)
            case "all-clients":
                return(
                    <Clients category="facility" />
                )
            case "import-clients":
                return(<ImportClients type="clients" />)
            case "institutions":
                return(
                    <Clients category="financial" />
                )
            case "import-institutions":
                return(<ImportClients type="banks" />)
            case "offbalance":
                return(
                    <Clients category="facility" offbalance />
                )
            case "import-offbalance":
                return(<ImportClients type="documents" />)
            case 'all-pds':
                return(<PDs />)
            case 'import-pd':
                return(<ImportPDs />)
            case 'irs':
                return(<IRS />)
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