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
import Settings from '../../containers/Settings/Settings'
import ViewIRSs from '../../containers/IRS/ViewIRSs'
import ViewStaging from '../../containers/Staging/ViewStaging'

export default (props: any) => {

    const t = useTranslation()

    const [cookies] = useCookies(['userinfo']);

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
            sub_name: 'Corporate - SME - Retail',
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
            sub_name: 'Corporate - SME - Retail',
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
            icon: "icon-users",
            name: t("Unused limits"),
            show: true,
            childs: [
                {
                    icon: "icon-hammer",
                    name: t("direct"),
                    link: "/limits",
                    show: true
                },
                {
                    icon: "icon-hammer",
                    name: t("indirect"),
                    link: "/limits-offbalance",
                    show: true
                },
                {
                    icon: "icon-tasks",
                    name: t("import_clients"),
                    link: "/import-limits",
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
            link: "/all-irs",
        },
        {
            icon: "icon-star",
            name: t("staging"),
            show: true,
            link: "/all-staging",
        },
        {
            icon: "icon-gears",
            name: t("settings"),
            show: cookies?.userinfo?.role?.name === 'Admin',
            childs: [
                {
                    icon: "icon-product",
                    name: t("constants"),
                    link: "/settings",
                    show: true
                },
                {
                    icon: "icon-product",
                    name: t("irs"),
                    link: "/irs",
                    show: true
                }
            ]
        }
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
                return(<ImportClients type="clients" link="/templates/direct-credit-facilities.xlsx" />)
            case "institutions":
                return(
                    <Clients category="financial" />
                )
            case "import-institutions":
                return(<ImportClients type="banks" link="/templates/direct-credit-facilities.xlsx" />)
            case "offbalance":
                return(
                    <Clients category="facility" offbalance />
                )
            case "import-offbalance":
                return(<ImportClients type="documents" link="/templates/direct-credit-facilities.xlsx" />)
            case "limits":
                return(
                    <Clients category="facility" type="limits" />
                )
            case "limits-offbalance":
                return(
                    <Clients category="facility" type="limits" offbalance />
                )
            case "import-limits":
                return(<ImportClients type="limits" link="/templates/direct-credit-facilities.xlsx" />)
            case 'all-pds':
                return(<PDs />)
            case 'import-pd':
                return(<ImportPDs />)
            case 'irs':
                return(<IRS />)
            case 'all-irs':
                return(<ViewIRSs />);
            case 'all-staging':
                return(<ViewStaging />);
            case 'settings':
                return(<Settings />);
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