import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { ControllersService } from 'src/app/in/controllers/_service/controllers.service';

@Component({
    selector: 'app-domain',
    templateUrl: './domain.component.html',
    styleUrls: ['./domain.component.scss']
})
export class DomainComponent implements OnInit {

    @Input() value: any;
    @Input() name: any;
    @Input() class: any;
    @Input() fixed: any;
    @Output() valueChange = new EventEmitter<[]>();
    public validOperators: any;
    public fields: any;
    tempValue: any;

    constructor(private api: ControllersService) {}

    async ngOnInit() {
        this.transformDomain();
        this.validOperators = await this.api.getValidOperators();
        this.getSchema();
    }

    async ngOnChanges() {
        this.getSchema();
        this.transformDomain();
    }

    async getSchema() {
        this.fields = await this.api.getSchema(this.class);
    }

    transformDomain() {
        if(this.value) {
            this.tempValue = [...this.value];
            // empty  domain : []
            if (this.tempValue.length == 0) {
                this.tempValue = [this.tempValue];
            }
            // 1 condition only : [ '{operand}', '{operator}', '{value}' ]
            else if (this.tempValue.length == 3 &&typeof this.tempValue[0] == 'string' &&typeof this.tempValue[1] == 'string') {
                this.tempValue = [[this.tempValue]];
            }
        }
    }

    public updateOperand(event: any, i: any, j: any) {
        this.tempValue[i][j][0] = event;
        this.tempValue[i][j][1] = '';
        this.tempValue[i][j][2] = this.defaultTypeValue(
        this.fields.fields[this.tempValue[i][j][0]].type);
    }

    public selectOperator(value: any, i: any, j: any) {
        this.tempValue[i][j][1] = value;
        if (value == 'in' || value == 'not in' ||!Array.isArray(this.tempValue[i][j][2])) {
            this.tempValue[i][j][2] = [];
        } else {
            this.tempValue[i][j][2] = this.defaultTypeValue(this.fields.fields[this.tempValue[i][j][0]].type);
        }
    }

    public changeValue(new_value: any, i: any, j: any) {
        if (this.tempValue[i][j][1] == 'in' || this.tempValue[i][j][1] == 'not in' || this.tempValue[i][j][1] == 'contains') {
            if (!Array.isArray(this.tempValue[i][j][2])) {
                this.tempValue[i][j][2] = [];
            }
            console.log(new_value);
            console.log(typeof new_value);
            const index = this.tempValue[i][j][2].indexOf(new_value);
            console.log(this.tempValue);
            console.log(index);
            if (index >= 0) {
                this.tempValue[i][j][2].splice(index, 1);
            } else {
                this.tempValue[i][j][2].push(new_value);
            }
        } else {
            this.tempValue[i][j][2] = new_value;
        }

        this.valueChange.emit(this.tempValue);
    }

    public addCondition(index: any) {
        this.tempValue[index].push(['', '', '']);
    }

    public addClause() {
        this.tempValue.push([['', '', '']]);
    }

    public removeCondition(i: any, j: any) {
        this.tempValue[i].splice(j, 1);
        this.valueChange.emit(this.tempValue);
    }

    public removeClause(i: any) {
        this.tempValue.splice(i, 1);
        this.valueChange.emit(this.tempValue);
    }

    private defaultTypeValue(type: any) {
        if (type == 'string' || type == 'text') {
            return '';
        } else if (
            type == 'integer' ||
            type == 'float' ||
            type == 'many2one' ||
            type == 'one2many' ||
            type == 'many2many'
        ) {
            return 0;
        } else if (type == 'array' || type == 'selection' || type == 'domain') {
            return [];
        } else {
            return '';
        }
    }

}
