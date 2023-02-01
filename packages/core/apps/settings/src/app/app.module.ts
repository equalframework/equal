import { NgModule, LOCALE_ID } from '@angular/core';

import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';

import { DateAdapter, MatNativeDateModule, MAT_DATE_LOCALE } from '@angular/material/core';
import { Platform, PlatformModule } from '@angular/cdk/platform';

import { SharedLibModule, AuthInterceptorService, CustomDateAdapter } from 'sb-shared-lib';
import { NgxMaterialTimepickerModule } from 'ngx-material-timepicker';

import { AppRoutingModule } from './app-routing.module';
import { AppRootComponent } from './app.root.component';
import { AppComponent } from './in/app.component';

import { MatTableModule} from '@angular/material/table'
/* HTTP requests interception dependencies */
import { HTTP_INTERCEPTORS } from '@angular/common/http';

import { registerLocaleData } from '@angular/common';
import localeFr from '@angular/common/locales/fr';
import { MAT_SNACK_BAR_DEFAULT_OPTIONS } from '@angular/material/snack-bar';
import { SettingsComponent } from './in/settings/settings.component';
import { WidgetToggleComponent } from './in/settings/sections/widget-toggle/widget-toggle.component';
import { WidgetSelectComponent } from './in/settings/sections/widget-select/widget-select.component';
import { WidgetInputComponent } from './in/settings/sections/widget-input/widget-input.component';
import { PermissionsComponent } from './in/settings/permissions/permissions.component';
import { PermissionRightsComponent } from './in/settings/permissions/component/permission-rights/permission-rights.component';
import { PermissionClassNameComponent } from './in/settings/permissions/component/permission-class-name/permission-class-name.component';



registerLocaleData(localeFr);

@NgModule({
  declarations: [    
    AppRootComponent,
    AppComponent,
    SettingsComponent,
    WidgetToggleComponent,
    WidgetSelectComponent,
    WidgetInputComponent,
    PermissionsComponent,
    PermissionRightsComponent,
    PermissionClassNameComponent    
  ],
  imports: [
    AppRoutingModule,
    BrowserModule,
    BrowserAnimationsModule,
    SharedLibModule,
    MatNativeDateModule,
    PlatformModule,
    NgxMaterialTimepickerModule.setLocale('fr-BE'),
    MatTableModule
  ],
  providers: [
    // add HTTP interceptor to inject AUTH header to any outgoing request
    { provide: HTTP_INTERCEPTORS, useClass: AuthInterceptorService, multi: true },
    { provide: MAT_SNACK_BAR_DEFAULT_OPTIONS, useValue: { duration: 4000, horizontalPosition: 'start' } },    
    { provide: MAT_DATE_LOCALE, useValue: 'fr-BE' },
    { provide: LOCALE_ID, useValue: 'fr-BE' },
    { provide: DateAdapter, useClass: CustomDateAdapter, deps: [MAT_DATE_LOCALE, Platform] }
  ],
  bootstrap: [AppRootComponent]
})
export class AppModule { }
