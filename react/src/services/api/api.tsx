import axios from 'axios';
import { useCookies, withCookies } from 'react-cookie';
import { addToDate } from '../hoc/helpers';

interface pagination {
    page: number,
    page_size: number,
    keyword?: string
}

interface pd {
    class_type_id: number;
    year: string;
    quarter: "q1" | "q2" | "q3" | "q4";
    path: string;
    attachments: number[],
    eco_parameter_base_value: number;
    eco_parameter_base_weight: number;
    eco_parameter_mild_value: number;
    eco_parameter_mild_weight: number;
    eco_parameter_heavy_value: number;
    eco_parameter_heavy_weight: number;
}

class API {

    url: string;

    constructor() {
        // this.url = "http://127.0.0.1:8000"
        this.url = "https://desolate-inlet-24536.herokuapp.com"

        const [cookies, _, removeCookie] = useCookies();

        // Add Auth header
        axios.interceptors.request.use( (config) => {

            config.headers["Accept"] = "application/json"
            config.headers["Content-Type"] = "application/json"
            config.headers["X-Requested-With"] = "XMLHttpRequest"
            if (cookies.userinfo) {
                config.headers["Authorization"] = cookies.userinfo.accessToken;
            }
            return (config);

        })

        // Handle 401
        axios.interceptors.response.use((response) => {
            if(!response)
                return Promise.reject(response)
            return response
        }, function (error) {
            if(!error)
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
        login(query: {email: string, password: string}, name?: string): any;
        logout(query: null, name?: string): any;
    } {
        var endpoints:any = {}

        endpoints.login = ( query: any, name='login' ) => axios.post( `${this.url}/${name}`, query )
        
        endpoints.logout = ( query: any, name='logout' ) => axios.post( `${this.url}/${name}`, query )

        return endpoints
    }


    /**
     * Clients APIs
     * @param {}
     */
     clients(): {
        index( query: pagination ): any;
        show( query: { id: number } ): any;
        search_cif( query: { cif: number } ): any;
        store( query: { path: string; year: string; quarter: "q1" | "q2" | "q3" | "q4"; } ): any;
    } {
        var endpoints:any = {}

        endpoints.index = ( query: any, name='clients' ) => axios.get( `${this.url}/${name}`, { params: query } )

        endpoints.show = ( query: any, name='clients' ) => axios.get( `${this.url}/${name}/${query.id}`, { params: query } )

        endpoints.search_cif = ( query: any, name='clients' ) => axios.get( `${this.url}/${name}/cif/${query.cif}`, { params: query } )
        
        endpoints.store = ( query: any, name='clients' ) => axios.post( `${this.url}/${name}`, query )

        return endpoints
    }


    /**
     * PD APIs
     * @param {}
     */
    pd(): {
        index( query: pagination ): any;
        show( query: { id: number } ): any;
        store( query: pd ): any;
        delete( query: { ids: number[] } ): any;
    } {
        var endpoints:any = {}

        endpoints.index = ( query: any, name='pd' ) => axios.get( `${this.url}/${name}`, { params: query } )

        endpoints.show = ( query: any, name='pd' ) => axios.get( `${this.url}/${name}/${query.id}`, { params: query } )
        
        endpoints.store = ( query: any, name='pd' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.delete = ( query: any, name="pd/delete" ) => axios.delete( `${this.url}/${name}`, { params: query } )

        return endpoints
    }


    /**
     * IRS APIs
     * @param {}
     */
     irs(): {
        irs( query: { class_type_id: number; category_id: number; } ): any;
        questions( query: { id: number; } ): any;
        store( query: any ): any;
        update( query: any ): any;
    } {
        var endpoints:any = {}

        endpoints.irs = ( query: any, name='irs/show' ) => axios.get( `${this.url}/${name}`, { params: query } )
        
        endpoints.store = ( query: any, name='irs/questions' ) => axios.post( `${this.url}/${name}`, query )
        
        endpoints.update = ( query: any, name='irs/questions' ) => axios.put( `${this.url}/${name}/${query.id}`, query )

        return endpoints
    }


    /**
     * Client IRS profile APIs
     * @param {}
     */
     irs_profile(): {
        index( query: pagination, id: number ): any;
        store( query: { client_id: number; answers: number[] } ): any;
    } {
        var endpoints:any = {}

        endpoints.index = ( query: any, id: number, name='irs/client-profile/all' ) => axios.get( `${this.url}/${name}/${id}`, { params: query } )

        endpoints.store = ( query: any, name='irs/client-profile' ) => axios.post( `${this.url}/${name}`, query )

        return endpoints
    }


    /**
     * Staging profile APIs
     * @param {}
     */
     staging_profile(): {
         index( query: pagination, id: number ): any;
         questions( query: { class_type_id: number; } ): any;
        store( query: { client_id: number; answers: { id: number; value?: number; }[] } ): any;
    } {
        var endpoints:any = {}

        endpoints.index = ( query: any, id: number, name='staging/client-profile/all' ) => axios.get( `${this.url}/${name}/${id}`, { params: query } )

        endpoints.questions = ( query: any, name='staging' ) => axios.get( `${this.url}/${name}/${query.class_type_id}`, { params: query } )

        endpoints.store = ( query: any, name='staging/client-profile' ) => axios.post( `${this.url}/${name}`, query )

        return endpoints
    }

    /**
     * Other APIs
     * @param {}
     */
     other(): {
        predefined(): any;
    } {
        var endpoints:any = {}

        endpoints.predefined = ( query: any, name='help/fetch-predefined' ) => axios.get( `${this.url}/${name}`, { params: query } )

        return endpoints
    }
    

}

export default API