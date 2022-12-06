import { NgModule } from '@angular/core';
import { PreloadAllModules, RouterModule, Routes } from '@angular/router';
import { AppComponent } from './in/app.component';

import { SettingsComponent } from './in/settings/settings.component';

import { PermissionsComponent } from './in/settings/permissions/permissions.component';

const routes: Routes = [
  /* routes specific to current app */
  {
    /*
     default route, for bootstrapping the App
      1) display a loader and try to authentify
      2) store user details (roles and permissions)
      3) redirect to applicable page (/apps or /auth)
     */
    path: '',
    component: AppComponent
  },
  {
    path: 'settings/permissions/edit/:id',
    component: PermissionsComponent
  },
  {
    path: 'settings/:package',
    component: SettingsComponent
  }
];

@NgModule({
  imports: [
    RouterModule.forRoot(routes, { preloadingStrategy: PreloadAllModules, onSameUrlNavigation: 'reload', useHash: true })    
  ],
  exports: [RouterModule]
})
export class AppRoutingModule { }
