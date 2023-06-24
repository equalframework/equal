import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { FormControl } from '@angular/forms';
import { Observable } from 'rxjs';
import { map, startWith } from 'rxjs/operators';

@Component({
    selector: 'app-auto-complete',
    templateUrl: './auto-complete.component.html',
    styleUrls: ['./auto-complete.component.scss']
})
export class AutoCompleteComponent implements OnInit {

    @Input() label: string;
    @Input() options: string[];
    @Input() value: any;
    @Output() updateValue = new EventEmitter<string>();

    control = new FormControl();
    filteredOptions: Observable<string[]>;

    constructor() { }

    ngOnInit(): void {
        this.filteredOptions = this.control.valueChanges.pipe(
        startWith(''),
        map(value => this.filter(value))
        );

        this.control.setValue(this.value);
    }

    private filter(value: string): string[] {
        const filterValue = value.toLowerCase();

        return Object.keys(this.options).filter(option => {
        return option.toLowerCase().includes(filterValue);
        });
    }

    public onSelectionChange(event: any) {
        this.updateValue.emit(event.option.value);
    }
}
