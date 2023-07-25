import { Component, OnInit, ViewEncapsulation, ViewChild } from '@angular/core';
import { ContextService } from 'sb-shared-lib';
import { WorkbenchService } from './_service/models.service'
import { FieldContentComponent } from './_components/field-content/field-content.component';
import { MatSnackBar } from '@angular/material/snack-bar';
import { prettyPrintJson } from 'pretty-print-json';

@Component({
  selector: 'app-models',
  templateUrl: './models.component.html',
  styleUrls: ['./models.component.scss'],
  encapsulation: ViewEncapsulation.None
})
export class ModelsComponent implements OnInit {

    public child_loaded = false;
    public step = 0;
    public selected_package: string = "";
    public selected_class: string = "";
    public selected_field: string = "";
    public classes_for_selected_package: string[] = [];
    // http://equal.local/index.php?get=config_packages
    public packages: string[];
    // http://equal.local/index.php?get=core_config_classes
    private eq_class: any;
    public schema: any;
    public fields_for_selected_class: string[];
    public types: any;
    @ViewChild(FieldContentComponent) childComponent: FieldContentComponent;

    constructor(
        private context: ContextService,
        private api: WorkbenchService,
        private snackBar: MatSnackBar
    ) { }

    public async ngOnInit() {
        this.packages = await this.api.getPackages();
        this.eq_class = await this.api.getClasses();
        this.types = await this.api.getTypes();
    }

    /**
     * Select a package when user click on it.
     *
     * @param eq_package the package that the user has selected
     */
    public onclickPackageSelect(eq_package: string) {
        this.selected_package = eq_package;
        this.classes_for_selected_package = this.eq_class[this.selected_package];
        this.selected_class = "";
        this.selected_field = "";
        this.child_loaded = false;
        this.step = 1;
    }

    /**
     * Update the name of a package.
     *
     * @param event contains the old and new name of the package
     */
    public onupdatePackage(event: { old_node: string, new_node: string }) {
        this.api.updatePackage(event.old_node, event.new_node);
        /* MAY BE USEFUL WHEN LINK TO BACKEND
        if (this.selected_package == event.old_node) {
            this.selected_package = event.new_node;
        }
        this.eq_class[event.new_node] = this.eq_class[event.old_node];
        delete this.eq_class[event.old_node];
        */
    }

    /**
     * Delete a package.
     *
     * @param eq_package the name of the package which will be deleted
     */
    public ondeletePackage(eq_package: string) {
        this.api.deletePackage(eq_package);
        /* MAY BE USEFUL WHEN LINK TO BACKEND
        if (this.selected_package == eq_package) {
            this.selected_package = "";
            this.selected_class = "";
            this.selected_field = "";
            this.child_loaded = false;
        }
        */
    }

    /**
     * Call the api to create a package.
     *
     * @param new_package the name of the new package
     */
    public oncreatePackage(new_package: any) {
        this.api.createPackage(new_package);
    }

    /**
     * Select a class.
     *
     * @param eq_class the class that the user has selected
     */
    public async onclickClassSelect(eq_class: string) {
        this.selected_class = eq_class;
        this.selected_field = "";
        this.child_loaded = false;
        this.schema = await this.api.getSchema(this.selected_package + '\\' + this.selected_class);
        this.fields_for_selected_class = Object.keys(this.schema.fields);
        this.step = 2;
    }

    /**
     * Update the name of a class for the selected package.
     *
     * @param event contains the old and new name of the class
     */
    public onupdateClass(event: { old_node: string, new_node: string }) {
        this.api.updateClass(this.selected_package, event.old_node, event.new_node);
        /* MAY BE USEFUL WHEN LINK TO BACKEND
        if (this.selected_class == event.old_node) {
            this.selected_class = event.new_node;
        }
        */
    }

    /**
     * Delete a class for the selected package.
     *
     * @param eq_class the name of the class which will be deleted
     */
    public ondeleteClass(eq_class: string) {
        this.api.deleteClass(this.selected_package, eq_class);
        /* MAY BE USEFUL WHEN LINK TO BACKEND
        if (this.selected_class == eq_class) {
            this.selected_class = "";
            this.selected_field = "";
            this.child_loaded = false;
        }
        */
    }

    /**
     * Create a class for the selected package.
     *
     * @param eq_class the name of the new class
     */
    public oncreateClass(eq_class: string) {
        this.api.createClass(this.selected_package, eq_class);
    }

    /**
     * Select a field.
     *
     * @param field the field that the user has selected
     */
    public onclickFieldSelect(field: string) {
        if (!this.child_loaded) {
            this.selected_field = field;
            this.child_loaded = true;
        } else {
            if (!this.childComponent?.hasChanged) {
                this.selected_field = field;
            } else {
                console.log("snackbar");
                this.snackBar.open('Save or abandon changes before changing context', 'Close', {
                    duration: 1500,
                    horizontalPosition: 'center',
                    verticalPosition: 'bottom'
                });
            }
        }
    }

    /**
     * Update the name of a field for the selected package/class.
     *
     * @param event contain the old and new name of the field
     */
    public onupdateField(event: { old_node: string, new_node: string }) {
        this.api.updateField(this.selected_package, this.selected_class, event.old_node, event.new_node);
        /* MAY BE USEFUL WHEN LINK TO BACKEND
        if (this.selected_field == event.old_node) {
            this.selected_field = event.new_node;
        }
        */
    }

    /**
     * Delete a field for the selected package/class.
     *
     * @param field the name of the field which will be deleted
     */
    public ondeleteField(field: string) {
        this.api.deleteField(this.selected_package, this.selected_class, field);
        /* MAY BE USEFUL WHEN LINK TO BACKEND
        if (this.selected_field == field) {
            this.selected_field = "";
            this.child_loaded = false;
        }
        */
    }

    /**
     * Create a field for the selected package/class.
     *
     * @param new_field name of the new field
     */
    public oncreateField(new_field: string) {
        this.api.createField(this.selected_package, this.selected_class, new_field)
    }

    /**
     * Update the schema of the selected class and selected fields.
     *
     * @param new_schema new field schema
     */
    public onUpdateSchema(new_schema: {}) {
        this.schema.fields[this.selected_field] = new_schema;
        this.api.updateSchema(this.schema, this.selected_package, this.selected_class);
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
