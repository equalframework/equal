import { Component, OnInit } from '@angular/core';
import { ApiService, EnvService } from 'sb-shared-lib';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { ActivatedRoute } from '@angular/router';
import { FormControl, FormGroup } from '@angular/forms';
import { startWith, map } from 'rxjs/operators';
import { Observable } from 'rxjs';
import { Location } from '@angular/common';
import { MatSnackBar } from '@angular/material/snack-bar';
@Component({
  selector: 'app-permissions',
  templateUrl: './permissions.component.html',
  styleUrls: ['./permissions.component.scss']
})
export class PermissionsComponent implements OnInit {


  // Select User/group
  public isChecked: any = false;
  public filteredOptionsGroups: Observable<any[]>;
  public filteredOptionsUsers: Observable<any[]>;
  public save = false;
  public rights: number;
  public className: any;
  public newClassName: string;
  public newUser: any;
  public newGroup: any;
  public controlGroup = new FormControl();
  public controlUser = new FormControl();
  public users: any;
  public groups: any;
  public thePermission: any;
  public permission_id: any;


  constructor(private api: ApiService,
    private env: EnvService,
    private route: ActivatedRoute,
    private _location: Location,
    private snackBar: MatSnackBar) { }

  async ngOnInit() {

    this.permission_id = this.route.snapshot.url[this.route.snapshot.url.length - 1].path;

    const environment: any = await this.env.getEnv();
    this.thePermission = await this.api.collect(
      'core\\Permission',
      ['id', '=', this.permission_id],
      ['class_name', 'user_id', 'group_id', 'rights'],
      'id', 'asc', 0, 100,
      environment.locale
    );
    this.rights = this.thePermission[0].rights;
    this.className = this.thePermission[0].class_name;


    this.users = await this.api.collect(
      'core\\User',
      [],
      ['login'],
      'id', 'asc', 0, 1000,
      environment.locale
    );

    this.groups = await this.api.collect(
      'core\\Group',
      [],
      [],
      'id', 'asc', 0, 100,
      environment.locale
    );

    this.filteredOptionsUsers = this.controlUser.valueChanges.pipe(
      startWith(''),

      map(value => this._filter(value)),
    );


    this.filteredOptionsGroups = this.controlGroup.valueChanges.pipe(
      startWith(''),
      map(valeur => this._filterGroups(valeur))
    );

    this.users.forEach((element: any) => {
      if (element.id == this.thePermission[0].user_id) {
        this.controlUser.setValue(element.login);
        this.isChecked = true;
      }
    })


    this.groups.forEach((element: any) => {
      if (element.id == this.thePermission[0].group_id) {
        this.controlGroup.setValue(element.name);
        this.isChecked = false;
      }
    })
  }


  onRightsChange(rights: number) {
    this.rights = rights;
    this.save = true;
  }
  onClassNameChange(className: any) {
    this.newClassName = className;
    this.save = true;
  }

  public onUserChange() {
    this.save = true;
    this.controlGroup.reset();
  }

  public onGroupChange() {
    this.save = true;
    this.controlUser.reset();
  }


  // Filter for autocomplete yallah
  private _filter(value: string): string[] {
    const filterValue = value;
    return this.users.filter((option: any) => option.login.includes(filterValue));

  }

  private _filterGroups(value: string): string[] {
    const filterValue = value;
    return this.groups.filter((option: any) => option.name.includes(filterValue));
  }


  public async onSubmit() {
    this.users.forEach((element: any) => {
      if (element.login == this.controlUser.value) {
        this.newUser = element.id;
      }
    })

    this.groups.forEach((element: any) => {
      if (element.name == this.controlGroup.value) {
        this.newGroup = element.id;
      }
    })

    if (!this.newUser) this.newUser = 0;
    if (!this.newGroup) this.newGroup = 0;
    this.api.update('core\\Permission', [this.permission_id], { user_id: this.newUser, group_id: this.newGroup, class_name: this.className, rights: this.rights }, true);
    this._location.back();

    // let snackBarRef = this.snackBar.open('Changes saved', 'Undo', {
    //   duration: 3000,
    //   verticalPosition: 'bottom',
    //   horizontalPosition: 'start',
    // });
    // let undo = "";
    // snackBarRef.onAction().subscribe(() => {
    //   undo = "undo";
    // })

    // snackBarRef.afterDismissed().subscribe(() => {
    //   if (undo != "undo") {
       
    //   }
    // });
  };
}
