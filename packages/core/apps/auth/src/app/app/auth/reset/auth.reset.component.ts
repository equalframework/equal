import { Component, OnInit, Input  } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

import { AuthService, ApiService } from 'sb-shared-lib';



@Component({
    selector: 'auth-reset',
    templateUrl: 'auth.reset.component.html',
    styleUrls: ['auth.reset.component.scss']
})
export class AuthResetComponent implements OnInit {
    public form: FormGroup;
    public loading:boolean = false;
    public submitted:boolean = false;
    public hidepass:boolean = true;
    public hideconfirm:boolean = true;
    public signin_error:boolean = false;
    public server_error:boolean = false;

    private token: string = '';

    constructor(
        private formBuilder: FormBuilder,
        private auth: AuthService,
        private api: ApiService,
        private router: Router,
        private route: ActivatedRoute
    ) {
        this.form = new FormGroup({});
    }

    // convenience getter for easy access to form fields
    public get f() {
        return this.form.controls;
    }

    public async ngOnInit() {

        // fetch token from route
        this.route.params.subscribe( async (params) => {
            if(params && params.hasOwnProperty('token')) {
            this.token = params['token'];
            }
        });

        // setup the form
        this.form = <FormGroup>this.formBuilder.group({
            password: ['', Validators.required, Validators.pattern('^.{8,}$')],
            confirm: ['', Validators.required]
        });

        this.form.get('password').valueChanges.subscribe( () => {
            this.submitted = false;
            this.f.password.setErrors(null);
        });

        this.form.get('confirm').valueChanges.subscribe( () => {
            this.submitted = false;
            this.f.confirm.setErrors(null);
        });
    }

    public async onSignin() {
        this.router.navigate(['/signin']);
    }

    public async onSubmit() {
        let error = false;

        if(this.f.password.value != this.f.confirm.value) {
            error = true;
            this.f.confirm.setErrors({'incorrect': true});
        }

        // prevent sumit for invalid form
        if (error || this.form.invalid) {
            return;
        }
        this.signin_error = false;
        this.server_error = false;
        this.submitted = true;
        this.loading = true;

        try {
            const data = await this.api.fetch('/?do=user_pass-update', {token: this.token, password: this.f.password.value, confirm: this.f.confirm.value});
            // success: we should be able to authenticate
            this.auth.authenticate();
            // AppRootComponent should now redirect to /apps
        }
        catch(response:any) {
            console.log(response);

            try {
                if(response.hasOwnProperty('status')) {
                    if(response.status == 0) {
                        throw {
                            code: 'server_error',
                            message: 'Server error'
                        };
                    }
                    if(response.hasOwnProperty('error') && response.error.hasOwnProperty('errors')) {
                        let code = Object.keys(response.error['errors'])[0];
                        let msg = response.error['errors'][code];
                        throw {
                            code: code,
                            message: msg
                        };
                    }
                }
                else {
                    throw {
                        code: 'server_error',
                        message: 'Server error'
                    };
                }
            }
            catch(exception:any) {
                if(exception.code == 'server_error') {
                    this.server_error = true;
                }
                else {
                    this.signin_error = true;
                }
            }
            // there was an error: stop loading indicator
            this.loading = false;
        }
    }

}