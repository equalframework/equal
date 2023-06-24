import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import {FormControl} from '@angular/forms';

@Component({
    selector: 'app-array',
    templateUrl: './array.component.html',
    styleUrls: ['./array.component.scss']
})
export class ArrayComponent implements OnInit {

    @Input() value: any;
    @Output() valueChange = new EventEmitter<[]>();
    public tempValue: string[] = [];
    public inputValue = '';
    public myControl: FormControl = new FormControl('');;
    private initialized = false;

    constructor( ) { }

    ngOnInit(): void {
        this.value ? this.tempValue = [...this.value]: this.tempValue = [];
        this.initialized = true;
    }

    public ngOnChanges() {
        if(this.initialized) {
            this.value ? this.tempValue = [...this.value]: this.tempValue = [];
        }
    }

    public updateValue() {
        this.valueChange.emit(<any> [...this.tempValue]);
    }

    public addValue(value: string) {
        if (value && value.trim() !== '') {
            this.tempValue.push(value.trim());
            this.myControl.setValue('');
        }

        this.updateValue();
    }

    public removeValue(element: string) {
        const index = this.tempValue.indexOf(element);
        if (index >= 0) {
            this.tempValue.splice(index, 1);
        }

        this.updateValue();
    }
}
