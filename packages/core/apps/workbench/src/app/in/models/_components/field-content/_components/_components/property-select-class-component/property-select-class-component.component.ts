import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { WorkbenchService } from 'src/app/in/models/_service/models.service';
import {FormControl} from '@angular/forms';
import { Observable } from 'rxjs';
import {map, startWith} from 'rxjs/operators';

@Component({
    selector: 'app-property-select-class-component',
    templateUrl: './property-select-class-component.component.html',
    styleUrls: ['./property-select-class-component.component.scss']
})
export class PropertySelectClassComponentComponent implements OnInit {

    @Input() value: any;
    @Input() name: any;
    @Input() description: any;
    @Output() valueChange = new EventEmitter<string>();
    private eq_class: any;
    public formatted_class: string[] = [];
    public myControl = new FormControl('');
    public filtered_formatted_class: Observable<string[]>
    private initialized = false;

    constructor(private api: WorkbenchService) { }

    async ngOnInit() {
        this.eq_class = await this.api.getClasses();
        this.formatClass(this.eq_class, '');
        this.filtered_formatted_class = this.myControl.valueChanges.pipe(
            startWith(''),
            map(value => this._filter(value || '')),
        );

        this.myControl.setValue(this.value);
    }

    public ngOnChanges() {
        this.myControl.setValue(this.value);
    }

    private formatClass(object: any, previous: string) {
        Object.keys(object).forEach(element => {
            object[element].forEach((item: string) => {
                this.formatted_class.push(previous + element + '\\' + item);
            });
        });
    }

    private _filter(value: string): string[] {
        const filterValue = value.toLowerCase();
        return this.formatted_class.filter((field: string) => field.toLowerCase().includes(filterValue));
    }

    public onSelectionChange(event: any) {
        this.valueChange.emit(event.option.value);
    }
}
