import { Injectable } from '@angular/core';
import { ApiService } from 'sb-shared-lib';
import { isArray } from 'lodash';

@Injectable({
    providedIn: 'root'
})
export class RoutesService {

    constructor(private api: ApiService) { }

    /**
     * Return all the routes available.
     *
     * @return A array with all routes
     */
    public async getRoutes() {
        try {
            return await this.api.fetch('?get=config_routes');
        }
        catch (response: any) {
            console.warn('fetch package error', response);
        }
    }

    /**
     * Return the announcement of a controller
     *
     * @param string the operation to do ex:?get=config_routes
     * @return array with the announcement of a controller
     */
    public async getAnnounceController(operation: string) {
        try {
            return await this.api.fetch(operation + '&announce=true');
        }
        catch (response: any) {
            return null;
        }
    }

    /**
     * Return the respond of a controller after execution
     *
     * @param string the operation to do ex:?get=config_routes
     * @param array all the parameters for the controller
     * @return array with the respond of a controller's execution
     */
    public async submitController(operation: string, params: []) {
        let stringParams = '';
        for(let key in params) {
            if(isArray(params[key])) {
                stringParams += '&' + key + '=[' + params[key] + "]";
            } else {
                stringParams += '&' + key + '=' + params[key];
            }
        }

        try {
            return await this.api.fetch(operation + stringParams);
        }
        catch (response: any) {
            return null;
        }
    }
}
