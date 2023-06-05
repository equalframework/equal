import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { ControllersComponent } from './controllers.component';

const routes: Routes = [
    // wildcard route (accept root and any sub route that does not match any of the routes above)
    {
        path: '**',
        component: ControllersComponent
    }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class ControllersRoutingModule {}
