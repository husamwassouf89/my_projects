import React from 'react'
import { useState } from 'react'
import { Collapse } from 'react-collapse'
import { Col, Row } from 'react-grid-system'
import { useTranslation } from 'react-multi-lang'
import { useDispatch, useSelector } from 'react-redux'
import { InputField, NumberField, SelectField } from '../../components/FormElements/FormElements'

import './IRS.css'
import { IRSSlice, IRSState } from './IRSSlice'

export default () => {

    // Hooks
    const [isLoading, setIsLoading] = useState<boolean>(false)

    // Redux
    const dispatch = useDispatch()
    const state = useSelector( ( state: { irs: IRSState } ) => state.irs)

    // Translation
    const t = useTranslation()

    return (
        <div className="irs">

            <form>
                <Row>
                    <Col md={6}>
                        <label>{t("class_type")}</label>
                        <SelectField placeholder={t("class_type")} value={{ label: "Individual", value: "individual" }} options={[
                            { label: "Individual", value: "individual" },
                            { label: "SME'S", value: "smes" },
                            { label: "Middle", value: "middle" },
                            { label: "Corporate", value: "corporate" }
                        ]} />
                    </Col>
                    <Col md={6}>
                        <label>{t("factor")}</label>
                        <SelectField placeholder={t("factor")} value={{ label: "Facility", value: "facility" }} options={[
                            { label: "Facility", value: "facility" },
                            { label: "Qualitative", value: "qualitative" },
                            { label: "Quanitative", value: "quanitative" }
                        ]} />
                    </Col>

                    <Col md={10}>
                        <h3 style={{ lineHeight: "50px" }}><span style={{ fontWeight: "normal" }}>Questions related to </span>Individuals &#x3E; Facility Factors</h3>
                    </Col>

                    <Col md={2}>
                        <NumberField min={0} max={100} placeholder={t("max_percentage")} value={45} disabled />
                    </Col>

                </Row>
            </form>

            {/* Questions */}
            <div className="questions">
                
                {
                    state.questions.map((question, q_index) => (
                        <div className="question margin-top-20">
                            <header>
                                <input placeholder="Type a question" />
                            </header>
                            <Collapse isOpened={true}>
                                <ul>
                                    { question.answers?.map((answer, a_index) => (
                                        <li>
                                            <input className="answer" placeholder="Type an answer" />
                                            <div className="percentage">
                                                <NumberField placeholder="Answer rate" value={answer.rate} />
                                            </div>
                                        </li>
                                    )) }
                                    <button className="button color-gold margin-top-30" style={{ margin: 30 }} onClick={() => dispatch( IRSSlice.actions.addAnswer({ q_index, answer: {} }) )}>Add answer</button>
                                </ul>
                            </Collapse>
                        </div>
                    ))
                }
            
                <button className="button bg-gold color-white margin-top-30" onClick={() => dispatch( IRSSlice.actions.addQuestion({}) )}>Add question</button>

                <br />
                <br />

            </div>

        </div>
    )

}