import { Component, OnInit, Input  } from '@angular/core';
import { Router } from '@angular/router';
import { ValidatorFn, FormBuilder, FormGroup, Validators, AbstractControl, ValidationErrors, AbstractControlOptions } from '@angular/forms';

import { AuthService } from 'sb-shared-lib';



@Component({
    selector: 'auth-signup',
    templateUrl: 'auth.signup.component.html',
    styleUrls: ['auth.signup.component.scss']
})
export class AuthSignupComponent implements OnInit {
    public form: FormGroup;
    public loading:boolean = false;
    public submitted:boolean = false;

    public hide_password:boolean = true;
    public hide_confirm:boolean = true;

    public signup_error:boolean = false;
    public server_error:boolean = false;

    constructor(
        private formBuilder: FormBuilder,
        private auth: AuthService,
        private router: Router
    ) {
        this.form = new FormGroup({});
    }

    // convenience getter for easy access to form fields
    public get f() {
        return this.form.controls;
    }

    public async ngOnInit() {
        // setup the form
        const formOptions: AbstractControlOptions = {validators: PasswordValidator};

        this.form = <FormGroup>this.formBuilder.group({
            email:      ['', [Validators.required, Validators.pattern('^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+$')]],
            username:   ['', Validators.required, Validators.minLength(6)],
            password:   ['', [Validators.required, Validators.minLength(8)]],
            confirm:    ['', Validators.required]
        }, formOptions);

        this.form.get('email').valueChanges.subscribe( () => {
            this.submitted = false;
        });

        this.form.get('username').valueChanges.subscribe( () => {
            this.submitted = false;
        });

        this.form.get('password').valueChanges.subscribe( () => {
            this.submitted = false;
        });

        this.form.get('confirm').valueChanges.subscribe( () => {
            this.submitted = false;
        });

    }

    public async onSubmit() {
        // prevent submission of invalid form
        if (this.form.invalid) {
            return;
        }
        this.signup_error = false;
        this.server_error = false;
        this.submitted = true;
        this.loading = true;

        try {
            const data = await this.auth.signUp(this.f.username.value, this.f.email.value, this.f.password.value);
            // success: we should be able to authenticate
            this.router.navigate(['/signup/sent'],  { state: { email: this.f.email.value, username: this.f.username.value, password: this.f.password.value, message_id: data.message_id} });
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
                    this.signup_error = true;
                }
            }
            // there was an error: stop loading indicator
            this.loading = false;
        }
    }

    public onSignin() {
        this.router.navigate(['/signin']);
    }

}

const PasswordValidator: ValidatorFn = (form: AbstractControl) => {
    const password = form.get('password').value;
    const confirm = form.get('confirm').value;
    if(password !== null && confirm !== null && password != confirm) {
        form.get('confirm').setErrors({mismatch: true});
        return <ValidationErrors> {mismatch: true};
    }
    form.get('confirm').setErrors(null);
    return null;
}