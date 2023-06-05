import { NgModule } from '@angular/core';
import { DateAdapter, MAT_DATE_LOCALE } from '@angular/material/core';
import { Platform } from '@angular/cdk/platform';
import { DatePipe } from '@angular/common';

import { SharedLibModule, CustomDateAdapter } from 'sb-shared-lib';
import { MatDialogModule } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatTableModule} from '@angular/material/table';
import { FormsModule } from '@angular/forms';
import { MatStepperModule } from '@angular/material/stepper';
import { MatTabsModule } from '@angular/material/tabs';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatIconModule } from '@angular/material/icon';

import { ControllersComponent } from './controllers.component';
import { ControllersRoutingModule } from './controllers-routing.module';
import { SearchListComponent } from './_components/search-list/search-list.component';
import { DeleteConfirmationComponent } from './_components/search-list-controller/delete-confirmation/delete-confirmation.component';
import { SearchListControllerComponent } from './_components/search-list-controller/search-list-controller.component';
import { RouterPropertyComponent } from './_components/router-property/router-property.component';
import { DescriptionComponent } from './_components/router-property/_components/description/description.component';
import { ParamsComponent } from './_components/router-property/_components/params/params.component';
import { ResponseComponent } from './_components/router-property/_components/response/response.component';
import { ConstantsComponent } from './_components/router-property/_components/constants/constants.component';
import { AccessComponent } from './_components/router-property/_components/access/access.component';
import { ArrayComponent } from './_components/router-property/_components/params/_components/array/array.component';
import { BooleanComponent } from './_components/router-property/_components/params/_components/boolean/boolean.component';
import { DomainComponent } from './_components/router-property/_components/params/_components/domain/domain.component';
import { StringComponent } from './_components/router-property/_components/params/_components/string/string.component';
import { NumberComponent } from './_components/router-property/_components/params/_components/number/number.component';
import { AutoCompleteComponent } from './_components/router-property/_components/params/_components/domain/_components/auto-complete/auto-complete.component';
import { ValueComponent } from './_components/router-property/_components/params/_components/domain/_components/value/value.component';
import { ValueSelectionComponent } from './_components/router-property/_components/params/_components/domain/_components/value-selection/value-selection.component';
import { ResponseComponentSubmit } from './_components/router-property/_components/params/_components/response/response.component'

@NgModule({
    imports: [
        SharedLibModule,
        ControllersRoutingModule,
        MatDialogModule,
        MatButtonModule,
        MatTableModule,
        MatStepperModule,
        MatTabsModule,
        FormsModule,
        MatFormFieldModule,
        MatInputModule,
        MatIconModule,
    ],
    declarations: [
        ControllersComponent,
        SearchListComponent,
        DeleteConfirmationComponent,
        SearchListControllerComponent,
        RouterPropertyComponent,
        DescriptionComponent,
        ParamsComponent,
        ResponseComponent,
        ConstantsComponent,
        AccessComponent,
        ArrayComponent,
        BooleanComponent,
        DomainComponent,
        StringComponent,
        NumberComponent,
        AutoCompleteComponent,
        ValueComponent,
        ValueSelectionComponent,
        ResponseComponentSubmit
    ],
    providers: [
        DatePipe,
        { provide: DateAdapter, useClass: CustomDateAdapter, deps: [MAT_DATE_LOCALE, Platform] }
    ]
})
export class AppInControllersModule { }
