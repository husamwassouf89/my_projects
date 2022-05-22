import axios from 'axios';
import { useCookies, withCookies } from 'react-cookie';
import { addToDate } from '../hoc/helpers';

interface pagination {
    page: number,
    page_size: number,
    keyword?: string,
    class_type_id?: number;
    class_type_category?: string;
    quarter?: string;
    year?: number;
    type?: string;
    limit?: 'yes' | 'no';
    filter_type?: string;
}

interface pd {
    class_type_id: number;
    year: string;
    quarter: "q1" | "q2" | "q3" | "q4";
    path: string;
    attachment_ids: number[],
    eco_parameter_base_value: number;
    eco_parameter_base_weight: number;
    eco_parameter_mild_value: number;
    eco_parameter_mild_weight: number;
    eco_parameter_heavy_value: number;
    eco_parameter_heavy_weight: number;
}

class API {

    url: string;
    source = axios.CancelToken.source()

    constructor() {
        // this.url = "http://127.0.0.1:8000"
        this.url = "https://ifrs.opalcityadvisory.com/api/public"

        const [cookies, _, removeCookie] = useCookies();

        // Add Auth header
        axios.interceptors.request.use((config) => {

            config.headers["Accept"] = "application/json"
            config.headers["Content-Type"] = "application/json"
            config.headers["X-Requested-With"] = "XMLHttpRequest"
            config.cancelToken = this.source.token;
            if (cookies.userinfo) {
                config.headers["Authorization"] = cookies.userinfo.accessToken;
            }
            return (config);

        })

        // Handle 401
        axios.interceptors.response.use((response) => {
            if (!response)
                return Promise.reject(response)
            return response
        }, function (error) {
            if (!error)
                return Promise.reject(error);
            if (401 === error.response?.status) {
                removeCookie("userinfo")
                removeCookie("token")
            } else {
                return Promise.reject(error);
            }
        });
    }

    /**
     * Authentication APIs
     * @param {}
     */
    auth(): {
        login(query: { email: string, password: string }, name?: string): any;
        logout(query: null, name?: string): any;
    } {
        var endpoints: any = {}

        endpoints.login = (query: any, name = 'login') => axios.post(`${this.url}/${name}`, query)

        endpoints.logout = (query: any, name = 'logout') => axios.post(`${this.url}/${name}`, query)

        return endpoints
    }


    /**
     * Clients APIs
     * @param {}
     */
    clients(): {
        index(query: pagination): any;
        show(query: { id: number }): any;
        search_cif(query: { cif: string; limit?: 'on' | 'off'; balance?: string; }): any;
        store(query: { path: string; year: string; quarter: "q1" | "q2" | "q3" | "q4"; type: string; replace?: boolean; }): any;
        import_limits(query: { path: string; year: string; quarter: "q1" | "q2" | "q3" | "q4"; type: string; replace?: boolean; }): any;
        change_financial_status(query: { id: number; financial_status: string; }): any;
        add_attachments(query: { id: number; attachment_ids: number[]; }): any;
        setStage(query: { id: number; stage: number; }): any;
        setGrade(query: { id: number; grade: number; }): any;
    } {
        var endpoints: any = {}

        endpoints.index = (query: any, name = 'clients') => axios.get(`${this.url}/${name}`, { params: query })

        endpoints.show = (query: any, name = 'clients') => axios.get(`${this.url}/${name}/${query.id}`, { params: query })

        endpoints.search_cif = (query: any, name = 'clients') => axios.get(`${this.url}/${name}/cif/${query.cif}`, { params: query })

        endpoints.store = (query: any, name = 'clients') => axios.post(`${this.url}/${name}`, query)

        endpoints.import_limits = (query: any, name = 'limits/import') => axios.post(`${this.url}/${name}`, query)

        endpoints.change_financial_status = (query: any, name = 'clients/change-financial-status') => axios.post(`${this.url}/${name}`, query)

        endpoints.add_attachments = (query: any, name = 'clients/add-attachments') => axios.post(`${this.url}/${name}/${query.id}`, query)

        endpoints.setStage = (query: any, name = 'clients/set-stage') => axios.post(`${this.url}/${name}/${query.id}/${query.stage}`, query)

        endpoints.setGrade = (query: any, name = 'clients/set-grade') => axios.post(`${this.url}/${name}/${query.id}/${query.grade}`, query)

        return endpoints
    }


    /**
     * PD APIs
     * @param {}
     */
    pd(): {
        index(query: pagination): any;
        show(query: { id: number }): any;
        store(query: pd): any;
        delete(query: { id: number }): any;
    } {
        var endpoints: any = {}

        endpoints.index = (query: any, name = 'pd') => axios.get(`${this.url}/${name}`, { params: query })

        endpoints.show = (query: any, name = 'pd') => axios.get(`${this.url}/${name}/${query.id}`, { params: query })

        endpoints.store = (query: any, name = 'pd') => axios.post(`${this.url}/${name}`, query)

        endpoints.delete = (query: any, name = "pd") => axios.delete(`${this.url}/${name}/${query.id}`, { params: query })

        return endpoints
    }


    /**
     * IRS APIs
     * @param {}
     */
    irs(): {
        index(query: pagination): any;
        irs(query: { class_type_id: number; category_id: number; financial_status: string; }): any;
        questions(query: { id: number; }): any;
        store(query: any): any;
        update(query: any): any;
        updatePercentage(query: { class_type_id: number; category_id: number; financial_status: string; percentage: number; }): any;
    } {
        var endpoints: any = {}

        endpoints.index = (query: any, name = 'clients/irs') => axios.get(`${this.url}/${name}`, { params: query })

        endpoints.irs = (query: any, name = 'irs/show') => axios.get(`${this.url}/${name}`, { params: query })

        endpoints.store = (query: any, name = 'irs/questions') => axios.post(`${this.url}/${name}`, query)

        endpoints.update = (query: any, name = 'irs/questions') => axios.put(`${this.url}/${name}/${query.id}`, query)

        endpoints.updatePercentage = (query: any, name = 'irs') => axios.post(`${this.url}/${name}`, query)

        return endpoints
    }


    /**
     * Client IRS profile APIs
     * @param {}
     */
    irs_profile(): {
        index(query: pagination, id: number): any;
        store(query: { client_id: number; answers: number[] }): any;
    } {
        var endpoints: any = {}

        endpoints.index = (query: any, id: number, name = 'irs/client-profile/all') => axios.get(`${this.url}/${name}/${id}`, { params: query })

        endpoints.store = (query: any, name = 'irs/client-profile') => axios.post(`${this.url}/${name}`, query)

        return endpoints
    }


    /**
     * Staging profile APIs
     * @param {}
     */
    staging_profile(): {
        staging_list(query: pagination): any;
        index(query: pagination, id: number): any;
        questions(query: { class_type_id: number; }): any;
        store(query: { client_id: number; answers: { id: number; value?: number; }[] }): any;
    } {
        var endpoints: any = {}

        endpoints.staging_list = (query: any, name = 'clients/stage') => axios.get(`${this.url}/${name}`, { params: query })

        endpoints.index = (query: any, id: number, name = 'staging/client-profile/all') => axios.get(`${this.url}/${name}/${id}`, { params: query })

        endpoints.questions = (query: any, name = 'staging') => axios.get(`${this.url}/${name}/${query.class_type_id}`, { params: query })

        endpoints.store = (query: any, name = 'staging/client-profile') => axios.post(`${this.url}/${name}`, query)

        return endpoints
    }

    /**
     * Settings APIs
     * @param {}
     */
    settings(): {
        general(): any;
        saveGeneral(query: { id: number; value: string | number; }): Promise<any>;
        documents(query: { page: number; page_size: number; }): any;
        saveDocuments(query: { id: number; ccf: string | number; }): Promise<any>;
        predefined(query: { page: number; page_size: number; }): any;
        savePredefined(query: { id: number; pd: string | number; lgd: string | number; }): Promise<any>;
        lgd(query: { page: number; page_size: number; }): any;
        saveLgd(query: { id: number; value: string | number; }): Promise<any>;
    } {
        var endpoints: any = {}

        endpoints.general = (query: any, name = 'settings/get-values') => axios.get(`${this.url}/${name}`, { params: query })

        endpoints.saveGeneral = (query: any, name = 'settings/update-value') => axios.post(`${this.url}/${name}/${query.id}/${query.value}`)

        endpoints.documents = (query: any, name = 'settings/show-document-types') => axios.get(`${this.url}/${name}`, { params: query })

        endpoints.saveDocuments = (query: any, name = 'settings/update-document-type') => axios.post(`${this.url}/${name}/${query.id}`, { ccf: query.ccf })

        endpoints.predefined = (query: any, name = 'settings/show-predefined') => axios.get(`${this.url}/${name}`, { params: query })

        endpoints.savePredefined = (query: any, name = 'settings/update-predefined') => axios.post(`${this.url}/${name}/${query.id}`, { pd: query.pd, lgd: query.lgd })

        endpoints.lgd = (query: any, name = 'settings/show-guarantee-lgd') => axios.get(`${this.url}/${name}`, { params: query })

        endpoints.saveLgd = (query: any, name = 'settings/update-guarantee-lgd') => axios.post(`${this.url}/${name}/${query.id}`, { value: query.value })

        return endpoints
    }

    /**
     * Other APIs
     * @param {}
     */
    other(): {
        predefined(): any;
    } {
        var endpoints: any = {}

        endpoints.predefined = (query: any, name = 'help/fetch-predefined') => axios.get(`${this.url}/${name}`, { params: query })

        return endpoints
    }

    abortCalls = () => this.source.cancel('Operation canceled due to route change.')


}

export default API