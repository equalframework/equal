import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { FormControl } from '@angular/forms';

@Component({
  selector: 'app-value-selection',
  templateUrl: './value-selection.component.html',
  styleUrls: ['./value-selection.component.scss']
})
export class ValueSelectionComponent implements OnInit {

    @Input() operand: any;
    @Input() type: any;
    @Input() value: any;
    @Input() operator: any;
    @Input() fields: any;
    @Output() changeValue = new EventEmitter<any>();

    public myControl = new FormControl('');

    constructor() { }

    ngOnInit(): void {
        this.myControl.setValue(this.value[0]);
    }

    ngOnChanges() {
        this.myControl.setValue(this.value[0]);
    }

    public containsOption(option: any) {
        return this.value.includes(option);
    }

    public changeOption(option: any) {
        console.log(option);
        this.changeValue.emit(option);
    }

    public onOptionChange(event: any) {
        console.log(event.value);
        this.changeValue.emit(event.value);
    }

}
