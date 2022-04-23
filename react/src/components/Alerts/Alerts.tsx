import React, { useState } from 'react'
import { render, unmountComponentAtNode } from 'react-dom'
import { useTranslation } from 'react-multi-lang'
import { uid } from '../../services/hoc/helpers'
import Modal from '../Modal/Modal'

import './Alerts.css'

interface StaticAlertProps {
    show: boolean,
    type: "warning" | "error" | "success" | "info",
    children?: any
}

export const StaticAlert = (props: StaticAlertProps) => {

    const [dasharray, setDasharray] = useState<number>(0)
    const id = uid("alert")

    return(
        <>
        {props.show ?
        <div className={"alert " + props.type }>
            <div className="alert-content">
                {props.children}
            </div>
        </div> : ""
        }
        </>
    )

}


interface ConfirmModalProps {
    message: string;
    onConfirm?: () => any;
    hideCancel?: boolean;
    onAction?: (actions: 'cancel' | 'okay') => void;
    okayText?: string;
    cancelText?: string;
}

const ConfirmModal = (props: ConfirmModalProps) => {
    
    const t = useTranslation()

    return(
        <Modal open={true} toggle={() => { if(props.onAction) { close(); } }} alwaysVisible={!props.onAction}>
            <p style={{ margin: "0 0 30px" }}>{props.message}</p>
            <button className="button bg-gold color-white" style={{ minWidth: 80 }} onClick={() => {
                props.onConfirm?.()
                props.onAction?.('okay')
                close()
            }}>{props.okayText || t("OK")}</button>
            { !props.hideCancel &&
            <>
            <span className="margin-10" />
            <button className="button" onClick={() => {
                props.onAction?.('cancel')
                close()
            }}>{props.cancelText || t("Cancel")}</button>
            </> }
        </Modal>
    )

}

const close = () => {
    const target = document.getElementById('confirm-alert')
    if (target) {
        unmountComponentAtNode(target)
        target.parentNode?.removeChild(target)
    }
}

export const Confirm = ( props: ConfirmModalProps ) => {
    let target = document.createElement('div')
    target.id = 'confirm-alert'
    document.body.appendChild(target)
    render(<ConfirmModal {...props} />, target)
}