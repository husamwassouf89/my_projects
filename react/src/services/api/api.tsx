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
    year: number;
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
        this.url = "http://127.0.0.1:8000"
        // this.url = "https://workshop.jaiasoft.com/api/public"

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