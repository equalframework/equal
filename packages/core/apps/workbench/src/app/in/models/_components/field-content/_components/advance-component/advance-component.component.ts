import { Component, OnInit, Input, Output, EventEmitter, ViewEncapsulation } from '@angular/core';

@Component({
    selector: 'app-advance-component',
    templateUrl: './advance-component.component.html',
    styleUrls: ['./advance-component.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class AdvanceComponentComponent implements OnInit {

    @Input() advanceField: any;
    @Input() field: any;
    @Input() fields: any;
    @Input() properties: any;
    @Input() actual_class: any;
    @Output() update_value_property = new EventEmitter<{ property: string, new_value: any }>();
    selectedProperty = '';

    constructor() { }

    ngOnInit(): void {
    }

    public ngOnChanges() {
    }

    /**
     * Change if you want the property or not
     *
     * @param property string name of the property which is not desired anymore
     */
    public changeDesired(property: any) {
        if (this.advanceField[property] != undefined) {
            this.update_value_property.emit({ property: property, new_value: undefined });
            this.selectedProperty = '';
        } else {
            let defaultTypeValue = this.defaultTypeValue(this.properties[property]['type']);
            this.update_value_property.emit({ property: property, new_value: defaultTypeValue });
            this.selectedProperty = property;
        }
    }

    /**
     * Return the default value for each type
     * Useful when the user just change the property in desired or if the property doesn't have value
     *
     * @param type string of the type
     * @returns any, the default value for the type
     */
    private defaultTypeValue(type: string) {
        if (type == "string" || type == "text") {
            return "";
        } else if (type == "integer" || type == "float" || type == "many2one" || type == "one2many" || type == "many2many") {
            return 0;
        } else if (type == "array" || type == "selection" || type == 'domain') {
            return [];
        } else {
            return "";
        }
    }

    /**
     * Emit to change the value of the property
     *
     * @param new_property object with the name of the property and the new value
     */
    updatePropertiesWithValue(new_property: any) {
        this.update_value_property.emit(new_property);
    }

    /**
     * Emit to change the value of the boolean property
     *
     * @param new_value boolean the new value
     * @param name string of the property
     */
    updateBooleanPropertiesWithValue(new_value: boolean, name: any) {
        this.update_value_property.emit({ property: name, new_value: new_value });
    }

    /**
     * Change the selected property, the property displayed will change
     *
     * @param property string of the name of the new selected property
     */
    public selectProperty(property: any) {
        if (this.field[property]) {
            this.selectedProperty = property;
        }
    }

    /**
     * Check if the property has a value
     *
     * @param property string, the name of the property
     * @returns true if has a value
     */
    public hasValue(property: any) {
        return this.advanceField[property] != undefined;
    }

    /**
     * Check if the property is a boolean
     *
     * @param property any, property to check
     * @returns true if boolean
     */
    public isBoolean(property: any):boolean {
        return this.properties[property]['type'] && this.properties[property]['type'] == 'boolean';
    }

    /**
     * Get the type of the property
     *
     * @param property string, the name of the property
     * @returns string of the type
     */
    public getType(property: string): string {
        return this.properties[property]['type'];
    }

    /**
     * Get the value of the property
     *
     * @param property string, the name of the property
     * @returns any, the value
     */
    public getValue(property: any): any {
        return this.field[property];
    }
}
