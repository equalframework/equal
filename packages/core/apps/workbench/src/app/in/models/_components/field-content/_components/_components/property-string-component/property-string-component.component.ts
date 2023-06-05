import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { FormControl } from '@angular/forms';
import { Observable } from 'rxjs';
import { map, startWith } from 'rxjs/operators';

@Component({
    selector: 'app-property-string-component',
    templateUrl: './property-string-component.component.html',
    styleUrls: ['./property-string-component.component.scss']
})
export class PropertyStringComponentComponent implements OnInit {

    @Input() value: any;
    @Input() name: any;
    @Input() description: any;
    @Input() field: any;
    @Output() valueChange = new EventEmitter<string>();
    public myControl = new FormControl('');
    public inputValue = '';
    public filteredSelection: Observable<string[]>;
    public isDesired: boolean;

    constructor() { }

    ngOnInit(): void {
        this.initialization();
    }

    public ngOnChanges() {
        this.initialization();
    }

    public initialization() {
        this.value ? this.inputValue = this.value : this.inputValue = '';

        if (this.field['selection']) {
            this.isDesired ? this.myControl.enable() : this.myControl.disable();
            this.filteredSelection = this.myControl.valueChanges.pipe(
                startWith(''),
                map(value => this._filter(value || '')),
            );
            this.myControl.setValue(this.value);
        }
    }

    private _filter(value: string): string[] {
        const filterValue = value.toLowerCase();
        return this.field['selection'].filter((field: string) => field.toLowerCase().includes(filterValue));
    }

    public updateValue(value: any) {
        console.log(value);
        this.valueChange.emit(value);
    }

    public onSelectDefault(value: any) {
        this.valueChange.emit(value);
    }
}
