import React, { useState } from 'react';
import { getPercentage, toFixed } from '../../../services/hoc/helpers';

const BanksMap = [ "AAA", "AA", "A", "BBB", "BB", "B", "CCC", "Default" ]

export default ({ PDDetails }: { PDDetails: any }) => {
    
    const [activeTab, setActiveTab] = useState<'default_calculation' | 'final_pd' | 'cumulative_pd'>('cumulative_pd')

    return(
        <div style={{ width: "90vw" }}>
            <ul className="tabs text-left" style={{ marginTop: 0, marginBottom: 40 }}>
                <li className={activeTab === "cumulative_pd" ? "active" : ""} onClick={() => setActiveTab("cumulative_pd")}>Migration Matrix</li>
                <li className={activeTab === "default_calculation" ? "active" : ""} onClick={() => setActiveTab("default_calculation")}>Corpr Default Calculation</li>
                <li className={activeTab === "final_pd" ? "active" : ""} onClick={() => setActiveTab("final_pd")}>Corpr Final PD</li>
            </ul>
            {activeTab === "default_calculation" ?
                <table className="table">
                    <thead>
                        <tr>
                            <th>{ PDDetails?.pd?.length < 10 ? 'From/to' : 'Degree' }</th>
                            <th>Default Rate</th>
                            <th>PD-TTC</th>
                            <th>PD-TTC after Regression</th>
                            <th>Asset correlation</th>
                            <th>TTC to PIT</th>
                        </tr>
                    </thead>
                    <tbody>
                        {[...Array(PDDetails?.pd?.length)].map((x, i) =>
                            <tr>
                                <td>{PDDetails?.pd?.length < 10 ? BanksMap[i] : i + 1}</td>
                                <td title={PDDetails?.default_rate[i]}>{getPercentage(PDDetails?.default_rate[i])}</td>
                                <td title={PDDetails?.pd_ttc[i]}>{getPercentage(PDDetails?.pd_ttc[i])}</td>
                                <td title={PDDetails?.pd_ttc_after_regression[i]}>{getPercentage(PDDetails?.pd_ttc_after_regression[i])}</td>
                                <td title={PDDetails?.asset_correlation[i]}>{getPercentage(PDDetails?.asset_correlation[i])}</td>
                                <td title={PDDetails?.ttc_to_pit[i]}>{getPercentage(PDDetails?.ttc_to_pit[i])}</td>
                            </tr>
                        )}
                    </tbody>
                </table> : activeTab === "final_pd" ?
                    <table className="table">
                        <thead>
                            <tr>
                                <th colSpan={4}></th>
                                <th title={PDDetails?.eco_parameter_base_weight}>{getPercentage(PDDetails?.eco_parameter_base_weight)}</th>
                                <th title={PDDetails?.eco_parameter_mild_weight}>{getPercentage(PDDetails?.eco_parameter_mild_weight)}</th>
                                <th title={PDDetails?.eco_parameter_heavy_weight}>{getPercentage(PDDetails?.eco_parameter_heavy_weight)}</th>
                                <th colSpan={2}></th>
                            </tr>
                            <tr>
                                <th rowSpan={2}>{ PDDetails?.pd?.length < 10 ? 'From/to' : 'Degree' }</th>
                                <th colSpan={3}>FX Macroeconomic Parameter</th>
                                <th colSpan={3}>Inclusion to the FX Percentages</th>
                                <th style={{ background: '#723b77' }} rowSpan={2}>Final Calibrated wieghted PD</th>
                                <th style={{ background: '#723b77' }} rowSpan={2}>Final Calibrated Used PD</th>
                            </tr>
                            <tr>
                                <th style={{ background: "#3498db", borderColor: "#3498db" }}>Base</th>
                                <th style={{ background: "#f39c12", borderColor: "#f39c12" }}>Mild Covid19 Shock</th>
                                <th style={{ background: "#e74c3c", borderColor: "#e74c3c" }}>Heavy Covid19 Shock</th>
                                <th style={{ background: "#3498db", borderColor: "#3498db" }}>Base</th>
                                <th style={{ background: "#f39c12", borderColor: "#f39c12" }}>Mild Covid19 Shock</th>
                                <th style={{ background: "#e74c3c", borderColor: "#e74c3c" }}>Heavy Covid19 Shock</th>
                            </tr>
                        </thead>
                        <tbody>
                            {[...Array(PDDetails?.pd?.length)].map((x, i) =>
                                <tr>
                                    <td>{PDDetails?.pd?.length < 10 ? BanksMap[i] : i + 1}</td>
                                    <td title={PDDetails?.eco_parameter_base_value}>{toFixed(PDDetails?.eco_parameter_base_value, 2)}</td>
                                    <td title={PDDetails?.eco_parameter_mild_value}>{toFixed(PDDetails?.eco_parameter_mild_value, 2)}</td>
                                    <td title={PDDetails?.eco_parameter_heavy_value}>{toFixed(PDDetails?.eco_parameter_heavy_value, 2)}</td>
                                    <td title={PDDetails?.inclusion.base[i]}>{getPercentage(PDDetails?.inclusion.base[i])}</td>
                                    <td title={PDDetails?.inclusion.mild[i]}>{getPercentage(PDDetails?.inclusion.mild[i])}</td>
                                    <td title={PDDetails?.inclusion.heavy[i]}>{getPercentage(PDDetails?.inclusion.heavy[i])}</td>
                                    <td title={PDDetails?.final_calibrated_weighted_pd[i]} style={{ fontWeight: 'bold' }}>{getPercentage(PDDetails?.final_calibrated_weighted_pd[i])}</td>
                                    <td title={PDDetails?.final_calibrated_used_PD[i]} style={{ fontWeight: 'bold' }}>{getPercentage(PDDetails?.final_calibrated_used_PD[i])}</td>
                                </tr>
                            )}
                        </tbody>
                    </table> :
                    <table className="table">
                        <thead>
                            <tr>
                                <th>{ PDDetails?.pd?.length < 10 ? 'From/to' : 'Degree' }</th>
                                {[...Array(PDDetails?.pd?.length)].map((x, i) => <th>{PDDetails?.pd?.length < 10 ? BanksMap[i] : i + 1}</th> )}
                            </tr>
                        </thead>
                        <tbody>
                            {[...Array(PDDetails?.pd?.length)].map((x, i) =>
                                <tr>
                                    <td>{PDDetails?.pd?.length < 10 ? BanksMap[i] : i + 1}</td>
                                    {[...Array(PDDetails?.pd[0]?.length)].map((x, j) =>
                                        <td title={PDDetails?.pd[i][j]}>{getPercentage(PDDetails?.pd[i][j])}</td>
                                    )}
                                </tr>
                            )}
                        </tbody>
                    </table>
            }
        </div>
    );
}