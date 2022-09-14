import { isValidJSON } from "./helpers"

export const havePermission = (permission: string): boolean => {
    
    let permissions = isValidJSON(localStorage.getItem("permissions") || "") ? JSON.parse(localStorage.getItem("permissions") || "") : null
    if(!permissions)
        return false

    for( var i = 0; i < permissions.length; i++ ) {
        if( permissions[i].name === permission )
            return true
    }

    return false
}