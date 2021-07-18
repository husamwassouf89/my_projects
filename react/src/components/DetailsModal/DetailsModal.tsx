import React from 'react'
import { useTranslation } from 'react-multi-lang'
import { useDispatch } from 'react-redux'
import { EllipsisLoader } from '../Loader/Loader'
import Modal from '../Modal/Modal'

import './DetailsModal.css'

interface DetailsModalProps {
    isOpen: boolean;
    toggle: Function;
    isLoading?: boolean;
    title?: string;
    product_attributes?: {
        name: string;
        value: string;
    }[];
    data: {
        [key: string]: any;
    };
}


export default (props: DetailsModalProps) => {

    // Redux
    const dispatch = useDispatch()

    // Translation
    const t = useTranslation()
    return <Modal open={props.isOpen} toggle={props.toggle}>
        {props.isLoading ?
            <div className="center"><EllipsisLoader /></div> :
            <>
                {props.title ? <h3 className="details-modal-title">{props.title}</h3> : ""}
                <table className="details-table">
                    {Object.keys(props.data).map((key, index) => (
                        <>
                        { props.data[key] === null ? "" :
                        <tr key={key}>
                            <td>{t(key)}</td>
                            <td>
                                <ul>
                                    {
                                        props.data[key].map((item: string, index: number) => (
                                            <li key={index}>{item}</li>
                                        ))
                                    }
                                </ul> : {props.data[key]}
                            </td>
                        </tr> }
                        </>
                    ))}
                </table>
            </>}
    </Modal>
}