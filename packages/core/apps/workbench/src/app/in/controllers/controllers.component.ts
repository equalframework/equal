import { Component, OnInit, ViewEncapsulation } from '@angular/core';
import { ControllersService } from './_service/controllers.service';
import { MatSnackBar } from '@angular/material/snack-bar';
import { prettyPrintJson } from 'pretty-print-json';

@Component({
    selector: 'app-controllers',
    templateUrl: './controllers.component.html',
    styleUrls: ['./controllers.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class ControllersComponent implements OnInit {

    public packages: any;
    public controllers: any;
    public selected_package = '';
    public selected_controller = '';
    public selected_property = '';
    public selected_type_controller = '';
    public schema: any;

    public step = 0;

    constructor(
        private api: ControllersService,
        private snackBar: MatSnackBar
    ) { }

    public async ngOnInit() {
        this.packages = await this.api.getPackages();
    }

    /**
     * Select a package when user click on it.
     *
     * @param eq_package the package that the user has selected
     */
    public async onclickPackageSelect(eq_package: string) {
        this.selected_package = eq_package;
        this.controllers = await this.api.getControllers(this.selected_package);
        this.selected_controller = "";
        this.selected_property = "";
        this.step = 1;
    }

    /**
     * Select a controller when user click on it.
     *
     * @param controller the controller that the user has selected
     */
    public async onclickControllerSelect(event: { type: string, name: string }) {
        this.selected_type_controller = event.type;
        let response = await this.api.getAnnounceController(event.type, this.selected_package, event.name);
        if (!response) {
            this.snackBar.open('Not allowed', 'Close', {
                duration: 1500,
                horizontalPosition: 'center',
                verticalPosition: 'bottom'
            });
        } else {
            this.selected_controller = event.name;
            this.selected_property = 'description'
            this.schema = response.announcement;
            this.step = 2;
        }
    }

    /**
     * Select a property when user click on it.
     *
     * @param property the property that the user has selected
     */
    public async onclickPropertySelect(property: string) {
        this.selected_property = property;
    }

    /**
     * Update the name of a controller.
     *
     * @param event contains the old and new name of the controller
     */
    public onupdateController(event: { type: string, old_node: string, new_node: string }) {

    }

    /**
     * Delete a controller.
     *
     * @param controller the name of the controller which will be deleted
     */
    public ondeleteController(event: { type: string, name: string }) {

    }

    /**
     * Call the api to create a controller.
     *
     * @param new_package the name of the new controller
     */
    public oncreateController(event: { type: string, name: string }) {

    }

    /**
     *
     * @returns a pretty HTML string of a schema in JSON.
     */
    public getJSONSchema() {
        if(this.schema) {
            return this.prettyPrint(this.schema);
        }
        return null;
    }

    public getProperties() {
        return Object.keys(this.schema);
    }

    public submitRequest(params: any) {
        console.warn(params);
    }

    /**
     * Function to pretty-print JSON objects as an HTML string
     *
     * @param input a JSON
     * @returns an HTML string
     */
    private prettyPrint(input: any) {
        return prettyPrintJson.toHtml(input);
    }
}
