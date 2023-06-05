import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { FormControl } from '@angular/forms';
import { Observable } from 'rxjs';
import { map, startWith } from 'rxjs/operators';

@Component({
    selector: 'app-property-integer-component',
    templateUrl: './property-integer-component.component.html',
    styleUrls: ['./property-integer-component.component.scss']
})
export class PropertyIntegerComponentComponent implements OnInit {

    @Input() value: any;
    @Input() name: any
    @Input() description: any;
    @Input() field: any;
    @Output() valueChange = new EventEmitter<number>();
    public isDesired: boolean;
    public tempValue: any;
    public autocompleteControl = new FormControl('');
    public filteredSelection: Observable<string[]>
    private initialized = false;

    constructor() { }

    ngOnInit(): void {
        this.initialization();
        this.initialized = true;
    }

    public ngOnChanges() {
        if (this.initialized) {
            this.initialization();
        }
    }

    public initialization() {
        // if property is not a required value (can't decide if isDesired if is mandatory) && have already a value
        this.value != undefined ? this.isDesired = true : this.isDesired = false;
        this.value != undefined ? this.tempValue = this.value : this.tempValue = 0;

        if (this.name == 'default' && this.field['selection'] != undefined) {
            this.isDesired ? this.autocompleteControl.enable() : this.autocompleteControl.disable();
            this.filteredSelection = this.autocompleteControl.valueChanges.pipe(
                startWith(''),
                map(value => this._filter(value || '')),
            );
            if (this.value) {
                this.autocompleteControl.setValue(this.value.toString());
            } else {
                this.autocompleteControl.setValue(this.field['selection'][0].toString());
            }
        }
    }

    private _filter(value: string): string[] {
        return this.field['selection'].map((num: number) => num.toString())
            .filter((field: string) => field.includes(value))
    }

    public updateValue(event: any) {
        this.valueChange.emit(+ event.target.value);
    }

    public onSelectDefault(event: any) {
        this.valueChange.emit(+ event.option.value);
    }

    public changeDesired() {
        this.isDesired = !this.isDesired;
        if (this.name == 'default' && this.field['selection'] != undefined) {
            this.autocompleteControl.disabled ? this.autocompleteControl.enable() : this.autocompleteControl.disable();
        }

        if (!this.isDesired) {
            this.valueChange.emit(undefined);
        } else {
            this.valueChange.emit(this.tempValue);
        }
    }
}
