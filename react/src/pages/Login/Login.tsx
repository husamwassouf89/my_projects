import { LanguageSwitcher, LightDarkModeSwitcher } from "../../components/FormElements/FormElements"
import LoginForm from "../../containers/LoginForm/LoginForm"

import Logo from '../../assets/images/logo/primary.svg'

import './Login.css'

const Login = () => {
    return(
        <div className="login-page">
            
            <div className="layout">

                <div className="form-holder">
                    <div className="text-center"><img src={Logo} className="logo" /></div>
                    <LoginForm />
                </div>

                <div className="switch-actions">
                    <LightDarkModeSwitcher />
                    <LanguageSwitcher />
                </div>

            </div>
            
        </div>
    )
}

export default Login