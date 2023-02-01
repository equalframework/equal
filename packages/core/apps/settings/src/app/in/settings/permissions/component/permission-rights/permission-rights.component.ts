import { Component, Input, OnInit, Output, EventEmitter } from '@angular/core';


@Component({
  selector: 'app-permission-rights',
  templateUrl: './permission-rights.component.html',
  styleUrls: ['./permission-rights.component.scss']
})
export class PermissionRightsComponent implements OnInit {

  @Input() rights: number;
  @Output() change = new EventEmitter;


  //checkboxes
  public permissionRights: any = {
    create: {
      completed: false, value: 1
    },
    read: {
      completed: false, value: 2
    },
    write: {
      completed: false, value: 4
    },
    delete: {
      completed: false, value: 8
    },
    manage: {
      completed: false, value: 16
    }
  };
  public keys = Object.keys(this.permissionRights);

  public allComplete: boolean = false;

  constructor() { }

  public ngOnChanges() {
    if (this.rights & this.permissionRights.create.value) this.permissionRights.create.completed = true;
    if (this.rights & this.permissionRights.read.value) this.permissionRights.read.completed = true;
    if (this.rights & this.permissionRights.write.value) this.permissionRights.write.completed = true;
    if (this.rights & this.permissionRights.delete.value) this.permissionRights.delete.completed = true;
    if (this.rights & this.permissionRights.manage.value) this.permissionRights.manage.completed = true;
  }

  public ngOnInit() {
  }

  updateAllComplete() {
    this.permissionRights != null && this.keys.every(right => this.permissionRights[right].completed);
    //find out which rights are attributed
    this.rights = 0;
    this.keys.forEach((right) => (
      this.permissionRights[right].completed == true ? this.rights += this.permissionRights[right].value : '')
    );
    //send the rights to the parent
    this.change.emit(this.rights);
  }

  //every change
  someComplete(): boolean {
    if (this.permissionRights == null) {
      return false;
    }
    return this.keys.filter(right => this.permissionRights[right].completed).length > 0 && this.rights != 31;
  }
  
  setAll(completed: boolean) {
    if (this.permissionRights == null) {
      return;
    }
    this.keys.forEach(right => (this.permissionRights[right].completed = completed));
    this.rights = 0;
    this.keys.forEach((right) => {
      (
        this.permissionRights[right].completed == true ? this.rights += this.permissionRights[right].value : '')
    }
    );
    this.change.emit(this.rights);
  }

}
