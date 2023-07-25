import { NgModule } from '@angular/core';
import { PreloadAllModules, RouterModule, Routes } from '@angular/router';
import { AppComponent } from './in/app.component';


const routes: Routes = [
  /* routes specific to current app */
  {
    /*
     default route, for bootstrapping the App
      1) display a loader and try to authenticate
      2) store user details (roles and permissions)
      3) redirect to applicable page (/apps or /auth)
     */
    path: '',
    component: AppComponent
  },
  {
    path: ':package/:app',
    component: AppComponent
  },
  {
    path: ':package',
    component: AppComponent
  }
];

@NgModule({
    imports: [
        RouterModule.forRoot(routes, { preloadingStrategy: PreloadAllModules, onSameUrlNavigation: 'reload', useHash: true })    
    ],
    exports: [RouterModule]
})
export class AppRoutingModule { }
