import { Component, OnInit, Input, Output, EventEmitter, ElementRef   } from '@angular/core';
import {FormControl} from '@angular/forms';

@Component({
  selector: 'app-value',
  templateUrl: './value.component.html',
  styleUrls: ['./value.component.scss']
})
export class ValueComponent implements OnInit {

    @Input() value: any;
    @Input() type: any;
    @Input() operator: any;
    @Output() changeValue = new EventEmitter<any>();
    public showOverlay = false;
    public myControl = new FormControl('');

    constructor(private elementRef : ElementRef) { }

    ngOnInit(): void {
        if(this.operator == 'in' || this.operator == 'not in' || this.operator == 'contains') {
            document.addEventListener('click', (event) => {
                const target = event.target as HTMLElement;
                if (!this.elementRef.nativeElement.contains(target) || target === this.elementRef.nativeElement.querySelector('#input')) {
                    console.log('test');
                    this.showOverlay = false;
                }
            });
        }
    }

    async ngOnChanges() {
        if(this.operator == 'in' || this.operator == 'not in') {
        document.addEventListener('click', (event) => {
            const target = event.target as HTMLElement;
            if (!this.elementRef.nativeElement.contains(target) || target === this.elementRef.nativeElement.querySelector('#input')) {
                this.showOverlay = false;
            }
        });
        }
    }

    public updateValueInteger(event: any) {
        this.changeValue.emit(+ event.target.value);
    }

    public updateValueString(event: any) {
        this.changeValue.emit(event.target.value)
    }

    public onChangeBoolean(event: any) {
        this.changeValue.emit(event.checked);
    }

    public addValueInteger(element: any) {
        this.changeValue.emit(+ element);
    }

    public addValueString(element: any) {
        this.changeValue.emit(element);
    }

    public removeValueInteger(element: any) {
        this.changeValue.emit(+ element);
    }

    public removeValueString(element: any) {
        this.changeValue.emit(element);
    }

}
