import { Component, OnInit } from '@angular/core';
import { ApiService } from 'sb-shared-lib';
import { AuthService } from 'sb-shared-lib';

/* 
This is the component that is bootstrapped by app.module.ts
*/


@Component({
  selector: 'app-root',
  templateUrl: './app.root.component.html',
  styleUrls: ['./app.root.component.scss']  
})
export class AppRootComponent implements OnInit {

  public ready: boolean = false;

  constructor(private auth: AuthService, private api: ApiService) {}

  
  public async ngOnInit() {

    this.auth.getObservable().subscribe( (user:any) => {
      console.log('received user object', user);
      if(user.id > 0) {
        this.ready = true;
      }
      else {
        this.navigateToAuth();  
      }      
    });

    try {
      await this.auth.authenticate();
    }
    catch(error:any) {
      this.navigateToAuth();
    }    

  }

  public navigateToAuth() {
    window.location.href = '/auth';
  }

}