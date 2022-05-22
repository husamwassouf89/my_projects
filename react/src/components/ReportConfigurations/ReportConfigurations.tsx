import React, { useState } from "react";
import { t } from "react-multi-lang";
import { Checkbox, InputField, SelectField } from "../FormElements/FormElements"
import Modal from "../Modal/Modal"

import { years } from '../../services/hoc/helpers'
import { ClassesMenu } from "../PredefinedMenus/PredefinedMenus";

export default (props: { open: boolean; toggle(): any; link: string; showTo?: boolean; }) => {

  const [classType, setClassType] = useState<any>();
  const [limits, setLimits] = useState<any>(false);
  const [year, setYear] = useState<number>()
  const [quarter, setQuarter] = useState<'q1' | 'q2' | 'q3' | 'q4'>()
  const [eYear, setEYear] = useState<number>()
  const [eQuarter, setEQuarter] = useState<'q1' | 'q2' | 'q3' | 'q4'>()

  const getLink = () => {
    if(
      classType &&
      year &&
      quarter &&
      (!props.showTo || eYear) &&
      (!props.showTo || eQuarter )
    )
      return `${props.link}?quarter1=${quarter}&year1=${year}&quarter2=${eQuarter}&year2=${eYear}&limits=${limits ? 'yes' : 'no'}&class_type_category=${classType}`
    return '#';
  }

  return(
    <Modal open={props.open} toggle={props.toggle}>
      <div style={{ minWidth: 500, textAlign: 'left' }}>
        <h2>Report Configurations</h2>
        <hr style={{ border: 'none', borderTop: '1px solid #DDD', marginBottom: 30 }} />
        <form action="">
          <div className="config">
              <SelectField onChange={(selected: any) => setClassType(selected?.value)} placeholder={t("class_type")} options={[
                  { label: "Direct credit facility", value: "facility" },
                  { label: "Financial Institutions", value: "financial" }
              ]} />
          </div>
          <Checkbox label="Unused Limits" style={{ margin: '20px 0' }} checked={limits} onChange={(e: React.ChangeEvent<HTMLInputElement>) => setLimits(e.target.checked)} />
          { props.showTo && <div className="sep text-center">FROM</div> }
          <div className="config">
              <SelectField defaultValue={year ? { label: year, value: year } : undefined} onChange={(selected: { value: number; }) => setYear(selected?.value)} placeholder={t("year")} options={years} />
          </div>
          <div className="config">
              <SelectField defaultValue={quarter ? { label: quarter?.toUpperCase(), value: quarter } : undefined} onChange={(selected: { value: 'q1' | 'q2' | 'q3' | 'q4'; }) => setQuarter(selected?.value)} placeholder={t("quarter")} options={[
                  { label: "Q1", value: "q1" },
                  { label: "Q2", value: "q2" },
                  { label: "Q3", value: "q3" },
                  { label: "Q4", value: "q4" }
              ]} />
          </div>
          {props.showTo &&
            <>
              <div className="sep text-center">TO</div>
              <div className="config">
                <SelectField defaultValue={eYear ? { label: eYear, value: eYear } : undefined} onChange={(selected: { value: number; }) => setEYear(selected?.value)} placeholder={t("year")} options={years} />
              </div>
              <div className="config">
                <SelectField defaultValue={eQuarter ? { label: eQuarter?.toUpperCase(), value: eQuarter } : undefined} onChange={(selected: { value: 'q1' | 'q2' | 'q3' | 'q4'; }) => setEQuarter(selected?.value)} placeholder={t("quarter")} options={[
                  { label: "Q1", value: "q1" },
                  { label: "Q2", value: "q2" },
                  { label: "Q3", value: "q3" },
                  { label: "Q4", value: "q4" }
                ]} />
              </div>
            </>}
          <br />
          <div className="text-center">
            <a href={getLink()} className="button bg-gold color-white" style={{ padding: '15px 20px' }}>View Report</a>
          </div>
        </form>
      </div>
    </Modal>
  )
}