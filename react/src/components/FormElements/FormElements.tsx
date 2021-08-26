// Libraries
import ReactTooltip from 'react-tooltip';
import { Icon } from "@iconify/react";
import sunIcon from '@iconify-icons/feather/sun';
import moonIcon from '@iconify-icons/feather/moon';
import Select from 'react-select'
import { setLanguage, useTranslation } from 'react-multi-lang';
import NumericInput from 'react-numeric-input';

// Tagify
import Tags from "@yaireo/tagify/dist/react.tagify"
import "@yaireo/tagify/dist/tagify.css"

// Helpers
import { uid } from '../../services/hoc/helpers';

// Stylesheet
import './FormElements.css'
import { useState } from 'react';

export const InputField = (props: any) => {

    let field: object = (({ type, onChange, defaultValue, disabled, max, min, value, style, onKeyPress }) => ({ type, onChange, defaultValue, disabled, max, min, value, style, onKeyPress }))(props);
    let inputLabel: string = props.placeholder ? props.placeholder : props.label ? props.label : '';
    let id = uid('input')

    return (
        <div className="input-box">
            <input {...field} autoComplete="" id={id} />
            { inputLabel ? <label className={props.value ? "active" : ''} htmlFor={id}>{inputLabel}</label> : ''}
            {props.error ? <i className="icon-error" data-tip={props.error}></i> : ''}
            {props.error ? <ReactTooltip place="left" type="error" effect="solid" delayHide={500} /> : ''}
        </div>
    )

}

export const Textarea = (props: any) => {
    let field: object = (({ onChange, defaultValue, disabled, value, rows }) => ({ onChange, defaultValue, disabled, value, rows }))(props);
    let inputLabel: string = props.placeholder ? props.placeholder : props.label ? props.label : '';
    let id = uid('input')

    return (
        <div className="input-box textarea-box">
            <textarea {...field} id={id} />
            { inputLabel ? <label className={props.value ? "active" : ''} htmlFor={id}>{inputLabel}</label> : ''}
            {props.error ? <i className="icon-error" data-tip={props.error}></i> : ''}
            {props.error ? <ReactTooltip place="left" type="error" effect="solid" delayHide={500} /> : ''}
        </div>
    )
}

export const Checkbox = (props: any) => {

    let field: object = (({ onChange, disabled, checked }) => ({ onChange, disabled, checked }))(props);
    let inputLabel: string = props.label;
    let id = uid('input')

    return (
        <div className="checkbox">
            <input {...field} type="checkbox" id={id} />
            <div>
                {inputLabel ? <label className={props.value ? "active" : ''} htmlFor={id}><i className="icon-checkmark" />{inputLabel}</label> : ''}
            </div>
        </div>
    )

}


export const SimpleCheckbox = (props: any) => {
    
    let field: object = (({ onChange, onClick, disabled, checked, className }) => ({ onChange, onClick, disabled, checked, className }))(props);
    let id = uid('input')

    return(
        <div className="simple-checkbox">
            <input type="checkbox" id={id} {...field} />
            <label htmlFor={id} ><i className="icon-checkmark" />{props.label ? " " + props.label : ""}</label>
        </div>
    )

}


export const LightDarkModeSwitcher = (props: any) => {

    const changeMode = (e: React.ChangeEvent<HTMLInputElement>) => {
        if(e.target.checked) {
            document.body.classList.add('dark')
            localStorage.setItem("theme", 'dark')
        }
        else {
            document.body.classList.remove('dark')
            localStorage.setItem("theme", 'light')
        }
    }

    return (
        <label style={{ display: 'inline-block', cursor: 'pointer' }}>
            <input className='toggle-checkbox' type='checkbox' onChange={changeMode} defaultChecked={ localStorage.getItem("theme") ? localStorage.getItem("theme") === 'dark' : false }></input>
            <div className='toggle-slot'>
                <div className='sun-icon-wrapper'>
                    <Icon icon={sunIcon} className="sun-icon" />
                </div>
                <div className='toggle-button'></div>
                <div className='moon-icon-wrapper'>
                    <Icon icon={moonIcon} className="moon-icon" />
                </div>
            </div>
        </label>
    )

}


export const LanguageSwitcher = (props: any) => {
    const changeLang = (e: React.ChangeEvent<HTMLInputElement>) => {
        if(e.target.checked) {
            setLanguage('ar')
            document.body.classList.add('rtl')
            localStorage.setItem("lang", "ar")
        }
        else {
            setLanguage('en')
            document.body.classList.remove('rtl')
            localStorage.setItem("lang", "en")
        }
    }

    return (
        <label style={{ display: 'inline-block', cursor: 'pointer' }} className="language-toggle">
            <input className='toggle-checkbox' type='checkbox' onChange={changeLang} defaultChecked={ localStorage.getItem("lang") ? localStorage.getItem("lang") === 'ar' : false }></input>
            <div className='toggle-slot'>
                <div className="ar">عربي</div>
                <div className='toggle-button'></div>
                <div className="en">En</div>
            </div>
        </label>
    )

}


export const SelectField = (props: any) => {

    return(
        <div className="select-holder">
            <Select {...props} className={"react-select" + " " + props.bg} classNamePrefix="react-select" />
            {props.error ? <i className="icon-error" data-tip={props.error}></i> : ''}
            {props.error ? <ReactTooltip place="left" type="error" effect="solid" delayHide={500} /> : ''}
        </div>
    )

}


export const TagsField = (props: any) => {
    return(
        <div className="tags-box">
            <Tags {...props} />
            {props.error ? <i className="icon-error" data-tip={props.error}></i> : ''}
            {props.error ? <ReactTooltip place="left" type="error" effect="solid" delayHide={500} /> : ''}
        </div>
    )
}


export const NumberField = (props: any) => {
    
    let field: object = (({ onChange, defaultValue, disabled, max, min, value }) => ({ onChange, defaultValue, disabled, max, min, value }))(props);
    let inputLabel: string = props.placeholder ? props.placeholder : props.label ? props.label : '';
    let id = uid('input')

    const [error, setError] = useState<string>("")

    const t = useTranslation()

    const enforceMinMax = (e: React.FocusEvent<HTMLInputElement>) => {
        
        if(e.currentTarget.value !== "") {
          
            if( parseInt(e.currentTarget.value) < parseInt(e.currentTarget.min) ) {
                var nativeInputValueSetter = typeof window !== 'undefined' ? Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, "value")?.set : null;
                if(nativeInputValueSetter)
                    nativeInputValueSetter.call(e.currentTarget, e.currentTarget.min)
                var event = new Event('input', { bubbles: true})
                e.currentTarget.dispatchEvent(event)
            }

            if(parseInt(e.currentTarget.value) > parseInt(e.currentTarget.max)) {
                var nativeInputValueSetter = typeof window !== 'undefined' ? Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, "value")?.set : null;
                if(nativeInputValueSetter)
                    nativeInputValueSetter.call(e.currentTarget, e.currentTarget.max)
                var event = new Event('input', { bubbles: true})
                e.currentTarget.dispatchEvent(event)
            }
        }

    }

    const validate = (e: React.ChangeEvent<HTMLInputElement>) => {
        
        setError("")

        if(props.onChange)
            props.onChange(e)
        
        if(e.target.value !== "") {

            if( parseInt(e.target.value) < parseInt(e.target.min) )
                setError(t("min_error", { value: e.target.min }))

            if(parseInt(e.currentTarget.value) > parseInt(e.currentTarget.max))
                setError(t("max_error", { value: e.target.max }))
        }

    }
    
    return(
        <div className={"input-box" + ( props.className ? " " + props.className : "" )}>
            <input style={props.bg ? { background: props.bg } : {}} {...field} value={props.value?.toString()} type="number" autoComplete="" id={id} onBlur={enforceMinMax} onChange={validate} />
            { inputLabel ? <label className={props.value || Number(props.value) === 0 ? "active" : ''} htmlFor={id}>{inputLabel}</label> : ''}
            {props.error || error ? <i className="icon-error" data-tip={error ? error : props.error}></i> : ''}
            {props.error || error ? <ReactTooltip place="left" type="error" effect="solid" delayHide={500} /> : ''}
        </div>
    )
}

interface radioProps {
    name: string;
    onChange(e: React.ChangeEvent<HTMLInputElement>): any;
    disabled?: boolean;
    checked?: boolean;
    label?: string;
}

export const RadioButton = (props: radioProps) => {

    let id = uid('radio')

    return (
        <div className="radio-button">
            <input {...props} type="radio" id={id} />
            { props.label ? <label htmlFor={id}>{props.label}</label> : ''}
        </div>
    )

}