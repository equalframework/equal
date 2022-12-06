import { Injectable } from '@angular/core';
import { MatSnackBar } from '@angular/material/snack-bar';
import { Observable, Subject } from 'rxjs';
import { ApiService } from 'sb-shared-lib';

@Injectable({
    providedIn: 'root'
})

export class SettingService {

    // booking object for conditionning API calls  
    public queue: any[] = [];

    constructor(
        private api: ApiService,
        private snackBar: MatSnackBar) { }

    public toQueue(idSetting: number, fieldsSetting: any): Observable<string> {
        
        let subject = new Subject<string>();
        //adding the elements to the queue
        this.queue.push({ id: idSetting, fields: fieldsSetting });
        let snackBarRef = this.snackBar.open('Changes saved', 'Undo', {
            duration: 3000,
            verticalPosition: 'bottom',
            horizontalPosition: 'start',
        });
        
        snackBarRef.onAction().subscribe( () => {
            // remove from the queue and send the old value back
            this.queue.shift();
            // send 'undo' to understand that it doesn't come from dismissed
            subject.next('undo');           
        })
       
        snackBarRef.afterDismissed().subscribe(() => {
            
        // if didn't do anything, sends the new value back
            if(this.queue.length > 0) {
                this.api.update('core\\setting\\SettingValue', [this.queue[0].id], { value: this.queue[0].fields.newValue }, true);
                subject.next(fieldsSetting.newValue);
            }
            this.queue.shift();
        });

        return subject.asObservable();
    }
}