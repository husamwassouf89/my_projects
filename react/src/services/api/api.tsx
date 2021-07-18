import axios from 'axios';
import { useCookies, withCookies } from 'react-cookie';
import { addToDate } from '../hoc/helpers';

interface pagination {
    page: number,
    page_size: number,
    keyword?: string
}

interface role {
    id?: number;
    name: string;
    permissions: number[];
}

interface user {
    id?: number;
    employee_id: number;
    email: string;
    password: string;
    role_id: number;
}

interface employee {
    id?: number;
    name: string;
    serial_no: string;
    mobile: string;
    job: string;
    salary: number;
    working_status: 'Working' | 'Pending' | 'Suspended';
}

interface salary {
    employee_id?: number;
    salary?: number;
    additional_time?: number;
    additional_value?: number;
    absence_time?: number;
    discount_value?: number;
    net_salary?: number;
    date?: string;
}

type attribute = {
    name?: string;
    type?: string;
    hint?: string;
    items?: string[];
    min?: number;
    max?: number;
}

interface component {
    id?: number;
    name: string;
    serial_no: string;
    description: string;
    attributes_list: attribute[];
}

interface product {
    id?: number;
    name?: string;
    serial_no?: string;
    description?: string;
    price?: number;
    components?: {
        id?: number;
        amount?: number;
        attributes: {
            attribute_id: number;
            value: string;
        }[];
    }[];
}

interface supplier {
    id?: number;
    name?: string;
    account_no?: string;
    serial_no?: string;
    phone?: string;
    fax?: string;
    mobile?: string;
    email?: string;
    website?: string;
    country?: string;
    government?: string;
    city?: string;
    postal_code?: string;
    department_number?: string;
    address_1?: string;
    address_2?: string;
    address_3?: string;
}

interface client extends supplier {
    type?: 'Cash' | 'Post Paid';
    debt_ceiling?: number;
}

interface order {
    id?: number;
    client_id?: number;
    order_no?: string;
    date?: string;
    summary?: string;
    products?: { id?: number; amount?: number; }[]
}

interface bill {
    id?: number;
    order_id?: number;
    client_id?: number;
    bill_no?: string;
    date?: string;
    type?: 'Cash' | 'Post Paid';
    total?: number;
    discount_percentage?: number;
    discount_amount?: number;
    discount_value?: number;
    total_net?: number;
    tax_amount?: number;
    net_after_tax?: number;
    net_after_tax_str?: string;
    paid?: number;
    remains?: number;
    seller_name?: string;
}

interface receiptOrder {
    id?: number;
    supplier_id?: number;
    receipt_order_no?: string;
    date?: string;
    type?: 'Cash' | 'Post Paid';
    paid?: number;
    remains?: number;
    total?: number;
    materials?: {
        component_id?: number;
        amount?: number;
        price?: number;
        attributes: {
            attribute_id: number;
            value: string;
        }[];
    }[];
}

interface receiptVoucher {
    id?: number;
    client_id?: number;
    order_id?: number;
    voucher_no?: string;
    date?: string;
    value?: number;
    paragraph?: string;
}

interface paymentVoucher {
    id?: number;
    client_id?: number;
    voucher_no?: string;
    date?: string;
    value?: number;
    paragraph?: string;
}

class API {

    url: string;

    constructor() {
        // this.url = "http://127.0.0.1:8000"
        this.url = "https://workshop.jaiasoft.com/api/public"

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
     * Roles APIs
     * @param {}
     */
    roles(): {
        index( query: pagination ): any;
        permissions( query: pagination ): any;
        show( query: { id: number } ): any;
        store( query: role ): any;
        update( query: role ): any;
        delete( query: { ids: number[] } ): any;
    } {
        var endpoints:any = {}

        endpoints.index = ( query: any, name='roles' ) => axios.get( `${this.url}/${name}`, { params: query } )

        endpoints.permissions = ( query: any, name='roles/permissions' ) => axios.get( `${this.url}/${name}`, { params: query } )

        endpoints.show = ( query: any, name='roles/show' ) => axios.get( `${this.url}/${name}`, { params: query } )
        
        endpoints.store = ( query: any, name='roles/store' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.update = ( query: any, name='roles/update' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.delete = ( query: any, name="roles/delete" ) => axios.delete( `${this.url}/${name}`, { params: query } )

        return endpoints
    }
    

    /**
     * Users APIs
     * @param {}
     */
    users(): {
        index( query: pagination ): any;
        show( query: { id: number } ): any;
        store( query: user ): any;
        update( query: user ): any;
        delete( query: { ids: number[] } ): any;
        fetch_my_info( query: null ): any;
        update_my_info( query: { name?: string; email?: string; mobile?: string; passowrd?: string; } ): any;
    } {
        var endpoints:any = {}

        endpoints.index = ( query: any, name='users' ) => axios.get( `${this.url}/${name}`, { params: query } )
        
        endpoints.store = ( query: any, name='users/store' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.update = ( query: any, name='users/update' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.delete = ( query: any, name="users/delete" ) => axios.delete( `${this.url}/${name}`, { params: query } )

        endpoints.fetch_my_info = ( query: null, name='users/fetch-user-info' ) => axios.get( `${this.url}/${name}`, { params: query } )

        endpoints.update_my_info = ( query: any, name='users/update-user-info' ) => axios.post( `${this.url}/${name}`, query )

        return endpoints
    }

    
    /**
     * Employees APIs
     * @param {}
     */
    employees(): {
        index( query: pagination & { has_user?: string } ): any;
        store( query: employee ): any;
        update( query: employee ): any;
        delete( query: { ids: number[] } ): any;
    } {
        var endpoints:any = {}

        endpoints.index = ( query: any, name='employees' ) => axios.get( `${this.url}/${name}`, { params: query } )
        
        endpoints.store = ( query: any, name='employees/store' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.update = ( query: any, name='employees/update' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.delete = ( query: any, name="employees/delete" ) => axios.delete( `${this.url}/${name}`, { params: query } )

        return endpoints
    }

    /**
     * Salaries APIs
     * @param {}
     */
    salaries(): {
        index( query: pagination ): any;
        store( query: { salaries: salary[] } ): any;
    } {
        var endpoints:any = {}

        endpoints.index = ( query: any, name='salaries' ) => axios.get( `${this.url}/${name}`, { params: query } )
        
        endpoints.store = ( query: any, name='salaries/store' ) => axios.post( `${this.url}/${name}`, query )

        return endpoints
    }


    /**
     * Components APIs
     * @param { type: "material" | "service" }
     */
    components(type: "material" | "service"): {
        index( query: pagination ): any;
        store( query: component ): any;
        update( query: component ): any;
        delete( query: { ids: number[] } ): any;
        show( query: { id: number } ): any;
    } {
        var endpoints:any = {}

        endpoints.index = ( query: any, name='components' ) => axios.get( `${this.url}/${name}`, { params: {...query, type} } )
        
        endpoints.store = ( query: any, name='components/store' ) => axios.post( `${this.url}/${name}`, {...query, type} )

        endpoints.update = ( query: any, name='components/update' ) => axios.post( `${this.url}/${name}`, {...query, type} )

        endpoints.delete = ( query: any, name="components/delete" ) => axios.delete( `${this.url}/${name}`, { params: {...query, type} } )

        endpoints.show = ( query: any, name='components/show' ) => axios.get( `${this.url}/${name}`, { params: {...query, type} } )

        return endpoints
    }

    

    /**
     * Products APIs
     * @param {}
     */
    products(): {
        index( query: pagination ): any;
        store( query: product ): any;
        update( query: product ): any;
        delete( query: { ids: number[] } ): any;
        show( query: { id: number } ): any;
    } {
        var endpoints:any = {}

        endpoints.index = ( query: any, name='products' ) => axios.get( `${this.url}/${name}`, { params: query } )
        
        endpoints.store = ( query: any, name='products/store' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.update = ( query: any, name='products/update' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.delete = ( query: any, name="products/delete" ) => axios.delete( `${this.url}/${name}`, { params: query } )

        endpoints.show = ( query: any, name='products/show' ) => axios.get( `${this.url}/${name}`, { params: query } )

        return endpoints
    }


    /**
     * Clients APIs
     * @param {}
     */
    clients(): {
        index( query: pagination ): any;
        store( query: client ): any;
        update( query: client ): any;
        delete( query: { ids: number[] } ): any;
        show( query: { id: number } ): any;
    } {
        var endpoints:any = {}

        endpoints.index = ( query: any, name='clients' ) => axios.get( `${this.url}/${name}`, { params: query } )
        
        endpoints.store = ( query: any, name='clients/store' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.update = ( query: any, name='clients/update' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.delete = ( query: any, name="clients/delete" ) => axios.delete( `${this.url}/${name}`, { params: query } )

        endpoints.show = ( query: any, name='clients/show' ) => axios.get( `${this.url}/${name}`, { params: query } )

        return endpoints
    }
    

    /**
     * Suppliers APIs
     * @param {}
     */
    suppliers(): {
        index( query: pagination ): any;
        store( query: supplier ): any;
        update( query: supplier ): any;
        delete( query: { ids: number[] } ): any;
        show( query: { id: number } ): any;
    } {
        var endpoints:any = {}

        endpoints.index = ( query: any, name='suppliers' ) => axios.get( `${this.url}/${name}`, { params: query } )
        
        endpoints.store = ( query: any, name='suppliers/store' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.update = ( query: any, name='suppliers/update' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.delete = ( query: any, name="suppliers/delete" ) => axios.delete( `${this.url}/${name}`, { params: query } )

        endpoints.show = ( query: any, name='suppliers/show' ) => axios.get( `${this.url}/${name}`, { params: query } )

        return endpoints
    }
        

    /**
     * Orders APIs
     * @param {}
     */
    orders(): {
        index( query: pagination ): any;
        store( query: order ): any;
        update( query: order ): any;
        delete( query: { ids: number[] } ): any;
        show( query: { id: number } ): any;
    } {
        var endpoints:any = {}

        endpoints.index = ( query: any, name='orders' ) => axios.get( `${this.url}/${name}`, { params: query } )
        
        endpoints.store = ( query: any, name='orders/store' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.update = ( query: any, name='orders/update' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.delete = ( query: any, name="orders/delete" ) => axios.delete( `${this.url}/${name}`, { params: query } )

        endpoints.show = ( query: any, name='orders/show' ) => axios.get( `${this.url}/${name}`, { params: query } )

        return endpoints
    }
        

    /**
     * Bills APIs
     * @param {}
     */
    bills(): {
        index( query: pagination ): any;
        store( query: bill ): any;
        update( query: bill ): any;
        delete( query: { ids: number[] } ): any;
        show( query: { id: number } ): any;
    } {
        var endpoints:any = {}

        endpoints.index = ( query: any, name='bills' ) => axios.get( `${this.url}/${name}`, { params: query } )
        
        endpoints.store = ( query: any, name='bills/store' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.update = ( query: any, name='bills/update' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.delete = ( query: any, name="bills/delete" ) => axios.delete( `${this.url}/${name}`, { params: query } )

        endpoints.show = ( query: any, name='bills/show' ) => axios.get( `${this.url}/${name}`, { params: query } )

        return endpoints
    }

    
    /**
     * Receipt orders APIs
     * @param {}
     */
    receipt_orders(): {
        index( query: pagination ): any;
        store( query: receiptOrder ): any;
        update( query: receiptOrder ): any;
        delete( query: { ids: number[] } ): any;
        show( query: { id: number } ): any;
    } {
        var endpoints:any = {}

        endpoints.index = ( query: any, name='receipt_orders' ) => axios.get( `${this.url}/${name}`, { params: query } )
        
        endpoints.store = ( query: any, name='receipt_orders/store' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.update = ( query: any, name='receipt_orders/update' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.delete = ( query: any, name="receipt_orders/delete" ) => axios.delete( `${this.url}/${name}`, { params: query } )

        endpoints.show = ( query: any, name='receipt_orders/show' ) => axios.get( `${this.url}/${name}`, { params: query } )

        return endpoints
    }


    /**
     * Receipt Vouchers APIs
     * @param {}
     */
    receipt_vouchers(): {
        index( query: pagination ): any;
        store( query: receiptVoucher ): any;
        update( query: receiptVoucher ): any;
        delete( query: { ids: number[] } ): any;
        show( query: { id: number } ): any;
    } {
        var endpoints:any = {}

        endpoints.index = ( query: any, name='receipt_vouchers' ) => axios.get( `${this.url}/${name}`, { params: query } )
        
        endpoints.store = ( query: any, name='receipt_vouchers/store' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.update = ( query: any, name='receipt_vouchers/update' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.delete = ( query: any, name="receipt_vouchers/delete" ) => axios.delete( `${this.url}/${name}`, { params: query } )

        endpoints.show = ( query: any, name='receipt_vouchers/show' ) => axios.get( `${this.url}/${name}`, { params: query } )

        return endpoints
    }

    
    /**
     * Payment Vouchers APIs
     * @param {}
     */
    payment_vouchers(): {
        index( query: pagination ): any;
        store( query: paymentVoucher ): any;
        update( query: paymentVoucher ): any;
        delete( query: { ids: number[] } ): any;
        show( query: { id: number } ): any;
    } {
        var endpoints:any = {}

        endpoints.index = ( query: any, name='pay_vouchers' ) => axios.get( `${this.url}/${name}`, { params: query } )
        
        endpoints.store = ( query: any, name='pay_vouchers/store' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.update = ( query: any, name='pay_vouchers/update' ) => axios.post( `${this.url}/${name}`, query )

        endpoints.delete = ( query: any, name="pay_vouchers/delete" ) => axios.delete( `${this.url}/${name}`, { params: query } )

        endpoints.show = ( query: any, name='pay_vouchers/show' ) => axios.get( `${this.url}/${name}`, { params: query } )

        return endpoints
    }

    /**
     * Reports APIs
     * @param {}
     */
     reports(): {
        client_orders( query: { id: number; } ): any;
        client_bills( query: { id: number; } ): any;
        order_bill( query: { id: number; grouped: "yes" | "no" } ): any;
        executive_order( query: { id: number; } ): any;
        inventory( query: {} ): any;
        clients_debt( query: {} ): any;
        suppliers_debt( query: {} ): any;
        receipt_vouchers( query: { after?: string; before?: string; } ): any;
        payment_vouchers( query: { after?: string; before?: string; } ): any;
        client_account( query: { id: number; } ): any;
        supplier_account( query: { id: number; } ): any;
        receipt_orders( query: { after?: string; before?: string; } ): any;
        sales( query: { after?: string; before?: string; } ): any;
        fund_flow( query: { after?: string; before?: string; } ): any;
    } {
        var endpoints:any = {}

        endpoints.client_orders = ( query: any, name='reports/client-orders' ) => axios.get( `${this.url}/${name}`, { params: query } )

        endpoints.client_bills = ( query: any, name='reports/client-bills' ) => axios.get( `${this.url}/${name}`, { params: query } )

        endpoints.order_bill = ( query: any, name='reports/order-price' ) => axios.get( `${this.url}/${name}`, { params: query } )

        endpoints.executive_order = ( query: any, name='reports/execute-order' ) => axios.get( `${this.url}/${name}`, { params: query } )

        endpoints.inventory = ( query: any, name='reports/inventory' ) => axios.get( `${this.url}/${name}`, { params: query } )

        endpoints.clients_debt = ( query: any, name='reports/clients-debt' ) => axios.get( `${this.url}/${name}`, { params: query } )

        endpoints.suppliers_debt = ( query: any, name='reports/suppliers-debt' ) => axios.get( `${this.url}/${name}`, { params: query } )

        endpoints.receipt_vouchers = ( query: any, name='reports/receipt-vouchers' ) => axios.get( `${this.url}/${name}`, { params: query } )

        endpoints.payment_vouchers = ( query: any, name='reports/pay-vouchers' ) => axios.get( `${this.url}/${name}`, { params: query } )

        endpoints.client_account = ( query: any, name='reports/client-account-statement' ) => axios.get( `${this.url}/${name}`, { params: query } )

        endpoints.supplier_account = ( query: any, name='reports/supplier-account-statement' ) => axios.get( `${this.url}/${name}`, { params: query } )

        endpoints.receipt_orders = ( query: any, name='reports/receipt-orders' ) => axios.get( `${this.url}/${name}`, { params: query } )

        endpoints.sales = ( query: any, name='reports/sales' ) => axios.get( `${this.url}/${name}`, { params: query } )

        endpoints.fund_flow = ( query: any, name='reports/fund-flow-statement' ) => axios.get( `${this.url}/${name}`, { params: query } )

        return endpoints
    }
    

}

export default API