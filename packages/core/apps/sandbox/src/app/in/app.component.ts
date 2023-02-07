import { Component, OnInit  } from '@angular/core';
import { ContextService } from 'sb-shared-lib';

@Component({
    selector: 'app',
    templateUrl: 'app.component.html',
    styleUrls: ['app.component.scss']
})
export class AppComponent implements OnInit  {

    public ready: boolean = false;

    constructor(
        private context: ContextService
    ) {}

    public ngOnInit() {
        this.context.ready.subscribe( (ready:boolean) => {
            this.ready = ready;
        });
    }

    public ngAfterViewInit() {
        console.log('AppComponent::ngAfterViewInit');
    }

}