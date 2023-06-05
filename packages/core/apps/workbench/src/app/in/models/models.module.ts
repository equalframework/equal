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

import { SearchListComponent } from './_components/search-list/search-list.component';
import { DeleteConfirmationComponent } from './_components/search-list/_components/delete-confirmation/delete-confirmation.component';
import { FieldContentComponent } from './_components/field-content/field-content.component';
import { RouterPropertyComponent } from './_components/field-content/_components/router-property/router-property.component';
import { PropertyBooleanComponentComponent } from './_components/field-content/_components/_components/property-boolean-component/property-boolean-component.component';
import { PropertyStringComponentComponent } from './_components/field-content/_components/_components/property-string-component/property-string-component.component';
import { PropertySelectClassComponentComponent } from './_components/field-content/_components/_components/property-select-class-component/property-select-class-component.component';
import { PropertySelectFieldComponentComponent } from './_components/field-content/_components/_components/property-select-field-component/property-select-field-component.component';
import { PropertyIntegerComponentComponent } from './_components/field-content/_components/_components/property-integer-component/property-integer-component.component';
import { PropertyArrayComponentComponent } from './_components/field-content/_components/_components/property-array-component/property-array-component.component';
import { AdvanceComponentComponent } from './_components/field-content/_components/advance-component/advance-component.component';
import { PropertyDomainComponent } from './_components/field-content/_components/_components/property-domain-component/property-domain.component';
import { AutoCompleteComponent } from './_components/field-content/_components/_components/property-domain-component/_components/auto-complete/auto-complete.component';
import { ValueComponent } from './_components/field-content/_components/_components/property-domain-component/_components/value/value.component';
import { ValueSelectionComponent } from './_components/field-content/_components/_components/property-domain-component/_components/value-selection/value-selection.component';
import { UsageComponent } from './_components/field-content/_components/usage/usage.component';

import { SharedLibModule, CustomDateAdapter } from 'sb-shared-lib';
import { ModelsComponent } from './models.component';
import { ModelsRoutingModule } from './models-routing.module';

@NgModule({
    imports: [
        SharedLibModule,
        MatTableModule,
        MatStepperModule,
        MatTabsModule,
        FormsModule,
        MatFormFieldModule,
        MatInputModule,
        ModelsRoutingModule
    ],
    declarations: [
        ModelsComponent,
        SearchListComponent,
        DeleteConfirmationComponent,
        FieldContentComponent,
        RouterPropertyComponent,
        PropertyBooleanComponentComponent,
        PropertyStringComponentComponent,
        PropertySelectClassComponentComponent,
        PropertySelectFieldComponentComponent,
        PropertyIntegerComponentComponent,
        PropertyArrayComponentComponent,
        AdvanceComponentComponent,
        PropertyDomainComponent,
        AutoCompleteComponent,
        ValueComponent,
        ValueSelectionComponent,
        UsageComponent,
    ],
    providers: [
        DatePipe,
        { provide: DateAdapter, useClass: CustomDateAdapter, deps: [MAT_DATE_LOCALE, Platform] }
    ]
})
export class AppInModelsModule { }
