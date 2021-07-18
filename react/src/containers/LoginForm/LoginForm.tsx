import React, { useState } from "react";

// Translation
import { useTranslation } from "react-multi-lang";

// Cookies
import { useCookies } from "react-cookie";

// Redux
import { useDispatch, useSelector } from 'react-redux'
import { loginSlice, loginState } from "./LoginFormSlice";

// Compoentns
import { Checkbox, InputField } from "../../components/FormElements/FormElements";
import { RippleLoader, SuccessMark } from "../../components/Loader/Loader";
import { StaticAlert } from "../../components/Alerts/Alerts";

// Stylesheet
import './LoginForm.css'

// Services
import API from '../../services/api/api'
import { addToDate } from "../../services/hoc/helpers";

export default function () {

    // Translation
    const t = useTranslation()

    // Redux
    const dispatch = useDispatch()
    const loginState = useSelector( ( state: { login: loginState } ) => state.login )

    // Hooks
    const [username, setUsername] = useState<string>("");
    const [usernameError, setUsernameError] = useState<string>("");
    const [password, setPassword] = useState<string>("");
    const [passwordError, setPasswordError] = useState<string>("");
    const [rememberMe, setRememberMe] = useState<boolean>(false);
    const [showSucessMark, setShowSuccessMark] = useState<boolean>(false)

    // Cookies hooks
    const [_, setCookie] = useCookies();

    // API
    const ENDPOINTS = new API()

    const login = () => {
        
        if(!username) {
            setUsernameError(t("required_error"))
            return
        }
        
        if(!password) {
            setPasswordError(t("required_error"))
            return
        }

        dispatch( loginSlice.actions.load() )

        setTimeout(() => {
            dispatch(loginSlice.actions.success())
            setShowSuccessMark(true)
            setTimeout(() => {
                let expires: Date = rememberMe ? addToDate(new Date(), "years", 1) : addToDate(new Date(), "hours", 1);
                setTimeout(() => {
                    setCookie("userinfo", { email: "majd.sh42@gmail.com", name: "Majd Shamma", role: { name: "Admin" }, accessToken: "bla" }, { expires: expires })
                    dispatch(loginSlice.actions.init())
                }, 10);
            }, 1500);
        }, 1000);

        return

        ENDPOINTS.auth().login({ email: username, password: password })
        .then((response: any) => {
            if( response.data.message === "Success! :D" ) {
                dispatch( loginSlice.actions.success() )
                setShowSuccessMark(true)
                setTimeout(() => {
                    let expires: Date = rememberMe ? addToDate( new Date(), "years", 1 ) : addToDate( new Date(), "hours", 1 );
                    localStorage.setItem("permissions", JSON.stringify(response.data.data.role.permissions))
                    setTimeout(() => {
                        setCookie("userinfo", {email: response.data.data.email, name: response.data.data.name, role: { name: response.data.data.role.name }, accessToken: response.data.data.access_token }, { expires: expires })
                        dispatch( loginSlice.actions.init() )
                    }, 10);
                }, 1500);
            }
            else
                dispatch( loginSlice.actions.error(true) )

        })
        .catch((error: any) => {
            dispatch( loginSlice.actions.error(true) )
        })

    }

    return(
        <div className="login-form">
            
            <form onSubmit={(e: React.FormEvent<HTMLFormElement>) => e.preventDefault()}>
                
                {
                    loginState.isError ? <StaticAlert show={true} type={"error"}>{t("login_error")}</StaticAlert> : ""
                }

                <InputField
                    value={username}
                    type="text"
                    placeholder={t('email')}
                    error={usernameError}
                    disabled={loginState.isLoading || loginState.isSuccess}
                    onChange={(e: React.ChangeEvent<HTMLInputElement>) => {
                        setUsername(e.currentTarget.value);
                        setUsernameError("")
                        if( loginState.isError ) dispatch( loginSlice.actions.error(false) )
                    } } />

                <InputField
                    value={password}
                    type="password"
                    placeholder={t('password')}
                    error={passwordError}
                    disabled={loginState.isLoading || loginState.isSuccess}
                    onChange={(e: React.ChangeEvent<HTMLInputElement>) => {
                        setPassword(e.currentTarget.value);
                        setPasswordError("")
                        if( loginState.isError ) dispatch( loginSlice.actions.error(false) )
                    } } />
                
                <Checkbox
                    label={t('remember_me')}
                    disabled={loginState.isLoading || loginState.isSuccess}
                    checked={rememberMe}
                    onChange={(e: React.ChangeEvent<HTMLInputElement>) => setRememberMe(e.target.checked)} />
                
                <div className="text-center"><button className={ "button bg-gold color-white round" + (loginState.isSuccess ? " scale" : '') } style={{ width: loginState.isLoading ? 50 : 200 }} onClick={login}>{ loginState.isLoading ? <RippleLoader /> : t('login') }</button></div>

                { showSucessMark ? <SuccessMark /> : '' }

            </form>
            
        </div>
    )

}