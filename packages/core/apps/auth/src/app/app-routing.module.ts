import { NgModule } from '@angular/core';
import { PreloadAllModules, RouterModule, Routes } from '@angular/router';


import { AuthSigninComponent } from './app/auth/signin/auth.signin.component';
import { AuthRecoverComponent } from './app/auth/recover/auth.recover.component';
import { AuthResetComponent } from './app/auth/reset/auth.reset.component';


const routes: Routes = [
  /* routes specific to current app */
  {
    path: 'signin',
    component: AuthSigninComponent
  },
  {
    path: 'recover',
    component: AuthRecoverComponent
  },
  {
    path: 'reset/:token',
    component: AuthResetComponent
  }
];

@NgModule({
  imports: [
    RouterModule.forRoot(routes, { preloadingStrategy: PreloadAllModules, onSameUrlNavigation: 'reload', useHash: true})
  ],
  exports: [RouterModule]
})
export class AppRoutingModule { }
