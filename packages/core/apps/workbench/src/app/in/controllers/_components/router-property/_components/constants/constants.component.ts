import { Component, OnInit, Input } from '@angular/core';

@Component({
   selector: 'app-constants',
   templateUrl: './constants.component.html',
   styleUrls: ['./constants.component.scss']
})
export class ConstantsComponent implements OnInit {

    @Input() value: any;

   constructor() { }

   ngOnInit(): void {
   }

}
