import { NgModule } from '@angular/core';
import { PreloadAllModules, RouterModule, Routes } from '@angular/router';
import { AppComponent } from './in/app.component';

const routes: Routes = [
    {
        path: 'models',
        loadChildren: () => import('./in/models/models.module').then(m => m.AppInModelsModule)
    },
    {
        path: 'controllers',
        loadChildren: () => import('./in/controllers/controllers.module').then(m => m.AppInControllersModule)
    },
    {
        path: 'routes',
        loadChildren: () => import('./in/routes/routes.module').then(m => m.AppInRoutesModule)
    },
    /**
    {
        path: 'views',
        loadChildren: () => import('./in/app.component').then(m => m.AppComponent)
    },
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
    }
];

@NgModule({
    imports: [
        RouterModule.forRoot(routes, { preloadingStrategy: PreloadAllModules, onSameUrlNavigation: 'reload', useHash: true })
    ],
    exports: [RouterModule]
})
export class AppRoutingModule { }
