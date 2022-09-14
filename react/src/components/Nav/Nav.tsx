import React, { useState } from 'react'
import { Link, Redirect, useLocation } from 'react-router-dom'

// Custom scrollbar
import { Scrollbars } from 'react-custom-scrollbars';

// Cookies
import { useCookies } from 'react-cookie'

// Redyx
import { useDispatch } from 'react-redux';
import { globalSlice } from '../../services/globalSlice/globalSlice';

// API
import API from '../../services/api/api';

// Stylesheet
import './Nav.css'

// Components
import { LanguageSwitcher, LightDarkModeSwitcher } from '../FormElements/FormElements'

// Assets
import Logo from '../../assets/images/logo/primary.svg'
import { UnmountClosed } from 'react-collapse';
import { useTranslation } from 'react-multi-lang';
import ReactTooltip from 'react-tooltip';
import ReportConfigurations from '../ReportConfigurations/ReportConfigurations';

interface NavProps {
    list: {
        icon: string,
        name: string,
        sub_name?: string;
        link?: string,
        show: boolean;
        childs?: {
            icon: string,
            name: string,
            link: string,
            show: boolean;
        }[]
    }[],
    active: string | null
}

export const SideNav = (props: NavProps) => {


    const getActiveIndexByName = (name: string | null): number => {
        let matched_index = 0
        props.list.map((item, index) => {
            if (item.link?.substring(1) === name)
                matched_index = index
        })
        return matched_index
    }

    const getActiveBoxPositionByIndex = (index: number): number => {
        // return index === 0 ? index * 90 : index === props.list.length - 1 ? index * 90 + 10 : index * 90 + 5
        return index * 90
    }

    const [activeBox, setActiveBox] = useState<number>(getActiveBoxPositionByIndex(getActiveIndexByName(props.active)))

    return (
        <nav className="side-nav">
            <Scrollbars
                autoHeight
                autoHeightMin="75vh"
                autoHide
                renderTrackHorizontal={props => <div {...props} className="track-horizontal" style={{display:"none"}}/>}
                renderThumbHorizontal={props => <div {...props} className="thumb-horizontal" style={{display:"none"}}/>} >
                <div className="active-box" style={{ top: activeBox }} />
                <ul>
                    {props.list.map((item, index) => {
                        if(!item.show)
                            return
                        if(item.childs)
                            return(
                                <>
                                <li key={"side-" + index} className={(item.childs.map(item => item.link.substring(1))).includes(props.active || "") ? "active" : ""} onClick={() => setActiveBox(getActiveBoxPositionByIndex(index))} data-tip={item.sub_name}>
                                    <Link to={item.childs[0]?.link || ""}>
                                        <span>
                                            <i className={item.icon}></i> {item.name}
                                        </span>
                                    </Link>
                                </li>
                                <UnmountClosed key={"collapse-" + index} isOpened={(item.childs.map(item => item.link.substring(1))).includes(props.active || "")}>
                                    <ul>
                                        {
                                            item.childs.map((child, child_index) => (
                                                <li key={"child-" + index + "-" + child_index} className={child.link.substring(1) === props.active ? "active" : ""}><Link to={child.link}><span><i className={child.icon}></i> {child.name}</span></Link></li>
                                            ))
                                        }
                                    </ul>
                                </UnmountClosed>
                                </>
                            )
                        else
                            return (
                                <li key={"side-" + index} className={item.link?.substring(1) === props.active ? "active" : ""} onClick={() => setActiveBox(getActiveBoxPositionByIndex(index))}><Link to={item.link || ""}><span><i className={item.icon}></i> {item.name}</span></Link></li>
                            )
                    })}
                </ul>
            </Scrollbars>
            <ReactTooltip place="top" effect="solid" />
        </nav>
    )
}

export const TopNav = () => {

    // React hooks
    const [redirect, setRedirect] = useState<boolean>(false);
    const [showReportConfig, setShowReportConfig] = useState<boolean>(false);
    const [link, setLink] = useState<string>('');
    const [showTo, setShowTo] = useState(false);

    // Cookies hooks
    const [cookie, __, removeCookie] = useCookies();

    // Redux
    const dispatch = useDispatch()

    // Translation
    const t = useTranslation()

    // API
    const ENDPOINTS = new API()

    const logout = () => {
        dispatch( globalSlice.actions.setIsLoading(true) )
        ENDPOINTS.auth().logout(null)
        .then((response: any) => {
            if( response.data.message ==="success" ) {
                dispatch( globalSlice.actions.setIsLoading(false) )
                removeCookie("userinfo")
                setTimeout(() => {
                    setRedirect(true)
                }, 10);
            }
        })
    }

    return (
        <nav className="top-nav">
            <img src={Logo} className="logo" />

            <div className="actions">

                <div className="reports-list">
                    <span>{t("reports")}</span>
                    <ul>
                        <li><a onClick={ () => {
                            setShowReportConfig(true);
                            setLink("https://ifrs.opalcityadvisory.com/api/public/reports/cif");
                            setShowTo(false);
                        }}>{t("cif")}</a></li>
                        <li><a onClick={ () => {
                            setShowReportConfig(true);
                            setLink("https://ifrs.opalcityadvisory.com/api/public/reports/cif-guarantee");
                            setShowTo(false);
                        }}>{t("cif_guarantee")}</a></li>
                        <li><a onClick={ () => {
                            setShowReportConfig(true);
                            setLink("https://ifrs.opalcityadvisory.com/api/public/reports/disclosure");
                            setShowTo(false);
                        }}>{t("disclosure")}</a></li>
                        <li><a onClick={ () => {
                            setShowReportConfig(true);
                            setLink("https://ifrs.opalcityadvisory.com/api/public/reports/ead-guarantee");
                            setShowTo(false);
                        }}>{t("ead_guarantee")}</a></li>
                        <li><a onClick={ () => {
                            setShowReportConfig(true);
                            setLink("https://ifrs.opalcityadvisory.com/api/public/reports/ecl");
                            setShowTo(false);
                        }}>{t("ecl")}</a></li>
                        <li><a onClick={ () => {
                            setShowReportConfig(true);
                            setLink("https://ifrs.opalcityadvisory.com/api/public/reports/facility-disclosure");
                            setShowTo(true);
                        }}>{t("facility_disclosure")}</a></li>
                        {/* <li><a href="https://ifrs.opalcityadvisory.com/api/public/reports/facility-disclosure">{t("facility_disclosure")}</a></li> */}
                    </ul>
                </div>

                <ReportConfigurations open={showReportConfig} toggle={() => setShowReportConfig(false)} link={link} showTo={showTo} />

                <div className="switchers">
                    <LightDarkModeSwitcher />
                    <span className="margin-10" />
                    <LanguageSwitcher />
                </div>


                <div className="userinfo">
                    <span>
                        <strong>{cookie?.userinfo?.name}</strong> <br /> {cookie?.userinfo?.role?.name}
                    </span>
                    <i className="icon-username-1" />
                </div>

                <i className="icon-logout" onClick={logout} />

            </div>

            { redirect ? <Redirect to='/'/> : '' }

        </nav>
    )

}