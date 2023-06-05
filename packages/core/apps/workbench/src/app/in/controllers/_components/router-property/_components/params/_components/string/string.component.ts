import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { FormControl } from '@angular/forms';
import { Observable } from 'rxjs';
import { map, startWith } from 'rxjs/operators';

@Component({
    selector: 'app-string',
    templateUrl: './string.component.html',
    styleUrls: ['./string.component.scss']
})
export class StringComponent implements OnInit {

    @Input() value: any;
    @Input() required: boolean;
    @Input() selection: any;
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

        if (this.selection) {
            if(!this.required && !this.selection.includes("")) {
                let tempValue = this.selection[0];
                this.selection[0] = "";
                this.selection.push(tempValue);
            }
            this.filteredSelection = this.myControl.valueChanges.pipe(
                startWith(''),
                map(value => this._filter(value || '')),
            );
            this.myControl.setValue(this.value);
        }
    }

    private _filter(value: string): string[] {
        const filterValue = value.toLowerCase();
        return this.selection.filter((value: string) => value.toLowerCase().includes(filterValue));
    }

    public updateValue(value: any) {
        if(value != '') {
            this.valueChange.emit(value);
        } else {
            this.valueChange.emit(undefined);
        }
    }

    public onSelectDefault(value: any) {
        if(value != '') {
            this.valueChange.emit(value);
        } else {
            this.valueChange.emit(undefined);
        }
    }
}
