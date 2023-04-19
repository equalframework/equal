import { NgModule, LOCALE_ID } from '@angular/core';

import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';

import { MatNativeDateModule, DateAdapter, MAT_DATE_LOCALE } from '@angular/material/core';
import { Platform, PlatformModule } from '@angular/cdk/platform';

import { SharedLibModule, AuthInterceptorService } from 'sb-shared-lib';

import { AppRoutingModule } from './app-routing.module';
import { AppRootComponent } from './app.root.component';

/* HTTP requests interception dependencies */
import { HTTP_INTERCEPTORS } from '@angular/common/http';


import { AuthRecoverComponent } from './app/auth/recover/auth.recover.component';
import { AuthSigninComponent } from './app/auth/signin/auth.signin.component';
import { AuthResetComponent } from './app/auth/reset/auth.reset.component';
import { AuthSignupComponent } from './app/auth/signup/auth.signup.component';
import { AuthSignupSentComponent } from './app/auth/signup/sent/auth.signup.sent.component';

@NgModule({
    declarations: [
        AppRootComponent, AuthRecoverComponent, AuthSigninComponent, AuthResetComponent, AuthSignupComponent, AuthSignupSentComponent
    ],
    imports: [
        AppRoutingModule,
        BrowserModule,
        BrowserAnimationsModule,
        SharedLibModule,
        MatNativeDateModule,
        PlatformModule,
    ],
    providers: [
        // add HTTP interceptor to inject AUTH header to any outgoing request
        { provide: HTTP_INTERCEPTORS, useClass: AuthInterceptorService, multi: true },
    ],
    bootstrap: [AppRootComponent]
})
export class AppModule { }