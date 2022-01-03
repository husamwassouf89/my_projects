import React, { useEffect, useState } from 'react'

// Custom scrollbar
import { Scrollbars } from 'react-custom-scrollbars';
import { uid } from '../../services/hoc/helpers';

import './Modal.css'

interface ModalProps {
    open: boolean;
    toggle: Function;
    children?: any;
    alwaysVisible?: boolean;
}

export default (props: ModalProps) => {

    const [out, setOut] = useState<boolean>(false)
    const [dasharray, setDasharray] = useState<number>(0)
    const id = uid("modal")

    const hide = () => {
        if(props.alwaysVisible)
            return
        setOut(true);
        setTimeout(() => {
            props.toggle()
            setOut(false);
        }, 300);
    }

    const handleEsc = (e: KeyboardEvent) => {
        if (e.key === 'Escape')
            hide()
    }

    useEffect(() => {
        window.addEventListener('keydown', handleEsc);
        return () => {
            // unsubscribe event
            window.removeEventListener('keydown', handleEsc);
        };
    }, []);

    return (
        <>
            { props.open ?
                <div id="modal-container" className={(out ? "out" : "") + " " + id} onClick={hide}>
                    <div className="modal-background">
                        <div ref={(component: HTMLDivElement) => { if (component) setDasharray(component.offsetWidth * 2 + component.offsetHeight * 2) }} className="modal" onClick={(e: React.MouseEvent<HTMLDivElement>) => e.stopPropagation()}>
                            <Scrollbars
                                className="modal-scroller"
                                autoHeight
                                autoHeightMin="100%" autoHeightMax="90vh"
                                autoHide
                                renderTrackHorizontal={props => <div {...props} className="track-horizontal" style={{ display: "none" }} />}
                                renderThumbHorizontal={props => <div {...props} className="thumb-horizontal" style={{ display: "none" }} />} >
                                <div className="modal-content">
                                    <i className="icon-close" onClick={hide} />
                                    {props.children}
                                </div>
                                {dasharray ?
                                    <svg className="modal-svg" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" preserveAspectRatio="none">
                                        <rect style={{ strokeDasharray: dasharray, strokeDashoffset: dasharray }} x="0" y="0" fill="none" width="226" height="162" rx="3" ry="3"></rect>
                                    </svg> : ""}
                            </Scrollbars>
                        </div>
                    </div>
                    <style>{`
                        @keyframes sketchIn {
                            0% {
                                stroke-dashoffset: ${dasharray};
                            }
                            100% {
                                stroke-dashoffset: 0;
                            }
                        }
                        
                        @keyframes sketchOut {
                            0% {
                                stroke-dashoffset: 0;
                            }
                            100% {
                                stroke-dashoffset: ${dasharray};
                            }
                        }
                        
                        @keyframes sketchInDark {
                            0% {
                                stroke-dashoffset: ${dasharray};
                            }
                            100% {
                                stroke-dashoffset: 0;
                                stroke: #1a2029;
                            }
                        }
                        
                        @keyframes sketchOutDark {
                            0% {
                                stroke-dashoffset: 0;
                                stroke: #1a2029;
                            }
                            100% {
                                stroke-dashoffset: ${dasharray};
                            }
                        }
                        #modal-container .modal-background .modal .modal-svg rect { animation: sketchIn 0.5s 0.3s cubic-bezier(0.165, 0.84, 0.44, 1) forwards; }
                        .dark #modal-container .modal-background .modal .modal-svg rect { animation: sketchInDark 0.5s 0.3s cubic-bezier(0.165, 0.84, 0.44, 1) forwards; }
                        #modal-container.out .modal-background .modal .modal-svg rect { animation: sketchOut 0.5s cubic-bezier(0.165, 0.84, 0.44, 1) forwards; }
.dark #modal-container.out .modal-background .modal .modal-svg rect { animation: sketchOutDark 0.5s cubic-bezier(0.165, 0.84, 0.44, 1) forwards; }
                    `}</style>
                </div> : ""}
        </>
    )

}