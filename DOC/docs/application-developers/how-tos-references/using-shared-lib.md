### Using `shared-lib` in your app

#### Linking `sb-shared-lib` in the node_modules of your app

To use sb-shared-lib, you need to link the library to your project.

In the root of your project directory (regardless to your node version) :
```
npm link sb-shared-lib
```

#### Using `shared-lib` in Angular

When you need to use the library in a Angular module :
```ts
import { SharedLibModule } from 'sb-shared-lib';

@NgModule({
    declarations: [

    ],
    imports: [
        CommonModule,
        SharedLibModule,
    ],
    exports: [
    ],
    providers: [

    ],
})
export class MyAppModule { }
```

More examples of using the library can be found in these [examples](./shared-lib-code-snippets.md).

---