import { Component, OnInit, Inject, ViewEncapsulation } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { prettyPrintJson } from 'pretty-print-json';

@Component({
    selector: 'app-response',
    templateUrl: './response.component.html',
    styleUrls: ['./response.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class ResponseComponentSubmit implements OnInit {

    ngOnInit(): void {
    }

    constructor(
        @Inject(MAT_DIALOG_DATA) public data: any,
        public dialogRef: MatDialogRef<ResponseComponentSubmit>
    ) {}

    onNoClick(): void {
      this.dialogRef.close();
    }

    /**
     *
     * @returns a pretty HTML string of a schema in JSON.
     */
    public getJSONSchema() {
        if(this.data) {
            return this.prettyPrint(this.data);
        }
        return null;
    }

    /**
     * Function to pretty-print JSON objects as an HTML string
     *
     * @param input a JSON
     * @returns an HTML string
     */
    private prettyPrint(input: any) {
        return prettyPrintJson.toHtml(input);
    }
}
