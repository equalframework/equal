import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core'
import { WorkbenchService } from 'src/app/in/models/_service/models.service';
import { MatFormFieldModule } from '@angular/material/form-field';
import { FormControl } from '@angular/forms';
import { Observable } from 'rxjs';
import { map, startWith } from 'rxjs/operators';

@Component({
    selector: 'app-property-select-field-component',
    templateUrl: './property-select-field-component.component.html',
    styleUrls: ['./property-select-field-component.component.scss']
})
export class PropertySelectFieldComponentComponent implements OnInit {

    @Input() value: any;
    @Input() name: any;
    @Input() description: any;
    @Input() field: any;
    @Output() valueChange = new EventEmitter<string>();
    private fields: any;
    public myControl = new FormControl('');
    public filteredFields: Observable<string[]>

    constructor(private api: WorkbenchService) { }

    async ngOnInit() {
        await this.initialization();
    }

    public async ngOnChanges() {
        await this.initialization();
    }

    private async initialization() {
        if (this.field['foreign_object']) {
            this.fields = await this.api.getSchema(this.field['foreign_object']);
            this.fields = Object.keys(this.fields['fields']);
            this.filteredFields = this.myControl.valueChanges.pipe(
                startWith(''),
                map(value => this._filter(value || '')),
            );
            this.myControl.setValue(this.value);
        }
    }

    private _filter(value: string): string[] {
        const filterValue = value.toLowerCase();
        return this.fields.filter((field: string) => field.toLowerCase().includes(filterValue));
    }

    public onSelectionChange(value: any) {
        this.valueChange.emit(value);
    }
}
