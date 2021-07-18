import React, { Component, useLayoutEffect } from 'react'
import { BrowserRouter as Router, Redirect, Route, Switch } from 'react-router-dom'

// Redux
import { useSelector } from 'react-redux'
import { globalState } from '../globalSlice/globalSlice'

// Media query
import { useMediaQuery } from 'react-responsive'

import ScrollToTop from './ScrollToTop'
import Login from '../../pages/Login/Login'
import Dashboard from '../../pages/Dashboard/Dashboard'
import { useCookies } from "react-cookie";
import { WhiteboxLoader } from '../../components/Loader/Loader'

export default () => {

    // Redux
    const globalState = useSelector((state: { global: globalState }) => state.global)

    // Cookies hooks
    const [cookies, _, __] = useCookies();

    // Media querys
    // const isTabletOrMobile = useMediaQuery({ query: '(max-width: 1224px)' })
    const isTabletOrMobile = useMediaQuery({ query: '(max-width: 1px)' })

    useLayoutEffect(() => {
        if (localStorage.getItem("lang"))
            document.body.classList.add(localStorage.getItem("lang") == 'ar' ? 'rtl' : 'ltr')
        if (localStorage.getItem("theme"))
            document.body.classList.add(String(localStorage.getItem("theme")))
    }, []);

    return (
        <>
        { isTabletOrMobile && false ? <div className="center" style={{ fontSize: "30px", width: "100%", padding: 20, textAlign: 'center' }}>Coming soon...</div> :
        <Router basename="/">
            { globalState.isLoading ? <WhiteboxLoader /> : ""}
            <ScrollToTop>
                {!cookies.userinfo ?

                    // Auth pages
                    <Switch>
                        <Route exact path="/" component={Login} />
                        <Route path="/" component={() => <Redirect to="/" />} />
                    </Switch> :

                    // Dashboard pages
                    <Switch>
                        <Route path="/:section" component={Dashboard} />
                        <Route path="/" component={Dashboard} />
                    </Switch>}
            </ScrollToTop>
        </Router> }
        </>
    )
}