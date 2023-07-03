import { Component, OnInit } from '@angular/core';
import { ApiService, EnvService } from 'sb-shared-lib';
import { ActivatedRoute, Router, NavigationEnd } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';

@Component({
    selector: 'app-settings',
    templateUrl: './settings.component.html',
    styleUrls: ['./settings.component.scss']
})
export class SettingsComponent implements OnInit {

    constructor(
        private api: ApiService,
        private env: EnvService,
        private route: ActivatedRoute,
        private translate:TranslateService,
        private router: Router
    ) { }

    public current_package:string
    public package: string = 'core';

    // data sorted by sections
    public sections: Array<any> = new Array();

    public sectionsMap: any = {};

    ngOnInit() {
        // Gets the right DATA for the right ROUTE PARAM
        this.route.params.subscribe(async params => {
                if(params.hasOwnProperty('package')) {
                    this.package = params.package;
                    const package_id = 'SETTINGS_LIST_' + this.package.toUpperCase();
                    const translation = this.translate.instant(package_id);
                    this.current_package = (package_id != translation)?translation:this.package;
                    this.reset();
                }
            });

        // Allow to switch the data (with initialize) when the route parameter changes
        this.router.events.subscribe( async (val) => {
                if (val instanceof NavigationEnd) {
                    this.reset().then( () => '' );
                }
            });
    }

    /**
     * Initialize the payload based on current route
     *
     */
    public async reset() {
        try {
            const environment:any = await this.env.getEnv();
            const data: any[] = await this.api.collect(
                    'core\\setting\\Setting',
                    ['package', '=', this.package],
                    ['package', 'section_id.name', 'section_id.code', 'section_id.description', 'description', 'setting_values_ids.value', 'name', 'code', 'type', 'setting_choices_ids.value', 'title', 'help', 'form_control'],
                    'id', 'asc', 0, 100,
                    environment.locale
                );

            // reset the array
            this.sections = [];
            this.sectionsMap = {};

            // group elements by section
            data.forEach(element => {
                if(!this.sectionsMap.hasOwnProperty(element.section_id.code)) {
                    this.sectionsMap[element.section_id.code] = [];
                    this.sections.push(element.section_id);
                }
                this.sectionsMap[element.section_id.code].push(element);
            });
        }
        catch(error) {
            console.log('something went wrong', error);
        }
    }
}