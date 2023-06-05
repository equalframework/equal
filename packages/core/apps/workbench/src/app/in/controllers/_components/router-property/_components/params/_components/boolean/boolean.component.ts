import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';

@Component({
    selector: 'app-boolean',
    templateUrl: './boolean.component.html',
    styleUrls: ['./boolean.component.scss']
})
export class BooleanComponent implements OnInit {

    @Input() value: any;
    @Input() required: boolean;
    @Output() valueChange = new EventEmitter<boolean>();
    public copyValue: boolean;
    public isDesired = false;

    constructor() { }

    ngOnInit(): void {
        this.initialization();
    }

    public ngOnChanges() {
        this.initialization();
    }

    private initialization() {
        if (this.value) {
            this.copyValue = true;
            this.isDesired = true;
        } else {
            this.copyValue = false;
            if(this.value != undefined) {
                this.isDesired = true;
            } else {
                this.isDesired = false;
            }
        }
    }

    public onChange(event: any) {
        this.valueChange.emit(event.checked);
    }

    public changeDesired() {
        if(this.isDesired) {
            this.valueChange.emit(undefined);
        } else {
            this.valueChange.emit(false);
        }
    }
}
