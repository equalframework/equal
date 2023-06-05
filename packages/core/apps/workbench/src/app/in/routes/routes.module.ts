import { NgModule } from '@angular/core';
import { DateAdapter, MAT_DATE_LOCALE } from '@angular/material/core';
import { Platform } from '@angular/cdk/platform';
import { DatePipe } from '@angular/common';

import { MatTableModule} from '@angular/material/table';
import { FormsModule } from '@angular/forms';
import { MatStepperModule } from '@angular/material/stepper';
import { MatTabsModule } from '@angular/material/tabs';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatExpansionModule } from '@angular/material/expansion';
import { SharedLibModule, CustomDateAdapter } from 'sb-shared-lib';
import { MatIconModule } from '@angular/material/icon';

import { RoutesComponent } from './routes.component';
import { RoutesRoutingModule } from './routes-routing.module';
import { ParamsComponent } from './_components/params/params.component';
import { ArrayComponent } from './_components/params/_components/array/array.component';
import { BooleanComponent } from './_components/params/_components/boolean/boolean.component';
import { DomainComponent } from './_components/params/_components/domain/domain.component';
import { StringComponent } from './_components/params/_components/string/string.component';
import { NumberComponent } from './_components/params/_components/number/number.component';
import { AutoCompleteComponent } from './_components/params/_components/domain/_components/auto-complete/auto-complete.component';
import { ValueComponent } from './_components/params/_components/domain/_components/value/value.component';
import { ValueSelectionComponent } from './_components/params/_components/domain/_components/value-selection/value-selection.component';
import { ResponseComponent } from './_components/params/_components/response/response.component'


@NgModule({
    imports: [
        SharedLibModule,
        MatTableModule,
        MatStepperModule,
        MatTabsModule,
        FormsModule,
        MatFormFieldModule,
        MatInputModule,
        RoutesRoutingModule,
        MatExpansionModule,
        MatIconModule
    ],
    declarations: [
        RoutesComponent,
        ParamsComponent,
        ArrayComponent,
        BooleanComponent,
        DomainComponent,
        StringComponent,
        NumberComponent,
        AutoCompleteComponent,
        ValueComponent,
        ValueSelectionComponent,
        ResponseComponent
    ],
    providers: [
        DatePipe,
        { provide: DateAdapter, useClass: CustomDateAdapter, deps: [MAT_DATE_LOCALE, Platform] }
    ]
})
export class AppInRoutesModule { }
