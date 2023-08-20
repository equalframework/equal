import { Component, Input, OnInit, OnChanges, SimpleChanges } from '@angular/core';
import { MatSnackBar } from '@angular/material/snack-bar';
import { MatSlideToggleChange } from "@angular/material/slide-toggle";
import { SettingService } from 'src/app/settingService';
import { FormControl } from '@angular/forms';
import { pairwise, startWith } from 'rxjs/operators';
@Component({
  selector: 'app-widget-toggle',
  templateUrl: './widget-toggle.component.html',
  styleUrls: ['./widget-toggle.component.scss']
})
export class WidgetToggleComponent implements OnInit {

  constructor(private service: SettingService) { }

  @Input() setting: any;

  public settingValue: any;
  public settingName: any;

  public previousValue: any;
  public focusState: any;
  public control = new FormControl();

  ngOnInit(): void {

    if (this.setting.setting_values_ids[0].value == 0) {
      this.settingValue = false;
    } 
    else {
      this.settingValue = true;
    }
    this.settingName = this.setting.code;

    this.control.valueChanges.pipe(
      startWith(this.settingValue),
      pairwise()
    ).subscribe(
      ([old, value]) => {
        this.settingValue = value;
        this.previousValue = old;
      }
    )
    this.control.setValue(this.settingValue);
  }

  public async valueChange(event: MatSlideToggleChange) {
    console.log('change');
    this.service.toQueue(this.setting.setting_values_ids[0].id, { newValue: this.settingValue, oldValue: this.previousValue }).subscribe((action) => {
      if (action == 'undo') {
        this.control.setValue(this.previousValue);
      }
    });

  }
}
