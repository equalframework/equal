import { Component, OnInit, Input  } from '@angular/core';
import { Router } from '@angular/router';
import { ValidatorFn, FormBuilder, FormGroup, Validators, AbstractControl, ValidationErrors, AbstractControlOptions } from '@angular/forms';

import { AuthService } from 'sb-shared-lib';



@Component({
    selector: 'auth-signup-sent',
    templateUrl: 'auth.signup.sent.component.html',
    styleUrls: ['auth.signup.sent.component.scss']
})
export class AuthSignupSentComponent implements OnInit {
    public loading:boolean = false;

    public state: any = {};

    constructor(
        private auth: AuthService,
        private router: Router
    ) {

        this.state = this.router.getCurrentNavigation().extras.state as {
            username: string,
            password: string,
            message_id: number
        };
    }


    public async ngOnInit() {

    }

    public async onResend() {
        // prevent submit for invalid form

        try {
            const data = await this.auth.signUp(this.state.username, this.state.password, this.state.message_id);
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

                }
                else {

                }
            }
            // there was an error: stop loading indicator
            this.loading = false;
        }
    }


}