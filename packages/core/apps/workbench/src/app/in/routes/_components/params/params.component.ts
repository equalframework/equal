import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { RoutesService } from 'src/app/in/routes/_service/routes.service';
import {MAT_DIALOG_SCROLL_STRATEGY_FACTORY, MatDialog, MatDialogConfig } from '@angular/material/dialog';
import { ResponseComponent } from './_components/response/response.component';
import { Clipboard } from '@angular/cdk/clipboard';
import { MatSnackBar } from '@angular/material/snack-bar';
import { isArray, isObject, isString } from 'lodash';

@Component({
    selector: 'app-params',
    templateUrl: './params.component.html',
    styleUrls: ['./params.component.scss']
})
export class ParamsComponent implements OnInit {

    @Input() values: any;
    @Input() operation: any;
    @Input() fixed_params: any;
    public paramsValue: any;
    public presentRequiredParams: any;
    public canSubmit = false;

    constructor(
        private api: RoutesService,
        public dialog: MatDialog,
        private clipboard: Clipboard,
        private snackBar: MatSnackBar
        ) { }

    ngOnInit(): void {
        this.initialization();
    }

    public ngOnChanges() {
        this.initialization();
    }

    public initialization() {
        this.fixed_params ? this.paramsValue = {... this.fixed_params} : this.paramsValue = {};
        this.presentRequiredParams = {};
        for(let key in this.values) {
            if(this.values[key]['default']) {
                this.paramsValue[key] = this.values[key]['default'];
                if(this.values[key]['required']) {
                    this.presentRequiredParams[key] = true;
                }
            } else if(this.values[key]['type'] == 'boolean' && this.values[key]['required']) {
                this.paramsValue[key] = false;
                this.presentRequiredParams[key] = true;
            } else {
                if(this.values[key]['required']) {
                    this.presentRequiredParams[key] = false;
                }

                if(this.fixed_params[key]) {
                    this.presentRequiredParams[key] = true;
                }
            }
        }
        this.updateCanSubmit();
    }

    public getType(value: any) {
        return this.values[value].type;
    }

    public getUsage(value: any) {
        return this.values[value].usage;
    }

    public getDefault(value: any) {
        return this.values[value].default;
    }

    public getDescription(key: any) {
        return this.values[key].description;
    }

    public getSelection(key: any) {
        return this.values[key].selection;
    }

    public updateParamsValue(new_value: any, params_name: any) {
        if(new_value == undefined) {
            delete this.paramsValue[params_name];
            if(this.values[params_name]['required']) {
                this.presentRequiredParams[params_name] = false;
            }
        } else {
            this.paramsValue[params_name] = new_value;
            if(this.values[params_name]['required']) {
                this.presentRequiredParams[params_name] = true;
            }
        }

        this.updateCanSubmit();
    }

    public updateCanSubmit() {
        if(Object.keys(this.presentRequiredParams).length == 0) {
            this.canSubmit = true;
        } else {
            if(Object.values(this.presentRequiredParams).includes(false)) {
                this.canSubmit = false;
            } else {
                this.canSubmit = true;
            }
        }
    }

    public getParamsValue(key: any) {
        return this.paramsValue[key];
    }

    public async submit() {
        console.log(this.paramsValue);
        let response = await this.api.submitController(this.operation, this.paramsValue);
        const dialogConfig = new MatDialogConfig();
        dialogConfig.height = '86vh';
        dialogConfig.width = '70vw';
        dialogConfig.closeOnNavigation = true;
        dialogConfig.position = {
            'top': '12vh'
        };
        const dialogRef = this.dialog.open(ResponseComponent, dialogConfig);
        dialogRef.componentInstance.data = response;
    }

    public getJSON() {
        let success = this.clipboard.copy(JSON.stringify(this.paramsValue));
        this.successCopyClipboard(success);
    }

    public getCLI() {
        let controller_name = this.operation.slice(1).split("&")[0];
        let stringParams = './equal.run --' + controller_name;
        for(let key in this.paramsValue) {
            if(isArray(this.paramsValue[key])) {
                let arrayString = (JSON.stringify(this.paramsValue[key])).replaceAll('"', '');
                console.log(arrayString)
                stringParams += ' --' + key + '=\'' + arrayString + '\'';
            } else if(isObject(this.paramsValue[key])) {
                stringParams += ' --' + key + '=\'{' + this.paramsValue[key] + '}\'';
            } else if(isString(this.paramsValue[key])) {
                stringParams += ' --' + key + '=' + (this.paramsValue[key].replaceAll('\\', '\\\\'));
            } else {
                stringParams += ' --' + key + '=' + this.paramsValue[key];
            }
        }
        let success = this.clipboard.copy(stringParams);
        this.successCopyClipboard(success);
    }

    public successCopyClipboard(success: boolean) {
        if(success) {
            this.snackBar.open('Successfully copy', '', {
                duration: 1000,
                horizontalPosition: 'center',
                verticalPosition: 'bottom'
            });
        } else {
            this.snackBar.open('Failed to copy to clipboard', '', {
                duration: 1000,
                horizontalPosition: 'center',
                verticalPosition: 'bottom'
            });
        }
        return JSON.stringify(this.paramsValue);
    }

    public isRequired(key: any) {
        return this.values[key].required ? true: false;
    }

    public getEntity() {
        return this.paramsValue['entity'];
    }

    public isFixed(key: any) {
        return this.fixed_params[key] != undefined;
    }
}
