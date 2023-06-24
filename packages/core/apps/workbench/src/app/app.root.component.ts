import { Component, OnInit, ViewEncapsulation } from '@angular/core';
import {FormBuilder, Validators} from '@angular/forms';
import {STEPPER_GLOBAL_OPTIONS} from '@angular/cdk/stepper';
import { Router } from '@angular/router';

import { ContextService, ApiService, AuthService, EnvService } from 'sb-shared-lib';

import * as $ from 'jquery';
import { type } from 'jquery';


/*
This is the component that is bootstrapped by app.module.ts
*/

declare global {
    interface Window { context: any; }
}

@Component({
    selector: 'app-root',
    templateUrl: './app.root.component.html',
    styleUrls: ['./app.root.component.scss'],
    providers: [
      {
        provide: STEPPER_GLOBAL_OPTIONS,
        useValue: {showError: true},
      },
    ],
    encapsulation: ViewEncapsulation.None
})
export class AppRootComponent implements OnInit {

    public show_side_menu: boolean = false;
    private app_settings_root_package: string = '';
    public show_side_bar: boolean = false;

    // original (full & translated) menu for left pane
    public leftMenu: any = [];

    constructor(
        private router: Router,
        private context:ContextService,
        private api:ApiService,
        private auth:AuthService,
        private env:EnvService,
    ) {}


    public async ngOnInit() {

        try {
            await this.auth.authenticate();
        }
        catch(err) {
            window.location.href = '/auth';
            return;
        }

        // load menus from server
        this.env.getEnv().then( async (environment:any) => {
            this.app_settings_root_package = (environment.app_settings_root_package)?environment.app_settings_root_package:'core';
            const data = await this.api.getMenu(this.app_settings_root_package, 'workbench.left');
            this.leftMenu = this.translateMenu(data.items, data.translation);
        });
    }

    private translateMenu(menu:any, translation: any) {
        let result: any[] = [];
        for(let item of menu) {
            if(item.id && translation.hasOwnProperty(item.id)) {
                item.label = translation[item.id].label;
            }
            if(item.children && item.children.length) {
                this.translateMenu(item.children, translation);
            }
            result.push(item);
        }
        return result;
    }

    /**
     * Items are handled as descriptors.
     * They always have a `route` property (if not, it is added and set to '/').
     * And might have an additional `context` property.
     * @param item
     */
    public onSelectItem(item:any) {
        console.log('SettingsAppRoot::onSelectItem', item);
        this.router.navigateByUrl(item.id);
    }

    public toggleSideMenu() {
        this.show_side_menu = !this.show_side_menu;
    }

    public toggleSideBar() {
        this.show_side_bar = !this.show_side_bar;
    }
}