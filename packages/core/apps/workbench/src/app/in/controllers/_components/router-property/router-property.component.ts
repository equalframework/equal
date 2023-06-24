import { Component, OnInit, Input } from '@angular/core';

@Component({
    selector: 'app-router-property',
    templateUrl: './router-property.component.html',
    styleUrls: ['./router-property.component.scss']
})
export class RouterPropertyComponent implements OnInit {

    @Input() selected_property: any;
    @Input() schema: any;
    @Input() eq_package: any;
    @Input() controller_name: any;
    @Input() selected_type_controller: any;

    constructor() { }

    ngOnInit(): void {
    }
}
