import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PermissionClassNameComponent } from './permission-class-name.component';

describe('PermissionClassNameComponent', () => {
  let component: PermissionClassNameComponent;
  let fixture: ComponentFixture<PermissionClassNameComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ PermissionClassNameComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(PermissionClassNameComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
