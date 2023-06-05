import { Component, OnInit, Input, Output, EventEmitter, ViewEncapsulation  } from '@angular/core';

@Component({
    selector: 'app-property-boolean-component',
    templateUrl: './property-boolean-component.component.html',
    styleUrls: ['./property-boolean-component.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class PropertyBooleanComponentComponent implements OnInit {

    @Input() value: any;
    @Input() name: any;
    @Input() description: any;
    @Output() valueChange = new EventEmitter<boolean>();
    copyValue: boolean;
    initialized: boolean = false;

    constructor() { }

    ngOnInit(): void {
        this.value ? this.value = true : this.value = false;
        this.initialized = true;
    }

    public ngOnChanges() {
        this.value ? this.value = true : this.value = false;
    }

    public onChange(event: any) {
        if(event.checked == true){
            this.valueChange.emit(event.checked);
        } else {
            this.valueChange.emit(undefined);
        }
    }
}
