import { Component, OnInit, ViewEncapsulation } from '@angular/core';
import { RoutesService } from './_service/routes.service'

@Component({
    selector: 'app-routes',
    templateUrl: './routes.component.html',
    styleUrls: ['./routes.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class RoutesComponent implements OnInit {

    public objectKeys = Object.keys;
    public routes: any;
    public filtered_route: any;
    public routes_controllers: any;
    public routes_fixed_params: any;

    constructor(private api: RoutesService) { }

    async ngOnInit() {
        this.routes = await this.api.getRoutes();
        console.log(this.routes)
        this.initialization();
    }

    /**
     * Initialize routes_controller
     * A object with the announce of controllers for the specified route
     * A another object with the fixed params in the url for each specified route
     */
    private initialization() {
        this.routes_controllers = {};
        this.routes_fixed_params = {};
        for(let key in this.routes) {
            if (!this.routes_controllers[key]) {
                this.routes_controllers[key] = {};
                this.routes_fixed_params[key] = {};
            }
            for(let operation in this.routes[key].methods) {
                this.initializationController(key, operation);
                this.initializationFixedParams(key, operation);
            }
        }

        this.onSearch("");
    }

    /**
     * Initialize route_controller with the announcement of controllers for the specified route
     *
     * @param key string, the path (/users, /user:id, ...)
     * @param operation string, the operation for the the path (GET, POST, ...)
     */
    private async initializationController(key: string, operation: string) {
        this.routes_controllers[key][operation] = await this.api.getAnnounceController(this.routes[key].methods[operation].operation);
        if(this.routes_controllers[key][operation]) {
            this.routes_controllers[key][operation] = this.routes_controllers[key][operation]['announcement'];
        }
    }

    /**
     * Initialize routes_fixed_params with a object with key as the name property and value as value property for the specified route
     *
     * @param key string, the path (/users, /user:id, ...)
     * @param operation string, the operation for the the path (GET, POST, ...)
     */
    private initializationFixedParams(key: string, operation: string) {
        let pairs = this.routes[key].methods[operation].operation.split("&");
        pairs = pairs.slice(1);
        for (const pair of pairs) {
            const [name, value] = pair.split("=");
            this.routes_fixed_params[key][operation] = { ...this.routes_fixed_params[key][operation], [name]: value };
        }
    }

    /**
     * Change filtered_route with the routes and operations which are respecting the input
     *
     * @param value string to search
     */
    public onSearch(value: string) {
        let temp_filtered: any = {}
        for(let key in this.routes) {
            // search on the name (/user, /group, ...)
            if (key.toLowerCase().includes(value.toLowerCase())) {
                temp_filtered[key] = [];
                temp_filtered[key] = Object.keys(this.routes[key].methods);
            }
            // search on file name
            else if(this.routes[key].info.file.toLowerCase().includes(value.toLowerCase())) {
                temp_filtered[key] = [];
                temp_filtered[key] = Object.keys(this.routes[key].methods);
            } else {
                for(let operation in this.routes[key].methods) {
                    // search on the operation (GET, POST, ...)
                    if(operation.toLowerCase().includes(value.toLowerCase())) {
                        temp_filtered[key] = [];
                        temp_filtered[key].push(operation);
                    }
                    // search ont the controller name
                    else {
                        let controller_name = this.routes[key].methods[operation].operation.split("=", 2)[1].split("&")[0];
                        if(controller_name.toLowerCase().includes(value.toLowerCase())) {
                            temp_filtered[key] = [];
                            temp_filtered[key].push(operation);
                        }
                    }
                }
            }
        }

        this.filtered_route = temp_filtered;
    }

    public getControllerName(route: string, operation: string) {
        return this.routes[route].methods[operation].operation.split("=", 2)[1].split("&")[0];
    }
}
