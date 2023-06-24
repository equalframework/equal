import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';

@Component({
    selector: 'app-router-property',
    templateUrl: './router-property.component.html',
    styleUrls: ['./router-property.component.scss']
})
export class RouterPropertyComponent implements OnInit {

    @Input() name: any;
    @Input() description: any
    @Input() type: any;
    @Input() value: any;
    @Input() field: any;
    @Input() fields: any;
    @Input() actual_class: any;
    @Output() update_value_property = new EventEmitter<{ property: string, new_value: any }>();

    constructor() { }

    ngOnInit(): void {
    }

    public ngOnChanges() {

    }

    /**
     * Emit a object with the property name as key and the new value as value
     *
     * @param new_value any new value
     */
    updateValue(new_value: any): void {
        this.update_value_property.emit({ property: this.name, new_value: new_value });
    }

    /**
     * Update all the select class property with the new value and update all the other value with null;
     *
     * @param new_value any new value for select class
     */
    updateSelectClass(new_value: any): void {
        this.update_value_property.emit({ property: this.name, new_value: new_value });
        this.update_value_property.emit({ property: 'foreign_field', new_value: null });
        if(this.field.domain != undefined){
            this.update_value_property.emit({ property: 'domain', new_value: null });
        }

        if(this.field.rel_table != undefined) {
            this.update_value_property.emit({ property: 'rel_table', new_value: null });
        }

        if(this.field.rel_local_key != undefined) {
            this.update_value_property.emit({ property: 'rel_local_key', new_value: null });
        }

        if(this.field.rel_foreign_key != undefined) {
            this.update_value_property.emit({ property: 'rel_foreign_key', new_value: null });
        }
    }
}
