import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';

@Injectable({
    providedIn: 'root'
})
export class AppStateService {

    private pathParamState = new BehaviorSubject<any>({});

    public pathParam: Observable<string>;

    constructor() {
        this.pathParam = this.pathParamState.asObservable();
    }

    public updateParamState(newPathParam: any) {
        this.pathParamState.next(newPathParam);
    }

}
