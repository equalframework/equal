import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { FormControl } from '@angular/forms';
import { Observable } from 'rxjs';
import { map, startWith } from 'rxjs/operators';

@Component({
    selector: 'app-number',
    templateUrl: './number.component.html',
    styleUrls: ['./number.component.scss']
})
export class NumberComponent implements OnInit {

    @Input() value: any;
    @Input() required: boolean;
    @Input() params_property: any;
    @Input() selection: any;
    @Input() fixed: boolean;
    @Output() valueChange = new EventEmitter<number>();
    public tempValue: any;
    public autocompleteControl = new FormControl('');
    public filteredSelection: Observable<string[]>

    ngOnInit(): void {
        this.initialization();
    }

    public ngOnChanges() {
        this.initialization();
    }

    public initialization() {
        // if property is not a required value (can't decide if isDesired if is mandatory) && have already a value
        this.value != undefined ? this.tempValue = this.value : this.tempValue = 0;
        if (this.fixed) {
            this.autocompleteControl.disable();
        }

        if (this.selection) {
            if(!this.required && !this.selection.includes("")) {
                let tempValue = this.selection[0];
                this.selection[0] = "";
                this.selection.push(tempValue);
            }
            this.filteredSelection = this.autocompleteControl.valueChanges.pipe(
                startWith(''),
                map(value => this._filter(value || '')),
            );
            this.autocompleteControl.setValue(this.value);
        }
    }

    private _filter(value: string): string[] {
        return this.selection.map((num: number) => num.toString())
            .filter((field: string) => field.includes(value))
    }


    public updateValue(value: any) {
        if(value == "") {
            this.valueChange.emit(undefined);
        } else {
            this.valueChange.emit(+ value);
        }
    }

    public onSelectDefault(value: any) {
        if(value == "") {
            this.valueChange.emit(undefined);
        } else {
            this.valueChange.emit(+ value);
        }
    }
}
