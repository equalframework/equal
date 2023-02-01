import { Component, OnInit } from '@angular/core';

import { ApiService, AuthService } from 'sb-shared-lib';


@Component({
    selector: 'apps',
    templateUrl: 'apps.component.html',
    styleUrls: ['apps.component.scss']
})
export class AppsComponent implements OnInit {

    public user: any = {};
    public user_apps: string[];

    public array: any;
    public apps: any;

    constructor(
        private auth: AuthService,
        private api: ApiService
        ) {
    }


    public async ngOnInit() {
        try {
            this.apps = await this.api.fetch("/?get=installed-apps");
        }
        catch (response){
            console.log(response);
        }
        this.auth.getObservable().subscribe( (user:any) => {
            this.user_apps = [];
            this.user = user;
        });
    }

    public getApps() {
        if(this.apps) {
            return Object?.keys(this.apps);
        }
        else {
            return [];
        }
    }

    public isGranted(app_name:string) {
        let app = this.apps[app_name];
        if(app.access?.groups && app.show_in_apps) {
            for(let group of app.access.groups) {
                if(this.auth.hasGroup(group)) {
                    return true;
                }
            }
        }
        return false;
    }

    public onSelect(app: any) {
        window.location.href = this.apps[app].url;
    }

    public async onclickDisconnect() {
        try {
            await this.auth.signOut();
            setTimeout( () => {
                window.location.href = '/auth';
            }, 500);
        }
        catch(err) {
            console.warn('unable to request signout');
        }
    }

}