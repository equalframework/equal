
### Code Snippets

#### Creating Custom API Service Using `ApiService`

```ts
import { ApiService } from 'sb-shared-lib';

@Injectable({
    providedIn: 'root'
})
export class CustomApiService {
    public cached_schema: any;

    constructor(
        private api: ApiService,
    ) { }

    /**
     * Sample function that checks consistency of a package.
     *
     * @param pkg Package to check.
     *
     * @return List of messages about the consistency of the package.
     */
    public async getPackageConsistency(pkg: string): Promise<string[]> {
        let ret = [];
        try {
            ret = await this.api.fetch('?do=test_package-consistency&package=' + pkg);
        }
        catch (e: any) {
            // Create a snack with an error message depending on the context and the HttpError instance.
            this.api.errorFeedback(e);
        }
        return ret;
    }
}
```

#### Using `EnvService` to Locate eQual Endpoints

```ts
import { EnvService } from 'sb-shared-lib';

@Component({
    selector: 'app-controller-info',
    templateUrl: './controller-info.component.html',
    styleUrls: ['./controller-info.component.scss'],
    encapsulation: ViewEncapsulation.Emulated
})
export class ControllerInfoComponent implements OnInit {
    public backend_url: string = "";
    public rest_api_url: string = "";

    constructor(
        private env: EnvService
    ) { }

    async ngOnInit() {
        const env = await this.env.getEnv();
        this.backend_url = env["backend_url"];
        this.rest_api_url = env["rest_api_url"];
    }
}
```

---