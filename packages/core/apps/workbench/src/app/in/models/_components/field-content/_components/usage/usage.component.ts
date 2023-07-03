import { Component, OnInit, Input, Output, EventEmitter, ViewEncapsulation } from '@angular/core';
import { WorkbenchService } from '../../../../_service/models.service'
import { FormControl } from '@angular/forms';
import { Observable } from 'rxjs';
import { map, startWith } from 'rxjs/operators';

@Component({
    selector: 'app-usage',
    templateUrl: './usage.component.html',
    styleUrls: ['./usage.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class UsageComponent implements OnInit {

    @Input() description: any;
    @Input() value: any;
    @Output() changeUsage = new EventEmitter<{ property: string, new_value: any }>();
    public usages: any;

    public type: any;
    public subtype: any;
    public variation: any;
    public length: any;
    public myTypeControl = new FormControl('');
    public filteredType: Observable<string[]>;

    constructor(private api: WorkbenchService) { }

    async ngOnInit() {
        this.usages = await this.api.getUsages();
        this.initialization()

        this.filteredType = this.myTypeControl.valueChanges.pipe(
            startWith(''),
            map(value => this._filter(value || '')),
        );
    }

    public ngOnChanges() {
        this.initialization();
    }

    /**
     * Initialize the variable's component on ngOnInit or ngOnChanges
     */
    private initialization() {
        if (this.value == undefined) {
            this.type = '';
            this.subtype = '';
            this.variation = '';
            this.length = undefined;
        } else {
            // split the value receive to have the type, subtype, variation and length in different variable
            let typeAndRest = this.value.split('/');
            this.type = typeAndRest[0];
            if (typeAndRest.length > 1) {
                if (typeAndRest[1].includes('.') && typeAndRest[1].includes(':')) {
                    let subtypeAndRest = typeAndRest[1].split('.');
                    this.subtype = subtypeAndRest[0];
                    [this.variation, this.length] = subtypeAndRest[1].split(':');
                } else if (!typeAndRest[1].includes('.') && typeAndRest[1].includes(':')) {
                    [this.subtype, this.length] = typeAndRest[1].split(':');
                    this.variation = '';
                } else if (typeAndRest[1].includes('.') && !typeAndRest[1].includes(':')) {
                    [this.subtype, this.variation] = typeAndRest[1].split('.');
                    this.length = undefined;
                } else {
                    this.subtype = typeAndRest[1].split('.')[0];
                    this.variation = "";
                    this.length = undefined;
                }
            }
        }

        this.myTypeControl.setValue(this.type);
    }

    /**
     * Return the types matching the value or the concatenated types/subtypes of the length of result is < 10
     *
     * @param value string to match
     * @returns array with the types or concatenated types/subtypes
     */
    private _filter(value: string): string[] {
        const filterValue = value.toLowerCase();
        let result = Object.keys(this.usages).filter((field: string) => field.toLowerCase().includes(filterValue));
        if (result.length <= 10) {
            Object.keys(this.usages).forEach((element) => {
                result = result.concat(
                    Object.keys(this.usages[element])
                        .map((field) => this.isNumber(field) ? field = `${element}/${this.usages[element][field]}` : `${element}/${field}`)
                        .filter((field: string) => field.toLowerCase().includes(filterValue))
                )
            })
        }
        return result;
    }

    /**
     * Select a new usage type for the field.
     *
     * @param value string of the new type selected
     */
    public onSelectType(value: string) {
        if (value.includes('/')) {
            [this.type, this.subtype] = value.split('/');
        } else {
            this.type = value;
            this.subtype = '';
        }
        this.variation = '';
        this.length = undefined;
        this.changeValue();
    }

    /**
     * Select a new usage subtype for the field.
     *
     * @param value string of the new subtype selected
     */
    public onSelectedSubtypeChange(value: string) {
        this.subtype = value;
        this.variation = '';
        this.length = undefined;
        this.changeValue();
    }

    /**
     * Select a new usage variation for the field.
     *
     * @param value string of the new variation selected
     */
    public onVariationChange(value: string) {
        this.variation = value;
        this.changeValue();
    }

    /**
     * Check if the variation need to be disabled based on the current type and subtype.
     *
     * @returns boolean to see if the variation need to be disable
     */
    public isVariationDisabled() {
        return !(this.usages && this.usages.hasOwnProperty(this.type)
            && this.usages[this.type].hasOwnProperty(this.subtype)
            && this.usages[this.type][this.subtype].hasOwnProperty('selection')
            && this.usages[this.type][this.subtype]['selection'].length > 0);
    }


    public updateLength(value: any) {
        value < 1 ? this.length = 1 : this.length = value;
        this.changeValue();
    }

    /**
     * Emit a new usage value with the current type, subtype, variation and length
     */
    private changeValue() {
        let newValue = "";
        newValue += this.type;
        if (this.subtype != "") {
            newValue += "/" + this.subtype;
            if (this.variation != "") {
                newValue += "." + this.variation;
            }
            if (this.length != undefined) {
                newValue += ":" + this.length;
            }
        }

        this.changeUsage.emit({ property: "usage", new_value: newValue })
    }

    /**
     * Check if the value is a number
     *
     * @param value any, the value to check
     * @returns true if number, false if not
     */
    public isNumber(value: any) {
        return !isNaN(parseFloat(value)) && isFinite(value);
    }

    /**
     * Get the key of a element of key-value
     *
     * @param element key-value element
     * @returns value if key is a number and the key if not
     */
    public getValueElement(element: any) {
        if (!isNaN(parseFloat(element.key)) && isFinite(element.key)) {
            return element.value;
        } else {
            return element.key;
        }
    }

    /**
     * Get the current length
     *
     * @returns number or undefined
     */
    public getLength() {
        if (this.length) {
            return this.length;
        } else {
            return;
        }
    }
}
