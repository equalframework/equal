import { Component, OnInit, Inject } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';

@Component({
    selector: 'app-delete-confirmation',
    templateUrl: './delete-confirmation.component.html'
})
export class DeleteConfirmationComponent implements OnInit {

    constructor(
        @Inject(MAT_DIALOG_DATA) public data: any,
        public dialogRef: MatDialogRef<DeleteConfirmationComponent>
    ) {}

    onNoClick(): void {
        this.dialogRef.close();
    }

    ngOnInit(): void {
    }
}
