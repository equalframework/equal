import { Component, OnInit, Input  } from '@angular/core';

import { Router } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

import { AuthService } from 'sb-shared-lib';


@Component({
  selector: 'auth-recover',
  templateUrl: 'auth.recover.component.html',
  styleUrls: ['auth.recover.component.scss']
})
export class AuthRecoverComponent implements OnInit {
    public form: FormGroup;
    public loading = false;
    public submitted = false;


    constructor(
        private formBuilder: FormBuilder,
        private router: Router,
        private auth: AuthService
    ) {
        this.form = new FormGroup({});
    }

    // convenience getter for easy access to form fields
    public get f() {
        return this.form.controls;
    }

    ngOnInit() {
        this.form = <FormGroup>this.formBuilder.group({
            email: ['', [Validators.required, Validators.pattern('^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+$')]]
        });

        this.form.get('email').valueChanges.subscribe( () => {
            this.submitted = false;
        });

    }

    public async onSubmit() {
        console.log('onSubmit');
        // prevent sumit for invalid form
        if (this.form.invalid) {
            return;
        }

        this.submitted = true;
        this.loading = true;

        try {
            console.log('sending request');
            const data = await this.auth.passRecover(this.f.email.value);
            this.loading = false;
        }
        catch(response:any) {
            if(response.hasOwnProperty('error')) {
                let code = Object.keys(response.error['errors'])[0];
                let msg = response.error['errors'][code];
                console.log({
                code: code,
                message: msg
                });
            }
            this.loading = false;
        }
    }

    public onSignin() {
        this.router.navigate(['/signin']);
    }
}