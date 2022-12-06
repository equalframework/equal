import { NgModule, LOCALE_ID } from '@angular/core';

import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';

import { MatNativeDateModule, DateAdapter, MAT_DATE_LOCALE } from '@angular/material/core';
import { Platform, PlatformModule } from '@angular/cdk/platform';


import { AppsComponent } from './apps.component';
import { AppRootComponent } from './app.root.component';

import { SharedLibModule, AuthInterceptorService } from 'sb-shared-lib';




/* HTTP requests interception dependencies */
import { HTTP_INTERCEPTORS } from '@angular/common/http';



@NgModule({
  declarations: [
    AppRootComponent, AppsComponent
  ],
  imports: [
    BrowserModule,
    BrowserAnimationsModule,
    SharedLibModule,
    MatNativeDateModule,
    PlatformModule
  ],
  providers: [
    // add HTTP interceptor to inject AUTH header to any outgoing request
    { provide: HTTP_INTERCEPTORS, useClass: AuthInterceptorService, multi: true }
  ],
  bootstrap: [AppRootComponent]
})
export class AppModule { }
