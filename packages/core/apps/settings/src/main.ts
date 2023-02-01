import { enableProdMode } from '@angular/core';
import { platformBrowserDynamic } from '@angular/platform-browser-dynamic';

import { AppModule } from './app/app.module';
import { EnvService} from 'sb-shared-lib';

const env = new EnvService();

env.getEnv().then( (environment:any) => {
  if (environment.production) {
    enableProdMode();
  }
  platformBrowserDynamic().bootstrapModule(AppModule)
  .catch( (err:any) => console.error(err));
})
.catch( (err:any) => console.error(err));